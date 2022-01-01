<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Date;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DateTest extends KernelTestCase
{
    public function testConstructor(): void
    {
        $date = new Date(2021, 12, 13);

        $this->assertEquals(2021, $date->year);
        $this->assertEquals(12, $date->month);
        $this->assertEquals(13, $date->day);
    }

    public function testToDateTimeConversion(): void
    {
        $date = new Date(2021, 12, 13);

        $this->assertEquals(new DateTime('2021-12-13'), $date->toDateTime());
    }

    public function testFromDateTime(): void
    {
        $date = Date::fromDateTime(
            new DateTime('2021-12-13')
        );

        $this->assertEquals(2021, $date->year);
        $this->assertEquals(12, $date->month);
        $this->assertEquals(13, $date->day);
    }
}
