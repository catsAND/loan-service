<?php declare(strict_types=1);

namespace App\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/clients')]
class ClientController extends AbstractController
{
    #[Route('', name: 'api_client_list', methods: ['GET'])]
    public function listClients(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/{client_id}', name: 'api_client_get', methods: ['GET'])]
    public function getClient(string $client_id): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('', name: 'api_client_create', methods: ['POST'])]
    public function createClient(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/{client_id}', name: 'api_client_update', methods: ['PUT'])]
    public function updateClient(string $client_id): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/{client_id}', name: 'api_client_delete', methods: ['DELETE'])]
    public function deleteClient(string $client_id): JsonResponse
    {
        return new JsonResponse();
    }
}
