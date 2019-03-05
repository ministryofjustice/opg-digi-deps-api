<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DeputyDto;

class DeputyAssembler
{
    /** @var ClientAssembler  */
    private $clientDtoAssembler;

    /**
     * @param ClientAssembler $clientDtoAssembler
     */
    public function __construct(ClientAssembler $clientDtoAssembler)
    {
        $this->clientDtoAssembler = $clientDtoAssembler;
    }

    /**
     * @param array $data
     * @return DeputyDto
     */
    public function assembleFromArray(array $data)
    {
        $this->throwExceptionIfMissingRequiredData($data);

        $deputy = $data;
        $dto = new DeputyDto(
            $deputy['id'],
            $deputy['firstname'],
            $deputy['lastname'],
            $deputy['email'],
            $deputy['role_name'],
            $deputy['address_postcode'],
            $deputy['odr_enabled']
        );

        $clients = [];
        foreach ($data['clients'] as $client) {
            $clients[] = $this->clientDtoAssembler->assembleFromArray($client);
        }

        $dto->setClients($clients);

        return $dto;
    }

    /**
     * @param array $data
     */
    private function throwExceptionIfMissingRequiredData(array $data)
    {
        if (!$this->dataIsValid($data)) {
            throw new \InvalidArgumentException(__CLASS__ . ': Missing all data required to build DTO');
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    private function dataIsValid(array $data)
    {
        return
            array_key_exists('id', $data) &&
            array_key_exists('firstname', $data) &&
            array_key_exists('lastname', $data) &&
            array_key_exists('email', $data) &&
            array_key_exists('role_name', $data) &&
            array_key_exists('address_postcode', $data) &&
            array_key_exists('odr_enabled', $data) &&
            array_key_exists('clients', $data);
    }
}
