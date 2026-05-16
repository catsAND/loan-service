<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

readonly class ApiErrorException extends \Throwable
{
    public function __construct(
        private string $type = '',
        private string $title = '',
        private int $status = 0,
        private string $detail = '',
        private array $extensions = [],
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
