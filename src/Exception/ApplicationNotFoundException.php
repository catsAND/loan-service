<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ApplicationNotFoundException extends ApiErrorException
{
    public function __construct(readonly string $id)
    {
        parent::__construct(
            type: 'https://api.example.com/errors/application-not-found', // FIXME:
            title: 'Application Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('Application with ID "%s" not found.', $id),
        );
    }
}
