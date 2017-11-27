<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN, EntityDir\User::ROLE_AD, EntityDir\User::ROLE_PA, EntityDir\User::ROLE_PA_ADMIN]);

        $data = $this->deserializeBodyContent($request, [
            'role_name' => 'notEmpty',
            'email' => 'notEmpty',
            'firstname' => 'mustExist',
            'lastname' => 'mustExist',
        ]);

        $loggedInUser = $this->getUser();
        $user = new EntityDir\User();

        $user = $this->populateUser($user, $data);

        $userService = $this->get('opg_digideps.user_service');
        $userService->addUser($loggedInUser, $user, $data);

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user'];
        $this->setJmsSerialiserGroups($groups);

        return $user;
    }

    /**
     * @Route("/casrec")
     * @Method({"POST"})
     */
    public function addCasrecUser(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN]);

        $data = $this->deserializeBodyContent($request, [
            'role_name' => 'notEmpty',
            'email' => 'notEmpty',
            'address_postcode' => 'mustExist',
            'firstname' => 'mustExist',
            'lastname' => 'mustExist',
        ]);

        $loggedInUser = $this->getUser();
        $user = new EntityDir\User();

        $user = $this->populateUser($user, $data);

        $userService = $this->get('opg_digideps.user_service');
        $userService->addCasrecUser($loggedInUser, $user);

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user'];
        $this->setJmsSerialiserGroups($groups);

        return $user;
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user User */

        if ($this->getUser()->getId() != $user->getId()
            && !$this->isGranted(EntityDir\User::ROLE_ADMIN)
            && !$this->isGranted(EntityDir\User::ROLE_AD)
            && !$this->isGranted(EntityDir\User::ROLE_PA) //TODO check user is also part of the team
            && !$this->isGranted(EntityDir\User::ROLE_PA_ADMIN) //TODO check user is also part of the team
        ) {
            throw $this->createAccessDeniedException("Non-admin not authorised to change other user's data");
        }

        $originalUser = clone $user;

        $data = $this->deserializeBodyContent($request);

        $this->populateUser($user, $data);

        $loggedInUser = $this->getUser();

        $userService = $this->get('opg_digideps.user_service');

        // If Editing PA user
        if ($loggedInUser->isPaAdministrator()) {
            $userService->editPaUser($originalUser, $user);
            $this->updateTeamAddresses($user, $data);
        } else {
            $userService->editUser($originalUser, $user);
        };

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

        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user User */
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

        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user EntityDir\User */
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

        return $user->getId();
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
            $user = $this->getRepository(EntityDir\User::class)->findOneBy(['email' => $filter]);
            if (!$user) {
                throw new \RuntimeException('User not found', 404);
            }
        } elseif ($what == 'case_number') {
            $client = $this->getRepository(EntityDir\Client::class)->findOneBy(['caseNumber' => $filter]);
            if (!$client) {
                throw new \RuntimeException('Client not found', 404);
            }
            if (empty($client->getUsers())) {
                throw new \RuntimeException('Client has not users', 404);
            }
            $user = $client->getUsers()[0];
        } elseif ($what == 'user_id') {
            $user = $this->getRepository(EntityDir\User::class)->find($filter);
            if (!$user) {
                throw new \RuntimeException('User not found', 419);
            }
        } else {
            throw new \RuntimeException('wrong query', 500);
        }

        $requestedUserIsLogged = $this->getUser()->getId() == $user->getId();

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user'];
        $this->setJmsSerialiserGroups($groups);

        // only allow admins to access any user, otherwise the user can only see himself
        if (!$this->isGranted(EntityDir\User::ROLE_ADMIN)
            && !$this->isGranted(EntityDir\User::ROLE_AD)
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
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        $user = $this->findEntityBy(EntityDir\User::class, $id);  /* @var $user EntityDir\User */

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
     * @Route("/get-all", defaults={"order_by" = "firstname", "sort_order" = "ASC"})
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN, EntityDir\User::ROLE_AD]);

        $order_by  = $request->get('order_by', 'id');
        $sort_order  = strtoupper($request->get('sort_order', 'DESC'));
        $limit  = $request->get('limit', 50);
        $offset  = $request->get('offset', 0);
        $roleName  = $request->get('role_name');
        $adManaged  = $request->get('ad_managed');
        $odrEnabled  = $request->get('odr_enabled');
        $q  = $request->get('q');

        $qb = $this->getRepository(EntityDir\User::class)->createQueryBuilder('u');
        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);
        $qb->orderBy('u.' . $order_by, $sort_order);

        if ($roleName) {
            $qb->andWhere('u.roleName = :role');
            $qb->setParameter('role', $roleName);
        }

        if ($adManaged) {
            $qb->andWhere('u.adManaged = true');
        }

        if ($odrEnabled) {
            $qb->andWhere('u.odrEnabled = true');
        }

        if ($q) {
            if (preg_match('/^[0-9t]{8}$/i', $q)) { // case number
                $qb->leftJoin('u.clients', 'c');
                $qb->andWhere('lower(c.caseNumber) = :cn');
                $qb->setParameter('cn', strtolower($q));
            } else { // mail or first/lastname or user or client
                $qb->leftJoin('u.clients', 'c');
                $qb->andWhere('lower(u.email) LIKE :qLike OR lower(u.firstname) LIKE :qLike OR lower(u.lastname) LIKE :qLike OR lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike ');
                $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            }
        }

        $this->setJmsSerialiserGroups(['user']);

        $users = $qb->getQuery()->getResult(); /* @var $reports Report[] */

        return $users;
        //$this->getRepository(EntityDir\User::class)->findBy($criteria, [$order_by => $sort_order], $limit, $offset);
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
        $user = $this->findEntityBy(EntityDir\User::class, ['email' => $email]);

        if ($user->getRoleName() == EntityDir\User::ROLE_LAY_DEPUTY && $type == 'pass-reset' && !$this->getAuthService()->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new \RuntimeException('Admin emails not accepted.', 403);
        }
        
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

        $user = $this->findEntityBy(EntityDir\User::class, ['registrationToken' => $token], 'User not found'); /* @var $user User */

        if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new \RuntimeException($user->getRoleName() . ' user role not allowed from this client.', 403);
        }

        // `user-login` contains number of clients and reports, needed to properly redirect the user to the right page after activation
        $this->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    /**
     * @Route("/agree-terms-use/{token}")
     * @Method({"PUT"})
     */
    public function agreeTermsUSe(Request $request, $token)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        $user = $this->findEntityBy(EntityDir\User::class, ['registrationToken' => $token], 'User not found'); /* @var $user EntityDir\User */

        if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new \RuntimeException($user->getRoleName() . ' user role not allowed from this client.', 403);
        }

        $user->setAgreeTermsUse(true);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush($user);

        return $user->getId();
    }

    /**
     * call setters on User when $data contains values.
     *
     * @param EntityDir\User  $user
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
            'odr_enabled' => 'setOdrEnabled',
            'ad_managed' => 'setAdManaged',
            'role_name' => 'setRoleName',
            'job_title' => 'setJobTitle',
            'co_deputy_client_confirmed' => 'setCoDeputyClientConfirmed',
        ]);

        if (array_key_exists('last_logged_in', $data)) {
            $user->setLastLoggedIn(new \DateTime($data['last_logged_in']));
        }

        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }

        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new \DateTime($data['token_date']));
        }

        if (!empty($data['role_name'])) {
            $roleToSet = $data['role_name'];
            $user->setRoleName($roleToSet);
        }

        return $user;
    }

    /**
     * Update both the team and other teammembers to have same address
     *
     * @param EntityDir\User $user
     * @param array $data
     */
    private function updateTeamAddresses(EntityDir\User $user, array $data)
    {
        if ( !empty($data['address1'])
            && !empty($data['address_postcode'])
            && !empty($data['address_country'])
        ) {
            //set the team address to the same
            $team = $user->getTeams()->first();
            $this->hydrateEntityWithArrayData($team, $data, [
                'address1' => 'setAddress1',
                'address2' => 'setAddress2',
                'address3' => 'setAddress3',
                'address_postcode' => 'setAddressPostcode',
                'address_country' => 'setAddressCountry'
            ]);
            $this->getEntityManager()->persist($team);

            //and the other team PAs addresses
            foreach ($team->getMembers() as $teamMember) {
                $this->hydrateEntityWithArrayData($teamMember, $data, [
                    'address1' => 'setAddress1',
                    'address2' => 'setAddress2',
                    'address3' => 'setAddress3',
                    'address_postcode' => 'setAddressPostcode',
                    'address_country' => 'setAddressCountry'
                ]);
                $this->getEntityManager()->persist($teamMember);
                $this->getEntityManager()->flush();
            }
        }
    }

    /**
     * @Route("/{id}/team", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getTeamByUserId(Request $request, $id)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_PA, EntityDir\User::ROLE_PA_ADMIN, EntityDir\User::ROLE_PA_TEAM_MEMBER]);

        $user = $this->getRepository(EntityDir\User::class)->find($id);
        if (!$user) {
            throw new \RuntimeException('User not found', 419);
        }

        if ($user->getTeams()->first() !== $this->getUser()->getTeams()->first()) {
            throw $this->createAccessDeniedException('User not part of the same team');
        }

        $this->setJmsSerialiserGroups(['team', 'team-users', 'user']);

        return $user->getTeams()->first();
    }
}
