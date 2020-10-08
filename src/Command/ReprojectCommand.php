<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Projector;
use App\Model\ProjectorCollection;
use App\Repository\AggregateRepository;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReprojectCommand extends Command
{
    protected static $defaultName = 'app:reproject';

    protected AggregateRepository $aggregateRepository;
    protected array $projections;

    public function __construct(AggregateRepository $aggregateRepository, ProjectorCollection $projections)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->projections = $projections->toArray();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Reprojects selected projections.')
            ->addArgument('aggregateName', InputArgument::OPTIONAL, 'Username')
            ->setHelp('This command allows you to recreate your projections.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $aggregateName = $input->getArgument('aggregateName');

        if (!$aggregateName) {
            $output->writeln('The following projections are available:');
            $output->writeln('');

            /**
             * @var Projector $projection
             */
            foreach ($this->projections as $key => $projection) {
                $output->writeln(sprintf('%d: %s', $key + 1, $projection->aggregateName()));
            }

            return 0;
        }

        /**
         * @var Projector $projection
         */
        foreach ($this->projections as $key => $projection) {
            if ($key + 1 === $aggregateName || $projection->aggregateName() === $aggregateName) {
                $events = $this->aggregateRepository->findAllEventsByAggregateName($projection->aggregateName());
                $projection->recreateFromHistory($events);
                $output->writeln(sprintf('Projection %s successfully recreated with %d events.', $projection->aggregateName(), count($events)));
                return 0;
            }
        }

        $output->writeln(sprintf('Projection %s not found.', $aggregateName));
        return 1;
    }
}
