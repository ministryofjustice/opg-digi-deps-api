<?php

namespace AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;

class LayDeputyshipDtoCollectionAssembler
{
    /** @var LayDeputyshipDtoAssembler */
    private $uploadDtoAssembler;

    /**
     * @param LayDeputyshipDtoAssembler $uploadDtoAssembler
     */
    public function __construct(LayDeputyshipDtoAssembler $uploadDtoAssembler)
    {
        $this->uploadDtoAssembler = $uploadDtoAssembler;
    }

    /**
     * @param array $data
     * @return LayDeputyshipDtoCollection
     */
    public function assembleFromArray(array $data)
    {
        $collection = new LayDeputyshipDtoCollection();

        foreach ($data as $uploadRow) {
            $item = $this->uploadDtoAssembler->assembleFromArray($uploadRow);
            $collection->append($item);
        }

        return $collection;
    }
}
