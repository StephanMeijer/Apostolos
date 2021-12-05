<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\Command\SummaryCommand;
use App\Factory\DateTimeFactory;
use App\Factory\EventFactory;
use App\Service\CalendarService;
use App\Service\ConfigLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SummaryCommandTest extends KernelTestCase
{
    /**
     * @dataProvider provider
     */
    public function testWithExampleDataset($args, $output): void
    {
        $realConfigLoader = new ConfigLoader();

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(
                file_get_contents(__DIR__ . '/Fixtures/ical.ics')
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

        $eventFactory = new EventFactory(new DateTimeFactory());
        $configLoader = $this->createMock(ConfigLoader::class);
        $configLoader
            ->expects($this->once())
            ->method('load')
            ->with('~/.apostolos.yml')
            ->willReturn(
                $realConfigLoader->load(__DIR__ . '/Fixtures/apostolos.yml')
            );

        $command = new SummaryCommand(
            new CalendarService(
                $httpClient, $eventFactory, $configLoader
            )
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute($args);

        $this->assertSame($output, $commandTester->getDisplay());
    }

    public function provider(): array
    {
        return [
            'Test 2021 12' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '12',
                    '--year' => '2021'
                ],
                'output' => file_get_contents(__DIR__ . '/Fixtures/Test-2021-12.txt'),
            ],
            'Test 2021 december' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'december',
                    '--year' => '2021'
                ],
                'output' => file_get_contents(__DIR__ . '/Fixtures/Test-2021-12.txt'),
            ],
            'Test 2021 dec' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'dec',
                    '--year' => '2021'
                ],
                'output' => file_get_contents(__DIR__ . '/Fixtures/Test-2021-12.txt'),
            ]
        ];
    }
}