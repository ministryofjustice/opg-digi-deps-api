<?php

namespace AppBundle\v2\Assembler\Report;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\v2\Assembler\StatusAssembler;
use AppBundle\v2\DTO\StatusDto;

class ReportAssemblerFromEntityDecorator implements ReportAssemblerInterface
{
    /** @var ReportAssemblerInterface  */
    private $decorated;

    /** @var StatusAssembler  */
    private $statusDtoAssembler;

    /** @var ReportRepository */
    private $reportRepository;

    /**
     * @param ReportAssemblerInterface $decorated
     * @param StatusAssembler $statusDtoAssembler
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        ReportAssemblerInterface $decorated,
        StatusAssembler $statusDtoAssembler,
        ReportRepository $reportRepository
    ) {
        $this->decorated = $decorated;
        $this->statusDtoAssembler = $statusDtoAssembler;
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param array $data
     * @return \AppBundle\v2\DTO\ReportDto
     */
    public function assembleFromArray(array $data)
    {
        $reportDto = $this->decorated->assembleFromArray($data);

        if (null === $reportDto->getId()) {
            return $reportDto;
        }

        if (null === ($reportEntity = $this->reportRepository->find($reportDto->getId()))) {
            return $reportDto;
        }

        $reportDto->setStatus( $this->assembleStatusDto($reportEntity));
        $reportDto->setAvailableSections($reportEntity->getAvailableSections());

        return $reportDto;
    }

    /**
     * @param Report $report
     * @return StatusDto
     */
    public function assembleStatusDto(Report $report)
    {
        return $this->statusDtoAssembler->assembleFromReport($report);
    }
}
