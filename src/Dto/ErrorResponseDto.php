<?php declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\HttpFoundation\Response;

final readonly class ErrorResponseDto
{
    /**
     * @param array<string, mixed> $extensions
     */
    public function __construct(
        public int $status,
        public string $type,
        public string $title,
        public string $detail,
        public array $extensions = [],
    ) {
    }

    public static function internalError(): self
    {
        return new self(
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            type: 'about:blank',
            title: 'Internal Server Error',
            detail: 'An unexpected error occurred.',
        );
    }
}
