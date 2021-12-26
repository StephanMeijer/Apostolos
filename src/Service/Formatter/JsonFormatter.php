<?php

declare(strict_types=1);

namespace App\Service\Formatter;

use App\DataStructure\CalendarRepresentation;
use App\DataStructure\Format;
use Symfony\Component\Console\Output\OutputInterface;

class JsonFormatter implements FormatterInterface
{
    /**
     * @param CalendarRepresentation $calendarRepresentation
     * @param OutputInterface $output
     *
     * @throws \JsonException
     */
    public function transform(CalendarRepresentation $calendarRepresentation, OutputInterface $output): void
    {
        $output->write(
            json_encode(
                $calendarRepresentation,
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            )
        );
    }

    public function supports(Format $format): bool
    {
        return $format === Format::JSON;
    }
}