<?php declare(strict_types=1);

namespace App\Service;

use App\Dto\ApplicationDto;
use App\Dto\UpdateApplicationDto;
use App\Entity\Application;
use App\Exception\ApplicationNotFoundException;
use App\Exception\ClientNotFoundException;
use App\Message\ApplicationCreatedMessage;
use App\Repository\ApplicationRepository;
use App\Repository\ClientRepository;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ApplicationService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ClientRepository $clientRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function getApplicationById(string $id): Application
    {
        $application = $this->applicationRepository->findActiveApplicationById($id);

        if (!$application) {
            throw new ApplicationNotFoundException($id);
        }

        return $application;
    }

    public function createApplication(ApplicationDto $applicationDto): Application
    {
        $client = $this->clientRepository->findActiveClientById($applicationDto->clientId);
        if (!$client) {
            throw new ClientNotFoundException($applicationDto->clientId);
        }

        $application = $this->applicationRepository->createApplication($applicationDto, $client);

        $this->messageBus->dispatch(new ApplicationCreatedMessage($application));

        return $application;
    }

    public function updateApplicationById(string $id, UpdateApplicationDto $applicationDto): Application
    {
        $application = $this->applicationRepository->findActiveApplicationById($id);

        if (!$application) {
            throw new ApplicationNotFoundException($id);
        }

        $application = $this->applicationRepository->updateApplication($application, $applicationDto);

        return $application;
    }

    public function deleteApplicationById(string $id): void
    {
        $application = $this->applicationRepository->findActiveApplicationById($id);

        if (!$application) {
            throw new ApplicationNotFoundException($id);
        }

        $application->setDeletedAt(new \DateTimeImmutable());
        $this->applicationRepository->save($application);
    }
}
