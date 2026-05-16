<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final readonly class PhoneExistException extends ApiErrorException
{
    public function __construct()
    {
        parent::__construct(
            type: 'https://api.example.com/errors/phone-exist', // FIXME:
            title: 'Phone number already exists',
            status: Response::HTTP_CONFLICT,
            detail: 'A client with the provided phone number already exists.',
            extensions: [
                'errors' => [
                    'field' => 'phoneNumber',
                    'message' => 'Phone number already exists.',
                ],
            ],
        );
    }
}
