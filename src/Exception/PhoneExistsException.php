<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class PhoneExistsException extends ApiErrorException
{
    public function __construct()
    {
        parent::__construct(
            type: self::DEFAULT_TYPE,
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
