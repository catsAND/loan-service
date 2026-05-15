<?php declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Dto\CreateClientDto;
use App\Repository\ClientRepository;
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
        private readonly ClientRepository $clientRepository,
        private readonly ClientTransformer $clientTransformer,
        private readonly ClientService $clientService,
    ) {
    }

    #[Route('', name: 'api_client_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = max(1, (int)$request->query->get('limit', 100));

        $data = $this->clientRepository->findAllActiveClients($page, $limit);
        $response = $this->clientTransformer->transformCollection($data);

        return new JsonResponse($response);
    }

    #[Route('/{client_id}', name: 'api_client_get', methods: ['GET'])]
    public function get(string $client_id): JsonResponse
    {
        $client = $this->clientService->getClientById($client_id);

        return new JsonResponse($this->clientTransformer->transform($client));
    }

    #[Route('', name: 'api_client_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateClientDto $createClientDto): JsonResponse
    {
        $client = $this->clientService->createClient($createClientDto);

        return new JsonResponse($this->clientTransformer->transform($client), 201);
    }

    #[Route('/{client_id}', name: 'api_client_update', methods: ['PUT'])]
    public function update(string $client_id, #[MapRequestPayload] CreateClientDto $createClientDto): JsonResponse
    {
        $client = $this->clientService->updateClientById($client_id, $createClientDto);

        return new JsonResponse($this->clientTransformer->transform($client));
    }

    #[Route('/{client_id}', name: 'api_client_delete', methods: ['DELETE'])]
    public function delete(string $client_id): JsonResponse
    {
        $this->clientService->deleteClientById($client_id);

        return new JsonResponse();
    }
}
