<?php declare(strict_types=1);

namespace App\Service;

use App\Dto\ClientDto;
use App\Entity\Client;
use App\Event\ClientCreatedEvent;
use App\Exception\ClientExsistException;
use App\Exception\ClientNotFoundException;
use App\Exception\EmailExistException;
use App\Exception\PhoneExistException;
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
            throw new ClientNotFoundException($id);
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
            throw new ClientNotFoundException($id);
        }

        $checkEmail = $this->clientRepository->findActiveClientByEmail($clientDto->email);
        if ($checkEmail && $checkEmail->getId() !== $id) {
            throw new EmailExistException;
        }

        $checkPhone = $this->clientRepository->findActiveClientByPhoneNumber($clientDto->phoneNumber);
        if ($checkPhone && $checkPhone->getId() !== $id) {
            throw new PhoneExistException;
        }

        $client = $this->clientRepository->updateClient($client, $clientDto);

        return $client;
    }

    public function deleteClientById(string $id): void
    {
        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException($id);
        }

        $client->setDeletedAt(new \DateTimeImmutable());
        $this->clientRepository->save($client);
    }
}
