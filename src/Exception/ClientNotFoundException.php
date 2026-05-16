<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ClientNotFoundException extends ApiErrorException
{
    public function __construct(readonly string $id)
    {
        parent::__construct(
            type: 'https://api.example.com/errors/client-not-found', // FIXME:
            title: 'Client Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('Client with ID "%s" not found.', $id),
        );
    }
}
