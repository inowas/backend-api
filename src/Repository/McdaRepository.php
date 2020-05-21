<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Mcda\Mcda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class McdaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mcda::class);
    }
}
