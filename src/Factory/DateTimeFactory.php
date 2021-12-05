<?php

declare(strict_types=1);

namespace App\Factory;

use DateTime;
use InvalidArgumentException;
use Sabre\VObject\Property;

class DateTimeFactory {
    public function build(Property $dateTime): DateTime
    {
        if (!method_exists($dateTime, 'getDateTime')) {
            throw new InvalidArgumentException('Expected something with DateTime');
        }

        return DateTime::createFromImmutable($dateTime->getDateTime());
    }
}