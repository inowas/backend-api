<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Mcda\Mcda;
use App\Model\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class McdaRepository extends ServiceEntityRepository implements ToolRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mcda::class);
    }

    /**
     * @param string $tool
     * @param User $user
     * @param bool $isPublic
     * @param bool $isArchived
     * @return array
     */
    public function getTool(string $tool, User $user, bool $isPublic, bool $isArchived): array
    {

        if ($isPublic) {
            return $this->createQueryBuilder('t')
                ->andWhere('t.tool LIKE :tool')
                ->andWhere('t.isPublic = :isPublic')
                ->andWhere('t.isArchived = :isArchived')
                ->setParameter('tool', $tool . '%')
                ->setParameter('isPublic', $isPublic)
                ->setParameter('isArchived', $isArchived)
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('t')
            ->andWhere('t.tool LIKE :tool')
            ->andWhere('t.user = :user')
            ->andWhere('t.isArchived = :isArchived')
            ->setParameter('tool', $tool . '%')
            ->setParameter('user', $user)
            ->setParameter('isArchived', $isArchived)
            ->getQuery()
            ->getResult();
    }
}
