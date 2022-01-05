<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SummaryCommand;
use App\DataStructure\Exception\InvalidCalendarException;
use App\Factory\DateTimeFactory;
use App\Factory\EventFactory;
use App\Service\CalendarService;
use App\Service\ConfigLoader;
use App\Service\Formatter\CliFormatter;
use App\Service\Formatter\JsonFormatter;
use InvalidArgumentException;
use Sabre\VObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SummaryCommandTest extends KernelTestCase
{
    use CommandTestTrait;

    /**
     * @dataProvider provider
     */
    public function testWithExampleDataset(
        array $args,
        string $inputICS,
        string $output,
        ?string $exception = null
    ): void {
        if ($exception) {
            $this->expectException($exception);
        }

        if (InvalidArgumentException::class === $exception) {
            $httpClient = $this->createMock(HttpClientInterface::class);
            $configLoader = $this->createMock(ConfigLoader::class);
        } else {
            $httpClient = $this->httpClientRespondingFixtures(
                [
                    [
                        'url' => 'https://calendar.google.com/calendar/ical/example/public/basic.ics',
                        'fixture' => $inputICS
                    ]
                ]
            );
            $configLoader = $this->configLoaderLoadingFixture('apostolos.yml');
        }

        $eventFactory = new EventFactory(new DateTimeFactory());

        $command = new SummaryCommand(
            new CalendarService(
                $httpClient, $eventFactory, $configLoader
            ),
            new JsonFormatter(),
            new CliFormatter()
        );

        $commandTester = new CommandTester($command);
        $code = $commandTester->execute($args);

        if (!$exception) {
            $this->assertSame(Command::SUCCESS, $code);
            $this->assertSame(trim($output), trim($commandTester->getDisplay()));
        }
    }

    public function testInvalidTestData(): void
    {
        $calendar = VObject\Reader::read(
            file_get_contents(__DIR__.'/Fixtures/invalid.ics')
        );

        $this->assertNotEmpty($calendar->validate());
    }

    public function provider(): array
    {
        return [
            'Test 2021 12' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '12',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => file_get_contents(__DIR__.'/Fixtures/Test-2021-12.txt'),
            ],
            'Test 2021 december' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'december',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => file_get_contents(__DIR__.'/Fixtures/Test-2021-12.txt'),
            ],
            'Test 2021 dec' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'dec',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => file_get_contents(__DIR__.'/Fixtures/Test-2021-12.txt'),
            ],
            'Test 2021 dec json' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'dec',
                    '--year' => '2021',
                    '--format' => 'json',
                ],
                'inputICS' => 'ical.ics',
                'output' => file_get_contents(__DIR__.'/Fixtures/Test-2021-12.json'),
            ],
            'Test 2021 abc (invalid year alphanumeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '12',
                    '--year' => 'abc',
                ],
                'inputICS' => 'ical.ics',
                'output' => '',
                'exception' => InvalidArgumentException::class,
            ],
            'Test 2021 abc (invalid month alphanumeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => 'abc',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => '',
                'exception' => InvalidArgumentException::class,
            ],
            'Test 2021 -1 (invalid month numeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '-1',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => '',
                'exception' => InvalidArgumentException::class,
            ],
            'Test 2021 0 (invalid month numeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '0',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => '',
                'exception' => InvalidArgumentException::class,
            ],
            'Test 2021 14 (invalid month numeric)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '14',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => '',
                'exception' => InvalidArgumentException::class,
            ],
            'NonExistent 2021 12 (invalid calendar)' => [
                'args' => [
                    'calendar' => 'NonExistent',
                    '--month' => '12',
                    '--year' => '2021',
                ],
                'inputICS' => 'ical.ics',
                'output' => '',
                'exception' => InvalidArgumentException::class,
            ],
            'Test 2021 12 (invalid ICS format)' => [
                'args' => [
                    'calendar' => 'Test',
                    '--month' => '12',
                    '--year' => '2021',
                ],
                'inputICS' => 'invalid.ics',
                'output' => '',
                'exception' => InvalidCalendarException::class,
            ],
        ];
    }
}
