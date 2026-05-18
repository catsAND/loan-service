<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class InvalidUuidFormatException extends ApiErrorException
{
    public function __construct(string $id)
    {
        parent::__construct(
            status: Response::HTTP_BAD_REQUEST,
            type: 'about:blank',
            title: 'Invalid UUID Format',
            detail: sprintf('The provided ID "%s" is not a valid UUID.', $id),
        );
    }
}
