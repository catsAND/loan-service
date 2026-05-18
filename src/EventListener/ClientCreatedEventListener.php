<?php declare(strict_types=1);

namespace App\EventListener;

use App\Event\ClientCreatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ClientCreatedEvent::class)]
final class ClientCreatedEventListener
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function onAppEventClientCreatedEvent(ClientCreatedEvent $event): void
    {
        $client = $event->getClient();

        $this->logger->info('Client created event triggered', ['event' => $event, 'clientId' => $client->getId()]);
    }
}
