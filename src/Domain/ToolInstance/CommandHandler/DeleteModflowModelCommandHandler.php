<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\DeleteModflowModelCommand;
use App\Model\Modflow\ModflowModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class DeleteModflowModelCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DeleteModflowModelCommand $command
     * @throws \Exception
     */
    public function __invoke(DeleteModflowModelCommand $command)
    {
        $userId = $command->metadata()['user_id'];
        $id = $command->id();

        $modflowModel = $this->entityManager->getRepository(ModflowModel::class)->findOneBy(['id' => $id]);

        if (!$modflowModel instanceof ModflowModel) {
            throw new RuntimeException('ToolInstance not found');
        }

        if ($modflowModel->userId() !== $userId) {
            throw new RuntimeException('The modflowModel cannot be deleted due to permission problems.');
        }

        $modflowModel->setIsArchived(true);
        $this->entityManager->persist($modflowModel);
        $this->entityManager->flush();
    }
}
