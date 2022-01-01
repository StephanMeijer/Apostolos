<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Duration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DurationTest extends KernelTestCase
{
    public function testAddMinutes(): void
    {
        $duration = new Duration(123);
        $duration->addMinutes(123);

        $this->assertEquals(6, $duration->getMinutes());
        $this->assertEquals(4, $duration->getHours());
    }

    public function testAddDuration(): void
    {
        $duration1 = new Duration(123);
        $duration2 = new Duration(333);
        $duration1->addDuration($duration2);

        $this->assertEquals(36, $duration1->getMinutes());
        $this->assertEquals(7, $duration1->getHours());
    }

    public function testConstructor(): void
    {
        $duration = new Duration(123);
        $this->assertEquals(3, $duration->getMinutes());
        $this->assertEquals(2, $duration->getHours());
    }

    public function testToText(): void
    {
        $duration = new Duration(123);
        $this->assertEquals('02:03', $duration->toText());
    }

    public function testToArray(): void
    {
        $duration = new Duration(123);
        $this->assertEquals(['minutes' => 3, 'hours' => 2], $duration->toArray());
    }

    public function testFromArray(): void
    {
        $duration = Duration::fromArray([
            'hours' => 12,
            'minutes' => 13,
        ]);

        $this->assertEquals(12, $duration->getHours());
        $this->assertEquals(13, $duration->getMinutes());
    }
}
