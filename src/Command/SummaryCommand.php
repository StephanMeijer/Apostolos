<?php

namespace App\Command;

use DateInterval;
use Exception;

use App\DataStructure\Event;
use App\Service\CalendarService;
use App\Factory\EventFactory;

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
        protected EventFactory    $eventFactory,
        protected CalendarService $calendarService
    )
    {
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

        if ($year === date('Y') && (int) $month > (int) date('m')) {
            throw new InvalidArgumentException('Cannot be in future');
        }

        $cal = $this->calendarService->getCalendar($input->getArgument('calendar'));

        // @TODO refactor into factory and test
        $events = array_map(
            function (VEvent $event): Event {
                return $this->eventFactory->build($event);
            },
            $cal->select("VEVENT")
        );

        $events = array_filter(
            $events,
            function (Event $event) use ($year, $month): bool {
                return
                    $month === $event
                                    ->start
                                    ->format('m') &&

                    $year === $event
                                    ->start
                                    ->format('Y');
            }
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

    protected function formatPeriod(int $minutes): string
    {
        $hours = intval($minutes / 60);
        $minutesLeft = $minutes % 60;

        return "$hours:$minutesLeft";
    }

    protected function validateYear(string $year): string
    {
        if (is_numeric($year)) {
            $year = (int) $year;
        } else {
            throw new InvalidArgumentException('Year should be valid');
        }

        if ($year > date('Y')) {
            throw new InvalidArgumentException('Year cannot be after this year');
        }

        return $year;
    }

    protected function translateMonth(string $month): string
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
                return (string) $i + 1;
            }
        }

        throw new InvalidArgumentException("Invalid month");
    }

    protected function minutesFromInterval(DateInterval $interval): int
    {
        return $interval->d * 24 * 60 + $interval->h * 60 + $interval->i;
    }
}