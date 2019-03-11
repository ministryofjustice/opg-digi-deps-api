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
        $dto = new DeputyDto();

        if (isset($data['id'])) {
            $dto->setId($data['id']);
        }

        if (isset($data['firstname'])) {
            $dto->setFirstName($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $dto->setLastName($data['lastname']);
        }

        if (isset($data['email'])) {
            $dto->setEmail($data['email']);
        }

        if (isset($data['roleName'])) {
            $dto->setRoleName($data['roleName']);
        }

        if (isset($data['addressPostcode'])) {
            $dto->setPostcode($data['addressPostcode']);
        }

        if (isset($data['ndrEnabled'])) {
            $dto->setNdrEnabled($data['ndrEnabled']);
        }

        if (isset($data['clients'])  && is_array($data['clients'])) {
            $dto->setClients($this->assembleDeputyClients($data['clients']));
        }

        return $dto;
    }

    /**
     * @param array $clients
     * @return array
     */
    private function assembleDeputyClients(array $clients)
    {
        $dtos = [];

        foreach ($clients as $client) {
            $dtos[] = $this->clientDtoAssembler->assembleFromArray($client);
        }

        return $dtos;
    }
}
