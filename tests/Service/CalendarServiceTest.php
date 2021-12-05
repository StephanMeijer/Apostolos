<?php

declare(strict_types=1);

namespace App\Tests\DataStructure;

use App\DataStructure\Event;
use App\Factory\EventFactory;
use App\Service\CalendarService;
use App\Factory\DateTimeFactory;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CalendarServiceTest extends KernelTestCase
{
    public function testDecodingIcal(): void
    {
        $calendars =[
            [
                'rate' => 123,
                'url' => 'http://example.com/events.ics',
                'name' => 'Client1'
            ],
            [
                'rate' => 12,
                'url' => 'http://example2.com/events.ics',
                'name' => 'Client12'
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getContent')
            ->willReturn(
                file_get_contents(__DIR__ . '/Fixtures/events.ics')
            );

        $config = $this->createMock(\App\Service\ConfigLoader::class);
        $config->expects($this->once())
            ->method('load')
            ->willReturn([ 'calendars' => $calendars ]);

        $http = $this->createMock(HttpClientInterface::class);
        $http->expects($this->once())
            ->method('request')
            ->with('GET', 'http://example.com/events.ics')
            ->willReturn($response);

        $calendarService = new CalendarService($http, new EventFactory(new DateTimeFactory()), $config, 'some-path');

        $events = $calendarService->getEvents('Client1', '2021', '11');

        $this->assertContainsOnlyInstancesOf(Event::class, $events);
        $this->assertCount(3, $events);

        $this->assertEquals(
            [
                new Event(
                    new \DateTime('2021-11-08 12:10:00.000000'),
                    new \DateTime('2021-11-08 20:35:00.000000'),
                    'Test event'
                ),
                new Event(
                    new \DateTime('2021-11-12 01:05:00.000000'),
                    new \DateTime('2021-11-12 05:05:00.000000'),
                    'Test event'
                ),
                new Event(
                    new \DateTime('2021-11-12 01:20:00.000000'),
                    new \DateTime('2021-11-12 05:05:00.000000'),
                    'Test event123'
                )
            ],
            $events
        );
    }
}