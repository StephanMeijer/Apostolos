<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use DateTime;
use App\DataStructure\Event;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventTest extends KernelTestCase
{
    public function testConstructor(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $description = 'test';

        $event = new Event($start, $end, $description);

        $this->assertEquals($start, $event->start);
        $this->assertEquals($end, $event->end);
        $this->assertEquals($description, $event->description);
    }

    public function testSetters(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $description = 'test';

        $event = new Event($start, $end, $description);

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