<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ReportDto;
use AppBundle\v2\DTO\StatusDto;

class ReportTransformer
{
    /** @var StatusTransformer */
    private $statusTransformer;

    /**
     * @param StatusTransformer $statusTransformer
     */
    public function __construct(StatusTransformer $statusTransformer)
    {
        $this->statusTransformer = $statusTransformer;
    }

    /**
     * @param ReportDto $dto
     * @return array
     */
    public function transform(ReportDto $dto)
    {
        $transformed = [
            'id' => $dto->getId(),
            'submitted' => $dto->getSubmitted(),
            'due_date' => $this->transformDate($dto, 'dueDate'),
            'submit_date' => $this->transformDate($dto, 'submitDate'),
            'un_submit_date' => $this->transformDate($dto, 'unSubmitDate'),
            'start_date' => $this->transformDate($dto, 'startDate'),
            'end_date' => $this->transformDate($dto, 'endDate')
        ];

        if (null !== $dto->getAvailableSections()) {
            $transformed['available_sections'] = $dto->getAvailableSections();
        }

        if ($dto->getStatus() instanceof StatusDto) {
            $transformed['status'] = $this->statusTransformer->transform($dto->getStatus());
        }

        return $transformed;
    }

    /**
     * @param ReportDto $dto
     * @param $property
     * @return null
     */
    private function transformDate(ReportDto $dto, $property)
    {
        $getter = sprintf('get%s', ucfirst($property));

        return $dto->{$getter}() instanceof \DateTime ? $dto->{$getter}()->format('Y-m-d\TH:i:sP') : null;
    }
}
