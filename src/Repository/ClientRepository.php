<?php declare(strict_types=1);

namespace App\Repository;

use App\Dto\ClientDto;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findAllActiveClients(int $page = 1, int $limit = 100): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findActiveClientById(string $id): ?Client
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Client $client): void
    {
        $this->getEntityManager()->persist($client);
        $this->getEntityManager()->flush();
    }

    public function findActiveClientByEmailOrPhone(string $email, string $phone): ?Client
    {
        return $this->createQueryBuilder('c')
            ->where('c.email = :email OR c.phone = :phone')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('email', $email)
            ->setParameter('phone', $phone)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveClientByEmail(string $email): ?Client
    {
        return $this->createQueryBuilder('c')
            ->where('c.email = :email')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveClientByPhone(string $phone): ?Client
    {
        return $this->createQueryBuilder('c')
            ->where('c.phone = :phone')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('phone', $phone)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function createClient(ClientDto $clientDto): Client
    {
        $client = new Client();
        $client
            ->setFirstName($clientDto->firstName)
            ->setLastName($clientDto->lastName)
            ->setEmail($clientDto->email)
            ->setPhone($clientDto->phoneNumber);

        $this->save($client);

        return $client;
    }

    public function updateClient(Client $client, ClientDto $clientDto): Client
    {
        $client
            ->setFirstName($clientDto->firstName)
            ->setLastName($clientDto->lastName)
            ->setEmail($clientDto->email)
            ->setPhone($clientDto->phoneNumber);

        $this->save($client);

        return $client;
    }
}
