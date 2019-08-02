<?php

namespace AppBundle\v2\Organisation\Assembler;

use AppBundle\v2\Organisation\DTO\OrganisationDto;
use AppBundle\v2\DTO\DtoPropertySetterTrait;

class OrganisationDtoAssembler
{
    use DtoPropertySetterTrait;

    /**
     * @param array $data
     * @return OrganisationDto
     */
    public function assembleFromArray(array $data)
    {
        $list = [];
        foreach($data as $result)
        {
            $dto = new OrganisationDto();

            $this->setPropertiesFromData($dto, $data);

            $list[] = $dto;
        }

        return $list;
    }
}
