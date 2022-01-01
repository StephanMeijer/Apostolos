<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ExportCommand;
use App\Factory\DateTimeFactory;
use App\Factory\EventFactory;
use App\Service\CalendarService;
use App\Service\ConfigLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ExportCommandTest extends KernelTestCase
{
    public function testWithExampleDataset(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(file_get_contents(__DIR__.'/Fixtures/ical.ics'));

        $eventFactory = new EventFactory(new DateTimeFactory());

        $realConfigLoader = new ConfigLoader();
        $configLoader = $this->createMock(ConfigLoader::class);
        $configLoader
            ->expects($this->once())
            ->method('load')
            ->with('~/.apostolos.yml')
            ->willReturn(
                $realConfigLoader->load(__DIR__.'/Fixtures/apostolos.yml')
            );

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://calendar.google.com/calendar/ical/example/public/basic.ics'
            )
            ->willReturn($response);

        $command = new ExportCommand(
            new CalendarService(
                $httpClient, $eventFactory, $configLoader
            )
        );

        $tester = new CommandTester($command);
        $code = $tester->execute(['calendar' => 'Test']);

        $this->assertSame(Command::SUCCESS, $code);
        $this->assertSame(
            trim(file_get_contents(__DIR__.'/Fixtures/Test-export.json')),
            trim($tester->getDisplay())
        );
    }
}
