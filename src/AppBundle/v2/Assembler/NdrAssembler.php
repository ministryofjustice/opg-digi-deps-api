<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\NdrDto;

class NdrAssembler
{
    /**
     * @param array $data
     * @return NdrDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new NdrDto();

        if (isset($data['id'])) {
            $dto->setId($data['id']);
        }

        return $dto;
    }
}
