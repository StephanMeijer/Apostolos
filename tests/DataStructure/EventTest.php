<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Event;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventTest extends KernelTestCase
{
    public function testConstructor(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $description = 'test';

        $event = new Event(null, $start, $end, $description);

        $this->assertEquals($start, $event->start);
        $this->assertEquals($end, $event->end);
        $this->assertEquals($description, $event->description);
    }

    public function testMinutes(): void
    {
        $event = new Event(
            null,
            new DateTime('2021-12-13T18:00'),
            new DateTime('2021-12-14T01:03:44'),
            ''
        );

        $this->assertEquals(7 * 60 + 3, $event->minutes());
    }

    public function testSetters(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $description = 'test';

        $event = new Event(null, $start, $end, $description);

        $newStart = $this->createMock(DateTime::class);
        $newEnd = $this->createMock(DateTime::class);

        $newDescription = 'test';

        $event->start = $newStart;
        $event->end = $newEnd;
        $event->description = $newDescription;

        $this->assertEquals($newStart, $event->start);
        $this->assertEquals($newEnd, $event->end);
        $this->assertEquals($newDescription, $event->description);
    }
}
