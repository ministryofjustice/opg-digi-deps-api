<?php

namespace AppBundle\DTO;

class DeputyDto implements \JsonSerializable
{
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $roleName;
    private $postcode;
    private $ndrEnabled;

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
            'roleName' => $this->roleName,
            'postcode' => $this->postcode,
            'ndrEnabled' => $this->ndrEnabled,
        ];
    }
}

