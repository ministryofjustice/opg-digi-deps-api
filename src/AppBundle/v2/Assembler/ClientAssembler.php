<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\Assembler\Report\ReportAssemblerInterface;
use AppBundle\v2\DTO\ClientDto;

class ClientAssembler
{
    /** @var ReportAssemblerInterface  */
    private $reportDtoAssembler;

    /** @var NdrAssembler */
    private $ndrDtoAssembler;

    /**
     * @param ReportAssemblerInterface $reportDtoAssembler
     * @param NdrAssembler $ndrDtoAssembler
     */
    public function __construct(ReportAssemblerInterface $reportDtoAssembler, NdrAssembler $ndrDtoAssembler)
    {
        $this->reportDtoAssembler = $reportDtoAssembler;
        $this->ndrDtoAssembler = $ndrDtoAssembler;
    }

    /**
     * @param array $data
     * @return ClientDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new ClientDto();

        if (isset($data['id'])) {
            $dto->setId($data['id']);
        }

        if (isset($data['caseNumber'])) {
            $dto->setCaseNumber($data['caseNumber']);
        }

        if (isset($data['firstname'])) {
            $dto->setFirstName($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $dto->setLastName($data['lastname']);
        }

        if (isset($data['email'])) {
            $dto->setEmail($data['email']);
        }

        if (isset($data['ndr']) && is_array($data['ndr'])) {
            $dto->setNdr($this->assembleNdrDto($data['ndr']));
        }

        if (isset($data['reports'])  && is_array($data['reports'])) {
            $dto->setReports($this->assembleReportDtos($data['reports']));
            $dto->setReportCount(count($data['reports']));
        }

        return $dto;
    }

    /**
     * @param array $reports
     * @return array
     */
    private function assembleReportDtos(array $reports)
    {
        $dtos = [];

        foreach ($reports as $report) {
            $dtos[] = $this->reportDtoAssembler->assembleFromArray($report);
        }

        return $dtos;
    }

    /**
     * @param array $ndr
     * @return
     */
    private function assembleNdrDto(array $ndr)
    {
        return $this->ndrDtoAssembler->assembleFromArray($ndr);
    }
}
