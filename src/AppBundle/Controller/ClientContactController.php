<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("")
 */
class ClientContactController extends RestController
{
    /**
     * @Route("/clients/{clientId}/clientcontacts", name="clientcontact_add")
     * @Method({"POST"})
     * @Security("has_role('ROLE_PA')")
     */
    public function add(Request $request, $clientId)
    {
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
     * @Security("has_role('ROLE_PA')")
     */
    public function update(Request $request, $id)
    {
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
     * @Route("/clientcontacts/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_PA')")
     */
    public function getOneById(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups')
            : ['clientcontact', 'clientcontact-client', 'client', 'client-users', 'current-report', 'report-id', 'user'];
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
     * @Security("has_role('ROLE_PA')")
     */
    public function delete($id)
    {
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
