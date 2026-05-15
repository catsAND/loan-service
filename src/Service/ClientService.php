<?php declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateClientDto;
use App\Entity\Client;
use App\Event\ClientCreatedEvent;
use App\Exception\ClientExsistException;
use App\Exception\ClientNotFoundException;
use App\Repository\ClientRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class ClientService
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    public function getClientById(string $id): Client
    {
        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException(sprintf('Client with ID "%s" not found.', $id));
        }

        return $client;
    }

    public function createClient(CreateClientDto $createClientDto): Client
    {
        $checkClient = $this->clientRepository->findActiveClientByEmailOrPhone($createClientDto->email, $createClientDto->phoneNumber);

        if ($checkClient) {
            throw new ClientExsistException;
        }

        $client = $this->clientRepository->createClient($createClientDto);

        $this->dispatcher->dispatch(new ClientCreatedEvent($client));

        return $client;
    }

    public function updateClientById(string $id, CreateClientDto $createClientDto): Client
    {
        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException(sprintf('Client with ID "%s" not found.', $id));
        }

        $client
            ->setFirstName($createClientDto->firstName)
            ->setLastName($createClientDto->lastName)
            ->setEmail($createClientDto->email)
            ->setPhone($createClientDto->phoneNumber);

        $this->clientRepository->save($client);

        return $client;
    }

    public function deleteClientById(string $id): void
    {
        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException(sprintf('Client with ID "%s" not found.', $id));
        }

        $client->setDeletedAt(new \DateTimeImmutable());
        $this->clientRepository->save($client);
    }
}
