<?php

namespace AppBundle\v2\Controller;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\Repository\DeputyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/deputy")
 */
class DeputyController
{
    /** @var DeputyRepository  */
    private $deputyRepository;

    /**
     * @param DeputyRepository $deputyRepository
     */
    public function __construct(DeputyRepository $deputyRepository)
    {
        $this->deputyRepository = $deputyRepository;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getByIdAction($id)
    {
        try {
            $deputyDto = $this->deputyRepository->getDtoById($id);

            return $this->buildSuccessResponse($deputyDto);
        } catch (\Exception $e) {
            return $this->buildErrorResponse($e);
        }
    }

    /**
     * @param DeputyDto $dto
     * @return JsonResponse
     */
    private function buildSuccessResponse(DeputyDto $dto)
    {
        return new JsonResponse([
            'success' => true,
            'data' => $dto->jsonSerialize(),
            'message' => ''
        ]);
    }

    /**
     * @param \Exception $e
     * @return JsonResponse
     */
    private function buildErrorResponse(\Exception $e)
    {
        return new JsonResponse([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
