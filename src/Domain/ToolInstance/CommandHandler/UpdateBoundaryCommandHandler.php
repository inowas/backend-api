<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\UpdateBoundaryCommand;
use App\Model\Modflow\ModflowModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

final class UpdateBoundaryCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UpdateBoundaryCommand $command
     * @throws Exception
     */
    public function __invoke(UpdateBoundaryCommand $command)
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

        $boundaries = $modflowModel->boundaries();
        $boundaries->updateBoundary($command->boundary());
        $modflowModel->setBoundaries($boundaries);

        $this->entityManager->persist($modflowModel);
        $this->entityManager->flush();
    }
}
