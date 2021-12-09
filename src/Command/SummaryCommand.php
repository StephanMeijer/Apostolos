<?php

declare(strict_types=1);

namespace App\Command;

use App\DataStructure\Date;
use App\DataStructure\Duration;
use App\DataStructure\Period;
use DateInterval;
use DateTime;
use Exception;

use App\DataStructure\Event;
use App\Service\CalendarService;

use InvalidArgumentException;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SummaryCommand extends Command
{
    protected static $defaultName = 'time:summary';

    public function __construct(
        protected CalendarService $calendarService
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
             */
            function(array $acc, Event $event): array
            {
                $dateEquals = function (DateTime $a, DateTime $b): bool {
                    return $a->format('Y-m-d') === $b->format('Y-m-d');
                };

                $found = false;

                foreach ($acc as &$period) {
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

        match (
            $input->getOption('format')
        ) {
            'json' => $this->outputJSON($periods, $month, $year, $output),
            default => $this->outputNormal($periods, $month, $year, $output)
        };

        return Command::SUCCESS;
    }

    /**
     * @param Period[] $periods
     * @throws Exception
     *
     * @throws JsonException
     */
    private function outputJSON(array $periods, string $month, string $year, OutputInterface $output): void
    {
        $outputData = array_reduce(
            $periods,
            function($acc, Period $period): array {
                $dayKey = $period->date->toDateTime()->format('Y-m-d');

                $acc['records'][] = [
                    "day" => $dayKey,
                    "duration" => $period->duration->toArray()
                ];

                $metaDuration = Duration::fromArray($acc['meta']['duration']);
                $metaDuration->addDuration($period->duration);
                $acc['meta']['duration'] = $metaDuration->toArray();

                return $acc;
            },
            [
                "meta" => [
                    "year" => (int) $year,
                    "month" => (int) $month,
                    "duration" => [
                        "hours" => 0,
                        "minutes" => 0
                    ]
                ],
                "records" => []
            ]
        );

        sort($outputData['records']);

        $output->write(
            json_encode(
                $outputData,
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * @param Period[] $periods
     * @throws Exception
     */
    private function outputNormal(array $periods, string $month, string $year, OutputInterface $output): void
    {
        $rows = [];

        foreach ($periods as $period) {
            $rows[] = [
                $period->date->toDateTime()->format('d-m-Y'),
                $period->duration->toText()
            ];
        }

        $totalDuration = array_reduce(
            $periods,
            function (Duration $acc, Period $period): Duration {
                $acc->addDuration($period->duration);
                return $acc;
            },
            new Duration(0)
        );

        sort($rows);

        $table = new Table($output);
        $table
            ->setHeaders(['Day (start)', 'Duration'])
            ->setRows($rows);
        $table->setHeaderTitle("Hours of $year-$month");
        $table->setFooterTitle("Total: " . $totalDuration->toText());
        $table->render();
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
            'juli', 'august', 'september',
            'october', 'november', 'december'
        ];

        foreach ($months as $i => $possbileMonth) {
            if (
                str_contains($possbileMonth, $month) ||
                $possbileMonth === $month
            ) {
                return (string) ($i + 1);
            }
        }

        throw new InvalidArgumentException("Invalid month");
    }

    private function minutesFromInterval(DateInterval $interval): int
    {
        return $interval->d * 24 * 60 + $interval->h * 60 + $interval->i;
    }
}