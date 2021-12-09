<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Date;
use App\DataStructure\Duration;
use App\DataStructure\Period;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PeriodTest extends KernelTestCase
{
    public function testConstructor(): void
    {
        $date = $this->createMock(Date::class);
        $duration = $this->createMock(Duration::class);

        $period = new Period($date, $duration);

        $this->assertSame($date, $period->date);
        $this->assertSame($duration, $period->duration);
    }
}