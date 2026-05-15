<?php declare(strict_types=1);

namespace App\EventListener;

use App\Event\ClientCreatedEvent;
use App\Repository\ClientRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ClientCreatedEvent::class)]
final class ClientCreatedEventListener
{
    public function __construct(private readonly ClientRepository $clientRepository)
    {
    }

    public function __invoke(ClientCreatedEvent $event): void
    {
        $client = $event->getClient();

        // Perform any additional actions like sending notification.
    }
}
