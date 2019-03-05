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
     * @param mixed $id
     * @return DeputyDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $firstName
     * @return DeputyDto
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param mixed $lastName
     * @return DeputyDto
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param mixed $email
     * @return DeputyDto
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param mixed $roleName
     * @return DeputyDto
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;
        return $this;
    }

    /**
     * @param mixed $postcode
     * @return DeputyDto
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @param mixed $ndrEnabled
     * @return DeputyDto
     */
    public function setNdrEnabled($ndrEnabled)
    {
        $this->ndrEnabled = $ndrEnabled;
        return $this;
    }

    /**
     * @param mixed $clients
     * @return DeputyDto
     */
    public function setClients($clients)
    {
        $this->clients = $clients;
        return $this;
    }
}

