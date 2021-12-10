<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\CreateModflowModelCommand;
use App\Model\Modflow\ModflowModel;
use App\Model\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class CreateModflowModelCommandHandler
{
    /** @var EntityManager */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CreateModflowModelCommand $command
     * @throws Exception
     */
    public function __invoke(CreateModflowModelCommand $command)
    {
        $id = $command->id();
        $userId = $command->metadata()['user_id'];
        $metadata = $command->toolMetadata();
        $discretization = $command->discretization();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id %s not found.', $userId));
        }

        /** @var ModflowModel $modflowModel */
        $modflowModel = ModflowModel::createWithParams($id, $user, 'T03', $metadata);
        $modflowModel->setDiscretization($discretization);
        $this->entityManager->persist($modflowModel);
        $this->entityManager->flush();
    }
}
