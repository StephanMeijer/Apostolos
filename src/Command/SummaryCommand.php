<?php

declare(strict_types=1);

namespace App\Command;

use App\DataStructure\CalendarRepresentation;
use App\DataStructure\Date;
use App\DataStructure\Duration;
use App\DataStructure\Event;
use App\DataStructure\Period;
use App\Service\CalendarService;
use App\Service\Transformer\CliTransformer;
use App\Service\Transformer\JsonTransformer;
use DateTime;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SummaryCommand extends Command
{
    protected static $defaultName = 'time:summary';

    public function __construct(
        protected CalendarService $calendarService,
        protected JsonTransformer $jsonTransformer,
        protected CliTransformer $cliTransformer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Summarize hours.')
            ->addArgument("calendar", InputArgument::REQUIRED)
            ->addOption('month', 'm', InputOption::VALUE_OPTIONAL, 'Month', date('m'))
            ->addOption('year', 'y', InputOption::VALUE_OPTIONAL, 'Year', date('Y'))
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Year', 'text');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $month = $this->translateMonth($input->getOption('month'));
        $year = $this->validateYear($input->getOption('year'));

        $events = $this->calendarService->getEvents(
            $input->getArgument('calendar'),
            $year,
            $month
        );

        $periods = array_reduce(
            $events,
            /**
             * @param Period[] $acc
             * @return Period[]
             *
             * @throws Exception
             */
            static function (array $acc, Event $event): array
            {
                $dateEquals = static function (DateTime $a, DateTime $b): bool {
                    return $a->format('Y-m-d') === $b->format('Y-m-d');
                };

                $found = false;

                foreach ($acc as $period) {
                    if ($dateEquals($period->date->toDateTime(), $event->start)) {
                        $period->duration->addMinutes($event->minutes());
                        $found = true;
                    }
                }

                if (!$found) {
                    $acc[] = new Period(
                        Date::fromDateTime($event->start),
                        new Duration($event->minutes())
                    );
                }

                return $acc;
            },
            []
        );

        sort($periods);

        $calendarRepresentation = new CalendarRepresentation(
            name: $input->getArgument('calendar'),
            month: $month,
            year: $year,
            periods: $periods
        );

        match ($input->getOption('format')) {
            'json' => $this->jsonTransformer->transform($calendarRepresentation, $output),
            default => $this->cliTransformer->transform($calendarRepresentation, $output)
        };

        return Command::SUCCESS;
    }

    private function validateYear(string $year): string
    {
        if (!is_numeric($year)) {
            throw new InvalidArgumentException('Year should be valid');
        }

        return $year;
    }

    private function translateMonth(string $month): string
    {
        if (is_numeric($month) && (int) $month >= 1 && (int) $month <= 12) {
            return $month;
        }

        $months = [
            'january', 'february', 'march',
            'april', 'may', 'june',
            'july', 'august', 'september',
            'october', 'november', 'december'
        ];

        foreach ($months as $i => $possibleMonth) {
            if ($possibleMonth === $month || str_contains($possibleMonth, $month)) {
                return (string) ($i + 1);
            }
        }

        throw new InvalidArgumentException("Invalid month");
    }
}