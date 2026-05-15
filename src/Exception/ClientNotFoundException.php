<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ClientNotFoundException extends ApiErrorException
{
    public function getTitle(): string
    {
        return 'Client Not Found';
    }

    public function getStatus(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getDetail(): string
    {
        return $this->getMessage();
    }
}
