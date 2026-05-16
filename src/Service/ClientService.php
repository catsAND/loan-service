<?php declare(strict_types=1);

namespace App\Service;

use App\Dto\ClientDto;
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
        private ClientRepository $clientRepository,
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

    public function createClient(ClientDto $clientDto): Client
    {
        $checkClient = $this->clientRepository->findActiveClientByEmailOrPhone($clientDto->email, $clientDto->phoneNumber);

        if ($checkClient) {
            throw new ClientExsistException;
        }

        $client = $this->clientRepository->createClient($clientDto);

        $this->dispatcher->dispatch(new ClientCreatedEvent($client));

        return $client;
    }

    public function updateClientById(string $id, ClientDto $clientDto): Client
    {
        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException(sprintf('Client with ID "%s" not found.', $id));
        }

        $client
            ->setFirstName($clientDto->firstName)
            ->setLastName($clientDto->lastName)
            ->setEmail($clientDto->email)
            ->setPhone($clientDto->phoneNumber);

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
