<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\ClientDto;

class ClientAssembler implements AssemblerInterface
{
    /**
     * @param array $data
     * @return ClientDto
     */
    public function assembleFromArray(array $data)
    {
        return new ClientDto(
            $data['c_id'],
            $data['c_casenumber'],
            $data['c_firstname'],
            $data['c_lastname'],
            $data['c_email'],
            $data['c_reportcount'],
            $data['c_ndrid']
        );
    }
}
