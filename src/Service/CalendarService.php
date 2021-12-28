<?php

declare(strict_types=1);

namespace App\Service;

use App\DataStructure\CalendarConfiguration;
use App\DataStructure\Exception\InvalidCalendarException;
use App\Factory\EventFactory;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\DataStructure\Event;

class CalendarService {
    protected mixed $config;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected EventFactory        $eventFactory,
        protected ConfigLoader        $configLoader,
        protected string              $configPath = '~/.apostolos.yml'
    ) {
        $this->config = $this->configLoader->load($configPath);
    }

    /**
     * @return CalendarConfiguration[]
     */
    public function listCalendars(): array
    {
        return array_map(
            static function (array $calendar): CalendarConfiguration {
                return new CalendarConfiguration(
                    $calendar['name'],
                    $calendar['url']
                );
            },
            $this->config['calendars'] ?? []
        );
    }

    public function getCalendar(string $name): ?CalendarConfiguration
    {
        return array_reduce(
            $this->listCalendars(),
            static function (?CalendarConfiguration $carry, CalendarConfiguration $calendar) use ($name): ?CalendarConfiguration {
                return $calendar->name === $name ? $calendar : $carry;
            },
            null
        );
    }

    /**
     * @return Event[]
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws InvalidCalendarException
     */
    public function getEvents(string $url, string $year, string $month): array
    {
        $calendar = VObject\Reader::read(
            $this
                ->httpClient
                ->request('GET', $url)
                ->getContent()
        );

        if (!empty($calendar->validate())) {
            throw new InvalidCalendarException('Calendar seems to be invalid.: ');
        }

        // @TODO refactor into factory and test
        $events = array_map(
            function (VEvent $event): Event {
                return $this->eventFactory->build($event);
            },
            $calendar->select("VEVENT")
        );

        $events = array_filter(
            $events,
            function (Event $event) use ($year, $month): bool {
                return
                    $month === $event
                        ->start
                        ->format('m') &&

                    $year === $event
                        ->start
                        ->format('Y');
            }
        );

        return array_values($events);
    }
}