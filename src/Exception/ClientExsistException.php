<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ClientExsistException extends ApiErrorException
{
    public function __construct()
    {
        parent::__construct(
            type: 'https://api.example.com/errors/client-exist', // FIXME:
            title: 'Client Already Exists',
            status: Response::HTTP_CONFLICT,
            detail: 'Client with the same email or phone number already exists.',
        );
    }
}
