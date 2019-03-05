<?php

namespace AppBundle\v2\DTO;

class ClientDto implements \JsonSerializable
{
    private $id;
    private $caseNumber;
    private $firstName;
    private $lastName;
    private $email;
    private $reportCount;
    private $ndrId;

    /**
     * @param $id
     * @param $caseNumber
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $reportCount
     * @param $ndrId
     */
    public function __construct($id, $caseNumber, $firstName, $lastName, $email, $reportCount, $ndrId)
    {
        $this->id = $id;
        $this->caseNumber = $caseNumber;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->reportCount = $reportCount;
        $this->ndrId = $ndrId;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'case_number' => $this->caseNumber,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'email' => $this->email,
            'total_report_count' => $this->reportCount,
            'ndrId' => $this->ndrId
        ];
    }
}
