<?php declare(strict_types=1);

namespace App\Transformer;

use App\Entity\Application;

final class ApplicationTransformer
{
    public function transform(Application $application): array
    {
        return [
            'id' => $application->getId(),
            'clientId' => $application->getClient()->getId(),
            'term' => $application->getTerm(),
            'amount' => $application->getAmount(),
            'currency' => $application->getCurrency(),
        ];
    }

    public function transformCollection(array $applications): array
    {
        return array_map([$this, 'transform'], $applications);
    }
}
