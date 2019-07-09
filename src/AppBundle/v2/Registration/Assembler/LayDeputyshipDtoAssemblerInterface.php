<?php

namespace AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\DTO\LayDeputyshipDto;

interface LayDeputyshipDtoAssemblerInterface
{
    /**
     * @param array $data
     * @return LayDeputyshipDto
     */
    public function assembleFromArray(array $data);

    /**
     * @param array $data
     * @return bool
     */
    public function canAssemble(array $data);
}
