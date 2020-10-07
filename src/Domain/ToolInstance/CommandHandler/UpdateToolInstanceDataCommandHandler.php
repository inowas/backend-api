<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\UpdateToolInstanceDataCommand;
use App\Model\Mcda\Mcda;
use App\Model\Modflow\ModflowModel;
use App\Model\SimpleTool\SimpleTool;
use App\Model\ToolInstance;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class UpdateToolInstanceDataCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UpdateToolInstanceDataCommand $command
     * @throws Exception
     */
    public function __invoke(UpdateToolInstanceDataCommand $command)
    {
        $userId = $command->metadata()['user_id'];
        $id = $command->id();

        $toolInstance = null;
        $tools = [Mcda::class, ModflowModel::class, SimpleTool::class];
        foreach ($tools as $toolClass) {
            /** @var ToolInstance $toolInstance */
            $toolInstance = $this->entityManager->getRepository($toolClass)->findOneBy(['id' => $id]);
            if ($toolInstance instanceof ToolInstance) {
                break 1;
            }
        }

        if (!$toolInstance instanceof ToolInstance) {
            throw new RuntimeException('Tool not found');
        }

        if ($toolInstance->userId() !== $userId) {
            throw new RuntimeException('The tool cannot be updated due to permission problems.');
        }

        $toolInstance->setData($command->data());
        $this->entityManager->persist($toolInstance);
        $this->entityManager->flush();
    }
}
