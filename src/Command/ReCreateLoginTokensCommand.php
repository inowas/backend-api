<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\User;
use App\Service\UserManager;

use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReCreateLoginTokensCommand extends Command
{

    protected static $defaultName = 'app:recreate-tokens';

    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Recreates login tokens.')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username')
            ->setHelp('This command allows you to recreate login tokens...');
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

        if ($username) {
            $user = $this->userManager->findUserByUsername($username);
            if ($user === null) {
                $output->writeln(sprintf("User with username: %s not found.", $username));
                return 1;
            }

            $user->createRandomLoginToken();
            $this->userManager->saveUser($user);
            $output->writeln(sprintf("Token recreates for User: '%s'.", $username));
            return 0;
        }

        $users = $this->userManager->findAllUsers();
        /** @var User $user */
        foreach ($users as $user) {
            $user->setLoginToken(Uuid::uuid4()->toString());
            $this->userManager->saveUser($user);
            $output->writeln(sprintf("Token recreates for User: '%s'.", $user->getUsername()));
        }

        return 0;
    }
}
