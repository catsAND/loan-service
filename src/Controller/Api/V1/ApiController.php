<?php declare(strict_types=1);

namespace App\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class ApiController extends AbstractController
{
    #[Route('/health', name: 'api_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/docs', name: 'api_docs', methods: ['GET'])]
    public function docs(): Response
    {
        return new Response(
            file_get_contents(dirname(__DIR__, 4).'/public/swagger-ui.html'),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html']
        );
    }
}
