<?php declare(strict_types=1);

namespace App\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/applications')]
class ApplicationController extends AbstractController
{
    #[Route('', name: 'api_application_list', methods: ['GET'])]
    public function listApplications(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/{application_id}', name: 'api_application_get', methods: ['GET'])]
    public function getApplication(string $application_id): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('', name: 'api_application_create', methods: ['POST'])]
    public function createApplication(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/{application_id}', name: 'api_application_update', methods: ['PUT'])]
    public function updateApplication(string $application_id): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/{application_id}', name: 'api_application_delete', methods: ['DELETE'])]
    public function deleteApplication(string $application_id): JsonResponse
    {
        return new JsonResponse();
    }
}
