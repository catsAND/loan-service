<?php declare(strict_types=1);

namespace App\Exception;

class ApiErrorException extends \RuntimeException
{
    public const string DEFAULT_TYPE = '/api/v1/docs';

    /**
     * @param array<string, mixed> $extensions
     */
    public function __construct(
        private readonly string $type = '',
        private readonly string $title = '',
        private readonly int $status = 0,
        private readonly string $detail = '',
        private readonly array $extensions = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($detail, $status, $previous);
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

    /**
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
