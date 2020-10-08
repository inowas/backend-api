<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\User\Command\CreateUserCommand as CreateUserCommandAlias;
use App\Service\UserManager;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class CreateUserCommand extends Command
{

    protected static $defaultName = 'app:create-user';

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
            ->setDescription('Creates a new user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->setHelp('This command allows you to create a user...')
        ;
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
        $password = $input->getArgument('password');

        if (!$this->userManager->usernameIsValidAndAvailable($username)) {
            throw new RuntimeException('Username ist not available already exits');
        }

        /** @var CreateUserCommandAlias $command */
        $command = CreateUserCommandAlias::fromParams($username, $password);
        $command->withAddedMetadata('user_id', 'CLI');
        $this->commandBus->dispatch($command);
        $output->writeln('User successfully generated!');

        return 0;
    }
}
