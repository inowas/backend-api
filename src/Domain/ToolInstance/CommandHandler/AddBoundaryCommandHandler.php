<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\AddBoundaryCommand;
use App\Model\Modflow\ModflowModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

final class AddBoundaryCommandHandler
{
    /** @var EntityManager */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AddBoundaryCommand $command
     * @throws Exception
     */
    public function __invoke(AddBoundaryCommand $command)
    {
        $isAdmin = $command->metadata()['is_admin'];
        $modelId = $command->id();
        $userId = $command->metadata()['user_id'];

        $modflowModel = $this->entityManager->getRepository(ModflowModel::class)->findOneBy(['id' => $modelId]);

        if (!$isAdmin && !$modflowModel instanceof ModflowModel) {
            throw new RuntimeException('ModflowModel not found');
        }

        if ($modflowModel->userId() !== $userId) {
            throw new RuntimeException('The Model cannot be updated due to permission problems.');
        }

        $boundaries = $modflowModel->boundaries();
        $boundaries->addBoundary($command->boundary());
        $modflowModel->setBoundaries($boundaries);

        $this->entityManager->persist($modflowModel);
        $this->entityManager->flush();
    }
}
