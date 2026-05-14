<?php declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ApiErrorException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request   = $event->getRequest();
        $requestId = $request->attributes->get(RequestIdListener::ATTRIBUTE_KEY);

        [$status, $type, $title, $detail, $extensions] = match (true) {
            $exception instanceof ApiErrorException => [
                $exception->getStatus(),
                $exception->getType(),
                $exception->getTitle(),
                $exception->getDetail(),
                $exception->getExtensions(),
            ],
            default => [
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'about:blank',
                'Internal Server Error',
                'An unexpected error occurred.',
                [],
            ],
        };

        $payload = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->getUri(),
            'request_id' => $requestId,
            ...$extensions,
        ];

        $headers = array_merge(
            [
                'Content-Type' => 'application/problem+json',
                'X-Request-Id' => $requestId,
            ],
        );

        $event->setResponse(new JsonResponse($payload, $status, $headers));
    }
}
