<?php

namespace AppBundle\v2\Organisation\DTO;

use AppBundle\v2\Organisation\DTO\OrganisationDto;

class OrganisationDtoCollection extends \ArrayObject
{
    /**
     * {@inheritDoc}
     */
    public function append($item)
    {
        if (!$item instanceof OrganisationDto) {
            throw new \InvalidArgumentException(sprintf(
                'Only items of type %s are allowed',
                OrganisationDto::class
            ));
        }

        parent::append($item);
    }
}
