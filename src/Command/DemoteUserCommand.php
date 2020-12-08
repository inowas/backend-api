<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\User\Command\DemoteUserCommand as DemoteUserCommandAlias;

use App\Service\UserManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class DemoteUserCommand extends Command
{

    protected static $defaultName = 'app:user:demote';

    private MessageBusInterface $commandBus;
    private UserManager $userManager;

    public function __construct(MessageBusInterface $commandBus, UserManager $userManager)
    {
        $this->commandBus = $commandBus;
        $this->userManager = $userManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Promotes a user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('role', InputArgument::REQUIRED, 'Role')
            ->setHelp('This command allows you to promote a user...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');

        $user = $this->userManager->findUserByUsername($username);
        if ($user === null) {
            $output->writeln(sprintf('User with username: %s not found.', $username));
            return 1;
        }

        $command = DemoteUserCommandAlias::fromParams($user->getId()->toString(), $role);
        $command->withAddedMetadata('user_id', 'CLI');
        $this->commandBus->dispatch($command);
        $output->writeln('User successfully demoted!');
        return 0;
    }
}
