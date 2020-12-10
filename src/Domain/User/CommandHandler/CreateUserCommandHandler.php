<?php

declare(strict_types=1);

namespace App\Domain\User\CommandHandler;

use App\Domain\User\Aggregate\UserAggregate;
use App\Domain\User\Command\CreateUserCommand;
use App\Domain\User\Event\UserHasBeenCreated;
use App\Domain\User\Projection\UserProjector;
use App\Model\ProjectorCollection;
use App\Repository\AggregateRepository;
use App\Service\UserManager;
use Exception;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class CreateUserCommandHandler
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
     * @param CreateUserCommand $command
     * @throws Exception
     */
    public function __invoke(CreateUserCommand $command)
    {
        $isAdmin = $command->getMetadataByKey('is_admin') === true || $command->getMetadataByKey('user_id') === 'CLI';
        if (!$isAdmin) {
            throw new RuntimeException('Bad credentials. Please use your admin-account.');
        }

        $username = $command->username();
        $password = $command->password();
        $roles = $command->roles();
        $isEnabled = $command->isEnabled();

        if (!$this->userManager->usernameIsValidAndAvailable($username)) {
            throw new RuntimeException('Username already in use');
        }

        $encryptedPassword = $this->userManager->encryptPassword($password);

        $aggregateId = Uuid::uuid4()->toString();
        $event = UserHasBeenCreated::fromParams(
            $aggregateId, $username, $encryptedPassword, $roles, $isEnabled
        );

        $aggregate = UserAggregate::withId($aggregateId);
        $aggregate->apply($event);

        $this->aggregateRepository->storeEvent($event);
        $projector = $this->projectors->getProjector(UserProjector::class);
        if (!$projector) {
            throw new RuntimeException(sprintf('Projector %s not found.', UserProjector::class));
        }
        $projector->apply($event);
    }
}
