<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\Factory\DateTimeFactory;
use DateTime;
use DateTimeImmutable;
use Sabre\VObject\Property\ICalendar\DateTime as SabreDateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DateTimeFactoryTest extends KernelTestCase
{
    public function testFactory(): void
    {
        $factory = new DateTimeFactory();

        $dateTime = new DateTime();
        $sabreDateTime = $this->createMock(SabreDateTime::class);
        $sabreDateTime
            ->expects($this->once())
            ->method('getDateTime')
            ->willReturn(DateTimeImmutable::createFromMutable($dateTime));

        $returnedDateTime = $factory->build($sabreDateTime);

        $this->assertEquals($dateTime, $returnedDateTime);
    }
}
