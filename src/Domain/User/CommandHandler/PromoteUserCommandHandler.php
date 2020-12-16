<?php

declare(strict_types=1);

namespace App\Domain\User\CommandHandler;

use App\Domain\User\Aggregate\UserAggregate;
use App\Domain\User\Command\PromoteUserCommand;
use App\Domain\User\Event\UserHasBeenPromoted;
use App\Model\ProjectorCollection;
use App\Model\User;
use App\Domain\User\Projection\UserProjector;
use App\Repository\AggregateRepository;
use App\Service\UserManager;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use RuntimeException;

class PromoteUserCommandHandler
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
     * @param PromoteUserCommand $command
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function __invoke(PromoteUserCommand $command)
    {
        $isAdmin = $command->getMetadataByKey('is_admin') === true || $command->getMetadataByKey('user_id') === 'CLI';
        if (!$isAdmin) {
            throw new RuntimeException('Bad credentials. Please use your admin-account.');
        }

        $user = $this->userManager->findUserById($command->userId());
        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $command->userId()));
        }

        if (in_array($command->role(), $user->getRoles(), true)) {
            throw new RuntimeException('Role already exists.');
        }

        $aggregateId = $command->userId();
        $event = UserHasBeenPromoted::fromParams($aggregateId, $command->role());
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
