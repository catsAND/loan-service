<?php declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Dto\ClientDto;
use App\Service\ClientService;
use App\Transformer\ClientTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientTransformer $clientTransformer,
        private readonly ClientService $clientService,
    ) {
    }

    #[Route('', name: 'api_client_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 100)));

        $result = $this->clientService->listClients($page, $limit);

        return new JsonResponse([
            'data' => $this->clientTransformer->transformCollection($result['data']),
            'pagination' => $result['pagination'],
        ]);
    }

    #[Route('/{clientId}', name: 'api_client_get', methods: ['GET'])]
    public function get(string $clientId): JsonResponse
    {
        $client = $this->clientService->getClientById($clientId);

        return new JsonResponse($this->clientTransformer->transform($client));
    }

    #[Route('', name: 'api_client_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ClientDto $clientDto): JsonResponse
    {
        $client = $this->clientService->createClient($clientDto);

        return new JsonResponse($this->clientTransformer->transform($client), 201);
    }

    #[Route('/{clientId}', name: 'api_client_update', methods: ['PUT'])]
    public function update(string $clientId, #[MapRequestPayload] ClientDto $clientDto): JsonResponse
    {
        $client = $this->clientService->updateClientById($clientId, $clientDto);

        return new JsonResponse($this->clientTransformer->transform($client));
    }

    #[Route('/{clientId}', name: 'api_client_delete', methods: ['DELETE'])]
    public function delete(string $clientId): JsonResponse
    {
        $this->clientService->deleteClientById($clientId);

        return new JsonResponse('', JsonResponse::HTTP_NO_CONTENT);
    }
}
