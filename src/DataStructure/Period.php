<?php

declare(strict_types=1);

namespace App\DataStructure;

class Period {
    public function __construct(
        public Date $date,
        public int $minutes
    ) { }
}