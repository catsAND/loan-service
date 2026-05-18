<?php declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\ApplicationDto;
use App\Dto\UpdateApplicationDto;
use App\Entity\Application;
use App\Entity\Client;
use App\Enum\CurrencyEnum;
use App\Exception\ApplicationNotFoundException;
use App\Exception\ClientNotFoundException;
use App\Message\ApplicationCreatedMessage;
use App\Repository\ApplicationRepository;
use App\Repository\ClientRepository;
use App\Service\ApplicationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class ApplicationServiceTest extends TestCase
{
    private ApplicationService $service;
    private ApplicationRepository&MockObject $applicationRepository;
    private ClientRepository&MockObject $clientRepository;
    private MessageBusInterface&MockObject $messageBus;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->applicationRepository = $this->createMock(ApplicationRepository::class);
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new ApplicationService(
            $this->applicationRepository,
            $this->clientRepository,
            $this->messageBus,
            $this->logger,
        );
    }

    public function testGetApplicationByIdSuccess(): void
    {
        $applicationId = '550e8400-e29b-41d4-a716-446655440000';
        $application = new Application();
        $application->setTerm(30);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findActiveApplicationById')
            ->with($applicationId)
            ->willReturn($application);

        $result = $this->service->getApplicationById($applicationId);

        $this->assertSame($application, $result);
        $this->assertSame(30, $result->getTerm());
    }

    public function testGetApplicationByIdNotFound(): void
    {
        $applicationId = '550e8400-e29b-41d4-a716-446655440000';

        $this->applicationRepository
            ->expects($this->once())
            ->method('findActiveApplicationById')
            ->with($applicationId)
            ->willReturn(null);

        $this->expectException(ApplicationNotFoundException::class);
        $this->service->getApplicationById($applicationId);
    }

    public function testListApplicationsReturnsApplicationsWithPagination(): void
    {
        $application = new Application();

        $this->applicationRepository
            ->expects($this->once())
            ->method('findAllActiveApplications')
            ->with(3, 20)
            ->willReturn([$application]);

        $this->applicationRepository
            ->expects($this->once())
            ->method('countActiveApplications')
            ->willReturn(41);

        $result = $this->service->listApplications(3, 20);

        $this->assertSame([$application], $result['data']);
        $this->assertSame([
            'total' => 41,
            'page' => 3,
            'limit' => 20,
            'pages' => 3,
        ], $result['pagination']);
    }

    public function testCreateApplicationSuccess(): void
    {
        $clientId = '550e8400-e29b-41d4-a716-446655440000';
        $dto = new ApplicationDto(
            clientId: $clientId,
            term: 30,
            amount: '3000.00',
            currency: CurrencyEnum::EUR,
        );

        $client = new Client();
        $application = new Application();

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn($client);

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ApplicationCreatedMessage::class))
            ->willReturn(new Envelope($application));

        $result = $this->service->createApplication($dto);

        $this->assertSame(30, $result->getTerm());
        $this->assertSame('3000.00', $result->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $result->getCurrency());
    }

    public function testCreateApplicationClientNotFound(): void
    {
        $clientId = '550e8400-e29b-41d4-a716-446655440000';
        $dto = new ApplicationDto(
            clientId: $clientId,
            term: 30,
            amount: '3000.00',
            currency: CurrencyEnum::EUR,
        );

        $this->clientRepository
            ->expects($this->once())
            ->method('findActiveClientById')
            ->with($clientId)
            ->willReturn(null);

        $this->expectException(ClientNotFoundException::class);
        $this->service->createApplication($dto);
    }

    public function testCreateApplicationDispatchesMessage(): void
    {
        $clientId = '550e8400-e29b-41d4-a716-446655440000';
        $dto = new ApplicationDto(
            clientId: $clientId,
            term: 30,
            amount: '3000.00',
            currency: CurrencyEnum::EUR,
        );

        $client = new Client();

        $this->clientRepository
            ->method('findActiveClientById')
            ->willReturn($client);

        $dispatchedMessage = null;
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessage) {
                $dispatchedMessage = $message;

                return new Envelope($message);
            });

        $this->service->createApplication($dto);

        $this->assertInstanceOf(ApplicationCreatedMessage::class, $dispatchedMessage);
    }

    public function testUpdateApplicationByIdSuccess(): void
    {
        $applicationId = '550e8400-e29b-41d4-a716-446655440000';
        $dto = new UpdateApplicationDto(
            term: 24,
            amount: '1500.00',
            currency: CurrencyEnum::EUR,
        );

        $application = new Application();

        $this->applicationRepository
            ->expects($this->once())
            ->method('findActiveApplicationById')
            ->with($applicationId)
            ->willReturn($application);

        $this->applicationRepository
            ->expects($this->once())
            ->method('save')
            ->with($application);

        $result = $this->service->updateApplicationById($applicationId, $dto);

        $this->assertSame(24, $result->getTerm());
        $this->assertSame('1500.00', $result->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $result->getCurrency());
    }

    public function testUpdateApplicationByIdNotFound(): void
    {
        $applicationId = '550e8400-e29b-41d4-a716-446655440000';
        $dto = new UpdateApplicationDto(
            term: 24,
            amount: '1500.00',
            currency: CurrencyEnum::EUR,
        );

        $this->applicationRepository
            ->expects($this->once())
            ->method('findActiveApplicationById')
            ->with($applicationId)
            ->willReturn(null);

        $this->expectException(ApplicationNotFoundException::class);
        $this->service->updateApplicationById($applicationId, $dto);
    }

    public function testDeleteApplicationByIdSuccess(): void
    {
        $applicationId = '550e8400-e29b-41d4-a716-446655440000';
        $application = new Application();

        $this->applicationRepository
            ->expects($this->once())
            ->method('findActiveApplicationById')
            ->with($applicationId)
            ->willReturn($application);

        $this->applicationRepository
            ->expects($this->once())
            ->method('save')
            ->with($application);

        $this->service->deleteApplicationById($applicationId);

        $this->assertNotNull($application->getDeletedAt());
    }

    public function testDeleteApplicationByIdNotFound(): void
    {
        $applicationId = '550e8400-e29b-41d4-a716-446655440000';

        $this->applicationRepository
            ->expects($this->once())
            ->method('findActiveApplicationById')
            ->with($applicationId)
            ->willReturn(null);

        $this->applicationRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(ApplicationNotFoundException::class);
        $this->service->deleteApplicationById($applicationId);
    }

    public function testCreateApplicationWithDifferentTerms(): void
    {
        $terms = [10, 15, 30];

        foreach ($terms as $term) {
            $this->setUp();

            $clientId = '550e8400-e29b-41d4-a716-446655440000';
            $dto = new ApplicationDto(
                clientId: $clientId,
                term: $term,
                amount: '3000.00',
                currency: CurrencyEnum::EUR,
            );

            $client = new Client();
            $application = new Application();

            $this->clientRepository
                ->expects($this->once())
                ->method('findActiveClientById')
                ->with($clientId)
                ->willReturn($client);

            $this->messageBus
                ->expects($this->once())
                ->method('dispatch')
                ->willReturn(new Envelope($application));

            $result = $this->service->createApplication($dto);

            $this->assertSame($term, $result->getTerm());
        }
    }

    public function testUpdateApplicationWithDifferentAmounts(): void
    {
        $testCases = [
            ['100.00', '100.00'],
            ['1500.50', '1500.50'],
            ['5000.00', '5000.00'],
        ];

        foreach ($testCases as [$expectedAmount, $inputAmount]) {
            $this->setUp();

            $applicationId = '550e8400-e29b-41d4-a716-446655440000';
            $dto = new UpdateApplicationDto(
                term: 30,
                amount: $inputAmount,
                currency: CurrencyEnum::EUR,
            );

            $application = new Application();

            $this->applicationRepository
                ->expects($this->once())
                ->method('findActiveApplicationById')
                ->with($applicationId)
                ->willReturn($application);

            $this->applicationRepository
                ->expects($this->once())
                ->method('save')
                ->with($application);

            $result = $this->service->updateApplicationById($applicationId, $dto);

            $this->assertSame($expectedAmount, $result->getAmount());
        }
    }
}
