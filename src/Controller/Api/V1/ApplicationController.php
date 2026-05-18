<?php declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Dto\ApplicationDto;
use App\Dto\UpdateApplicationDto;
use App\Service\ApplicationService;
use App\Transformer\ApplicationTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/applications')]
class ApplicationController extends AbstractController
{
    public function __construct(
        private readonly ApplicationTransformer $applicationTransformer,
        private readonly ApplicationService $applicationService,
    ) {
    }

    #[Route('', name: 'api_application_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 100)));

        $result = $this->applicationService->listApplications($page, $limit);

        return new JsonResponse([
            'data' => $this->applicationTransformer->transformCollection($result['data']),
            'pagination' => $result['pagination'],
        ]);
    }

    #[Route('/{applicationId}', name: 'api_application_get', methods: ['GET'])]
    public function get(string $applicationId): JsonResponse
    {
        $application = $this->applicationService->getApplicationById($applicationId);

        return new JsonResponse($this->applicationTransformer->transform($application));
    }

    #[Route('', name: 'api_application_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ApplicationDto $applicationDto): JsonResponse
    {
        $application = $this->applicationService->createApplication($applicationDto);

        return new JsonResponse($this->applicationTransformer->transform($application), 201);
    }

    #[Route('/{applicationId}', name: 'api_application_update', methods: ['PUT'])]
    public function update(string $applicationId, #[MapRequestPayload] UpdateApplicationDto $updateApplicationDto): JsonResponse
    {
        $application = $this->applicationService->updateApplicationById($applicationId, $updateApplicationDto);

        return new JsonResponse($this->applicationTransformer->transform($application));
    }

    #[Route('/{applicationId}', name: 'api_application_delete', methods: ['DELETE'])]
    public function delete(string $applicationId): JsonResponse
    {
        $this->applicationService->deleteApplicationById($applicationId);

        return new JsonResponse('', JsonResponse::HTTP_NO_CONTENT);
    }
}
