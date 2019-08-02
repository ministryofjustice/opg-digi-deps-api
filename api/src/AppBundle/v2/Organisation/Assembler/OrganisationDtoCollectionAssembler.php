<?php

namespace AppBundle\v2\Organisation\Assembler;

use AppBundle\v2\Organisation\DTO\OrganisationDtoCollection;

class OrganisationDtoCollectionAssembler
{
    /** @var OrganisationDtoAssembler */
    private $organisationDtoAssembler;

    /**
     * @param OrganisationDtoAssembler $organisationDtoAssembler
     */
    public function __construct($organisationDtoAssembler)
    {
        $this->organisationDtoAssembler = $organisationDtoAssembler;
    }

    /**
     * @param array $data
     * @return OrganisationDtoCollection
     */
    public function assembleFromArray(array $data)
    {
        $collection = new OrganisationDtoCollection();

        foreach ($data as $row) {
            $item = $this->organisationDtoAssembler->assembleFromArray($row);
            $collection->append($item);
        }

        return $collection;
    }
}
