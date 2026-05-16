<?php declare(strict_types=1);

namespace App\Repository;

use App\Dto\ApplicationDto;
use App\Dto\UpdateApplicationDto;
use App\Entity\Application;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function findAllActiveApplications(int $page = 1, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.deletedAt IS NULL')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findActiveApplicationById(string $id): ?Application
    {
        return $this->createQueryBuilder('a')
            ->where('a.id = :id')
            ->andWhere('a.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Application $application): void
    {
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }

    public function createApplication(ApplicationDto $applicationDto, Client $client): Application
    {
        $application = new Application()
            ->setClient($client)
            ->setTerm($applicationDto->term)
            ->setAmount((string)$applicationDto->amount)
            ->setCurrency($applicationDto->currency);

        $this->save($application);

        return $application;
    }

    public function updateApplication(Application $application, UpdateApplicationDto $applicationDto): Application
    {
        $application
            ->setTerm($applicationDto->term)
            ->setAmount((string)$applicationDto->amount)
            ->setCurrency($applicationDto->currency);

        $this->save($application);

        return $application;
    }
}
