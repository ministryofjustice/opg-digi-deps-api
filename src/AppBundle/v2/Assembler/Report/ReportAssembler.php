<?php

namespace AppBundle\v2\Assembler\Report;

use AppBundle\v2\DTO\ReportDto;

class ReportAssembler implements ReportAssemblerInterface
{
    /**
     * @param array $data
     * @return ReportDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new ReportDto();

        if (isset($data['id'])) {
            $dto->setId($data['id']);
        }

        if (isset($data['submitted'])) {
            $dto->setSubmitted($data['submitted']);
        }

        if (isset($data['dueDate'])) {
            $dto->setDueDate($data['dueDate']);
        }

        if (isset($data['submitDate'])) {
            $dto->setSubmitDate($data['submitDate']);
        }

        if (isset($data['unSubmitDate'])) {
            $dto->setUnsubmitDate($data['unSubmitDate']);
        }

        if (isset($data['startDate'])) {
            $dto->setStartDate($data['startDate']);
        }

        if (isset($data['endDate'])) {
            $dto->setEndDate($data['endDate']);
        }

        $dto->setAvailableSections(['profCurrentFees']);

        return $dto;
    }
}
