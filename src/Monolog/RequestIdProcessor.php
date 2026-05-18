<?php declare(strict_types=1);

namespace App\Monolog;

use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class RequestIdProcessor
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $record;
        }

        $requestId = $request->attributes->get('_request_id');

        if (!is_string($requestId) || '' === $requestId) {
            return $record;
        }

        return $record->with(
            extra: [
                ...$record->extra,
                'request_id' => $requestId,
            ],
        );
    }
}
