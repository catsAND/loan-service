<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 100)]
final class RequestIdListener
{
    public const ATTRIBUTE_KEY = '_request_id';
    public const HEADER_KEY = 'X-Request-Id';

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $requestId = $request->headers->get(self::HEADER_KEY);

        if (!is_string($requestId) || $requestId === '') {
            $requestId = Uuid::v4()->toRfc4122();
        }

        $request->attributes->set(self::ATTRIBUTE_KEY, $requestId);
    }
}
