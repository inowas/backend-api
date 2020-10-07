<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\DeleteToolInstanceCommand;
use App\Model\Mcda\Mcda;
use App\Model\Modflow\ModflowModel;
use App\Model\SimpleTool\SimpleTool;
use App\Model\ToolInstance;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class DeleteToolInstanceCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DeleteToolInstanceCommand $command
     * @throws Exception
     */
    public function __invoke(DeleteToolInstanceCommand $command)
    {
        $userId = $command->metadata()['user_id'];
        $id = $command->id();


        $toolInstance = $this->entityManager->getRepository(SimpleTool::class)->findOneBy(['id' => $id]);

        if (null === $toolInstance) {
            $toolInstance = $this->entityManager->getRepository(Mcda::class)->findOneBy(['id' => $id]);
        }

        if (null === $toolInstance) {
            $toolInstance = $this->entityManager->getRepository(ModflowModel::class)->findOneBy(['id' => $id]);
        }

        if (!$toolInstance instanceof ToolInstance) {
            throw new RuntimeException('ToolInstance not found');
        }

        if ($toolInstance->userId() !== $userId) {
            throw new RuntimeException('The tool cannot be deleted due to permission problems.');
        }

        $toolInstance->setIsArchived(true);
        $this->entityManager->persist($toolInstance);
        $this->entityManager->flush();
    }
}
