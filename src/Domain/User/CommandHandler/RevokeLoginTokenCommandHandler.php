<?php

declare(strict_types=1);

namespace App\Domain\User\CommandHandler;

use App\Domain\User\Command\RevokeLoginTokenCommand;
use App\Service\UserManager;
use Exception;
use RuntimeException;

class RevokeLoginTokenCommandHandler
{
    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param RevokeLoginTokenCommand $command
     * @throws Exception
     */
    public function __invoke(RevokeLoginTokenCommand $command)
    {
        $isAdmin = $command->metadata()['is_admin'];
        if (!$isAdmin) {
            throw new RuntimeException('Bad credentials. Please use your admin-account.');
        }

        $this->userManager->createRandomLoginToken($command->userId());
    }
}
