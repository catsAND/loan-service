<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ClientExsistException extends ApiErrorException
{
    public function getTitle(): string
    {
        return 'Client Already Exists';
    }

    public function getStatus(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getDetail(): string
    {
        return 'Client with the same email or phone number already exists.';
    }
}
