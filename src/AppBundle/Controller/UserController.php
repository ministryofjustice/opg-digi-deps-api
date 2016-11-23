<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/user")
 */
class UserController extends RestController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function add(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $data = $this->deserializeBodyContent($request, [
            'role_id' => 'notEmpty',
            'email' => 'notEmpty',
            'firstname' => 'mustExist',
            'lastname' => 'mustExist',
        ]);

        $user = new EntityDir\User();

        $this->populateUser($user, $data);
        $user->setRegistrationDate(new \DateTime());

        /*
         * Not sure we need this check, email field is set as unique in the db. May be try catch the unique value exception
         * thrown when persist flush ?
         */
        if ($user->getEmail() && $this->getRepository('User')->findOneBy(['email' => $user->getEmail()])) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.");
        }

        // send activation email
        $user->recreateRegistrationToken();

        $this->persistAndFlush($user);

        return ['id' => $user->getId()];
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy('User', $id, 'User not found'); /* @var $user User */

        if ($this->getUser()->getId() != $user->getId() && !$this->isGranted(EntityDir\Role::ADMIN)) {
            throw $this->createAccessDeniedException("Non-admin not authorised to change other user's data");
        }

        $data = $this->deserializeBodyContent($request);

        $this->populateUser($user, $data);

        $this->getEntityManager()->flush($user);

        return ['id' => $user->getId()];
    }

    /**
     * //TODO take user from logged user.
     * 
     * @Route("/{id}/is-password-correct")
     * @Method({"POST"})
     */
    public function isPasswordCorrect(Request $request, $id)
    {
        // for both ADMIN and DEPUTY

        $user = $this->findEntityBy('User', $id, 'User not found'); /* @var $user User */
        if ($this->getUser()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("Not authorised to check other user's password");
        }

        $data = $this->deserializeBodyContent($request, [
            'password' => 'notEmpty',
        ]);

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);

        $oldPassword = $encoder->encodePassword($data['password'], $user->getSalt());
        if ($oldPassword == $user->getPassword()) {
            return true;
        }

        return false;
    }

    /**
     * change password, activate user and send remind email.
     *
     * @Route("/{id}/set-password")
     * @Method({"PUT"})
     */
    public function changePassword(Request $request, $id)
    {
        //for both admin and users

        $user = $this->findEntityBy('User', $id, 'User not found'); /* @var $user EntityDir\User */
        if ($this->getUser()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("Not authorised to change other user's data");
        }

        $data = $this->deserializeBodyContent($request, [
            'password_plain' => 'notEmpty',
        ]);

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $newPassword = $encoder->encodePassword($data['password_plain'], $user->getSalt());

        $user->setPassword($newPassword);

        if (array_key_exists('set_active', $data)) {
            $user->setActive($data['set_active']);
        }

        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        return $this->getOneByFilter($request, 'user_id', $id);
    }

    /**
     * @Route("/get-one-by/{what}/{filter}", requirements={
     *   "what" = "(user_id|email|case_number)"
     * })
     * @Method({"GET"})
     */
    public function getOneByFilter(Request $request, $what, $filter)
    {
        if ($what == 'email') {
            $user = $this->getRepository('User')->findOneBy(['email' => $filter]);
            if (!$user) {
                throw new \RuntimeException('User not found', 404);
            }
        } elseif ($what == 'case_number') {
            $client = $this->getRepository('Client')->findOneBy(['caseNumber' => $filter]);
            if (!$client) {
                throw new \RuntimeException('Client not found', 404);
            }
            if (empty($client->getUsers())) {
                throw new \RuntimeException('Client has not users', 404);
            }
            $user = $client->getUsers()[0];
        } elseif ($what == 'user_id') {
            $user = $this->getRepository('User')->find($filter);
            if (!$user) {
                throw new \RuntimeException('User not found', 419);
            }
        } else {
            throw new \RuntimeException('wrong query', 500);
        }

        $requestedUserIsLogged = $this->getUser()->getId() == $user->getId();

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user', 'role'];
        $this->setJmsSerialiserGroups($groups);

        // only allow admins to access any user, otherwise the user can only see himself
        if (!$this->isGranted(EntityDir\Role::ADMIN)
            && !$this->isGranted(EntityDir\Role::AD)
            && !$requestedUserIsLogged) {
            throw $this->createAccessDeniedException("Not authorised to see other user's data");
        }

        return $user;
    }

    /**
     * Delete user with clients.
     *
     * @Route("/{id}")
     * @Method({"DELETE"})
     * 
     * @param int $id
     */
    public function delete($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $user = $this->findEntityBy('User', $id);  /* @var $user EntityDir\User */

        // delete clients
        foreach ($user->getClients() as $client) {
            if (count($client->getReports()) > 0) {
                throw new \RuntimeException('cannot delete user with reports');
            }
            $this->getEntityManager()->remove($client);
        }

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @Route("/count")
     * @Method({"GET"})
     */
    public function userCount()
    {
        $this->denyAccessUnlessGranted([EntityDir\Role::ADMIN, EntityDir\Role::AD]);

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(user.id)');
        $qb->from('AppBundle\Entity\User', 'user');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * @Route("/get-all/{order_by}/{sort_order}/{limit}/{offset}", defaults={"order_by" = "firstname", "sort_order" = "ASC"})
     * @Method({"GET"})
     */
    public function getAll($order_by, $sort_order, $limit, $offset)
    {
        $this->denyAccessUnlessGranted([EntityDir\Role::ADMIN, EntityDir\Role::AD]);

        return $this->getRepository('User')->findBy([], [$order_by => $sort_order], $limit, $offset);
    }

    /**
     * Requires client secret.
     * 
     * @Route("/recreate-token/{email}/{type}", defaults={"email": "none"}, requirements={
     *   "type" = "(activate|pass-reset)"
     * })
     * @Method({"PUT"})
     */
    public function recreateToken(Request $request, $email, $type)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }
        $user = $this->findEntityBy('User', ['email' => $email]);

        //TODO consider an AD key from admin area
        /*$isAd = $this->getUser()->getRole();
        if (!$isAd && !$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new \RuntimeException($user->getRole()->getRole().' user role not allowed from this client.', 403);
        }*/

        $user->recreateRegistrationToken();

        $this->getEntityManager()->flush($user);

        $this->setJmsSerialiserGroups(['user']);

        return $user;
    }

    /**
     * @Route("/get-by-token/{token}")
     * @Method({"GET"})
     */
    public function getByToken(Request $request, $token)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        $user = $this->findEntityBy('User', ['registrationToken' => $token], 'User not found'); /* @var $user User */

        if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new \RuntimeException($user->getRole()->getRole().' user role not allowed from this client.', 403);
        }

        return $user;
    }

    /**
     * call setters on User when $data contains values.
     * 
     * @param User  $user
     * @param array $data
     */
    private function populateUser(EntityDir\User $user, array $data)
    {
        // Cannot easily(*) use JSM deserialising with already constructed objects. 
        // Also. It'd be possible to differentiate when a NULL value is intentional or not
        // (*) see options here https://github.com/schmittjoh/serializer/issues/79
        // http://jmsyst.com/libs/serializer/master/event_system

        $this->hydrateEntityWithArrayData($user, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'address1' => 'setAddress1',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country' => 'setAddressCountry',
            'phone_alternative' => 'setPhoneAlternative',
            'phone_main' => 'setPhoneMain',
        ]);

        if (array_key_exists('role_id', $data)) {
            $role = $this->findEntityBy('Role', $data['role_id'], 'Role not found');
            $user->setRole($role);
        }

        if (array_key_exists('last_logged_in', $data)) {
            $user->setLastLoggedIn(new \DateTime($data['last_logged_in']));
        }

        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }

        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new \DateTime($data['token_date']));
        }

        if (array_key_exists('odr_enabled', $data)) {
            $user->setOdrEnabled($data['odr_enabled']);
        }
    }
}
