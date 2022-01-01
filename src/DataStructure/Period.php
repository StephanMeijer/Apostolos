<?php

declare(strict_types=1);

namespace App\DataStructure;

use Exception;
use JsonSerializable;

class Period implements JsonSerializable
{
    public function __construct(
        public Date $date,
        public Duration $duration
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function jsonSerialize(): array
    {
        return [
            'day' => $this->date->toDateTime()->format('Y-m-d'),
            'duration' => $this->duration->toArray(),
        ];
    }
}
