<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\CalendarConfiguration;
use App\DataStructure\Event;
use App\Factory\DateTimeFactory;
use App\Factory\EventFactory;
use App\Service\CalendarService;
use App\Service\ConfigLoader;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CalendarServiceTest extends KernelTestCase
{
    public function testListCalendars(): void
    {
        $config = $this->configMock();

        $http = $this->createMock(HttpClientInterface::class);
        $eventFactory = $this->createMock(EventFactory::class);

        $calendarService = new CalendarService($http, $eventFactory, $config, 'some-path');

        $configurations = $calendarService->listCalendars();

        $this->assertCount(2, $configurations);
        $this->assertContainsOnlyInstancesOf(CalendarConfiguration::class, $configurations);

        $this->assertEquals(
            new CalendarConfiguration('Client1', 'http://example.com/events.ics'),
            $configurations[0]
        );

        $this->assertEquals(
            new CalendarConfiguration('Client12', 'http://example2.com/events.ics'),
            $configurations[1]
        );
    }

    public function testDecodingIcal(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getContent')
            ->willReturn(
                file_get_contents(__DIR__.'/Fixtures/events.ics')
            );

        $config = $this->configMock();

        $http = $this->createMock(HttpClientInterface::class);
        $http->expects($this->once())
            ->method('request')
            ->with('GET', 'http://example.com/events.ics')
            ->willReturn($response);

        $calendarService = new CalendarService($http, new EventFactory(new DateTimeFactory()), $config, 'some-path');

        $events = $calendarService->getEvents('http://example.com/events.ics', '2021', '11');

        $this->assertContainsOnlyInstancesOf(Event::class, $events);
        $this->assertCount(3, $events);

        $this->assertEquals(
            [
                new Event(
                    null,
                    new DateTime('2021-11-08 12:10:00.000000'),
                    new DateTime('2021-11-08 20:35:00.000000'),
                    'Test event'
                ),
                new Event(
                    null,
                    new DateTime('2021-11-12 01:05:00.000000'),
                    new DateTime('2021-11-12 05:05:00.000000'),
                    'Test event'
                ),
                new Event(
                    null,
                    new DateTime('2021-11-12 01:20:00.000000'),
                    new DateTime('2021-11-12 05:05:00.000000'),
                    'Test event123'
                ),
            ],
            $events
        );
    }

    private function configMock(): ConfigLoader
    {
        $calendars = [
            [
                'rate' => 123,
                'url' => 'http://example.com/events.ics',
                'name' => 'Client1',
            ],
            [
                'rate' => 12,
                'url' => 'http://example2.com/events.ics',
                'name' => 'Client12',
            ],
        ];

        $config = $this->createMock(ConfigLoader::class);
        $config->expects($this->once())
            ->method('load')
            ->willReturn(['calendars' => $calendars]);

        return $config;
    }
}
