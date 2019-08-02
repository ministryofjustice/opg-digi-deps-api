<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\Organisation\DTO\OrganisationDto;

class OrganisationTransformer
{
    /**
     * @param OrganisationDto $dto
     * @param array $exclude
     * @return array
     */
    public function transform(OrganisationDto $dto, array $exclude = [])
    {
        $transformed = [
            'id' => $dto->getId(),
            'organisation_name' => $dto->getOrganisationtName(),
            'email_domain' => $dto->getEmailDomain()
        ];

        return $transformed;
    }
}
