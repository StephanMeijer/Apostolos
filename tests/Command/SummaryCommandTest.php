<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\Command\SummaryCommand;
use App\Factory\DateTimeFactory;
use App\Factory\EventFactory;
use App\Service\CalendarService;
use App\Service\ConfigLoader;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Console\Command\Command;

class SummaryCommandTest extends KernelTestCase
{
    /**
     * @dataProvider provider
     */
    public function testWithExampleDataset(array $args, string $output, ?string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $realConfigLoader = new ConfigLoader();

        $response = $this->createMock(ResponseInterface::class);
        empty($exception) && $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(
                file_get_contents(__DIR__ . '/Fixtures/ical.ics')
            );

        $httpClient = $this->createMock(HttpClientInterface::class);
        empty($exception) && $httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://calendar.google.com/calendar/ical/example/public/basic.ics'
            )
            ->willReturn($response);

        $eventFactory = new EventFactory(new DateTimeFactory());
        $configLoader = $this->createMock(ConfigLoader::class);
        empty($exception) && $configLoader
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
        $code = $commandTester->execute($args);

        if (!$exception) {
            $this->assertSame(Command::SUCCESS, $code);
            $this->assertSame($output, $commandTester->getDisplay());
        }
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
            ],
            'Test 2021 dec json' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'dec',
                    '--year' => '2021',
                    '--format' => 'json'
                ],
                'output' => file_get_contents(__DIR__ . '/Fixtures/Test-2021-12.json'),
            ],
            'Test 2021 abc (invalid year alphanumeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '12',
                    '--year' => 'abc'
                ],
                'output' => "",
                'exception' => InvalidArgumentException::class
            ],
            'Test 2021 abc (invalid month alphanumeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'abc',
                    '--year' => '2021'
                ],
                'output' => "",
                'exception' => InvalidArgumentException::class
            ],
            'Test 2021 -1 (invalid month numeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '-1',
                    '--year' => '2021'
                ],
                'output' => "",
                'exception' => InvalidArgumentException::class
            ],
            'Test 2021 0 (invalid month numeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '0',
                    '--year' => '2021'
                ],
                'output' => "",
                'exception' => InvalidArgumentException::class
            ],
            'Test 2021 14 (invalid month numeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '14',
                    '--year' => '2021'
                ],
                'output' => "",
                'exception' => InvalidArgumentException::class
            ],
        ];
    }
}