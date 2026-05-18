<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class EmailExistsException extends ApiErrorException
{
    public function __construct()
    {
        parent::__construct(
            type: self::DEFAULT_TYPE,
            title: 'Client conflict',
            status: Response::HTTP_CONFLICT,
            detail: 'A client with the provided email already exists.',
            extensions: [
                'errors' => [
                    'field' => 'email',
                    'message' => 'Email already exists.',
                ],
            ],
        );
    }
}
