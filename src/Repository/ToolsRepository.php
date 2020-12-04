<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class ToolsRepository extends ServiceEntityRepository implements ToolRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function getAllTools(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.isArchived = :isArchived')
            ->andWhere('t.isScenario = :isScenario')
            ->setParameter('isArchived', false)
            ->setParameter('isScenario', false)
            ->getQuery()
            ->getResult();
    }
}
