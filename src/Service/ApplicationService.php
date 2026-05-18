<?php declare(strict_types=1);

namespace App\Service;

use App\Dto\ApplicationDto;
use App\Dto\UpdateApplicationDto;
use App\Entity\Application;
use App\Exception\ApplicationNotFoundException;
use App\Exception\ClientNotFoundException;
use App\Exception\InvalidUuidFormatException;
use App\Message\ApplicationCreatedMessage;
use App\Repository\ApplicationRepository;
use App\Repository\ClientRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class ApplicationService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ClientRepository $clientRepository,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    public function getApplicationById(string $id): Application
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidUuidFormatException($id);
        }

        $application = $this->applicationRepository->findActiveApplicationById($id);

        if (!$application) {
            throw new ApplicationNotFoundException($id);
        }

        return $application;
    }

    /**
     * @return array{
     *     data: list<Application>,
     *     pagination: array{total: int, page: int, limit: int, pages: int}
     * }
     */
    public function listApplications(int $page, int $limit): array
    {
        $data = $this->applicationRepository->findAllActiveApplications($page, $limit);
        $count = $this->applicationRepository->countActiveApplications();

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

    public function createApplication(ApplicationDto $applicationDto): Application
    {
        try {
            if (!Uuid::isValid($applicationDto->clientId)) {
                throw new InvalidUuidFormatException($applicationDto->clientId);
            }

            $client = $this->clientRepository->findActiveClientById($applicationDto->clientId);
            if (!$client) {
                throw new ClientNotFoundException($applicationDto->clientId);
            }

            $application = new Application()
                ->setClient($client)
                ->setTerm($applicationDto->term)
                ->setAmount($applicationDto->amount)
                ->setCurrency($applicationDto->currency);

            $this->applicationRepository->save($application);

            $this->logger->info('Application created', ['applicationId' => $application->getId()]);

            $this->messageBus->dispatch(new ApplicationCreatedMessage($application));

            return $application;
        } catch (\Exception $e) {
            $this->logger->error('Error creating application', ['exception' => $e]);
            throw $e;
        }
    }

    public function updateApplicationById(string $id, UpdateApplicationDto $applicationDto): Application
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidUuidFormatException($id);
        }

        $application = $this->applicationRepository->findActiveApplicationById($id);

        if (!$application) {
            throw new ApplicationNotFoundException($id);
        }

        $application
            ->setTerm($applicationDto->term)
            ->setAmount($applicationDto->amount)
            ->setCurrency($applicationDto->currency);

        $this->applicationRepository->save($application);

        $this->logger->info('Application updated', ['applicationId' => $application->getId()]);

        return $application;
    }

    public function deleteApplicationById(string $id): void
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidUuidFormatException($id);
        }

        $application = $this->applicationRepository->findActiveApplicationById($id);

        if (!$application) {
            throw new ApplicationNotFoundException($id);
        }

        $application->setDeletedAt(new \DateTimeImmutable());
        $this->applicationRepository->save($application);

        $this->logger->info('Application deleted', ['applicationId' => $application->getId()]);
    }
}
