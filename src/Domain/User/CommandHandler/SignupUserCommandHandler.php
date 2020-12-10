<?php

declare(strict_types=1);

namespace App\Domain\User\CommandHandler;

use App\Domain\User\Aggregate\UserAggregate;
use App\Domain\User\Command\SignupUserCommand;
use App\Domain\User\Event\UserHasBeenCreated;
use App\Domain\User\Event\UserProfileHasBeenChanged;
use App\Domain\User\Projection\UserProjector;
use App\Model\ProjectorCollection;
use App\Repository\AggregateRepository;
use App\Service\UserManager;
use Exception;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class SignupUserCommandHandler
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
     * @param SignupUserCommand $command
     * @throws Exception
     */
    public function __invoke(SignupUserCommand $command)
    {
        $username = $command->email();
        $password = $command->password();
        $roles = ['ROLE_USER'];
        $isEnabled = true;

        if (!$this->userManager->usernameIsValidAndAvailable($username)) {
            throw new RuntimeException('Username already in use');
        }

        $encryptedPassword = $this->userManager->encryptPassword($password);

        // Create User
        $aggregateId = Uuid::uuid4()->toString();
        $createUserEvent = UserHasBeenCreated::fromParams($aggregateId, $username, $encryptedPassword, $roles, $isEnabled);
        $aggregate = UserAggregate::withId($aggregateId);
        $aggregate->apply($createUserEvent);
        $this->aggregateRepository->storeEvent($createUserEvent);
        $projector = $this->projectors->getProjector(UserProjector::class);
        if (!$projector) {
            throw new RuntimeException(sprintf('Projector %s not found.', UserProjector::class));
        }
        $projector->apply($createUserEvent);

        // Update UserProfile
        $updateProfileEvent = UserProfileHasBeenChanged::fromParams($aggregateId, [
            'name' => $command->name(),
            'email' => $command->email()
        ]);
        $aggregate->apply($updateProfileEvent);
        $this->aggregateRepository->storeEvent($updateProfileEvent);
        $projector = $this->projectors->getProjector(UserProjector::class);
        if (!$projector) {
            throw new RuntimeException(sprintf('Projector %s not found.', UserProjector::class));
        }
        $projector->apply($updateProfileEvent);
    }
}
