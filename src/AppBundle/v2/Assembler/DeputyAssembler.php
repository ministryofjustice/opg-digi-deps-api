<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DeputyDto;

class DeputyAssembler implements AssemblerInterface
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
        $deputy = $data[0];
        $dto = new DeputyDto(
            $deputy['u_id'],
            $deputy['u_firstname'],
            $deputy['u_lastname'],
            $deputy['u_email'],
            $deputy['u_rolename'],
            $deputy['u_postcode'],
            $deputy['u_ndrenabled']
        );

        $clients = [];
        foreach ($data as $client) {
            $clients[] = $this->clientDtoAssembler->assembleFromArray($client);
        }

        $dto->setClients($clients);

        return $dto;
    }
}
