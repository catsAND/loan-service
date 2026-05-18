<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @return list<Client>
     */
    public function findAllActiveClients(int $page = 1, int $limit = 100): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countActiveClients(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
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
            ->where('(c.email = :email OR c.phone = :phone)')
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

    public function findActiveClientByPhoneNumber(string $phone): ?Client
    {
        return $this->createQueryBuilder('c')
            ->where('c.phone = :phone')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('phone', $phone)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
