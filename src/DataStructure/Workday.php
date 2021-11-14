<?php

namespace App\DataStructure;

use DateTimeImmutable;

class Workday {
    public function __construct(
        public string $year,
        public string $month,
        public string $day,
        public int $minutes
    ) { }
}