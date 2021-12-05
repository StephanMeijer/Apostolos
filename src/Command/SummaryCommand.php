<?php

declare(strict_types=1);

namespace App\Command;

use DateInterval;
use Exception;

use App\DataStructure\Event;
use App\Service\CalendarService;

use InvalidArgumentException;
use Sabre\VObject\Component\VEvent;
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
            ->addOption('year', 'y', InputOption::VALUE_OPTIONAL, 'Year', date('Y'));
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

        $days = [];

        foreach ($events as $event) {
            $interval = $event->start->diff($event->end);

            $minutes = $this->minutesFromInterval($interval);

            $dayKey = $event->start->format('d');

            if (isset($days[$dayKey])) {
                $days[$dayKey] = $days[$dayKey] + $minutes;
            } else {
                $days[$dayKey] = $minutes;
            }
        }

        $rows = [];

        foreach ($days as $d => $minutes) {
            $rows[] = ["$d-$month-$year", $this->formatPeriod($minutes)];
        }

        sort($rows);

        $table = new Table($output);
        $table
            ->setHeaders(['Day (start)', 'Duration'])
            ->setRows($rows);
        $table->setHeaderTitle("Hours of $year-$month");
        $table->setFooterTitle("Total: " . $this->formatPeriod(array_sum(array_values($days))));
        $table->render();

        return Command::SUCCESS;
    }

    private function formatPeriod(int $minutes): string
    {
        $hours = intval($minutes / 60);
        $minutesLeft = $minutes % 60;

        return sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutesLeft);
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
        if (is_numeric($month)) {
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