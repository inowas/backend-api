<?php

declare(strict_types=1);

namespace App\Domain\User\CommandHandler;

use App\Domain\User\Aggregate\UserAggregate;
use App\Model\ProjectorCollection;
use App\Model\User;
use App\Domain\User\Command\DeleteUserCommand;
use App\Domain\User\Event\UserHasBeenDeleted;
use App\Domain\User\Projection\UserProjector;
use App\Repository\AggregateRepository;
use App\Service\UserManager;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use RuntimeException;

class DeleteUserCommandHandler
{
    private AggregateRepository $aggregateRepository;
    private ProjectorCollection $projectors;
    private UserManager $userManager;

    public function __construct(AggregateRepository $aggregateRepository, UserManager $userManager, ProjectorCollection $projectors)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->projectors = $projectors;
        $this->userManager = $userManager;
    }

    /**
     * @param DeleteUserCommand $command
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function __invoke(DeleteUserCommand $command)
    {
        $isAdmin = $command->getMetadataByKey('is_admin') === true || $command->getMetadataByKey('user_id') === 'CLI';
        if (!$isAdmin) {
            throw new RuntimeException('Bad credentials. Please use your admin-account.');
        }

        $executorId = $command->metadata()['user_id'];
        $userToDeleteId = $command->userId();

        if ($executorId === $userToDeleteId) {
            throw new RuntimeException('You cannot delete your own identity. Please ask another admin.');
        }


        $user = $this->userManager->findUserById($userToDeleteId);
        if (!$user instanceof User) {
            throw new RuntimeException('User not found, already deleted?');
        }

        $aggregateId = $userToDeleteId;
        $event = UserHasBeenDeleted::fromParams($aggregateId);
        $aggregate = $this->aggregateRepository->findAggregateById(UserAggregate::class, $aggregateId);
        $aggregate->apply($event);

        $this->aggregateRepository->storeEvent($event);
        $projector = $this->projectors->getProjector(UserProjector::class);
        if (!$projector) {
            throw new RuntimeException(sprintf('Projector %s not found.', UserProjector::class));
        }
        $projector->apply($event);
    }
}
