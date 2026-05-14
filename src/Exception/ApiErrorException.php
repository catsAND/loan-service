<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ApiErrorException extends \Exception
{
    public function getType(): string
    {
        return 'https://api.example.com/errors/not-found'; // FIXME:
    }

    public function getTitle(): string
    {
        return 'Internal Server Error';
    }

    public function getStatus(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getDetail(): string
    {
        return 'An unexpected error occurred.';
    }

    public function getExtensions(): array
    {
        return [];
    }
}
