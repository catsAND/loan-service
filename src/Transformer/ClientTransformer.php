<?php declare(strict_types=1);

namespace App\Transformer;

use App\Entity\Client;

final class ClientTransformer
{
    /**
     * @return array{
     *     id: string|null,
     *     firstName: string,
     *     lastName: string,
     *     email: string,
     *     phoneNumber: string
     * }
     */
    public function transform(Client $client): array
    {
        return [
            'id' => $client->getId(),
            'firstName' => $client->getFirstName(),
            'lastName' => $client->getLastName(),
            'email' => $client->getEmail(),
            'phoneNumber' => $client->getPhone(),
        ];
    }

    /**
     * @param list<Client> $clients
     *
     * @return list<array{
     *     id: string|null,
     *     firstName: string,
     *     lastName: string,
     *     email: string,
     *     phoneNumber: string
     * }>
     */
    public function transformCollection(array $clients): array
    {
        return array_map([$this, 'transform'], $clients);
    }
}
