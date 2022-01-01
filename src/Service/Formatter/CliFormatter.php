<?php

declare(strict_types=1);

namespace App\Service\Formatter;

use App\DataStructure\CalendarRepresentation;
use App\DataStructure\Format;
use App\DataStructure\Period;
use Exception;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class CliFormatter implements FormatterInterface
{
    /**
     * @throws Exception
     */
    public function transform(CalendarRepresentation $calendarRepresentation, OutputInterface $output): void
    {
        $table = new Table($output);
        $table
            ->setHeaderTitle("Hours of $calendarRepresentation->year-$calendarRepresentation->month")
            ->setFooterTitle('Total: '.$calendarRepresentation->getTotalDuration()->toText())
            ->setHeaders(['Day (start)', 'Duration'])
            ->setRows(
                array_map(
                    static function (Period $period): array {
                        return [
                            $period->date->toDateTime()->format('d-m-Y'),
                            $period->duration->toText(),
                        ];
                    },
                    $calendarRepresentation->periods
                )
            )
            ->render();
    }

    public function supports(Format $format): bool
    {
        return Format::CLI === $format;
    }
}
