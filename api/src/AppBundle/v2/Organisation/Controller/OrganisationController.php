<?php

namespace AppBundle\v2\Organisation\Controller;

use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\v2\Organisation\Assembler\OrganisationDtoCollectionAssembler;
use AppBundle\v2\Transformer\OrganisationTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/organisation")
 */
class OrganisationController
{
    /** @var OrganisationRepository  */
    private $repository;

    /** @var OrganisationDtoCollectionAssembler */
    private $assembler;

    /** @var OrganisationTransformer */
    private $transformer;

    /**
     * @param OrganisationRepository $repository
     * @param OrganisationDtoCollectionAssembler $assembler
     * @param OrganisationTransformer $transformer
     */
    public function __construct(OrganisationRepository $repository, OrganisationDtoCollectionAssembler $assembler, OrganisationTransformer $transformer)
    {
        $this->repository = $repository;
        $this->assembler = $assembler;
        $this->transformer = $transformer;
    }

    /**
     * @Route("/get-all")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAllAction(Request $request)
    {
        $data = $this->repository->findAll();

        $dto = $this->assembler->assembleFromArray($data);
        $transformedDto = $this->transformer->transform($dto);

        return $this->buildSuccessResponse($transformedDto);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    private function buildSuccessResponse(array $data)
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'message' => ''
        ]);
    }
}
