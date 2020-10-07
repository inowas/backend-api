<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\AddLayerCommand;
use App\Model\Modflow\ModflowModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

final class AddLayerCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AddLayerCommand $command
     * @throws Exception
     */
    public function __invoke(AddLayerCommand $command)
    {
        $modelId = $command->id();
        $userId = $command->metadata()['user_id'];

        $modflowModel = $this->entityManager->getRepository(ModflowModel::class)->findOneBy(['id' => $modelId]);

        if (!$modflowModel instanceof ModflowModel) {
            throw new RuntimeException('ModflowModel not found');
        }

        if ($modflowModel->userId() !== $userId) {
            throw new RuntimeException('The Model cannot be updated due to permission problems.');
        }

        $soilmodel = $modflowModel->soilmodel();
        $soilmodel->addLayer($command->layer());
        $modflowModel->setSoilmodel($soilmodel);

        $this->entityManager->persist($modflowModel);
        $this->entityManager->flush();
    }
}
