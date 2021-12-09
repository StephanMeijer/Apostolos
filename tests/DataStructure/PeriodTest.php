<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Date;
use App\DataStructure\Period;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PeriodTest extends KernelTestCase
{
    public function testConstructor(): void
    {
        $date = $this->createMock(Date::class);
        $period = new Period($date, 123123);

        $this->assertSame($date, $period->date);
        $this->assertSame(123123, $period->minutes);
    }
}