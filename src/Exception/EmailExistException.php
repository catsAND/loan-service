<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final readonly class EmailExistException extends ApiErrorException
{
    public function __construct()
    {
        parent::__construct(
            type: 'https://api.example.com/errors/email-exist', // FIXME:
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
