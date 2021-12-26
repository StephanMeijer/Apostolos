<?php

declare(strict_types=1);

namespace App\Service\Formatter;

use App\DataStructure\CalendarRepresentation;
use App\DataStructure\Format;
use Symfony\Component\Console\Output\OutputInterface;

interface FormatterInterface
{
    /**
     * @param CalendarRepresentation $calendarRepresentation
     * @return string Output: can be
     */
    public function transform(CalendarRepresentation $calendarRepresentation, OutputInterface $output): void;

    public function supports(Format $format): bool;
}