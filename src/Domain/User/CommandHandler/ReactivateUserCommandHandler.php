<?php

declare(strict_types=1);

namespace App\Domain\User\CommandHandler;

use App\Domain\User\Aggregate\UserAggregate;
use App\Model\ProjectorCollection;
use App\Model\User;
use App\Domain\User\Command\ReactivateUserCommand;
use App\Domain\User\Event\UserHasBeenReactivated;
use App\Domain\User\Projection\UserProjector;
use App\Repository\AggregateRepository;
use App\Service\UserManager;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use RuntimeException;

class ReactivateUserCommandHandler
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
     * @param ReactivateUserCommand $command
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function __invoke(ReactivateUserCommand $command)
    {
        $isAdmin = $command->getMetadataByKey('is_admin') === true || $command->getMetadataByKey('user_id') === 'CLI';
        if (!$isAdmin) {
            throw new RuntimeException('Bad credentials. Please use your admin-account.');
        }

        $isAdmin = $command->metadata()['is_admin'];
        $userId = $command->metadata()['user_id'];

        if (($isAdmin && $command->userId())) {
            $userId = $command->userId();
        }

        // Is it different from the old one?
        $user = $this->userManager->findUserById($userId);

        if (!$user instanceof User) {
            throw new RuntimeException('User not found');
        }

        $aggregateId = $userId;
        $event = UserHasBeenReactivated::fromParams($aggregateId);
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
