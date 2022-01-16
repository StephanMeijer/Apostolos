<?php

declare(strict_types=1);

namespace App\DataStructure;

use DateInterval;
use DateTime;

class Event
{
    public function __construct(
        public ?string $identifier,
        public DateTime $start,
        public DateTime $end,
        public string $description
    ) {
    }

    private function getInterval(): DateInterval
    {
        return $this->start->diff($this->end);
    }

    public function minutes(): int
    {
        $interval = $this->getInterval();

        return $interval->d * 24 * 60 + $interval->h * 60 + $interval->i;
    }
}
