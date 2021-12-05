<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\Factory\DateTimeFactory;

use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Sabre\VObject\Property\Text;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use Sabre\VObject\Property\ICalendar\DateTime as SabreDateTime;

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

    public function testNoDateTime(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = new DateTimeFactory();

        $sabreText = $this->createMock(Text::class);
        $factory->build($sabreText);
    }
}