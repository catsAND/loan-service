<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Client;

final readonly class ClientCreatedEvent
{
    public function __construct(private Client $client)
    {
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
