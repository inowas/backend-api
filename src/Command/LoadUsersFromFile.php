<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\User\Command\ChangeUserProfileCommand;
use App\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class LoadUsersFromFile extends Command
{
    protected static $defaultName = 'app:load-users';

    /** @var MessageBusInterface $commandBus */
    private MessageBusInterface $commandBus;

    /** @var EntityManagerInterface $entityManager */
    private EntityManagerInterface $entityManager;

    /** @var KernelInterface $kernel */
    private KernelInterface $kernel;

    public function __construct(MessageBusInterface $commandBus, EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->commandBus = $commandBus;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Load users from file.')
            ->addArgument('file', InputArgument::OPTIONAL, 'file')
            ->setHelp('This command allows you to load users from a file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $filename = sprintf('%s/%s', $this->kernel->getProjectDir(), $file);
        if (!is_file($filename)) {
            $output->writeln(sprintf('File "%s" not found!', $filename));
            return 1;
        }

        try {
            $users = json_decode(file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        $heads = $users['heads'];
        $users = $users['users'];


        /**
         * @var array $users
         */
        foreach ($users as $item) {
            $item = array_combine($heads, $item);
            $roles = array_unique(array_merge(['ROLE_USER'], $item['roles']));

            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $item['username']]);

            if (!$user instanceof User) {
                $user = new User($item['username'], $item['password'], $roles);
                $command = \App\Domain\User\Command\CreateUserCommand::fromParams($user->getUsername(), $user->getPassword(), $user->getRoles());
                $command->withAddedMetadata('is_admin', true);

                try {
                    $this->commandBus->dispatch($command);
                } catch (Exception $exception) {
                    $output->write(sprintf('Error creating user with username %s', $user->getUsername()));
                }
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $item['username']]);

            $command = ChangeUserProfileCommand::fromPayload(['profile' => ['name' => $item['name'], 'email' => $item['email']]]);
            $command->withAddedMetadata('is_admin', true);
            $command->withAddedMetadata('user_id', $user->getId()->toString());

            try {
                $this->commandBus->dispatch($command);
            } catch (Exception $exception) {
                $output->write(sprintf('Error updating profile of user with username %s', $user->getUsername()));
            }

            $output->writeln(sprintf('User created/updated: %s with roles %s', $user->getUsername(), implode(', ', $user->getRoles())));
        }

        return 0;
    }
}
