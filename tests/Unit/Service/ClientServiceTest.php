<?php declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\ClientDto;
use App\Entity\Client;
use App\Event\ClientCreatedEvent;
use App\Exception\ClientNotFoundException;
use App\Exception\EmailExistsException;
use App\Exception\PhoneExistsException;
use App\Normalizer\EmailNormalizer;
use App\Repository\ClientRepository;
use App\Service\ClientService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ClientServiceTest extends TestCase
{
    private ClientService $service;
    private ClientRepository&MockObject $clientRepository;
    private EventDispatcherInterface&MockObject $dispatcher;
    private EmailNormalizer $emailNormalizer;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->emailNormalizer = new EmailNormalizer();
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new ClientService(
            $this->dispatcher,
            $this->clientRepository,
            $this->emailNormalizer,
            $this->logger,
        );
    }

    private function setClientId(Client $client, string $id): void
    {
        $reflection = new \ReflectionProperty($client, 'id');
        $reflection->setValue($client, $id);
    }

    public function testGetClientByIdSuccess(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $client = new Client();
        $client->setFirstName('John');
        $client->setLastName('Doe');

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($client);

        $result = $this->service->getClientById($clientId);

        $this->assertSame($client, $result);
        $this->assertSame('John', $result->getFirstName());
        $this->assertSame('Doe', $result->getLastName());
    }

    public function testGetClientByIdNotFound(): void
    {
        // Arrange
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn(null);

        $this->expectException(ClientNotFoundException::class);
        $this->service->getClientById($clientId);
    }

    public function testListClientsReturnsClientsWithPagination(): void
    {
        $client = new Client();

        $this->clientRepository
            ->expects($this->once())
            ->method('findAllActiveClients')
            ->with(2, 25)
            ->willReturn([$client]);

        $this->clientRepository
            ->expects($this->once())
            ->method('countActiveClients')
            ->willReturn(51);

        $result = $this->service->listClients(2, 25);

        $this->assertSame([$client], $result['data']);
        $this->assertSame([
            'total' => 51,
            'page' => 2,
            'limit' => 25,
            'pages' => 3,
        ], $result['pagination']);
    }

    public function testCreateClientSuccess(): void
    {
        $dto = new ClientDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@mail.com',
            phoneNumber: '+37101234567',
        );

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ClientCreatedEvent::class));

        $result = $this->service->createClient($dto);

        $this->assertSame('John', $result->getFirstName());
        $this->assertSame('Doe', $result->getLastName());
        $this->assertSame('john.doe@mail.com', $result->getEmail());
        $this->assertSame('+37101234567', $result->getPhone());
    }

    public function testCreateClientWithEmailSuccess(): void
    {
        $dto = new ClientDto(
            firstName: 'John',
            lastName: 'Doe',
            email: '  JOHN.DOE@MAIL.COM   ',
            phoneNumber: '+37101234567',
        );

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ClientCreatedEvent::class));

        $result = $this->service->createClient($dto);

        $this->assertSame('John', $result->getFirstName());
        $this->assertSame('Doe', $result->getLastName());
        $this->assertSame('john.doe@mail.com', $result->getEmail());
        $this->assertSame('+37101234567', $result->getPhone());
    }

    public function testCreateClientDispatchesEvent(): void
    {
        $dto = new ClientDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@mail.com',
            phoneNumber: '+37101234567',
        );

        $this->clientRepository
            ->method('findActiveClientByEmailOrPhone')
            ->willReturn(null);

        $dispatchedEvent = null;
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use (&$dispatchedEvent) {
                $dispatchedEvent = $event;

                return $event;
            });

        $client = $this->service->createClient($dto);

        $this->assertInstanceOf(ClientCreatedEvent::class, $dispatchedEvent);
        $this->assertSame($client, $dispatchedEvent->getClient());
    }

    public function testUpdateClientByIdSuccess(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $dto = new ClientDto(
            firstName: 'Jane',
            lastName: 'Smith',
            email: 'jane.smith@mail.com',
            phoneNumber: '+37101234561',
        );

        $currentClient = new Client();

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($currentClient);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByEmail')
            ->with('jane.smith@mail.com')
            ->willReturn(null);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByPhoneNumber')
            ->with('+37101234561')
            ->willReturn(null);

        $result = $this->service->updateClientById($clientId, $dto);

        $this->assertSame('Jane', $result->getFirstName());
        $this->assertSame('Smith', $result->getLastName());
        $this->assertSame('jane.smith@mail.com', $result->getEmail());
        $this->assertSame('+37101234561', $result->getPhone());
    }

    public function testUpdateClientWithEmailByIdSuccess(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $dto = new ClientDto(
            firstName: 'Jane',
            lastName: 'Smith',
            email: ' JANE.SMITH@MAIL.COM  ',
            phoneNumber: '+37101234561',
        );

        $currentClient = new Client();

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($currentClient);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByEmail')
            ->with('jane.smith@mail.com')
            ->willReturn(null);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByPhoneNumber')
            ->with('+37101234561')
            ->willReturn(null);

        $result = $this->service->updateClientById($clientId, $dto);

        $this->assertSame('Jane', $result->getFirstName());
        $this->assertSame('Smith', $result->getLastName());
        $this->assertSame('jane.smith@mail.com', $result->getEmail());
        $this->assertSame('+37101234561', $result->getPhone());
    }

    public function testUpdateClientAllowsSameClientEmailAndPhone(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $dto = new ClientDto(
            firstName: 'Jane',
            lastName: 'Smith',
            email: 'john.doe@mail.com',
            phoneNumber: '+37101234567',
        );

        $currentClient = new Client();
        $this->setClientId($currentClient, $clientId);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($currentClient);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByEmail')
            ->with('john.doe@mail.com')
            ->willReturn($currentClient);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByPhoneNumber')
            ->with('+37101234567')
            ->willReturn($currentClient);

        $this->clientRepository
            ->expects($this->once())
            ->method('save')
            ->with($currentClient);

        $result = $this->service->updateClientById($clientId, $dto);

        $this->assertSame($currentClient, $result);
        $this->assertSame('Jane', $result->getFirstName());
        $this->assertSame('Smith', $result->getLastName());
        $this->assertSame('john.doe@mail.com', $result->getEmail());
        $this->assertSame('+37101234567', $result->getPhone());
    }

    public function testUpdateClientByIdNotFound(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $dto = new ClientDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@mail.com',
            phoneNumber: '+37101234567',
        );

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn(null);

        $this->expectException(ClientNotFoundException::class);
        $this->service->updateClientById($clientId, $dto);
    }

    public function testUpdateClientEmailAlreadyExists(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $dto = new ClientDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@mail.com',
            phoneNumber: '+37101234567',
        );

        $currentClient = new Client();
        $otherClient = new Client();

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($currentClient);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByEmail')
            ->with('john.doe@mail.com')
            ->willReturn($otherClient);

        $this->expectException(EmailExistsException::class);
        $this->service->updateClientById($clientId, $dto);
    }

    public function testUpdateClientPhoneAlreadyExists(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $dto = new ClientDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@mail.com',
            phoneNumber: '+37101234567',
        );

        $currentClient = new Client();
        $otherClient = new Client();

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($currentClient);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByEmail')
            ->with('john.doe@mail.com')
            ->willReturn(null);

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientByPhoneNumber')
            ->with('+37101234567')
            ->willReturn($otherClient);

        $this->expectException(PhoneExistsException::class);
        $this->service->updateClientById($clientId, $dto);
    }

    public function testDeleteClientByIdSuccess(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';
        $client = new Client();

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($client);

        $this->clientRepository
            ->expects($this->once())
            ->method('save')
            ->with($client);

        $this->service->deleteClientById($clientId);

        $this->assertNotNull($client->getDeletedAt());
    }

    public function testDeleteClientByIdNotFound(): void
    {
        $clientId = '019e325a-2f22-7ce6-a46a-15606cf7c7cf';

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn(null);

        $this->expectException(ClientNotFoundException::class);
        $this->service->deleteClientById($clientId);
    }
}
