<?php

declare(strict_types=1);

namespace App\Command;

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
        $this
            ->setHelp('Export hours.')
            ->addArgument('calendar', InputArgument::REQUIRED);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->calendarService->getCalendar($input->getArgument('calendar'));

        if (!$config) {
            throw new InvalidArgumentException('Calendar not found.');
        }

        $periods = $this->calendarService->getPeriods($config->url);

        $output->writeln(
            json_encode(
                $this->normalize($periods),
                JSON_PRETTY_PRINT
            )
        );

        return Command::SUCCESS;
    }

    /**
     * @param Period[] $periods
     *
     * @throws Exception
     */
    private function normalize(array $periods): array
    {
        return array_map(
            static function (Period $period): array {
                return [
                    'day' => $period->date->toDateTime()->format('Y-m-d'),
                    'duration' => $period->duration->toText(),
                ];
            },
            $periods
        );
    }
}
