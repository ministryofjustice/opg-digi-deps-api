<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    /**
     * Add client
     * 
     * @Route("/upsert")
     * @Method({"POST", "PUT"})
     */
    public function upsertAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $data = $this->deserializeBodyContent($request);
      
        if ($request->getMethod() == "POST"){
            $userId = $data['users'][0];
            if (!in_array($this->getUser()->getId(), [$userId])) {
                throw $this->createAccessDeniedException('User not allowed');
            }
            $user = $this->findEntityBy('User', $userId, "User with id: {$userId}  does not exist");
            $client = new EntityDir\Client();
            $client->addUser($user);
            
        } else {
            $client = $this->findEntityBy('Client', $data['id'], 'Client not found');
            if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }
        
        $this->hydrateEntityWithArrayData($client, $data, [
            'firstname' => 'setFirstname', 
            'lastname' => 'setLastname', 
            'case_number' => 'setCaseNumber', 
            'allowed_court_order_types' => 'setAllowedCourtOrderTypes', 
            'address' => 'setAddress', 
            'address2' => 'setAddress2', 
            'postcode' => 'setPostcode', 
            'country' => 'setCountry', 
            'county' => 'setCounty', 
            'phone' => 'setPhone', 
        ]);
        $client->setCourtDate(new \DateTime($data['court_date']));
        
        $this->getEntityManager()->persist($client);
        $this->getEntityManager()->flush();
        
        return ['id' => $client->getId() ];
    }
    
    /**
     * @param integer $userId
     * 
     * @return EntityDir\Client
     */
    private function add($userId)
    {
       $user = $this->findEntityBy('User', $userId, "User with id: {$userId}  does not exist");
        
       $client = new EntityDir\Client();
       $client->addUser($user);
        
       return $client;
    }
    

    /**
     * @Route("/{id}", name="client_find_by_id" )
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function findByIdAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array)$request->query->get('groups'));
        }
        
        $client = $this->findEntityBy('Client', $id);
        
        if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }
        
        return $client;
    }
    
}
