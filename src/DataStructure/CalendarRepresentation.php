<?php

declare(strict_types=1);

namespace App\DataStructure;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class CalendarRepresentation implements JsonSerializable
{
    /**
     * @param string   $name
     * @param string   $month
     * @param string   $year
     * @param Period[] $periods
     */
    public function __construct(
        public readonly string $name,
        public readonly string $month,
        public readonly string $year,
        public readonly array $periods
    ) {
    }

    public function getTotalDuration(): Duration
    {
        return array_reduce(
            $this->periods,
            static function (Duration $acc, Period $period): Duration {
                $acc->addDuration($period->duration);

                return $acc;
            },
            new Duration(0)
        );
    }

    #[ArrayShape(['meta' => 'array', 'records' => "\App\DataStructure\Period[]"])]
    public function jsonSerialize(): array
    {
        return [
            'meta' => [
                'calendar' => $this->name,
                'year' => (int) $this->year,
                'month' => (int) $this->month,
                'duration' => $this->getTotalDuration(),
            ],
            'records' => $this->periods,
        ];
    }
}
