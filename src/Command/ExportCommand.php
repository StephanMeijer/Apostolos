<?php

declare(strict_types=1);

namespace App\Command;

use App\DataStructure\CalendarConfiguration;
use App\DataStructure\Period;
use App\Service\CalendarService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    protected static $defaultName = 'time:export';

    public function __construct(
        protected CalendarService $calendarService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Export hours.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configs = $this->calendarService->listCalendars();

        $periods = array_reduce(
            $configs,

            /**
             * @param Period[] $periods
             * @return Period[]
             */
            function (array $periods, CalendarConfiguration $configuration): array
            {
                return array_merge(
                    $periods,
                    $this->normalize(
                        $this->calendarService->getPeriods($configuration->url),
                        $configuration->name
                    )
                );
            },
            []
        );

        $output->writeln(json_encode($periods,JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }

    /**
     * @param Period[] $periods
     *
     * @throws Exception
     */
    private function normalize(array $periods, string $calendar): array
    {
        return array_map(
            static function (Period $period) use ($calendar): array {
                return [
                    'day' => $period->date->toDateTime()->format('Y-m-d'),
                    'duration' => $period->duration->toFloat(precision: 2),
                    'calendar' => $calendar
                ];
            },
            $periods
        );
    }
}
