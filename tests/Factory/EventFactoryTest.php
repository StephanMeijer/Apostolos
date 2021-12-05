<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\Factory\EventFactory;
use DateTime;
use DateTimeImmutable;

use App\Factory\DateTimeFactory;

use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property\ICalendar\DateTime as SabreDateTime;
use Sabre\VObject\Property\Text;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventFactoryTest extends KernelTestCase
{
    public function testFactory(): void
    {
        $startDT = $this->createMock(DateTime::class);
        $startSabre = $this->createMock(SabreDateTime::class);

        $endDT = $this->createMock(DateTime::class);
        $endSabre = $this->createMock(SabreDateTime::class);

        $summaryText = "This is a lorem ipsum dolor sit amet.";
        $summary = $this->createMock(Text::class);
        $summary
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($summaryText);

        $dateTimeFactory = $this->createMock(DateTimeFactory::class);
        $dateTimeFactory
            ->expects($this->exactly(2))
            ->method('build')
            ->withConsecutive(
                [$startSabre],
                [$endSabre]
            )->willReturnOnConsecutiveCalls(
                $startDT,
                $endDT
            );

        $eventIn = $this->createMock(VEvent::class);
        $eventIn->expects($this->exactly(3))
            ->method('__get')
            ->withConsecutive(
                ['DTSTART'],
                ['DTEND'],
                ['SUMMARY']
            )->willReturnOnConsecutiveCalls(
                $startSabre,
                $endSabre,
                $summary
            );

        $factory = new EventFactory($dateTimeFactory);

        $eventOut = $factory->build($eventIn);

        $this->assertSame($startDT, $eventOut->start);
        $this->assertSame($endDT, $eventOut->end);
        $this->assertSame($summaryText, $eventOut->description);
    }
}