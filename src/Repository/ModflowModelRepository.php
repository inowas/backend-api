<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Modflow\ModflowModel;
use App\Model\User;
use Doctrine\Persistence\ManagerRegistry;

class ModflowModelRepository extends ToolsRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModflowModel::class);
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
                ->andWhere('t.isScenario = :isScenario')
                ->setParameter('tool', $tool . '%')
                ->setParameter('isPublic', $isPublic)
                ->setParameter('isArchived', $isArchived)
                ->setParameter('isScenario', false)
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('t')
            ->andWhere('t.tool LIKE :tool')
            ->andWhere('t.user = :user')
            ->andWhere('t.isArchived = :isArchived')
            ->andWhere('t.isScenario = :isScenario')
            ->setParameter('tool', $tool . '%')
            ->setParameter('user', $user)
            ->setParameter('isArchived', $isArchived)
            ->setParameter('isScenario', false)
            ->getQuery()
            ->getResult();
    }
}
