<?php

namespace AppBundle\v2\Registration\Uploader;

use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;

interface LayDeputyshipUploaderInterface
{
    public function upload(LayDeputyshipDtoCollection $collection): array;
}
