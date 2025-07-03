<?php

namespace App\UI\Controller;

use App\Application\DTO\ProduceDTO;
use App\Application\DTO\ProduceListFiltersDTO;
use App\Application\Mapper\ProduceMapper;
use App\Application\Service\ProduceImporter;
use App\Application\Service\ProduceService;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Symfony\Component\Routing\Requirement\Requirement;


#[AsController]
#[Route('/api/produce')]
final  class ProduceController extends AbstractController
{
    public function __construct(
        private readonly ProduceImporter $produceImporter,
        private readonly ProduceService  $produceService,
        private readonly  ProduceMapper $produceMapper,
    )
    {
    }

    #[Route('', name: 'add_produce', methods: ['POST'])]
    public function add(
        #[MapRequestPayload(type: ProduceDTO::class)] array $produceDTOs,
    ): JsonResponse
    {
        try {
            $result = $this->produceImporter->import($produceDTOs);
            return $this->json([
                'importedCount' => $result->importedCount,
                'errors' => $result->errors,
            ],
                $result->errors ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK);
        } catch (\Throwable $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/{type}', name: 'get_list', requirements: ['type' => new EnumRequirement(ProduceType::class)],
        methods: ['GET'])]
    public function list(
        ProduceType                             $type,
        #[MapQueryString] ProduceListFiltersDTO $filters= new ProduceListFiltersDTO(),
        #[MapQueryParameter(name: 'returnUnit', validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        ?ProduceUnitType                        $returnUnit = ProduceUnitType::GRAM
    ): JsonResponse
    {
        $list = $this->produceService->list($type , $filters, $returnUnit);

        if (empty($list)) {
            return $this->json(['message' => 'No produce found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($this->produceMapper->toDtoList($list,$returnUnit), Response::HTTP_OK, [], ['groups' => 'produce:read']);
    }

    #[Route('/{type}/{id}', name: 'get_one', requirements: ['type' => new EnumRequirement(ProduceType::class), 'id' => Requirement::DIGITS],
        methods: ['GET'])]
    public function get(
        ProduceType $type, int $id,
        #[MapQueryParameter(name: 'returnUnit', validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)] ?ProduceUnitType $returnUnit = ProduceUnitType::GRAM
    ): JsonResponse
    {
        $produce = $this->produceService->getOne($type, $id);
        if (!$produce) {
            return $this->json(['message' => 'Produce not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(
            $this->produceMapper->toDto($produce, $returnUnit),
            Response::HTTP_OK,
            [],
            ['groups' => 'produce:read']
        );
    }

    #[Route('/{type}/{id}', requirements: ['type' => new EnumRequirement(ProduceType::class), 'id' => Requirement::DIGITS],
        methods: ['DELETE'])]
    public function remove(ProduceType $type, int $id): JsonResponse
    {
        $result = $this->produceService->remove($type, $id);

        if (!$result) {
            return $this->json(['message' => 'Produce not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(['message' => 'Removed successfully'], Response::HTTP_NO_CONTENT);
    }
}