<?php

namespace AppBundle\v2\Assembler;

interface AssemblerInterface
{
    /**
     * @param array $dto
     * @return mixed
     */
    public function assembleFromArray(array $dto);
}
