<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    /**
     * Add/Edit a client.
     * When added, the current logged used will be added
     *
     * @Route("/upsert")
     * @Method({"POST", "PUT"})
     */
    public function upsertAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'POST') {
            $user = $this->getUser();
            $client = new EntityDir\Client();
            $client->addUser($user);
        } else {
            $client = $this->findEntityBy(EntityDir\Client::class, $data['id'], 'Client not found');
            if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        $this->hydrateEntityWithArrayData($client, $data, [
            'firstname'   => 'setFirstname',
            'lastname'    => 'setLastname',
            'address'     => 'setAddress',
            'address2'    => 'setAddress2',
            'postcode'    => 'setPostcode',
            'country'     => 'setCountry',
            'county'      => 'setCounty',
            'phone'       => 'setPhone',
            'email'       => 'setEmail',
        ]);

        if ($this->getUser()->isLayDeputy()) {
            $client->setCourtDate(new \DateTime($data['court_date']));
            $this->hydrateEntityWithArrayData($client, $data, [
                'case_number' => 'setCaseNumber',
            ]);
        }

        if (array_key_exists('date_of_birth', $data)) {
            $dob = $data['date_of_birth'] ? new \DateTime($data['date_of_birth']) : null;
            $client->setDateOfBirth($dob);
        }

        $this->persistAndFlush($client);

        //add ODR if not added yet
        // TODO move to listener or service
        if (!$client->getOdr()) {
            $odr = new EntityDir\Odr\Odr($client);
            $this->persistAndFlush($odr);
        }

        return ['id' => $client->getId()];
    }

    /**
     * @Route("/{id}", name="client_find_by_id", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function findByIdAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['client'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        return $client;
    }

    /**
     * @Route("/{id}/archive", name="client_archive", requirements={"id":"\d+"})
     * @Method({"PUT"})
     *
     * @param int $id
     */
    public function archiveAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_PA, EntityDir\User::ROLE_PA_ADMIN, EntityDir\User::ROLE_PA_TEAM_MEMBER]);
        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        foreach ($client->getUsers() as $user) {
            $client->removeUser($user);
        }
        $this->persistAndFlush($client);

        return [
            'id' => $client->getId()
        ];
    }

    /**
     * @Route("/get-all", defaults={"order_by" = "lastname", "sort_order" = "ASC"})
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN, EntityDir\User::ROLE_AD]);

        $this->setJmsSerialiserGroups(['client']);

        return $this->getRepository(EntityDir\Client::class)->searchClients(
            $request->get('q'),
            $request->get('order_by'),
            $request->get('sort_order'),
            $request->get('limit'),
            $request->get('offset')
        );

    }
}
