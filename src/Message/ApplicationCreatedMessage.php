<?php declare(strict_types=1);

namespace App\Message;

use App\Entity\Application;

final readonly class ApplicationCreatedMessage
{
    public function __construct(private Application $application)
    {
    }

    public function getApplication(): Application
    {
        return $this->application;
    }
}
