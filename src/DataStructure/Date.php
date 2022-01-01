<?php

declare(strict_types=1);

namespace App\DataStructure;

use DateTime;
use Exception;

class Date
{
    public function __construct(
        public int $year,
        public int $month,
        public int $day
    ) {
    }

    public static function fromDateTime(DateTime $dateTime): self
    {
        return new self(
            (int) $dateTime->format('Y'),
            (int) $dateTime->format('m'),
            (int) $dateTime->format('d')
        );
    }

    /**
     * @throws Exception
     */
    public function toDateTime(): DateTime
    {
        return new DateTime(join('-', [$this->year, $this->month, $this->day]));
    }
}
