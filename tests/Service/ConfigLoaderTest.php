<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\Service\ConfigLoader;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigLoaderTest extends KernelTestCase
{
    public function testDecodingConfig(): void
    {
        $loader = new ConfigLoader();

        $this->assertEquals(
            [
                'calendars' => [
                    'Client1' => 'https://calendar.google.com/calendar/ical/692cf04a-1d49-4772-b09b-0ec324853277/basic.ics',
                    'Client2' => 'https://calendar.google.com/calendar/ical/0f26df45-f42e-4e56-b495-67cb11f5ea91/basic.ics'
                ]
            ],
            $loader->load(__dir__ . "/Fixtures/config.yml")
        );
    }
}