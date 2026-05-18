<?php declare(strict_types=1);

namespace App\Service;

use App\Dto\ClientDto;
use App\Entity\Client;
use App\Event\ClientCreatedEvent;
use App\Exception\ClientExistsException;
use App\Exception\ClientNotFoundException;
use App\Exception\EmailExistsException;
use App\Exception\InvalidUuidFormatException;
use App\Exception\PhoneExistsException;
use App\Normalizer\EmailNormalizer;
use App\Repository\ClientRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class ClientService
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private ClientRepository $clientRepository,
        private EmailNormalizer $emailNormalizer,
        private LoggerInterface $logger,
    ) {
    }

    public function getClientById(string $id): Client
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidUuidFormatException($id);
        }

        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException($id);
        }

        return $client;
    }

    /**
     * @return array{
     *     data: list<Client>,
     *     pagination: array{total: int, page: int, limit: int, pages: int}
     * }
     */
    public function listClients(int $page, int $limit): array
    {
        $data = $this->clientRepository->findAllActiveClients($page, $limit);
        $count = $this->clientRepository->countActiveClients();

        return [
            'data' => $data,
            'pagination' => [
                'total' => $count,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int) ceil($count / $limit),
            ],
        ];
    }

    public function createClient(ClientDto $clientDto): Client
    {
        $client = new Client()
            ->setFirstName($clientDto->firstName)
            ->setLastName($clientDto->lastName)
            ->setEmail($this->emailNormalizer->normalize($clientDto->email))
            ->setPhone($clientDto->phoneNumber);

        try {
            $this->clientRepository->save($client);
        } catch (UniqueConstraintViolationException $e) {
            throw new ClientExistsException();
        }

        $this->logger->info('Client created', ['clientId' => $client->getId()]);

        $this->dispatcher->dispatch(new ClientCreatedEvent($client));

        return $client;
    }

    public function updateClientById(string $id, ClientDto $clientDto): Client
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidUuidFormatException($id);
        }

        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException($id);
        }

        $clientEmail = $this->emailNormalizer->normalize($clientDto->email);

        $checkEmail = $this->clientRepository->findActiveClientByEmail($clientEmail);
        if ($checkEmail && $checkEmail->getId() !== $id) {
            throw new EmailExistsException();
        }

        $checkPhone = $this->clientRepository->findActiveClientByPhoneNumber($clientDto->phoneNumber);
        if ($checkPhone && $checkPhone->getId() !== $id) {
            throw new PhoneExistsException();
        }

        $client
            ->setFirstName($clientDto->firstName)
            ->setLastName($clientDto->lastName)
            ->setEmail($clientEmail)
            ->setPhone($clientDto->phoneNumber);

        try {
            $this->clientRepository->save($client);
        } catch (UniqueConstraintViolationException $e) {
            throw new ClientExistsException();
        }

        $this->logger->info('Client updated', ['clientId' => $client->getId()]);

        return $client;
    }

    public function deleteClientById(string $id): void
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidUuidFormatException($id);
        }

        $client = $this->clientRepository->findActiveClientById($id);

        if (!$client) {
            throw new ClientNotFoundException($id);
        }

        $client->setDeletedAt(new \DateTimeImmutable());
        $this->clientRepository->save($client);

        $this->logger->info('Client deleted', ['clientId' => $client->getId()]);
    }
}
