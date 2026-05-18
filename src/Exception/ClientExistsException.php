<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ClientExistsException extends ApiErrorException
{
    public function __construct()
    {
        parent::__construct(
            type: self::DEFAULT_TYPE,
            title: 'Client Already Exists',
            status: Response::HTTP_CONFLICT,
            detail: 'Client with the same email or phone number already exists.',
        );
    }
}
