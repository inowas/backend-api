<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\DeleteScenarioAnalysisCommand;
use App\Model\SimpleTool\SimpleTool;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class DeleteScenarioAnalysisCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DeleteScenarioAnalysisCommand $command
     * @throws \Exception
     */
    public function __invoke(DeleteScenarioAnalysisCommand $command)
    {
        $id = $command->id();
        $isAdmin = $command->metadata()['is_admin'];
        $userId = $command->metadata()['user_id'];

        $simpleTool = $this->entityManager->getRepository(SimpleTool::class)->findOneBy(['id' => $id]);

        if (!$simpleTool instanceof SimpleTool) {
            throw new RuntimeException('ToolInstance not found');
        }

        if (!$isAdmin && $simpleTool->userId() !== $userId) {
            throw new RuntimeException('The scenarioAnalysis cannot be deleted due to permission problems.');
        }

        $simpleTool->setIsArchived(true);
        $this->entityManager->persist($simpleTool);
        $this->entityManager->flush();
    }
}
