<?php

declare(strict_types=1);

namespace App\DataStructure;

class CalendarConfiguration
{
    public function __construct(
        public readonly string $name,
        public readonly string $url
    ) {
    }
}
