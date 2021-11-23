<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\CreateScenarioAnalysisCommand;
use App\Model\Modflow\ModflowModel;
use App\Model\Modflow\Packages;
use App\Model\ScenarioAnalysis\ScenarioAnalysis;
use App\Model\SimpleTool\SimpleTool;
use App\Model\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class CreateScenarioAnalysisCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CreateScenarioAnalysisCommand $command
     * @throws Exception
     */
    public function __invoke(CreateScenarioAnalysisCommand $command)
    {
        $id = $command->id();
        $userId = $command->metadata()['user_id'];
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id %s not found.', $userId));
        }

        $tool = 'T07';
        $metadata = $command->toolMetadata();
        $instance = SimpleTool::createWithParams($id, $user, $tool, $metadata);

        // Make a clone from the basemodel with a new id
        $baseModelId = $command->basemodelId();
        $newModelId = Uuid::uuid4()->toString();

        $this->cloneModel($baseModelId, $newModelId, $user);

        $scenarioAnalysis = ScenarioAnalysis::createWithBaseId($newModelId);
        $instance->setData($scenarioAnalysis->toArray());
        $this->entityManager->persist($instance);
        $this->entityManager->flush();
    }

    /**
     * @param $modelId
     * @param $newModelId
     * @param $user
     * @throws Exception
     */
    private function cloneModel($modelId, $newModelId, $user): void
    {
        $modelToClone = $this->entityManager->getRepository(ModflowModel::class)->findOneBy(['id' => $modelId]);

        if (!$modelToClone instanceof ModflowModel) {
            throw new RuntimeException('Model not found.');
        }

        $clonedModel = clone $modelToClone;
        $clonedModel->setId($newModelId);
        $clonedModel->setIsScenario(true);
        $clonedModel->setUser($user);
        $clonedModel->setName(sprintf('%s (basemodel)', $clonedModel->name()));
        $this->entityManager->persist($clonedModel);
        $this->entityManager->flush();

        /** @var Packages $packages */
        $packages = $this->entityManager->getRepository(Packages::class)->findOneBy(['id' => $modelId]);
        if (!$packages instanceof Packages) {
            throw new RuntimeException('Packages not found');
        }

        $packagesClone = clone $packages->clone($newModelId);

        $this->entityManager->clear(Packages::class);
        $this->entityManager->persist($packagesClone);
        $this->entityManager->flush();
    }
}
