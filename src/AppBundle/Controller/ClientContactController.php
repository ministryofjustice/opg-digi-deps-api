<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\Common\Util\Debug as doctrineDebug;

/**
 * @Route("")
 */
class ClientContactController extends RestController
{
    /**
     * @Route("/clients/{clientId}/clientcontacts", name="clientcontact_add")
     * @Method({"POST"})
     */
    public function add(Request $request, $clientId)
    {
        // checks
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        $client = $this->findEntityBy(EntityDir\Client::class, $clientId);
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        $data = $this->deserializeBodyContent($request);
        $clientContact = new EntityDir\ClientContact();
        $this->hydrateEntityWithArrayData($clientContact, $data, [
            'first_name'   => 'setFirstName',
            'last_name'    => 'setLastName',
            'job_title'    => 'setJobTitle',
            'phone'        => 'setPhone',
            'address1'     => 'setAddress1',
            'address2'     => 'setAddress2',
            'address3'     => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country'  => 'setAddressCountry',
            'email'        => 'setEmail',
            'org_name'     => 'setOrgName',
        ]);

        $clientContact->setClient($client);
        $clientContact->setCreatedBy($this->getUser());
        $this->persistAndFlush($clientContact);

        return ['id' => $clientContact->getId()];
    }


    /**
     * Update contact
     * Only the creator can update the note
     *
     * @Route("/clientcontacts/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        $clientContact = $this->findEntityBy(EntityDir\ClientContact::class, $id);
        $this->denyAccessIfClientDoesNotBelongToUser($clientContact->getClient());

        $data = $this->deserializeBodyContent($request);
        $this->hydrateEntityWithArrayData($clientContact, $data, [
            'first_name'   => 'setFirstName',
            'last_name'    => 'setLastName',
            'job_title'    => 'setJobTitle',
            'phone'        => 'setPhone',
            'address1'     => 'setAddress1',
            'address2'     => 'setAddress2',
            'address3'     => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country'  => 'setAddressCountry',
            'email'        => 'setEmail',
            'org_name'     => 'setOrgName',
        ]);
        $this->getEntityManager()->flush($clientContact);
        return $clientContact->getId();
    }


    /**
     * @Route("/clients/{clientId}/clientcontacts/{id}")
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups')
            : ['clientcontacts', 'user'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $clientContact = $this->findEntityBy(EntityDir\ClientContact::class, $id);
        $this->denyAccessIfClientDoesNotBelongToUser($clientContact->getClient());

        return $clientContact;
    }



    /**
     * Delete contact
     * Only the creator can delete the note
     *
     * @Route("/clientcontacts/{id}")
     * @Method({"DELETE"})
     */
    public function delete($id)
    {
        $this->get('logger')->debug('Deleting client contact ' . $id);

        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        try {
            $clientContact = $this->findEntityBy(EntityDir\ClientContact::class, $id);
            $this->denyAccessIfClientDoesNotBelongToUser($clientContact->getClient());

            $this->getEntityManager()->remove($clientContact);
            $this->getEntityManager()->flush($clientContact);
        } catch (\Exception $e) {
            $this->get('logger')->error('Failed to delete client contact ID: ' . $id . ' - ' . $e->getMessage());
        }

        return [];
    }
}
