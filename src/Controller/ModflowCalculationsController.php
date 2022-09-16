<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Modflow\ModflowModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class ModflowCalculationsController
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/modflowcalculations", name="modflowcalculation_list", methods={"GET"})
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var ModflowModel $modflowModel */
        $modflowModels = $this->entityManager->getRepository(ModflowModel::class)->findBy(['isArchived' => false]);

        $calculationIds = [];
        foreach ($modflowModels as $modflowModel) {
            if (!$modflowModel->calculation()->latest()) {
                continue;
            }

            $calculationIds[] = $modflowModel->calculation()->latest();
        }

        return new JsonResponse($calculationIds);
    }
}
