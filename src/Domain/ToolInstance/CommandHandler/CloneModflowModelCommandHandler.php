<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\CommandHandler;

use App\Domain\ToolInstance\Command\CloneModflowModelCommand;
use App\Model\Modflow\ModflowModel;
use App\Model\Modflow\Packages;
use App\Model\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class CloneModflowModelCommandHandler
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CloneModflowModelCommand $command
     * @throws Exception
     */
    public function __invoke(CloneModflowModelCommand $command)
    {
        $isAdmin = $command->metadata()['is_admin'];
        $userId = $command->metadata()['user_id'];
        $originId = $command->id();
        $cloneId = $command->newId();
        $cloneAsTool = $command->isTool();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);
        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id %s not found.', $userId));
        }

        $original = $this->entityManager->getRepository(ModflowModel::class)->findOneBy(['id' => $originId]);

        if (!$original instanceof ModflowModel) {
            throw new RuntimeException('ModflowModel not found');
        }

        # The user needs to be the owner of the model or the model has to be public
        $canBeCloned = ($userId === $original->userId() || true === $original->isPublic());
        if (!$isAdmin && !$canBeCloned) {
            throw new RuntimeException('The ModflowModel cannot be cloned due to permission problems.');
        }

        $clone = clone $original;
        $clone->setId($cloneId);
        $clone->setUser($user);
        $clone->setIsScenario(!$cloneAsTool);

        $this->entityManager->persist($clone);
        $this->entityManager->flush();

        /** @var Packages $packages */
        $packages = $this->entityManager->getRepository(Packages::class)->findOneBy(['id' => $originId]);
        if (!$packages instanceof Packages) {
            throw new RuntimeException('Packages not found');
        }

        $packagesClone = $packages->clone($cloneId);

        $this->entityManager->clear(Packages::class);
        $this->entityManager->persist($packagesClone);
        $this->entityManager->flush();
    }
}
