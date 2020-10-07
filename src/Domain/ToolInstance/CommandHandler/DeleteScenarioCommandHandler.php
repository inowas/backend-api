<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\DeleteScenarioCommand;
use App\Model\ScenarioAnalysis\ScenarioAnalysis;
use App\Model\SimpleTool\SimpleTool;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class DeleteScenarioCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DeleteScenarioCommand $command
     * @throws Exception
     */
    public function __invoke(DeleteScenarioCommand $command)
    {
        $id = $command->id();
        $userId = $command->metadata()['user_id'];


        $simpleTool = $this->entityManager->getRepository(SimpleTool::class)->findOneBy(['id' => $id]);
        if (!$simpleTool instanceof SimpleTool) {
            throw new RuntimeException('ToolInstance not found');
        }

        if ($simpleTool->userId() !== $userId) {
            throw new RuntimeException('The scenarioAnalysis cannot be changed due to permission problems.');
        }

        $scenarioAnalysis = ScenarioAnalysis::fromArray($simpleTool->data());
        $scenarioAnalysis->removeScenarioId($command->scenarioId());

        $simpleTool->setData($scenarioAnalysis->toArray());
        $this->entityManager->persist($simpleTool);
        $this->entityManager->flush();
    }
}
