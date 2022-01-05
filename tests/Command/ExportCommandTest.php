<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ExportCommand;
use App\Factory\DateTimeFactory;
use App\Factory\EventFactory;
use App\Service\CalendarService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ExportCommandTest extends KernelTestCase
{
    use CommandTestTrait;

    public function testWithExampleDataset(): void
    {
        $eventFactory = new EventFactory(new DateTimeFactory());

        $configLoader = $this->configLoaderLoadingFixture('apostolos.yml');

        $httpClient = $this->httpClientRespondingFixtures(
            [
                [
                    'url' => 'https://calendar.google.com/calendar/ical/example/public/basic.ics',
                    'fixture' => 'ical.ics'
                ],
                [
                    'url' => 'https://calendar.google.com/basic.ics',
                    'fixture' => 'ical.ics'
                ]
            ]
        );

        $command = new ExportCommand(
            new CalendarService(
                $httpClient, $eventFactory, $configLoader
            )
        );

        $tester = new CommandTester($command);
        $code = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertSame(
            trim(file_get_contents(__DIR__.'/Fixtures/Test-export.json')),
            trim($tester->getDisplay())
        );
    }
}
