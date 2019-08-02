<?php

namespace AppBundle\v2\Organisation\Assembler;

use AppBundle\v2\Organisation\DTO\AddressDto;
use AppBundle\v2\DTO\DtoPropertySetterTrait;

class AddressAssembler
{
    use DtoPropertySetterTrait;

    /** @var AddressAssembler */
    private $addressDtoAssembler;

    /**
     * @param AddressAssembler $addressDtoAssembler
     */
    public function __construct(AddressAssembler $addressDtoAssembler)
    {
        $this->addressDtoAssembler = $addressDtoAssembler;
    }

    /**
     * @param array $data
     * @return AddressDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new AddressDto();

        $exclude = [];
        $this->setPropertiesFromData($dto, $data, $exclude);

        return $dto;
    }

}
