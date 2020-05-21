<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Modflow\ModflowModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ModflowModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModflowModel::class);
    }
}
