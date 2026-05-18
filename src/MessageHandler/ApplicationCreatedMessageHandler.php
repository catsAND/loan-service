<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ApplicationCreatedMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ApplicationCreatedMessageHandler
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(ApplicationCreatedMessage $message): void
    {
        $application = $message->getApplication();

        $this->logger->info('Application created message handled', ['message' => $message, 'applicationId' => $application->getId()]);
    }
}
