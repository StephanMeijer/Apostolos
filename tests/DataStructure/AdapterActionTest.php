<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\AdapterAction;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdapterActionTest extends KernelTestCase
{
    public function testConstant(): void
    {
        $this->assertEquals('CREATE', AdapterAction::CREATE->name);
        $this->assertEquals('DELETE', AdapterAction::DELETE->name);
        $this->assertEquals('UPDATE', AdapterAction::UPDATE->name);
        $this->assertEquals('DELETE', AdapterAction::DELETE->name);
    }
}
