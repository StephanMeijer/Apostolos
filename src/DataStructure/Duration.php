<?php

declare(strict_types=1);

namespace App\DataStructure;

class Duration {
    public function __construct(private int $minutes) {}

    /**
     * @param array<string, int> $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['hours'] * 60 + $array['minutes']
        );
    }

    public function addDuration(Duration $duration): void
    {
        $this->addMinutes($duration->getHours() * 60 + $duration->getMinutes());
    }

    public function addMinutes(int $minutes): void
    {
        $this->minutes += $minutes;
    }

    public function getMinutes(): int
    {
        return $this->minutes % 60;
    }

    public function getHours(): int
    {
        return (int) floor($this->minutes / 60);
    }

    public function toText(): string
    {
        return sprintf('%02d', $this->getHours()) . ':' . sprintf('%02d', $this->getMinutes());
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'hours' => $this->getHours(),
            'minutes' => $this->getMinutes()
        ];
    }
}