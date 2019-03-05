<?php

namespace AppBundle\v2\DTO;

class DeputyDto implements \JsonSerializable
{
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $roleName;
    private $postcode;
    private $ndrEnabled;
    private $clients;

    /**
     * @param $id
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $roleName
     * @param $postcode
     * @param $ndrEnabled
     */
    public function __construct($id, $firstName, $lastName, $email, $roleName, $postcode, $ndrEnabled)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->roleName = $roleName;
        $this->postcode = $postcode;
        $this->ndrEnabled = $ndrEnabled;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'email' => $this->email,
            'role_name' => $this->roleName,
            'address_postcode' => $this->postcode,
            'ndr_enabled' => $this->ndrEnabled,
            'clients' => $this->serializeClients()
        ];
    }

    /**
     * @return array
     */
    private function serializeClients()
    {
        $serializedClients = [];

        foreach ($this->clients as $client) {
            $serializedClients[] = $client->jsonSerialize();
        }

        return $serializedClients;
    }

    /**
     * @param mixed $clients
     */
    public function setClients($clients)
    {
        $this->clients = $clients;
    }
}

