<?php declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ExceptionMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ApiExceptionListener
{
    public function __construct(
        private readonly ExceptionMapper $exceptionMapper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $requestId = $request->attributes->get(RequestIdListener::ATTRIBUTE_KEY);

        $this->logger->error('API exception occurred', ['exception' => $exception]);

        $error = $this->exceptionMapper->map($exception);

        $payload = [
            'type' => $error->type,
            'title' => $error->title,
            'status' => $error->status,
            'detail' => $error->detail,
            'instance' => $request->getRequestUri(),
            'request_id' => $requestId,
            ...$error->extensions,
        ];

        $headers = [
            'Content-Type' => 'application/problem+json',
            'X-Request-Id' => $requestId,
        ];

        $event->setResponse(new JsonResponse($payload, $error->status, $headers));
    }
}
