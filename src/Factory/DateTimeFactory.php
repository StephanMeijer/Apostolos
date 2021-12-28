<?php

declare(strict_types=1);

namespace App\Factory;

use DateTime;
use Sabre\VObject\Property;

class DateTimeFactory {
    /**
     * @param Property\ICalendar\DateTime $dateTime
     * @return DateTime
     */
    public function build(Property\ICalendar\DateTime $dateTime): DateTime
    {
        return DateTime::createFromImmutable($dateTime->getDateTime());
    }
}