<?php

declare(strict_types=1);

namespace App\DataStructure;

use DateTime;

class Event {
    public function __construct(
        public DateTime $start,
        public DateTime $end,
        public string $description
    ) { }
}