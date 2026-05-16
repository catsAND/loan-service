<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ApplicationCreatedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ApplicationCreatedMessageHandler
{
    public function __invoke(ApplicationCreatedMessage $message): void
    {
        $application = $message->getApplication();

        // Perform any additional actions like sending notification.
    }
}
