<?php declare(strict_types=1);

namespace App\Transformer;

use App\Entity\Application;

final class ApplicationTransformer
{
    /**
     * @return array{
     *     id: string|null,
     *     clientId: string|null,
     *     term: int,
     *     amount: string,
     *     currency: \App\Enum\CurrencyEnum
     * }
     */
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

    /**
     * @param list<Application> $applications
     *
     * @return list<array{
     *     id: string|null,
     *     clientId: string|null,
     *     term: int,
     *     amount: string,
     *     currency: \App\Enum\CurrencyEnum
     * }>
     */
    public function transformCollection(array $applications): array
    {
        return array_map([$this, 'transform'], $applications);
    }
}
