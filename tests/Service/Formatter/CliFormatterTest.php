<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Format;
use App\Service\Formatter\CliFormatter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CliFormatterTest extends KernelTestCase
{
    public function testSupports(): void
    {
        $formatter = new CliFormatter();

        $this->assertTrue($formatter->supports(Format::CLI));
    }
}