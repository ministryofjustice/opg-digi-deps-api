<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\OrganisationDto;
use AppBundle\v2\DTO\AddressDto;
use AppBundle\v2\DTO\UserDto;

class AddressTransformer
{

    /**
     * @param AddressDto $dto
     * @param array $exclude
     * @return array
     */
    public function transform(AddressDto $dto, array $exclude = [])
    {
        $transformed = [
            'id' => $dto->getId(),
            'address1' => $dto->getAddress1(),
            'deputyAddressNo' => $dto->getDeputyAddressNo(),
            'address2' => $dto->getAddress2(),
            'address3' => $dto->getAddress3(),
            'address4' => $dto->getAddress4(),
            'address5' => $dto->getAddress5(),
            'email1' => $dto->getEmail1(),
            'email2' => $dto->getEmail2(),
            'email3' => $dto->getEmail3(),
            'postcode' => $dto->getPostcode(),
            'country' => $dto->getCountry()
        ];

        if (!in_array('organisation', $exclude)) {
            $transformed['organisation'] = $this->transformOrganisation($dto->getOrganisation());
        }

        return $transformed;
    }

    /**
     * @param ClientDto $dto
     * @return null|string
     */
    private function transformArchivedAt(ClientDto $dto)
    {
        return $dto->getArchivedAt() instanceof \DateTime ? $dto->getArchivedAt()->format('Y-m-d H:i:s') : null;
    }

    /**
     * @param array $reports
     * @return array
     */
    private function transformReports(array $reports)
    {
        if (empty($reports)) {
            return [];
        }

        $transformed = [];

        foreach ($reports as $report) {
            if ($report instanceof ReportDto) {
                $transformed[] = $this->reportTransformer->transform($report);
            }
        }

        return $transformed;
    }

    /**
     * @param NdrDto $ndr
     * @return array
     */
    private function transformNdr(NdrDto $ndr)
    {
        return $this->ndrTransformer->transform($ndr);
    }
}
