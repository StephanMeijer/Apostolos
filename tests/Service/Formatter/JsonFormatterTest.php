<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Format;
use App\Service\Formatter\JsonFormatter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JsonFormatterTestFormatterTest extends KernelTestCase
{
    public function testSupports(): void
    {
        $formatter = new JsonFormatter();

        $this->assertTrue($formatter->supports(Format::JSON));
    }
}
