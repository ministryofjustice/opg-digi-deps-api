<?php

namespace AppBundle\v2\Registration\Assembler;

use AppBundle\Entity\CasRec;
use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;

class LayDeputyshipDtoAssembler
{
    use DtoPropertySetterTrait;

    /**
     * @param array $data
     * @return LayDeputyshipDto
     */
    public function assembleFromArray(array $data)
    {
        if (!$this->canAssemble($data)) {
            throw new \InvalidArgumentException('Cannot assemble LayDeputyshipDto: Missing expected data');
        }

        return
            (new LayDeputyshipDto())
            ->setCaseNumber(CasRec::normaliseCaseNumber($data['Case']))
            ->setClientSurname(CasRec::normaliseSurname($data['Surname']))
            ->setDeputyNumber(CasRec::normaliseDeputyNo($data['Deputy No']))
            ->setDeputySurname(CasRec::normaliseSurname($data['Dep Surname']))
            ->setDeputyPostcode(CasRec::normaliseSurname($data['Dep Postcode']))
            ->setTypeOfReport($data['Typeofrep'])
            ->setCorref($data['Corref'])
            ->setIsNdrEnabled($this->determineNdrStatus($data['NDR']));
    }

    /**
     * @param array $data
     * @return bool
     */
    private function canAssemble(array $data): bool
    {
        return
            array_key_exists('Case', $data) &&
            array_key_exists('Surname', $data) &&
            array_key_exists('Deputy No', $data) &&
            array_key_exists('Dep Surname', $data) &&
            array_key_exists('Dep Postcode', $data) &&
            array_key_exists('Typeofrep', $data) &&
            array_key_exists('Corref', $data) &&
            array_key_exists('NDR', $data);
    }

    /**
     * @param $value
     * @return bool
     */
    private function determineNdrStatus($value): bool
    {
        return ($value === 1 || $value === 'Y') ? true : false;
    }
}
