<?php

declare(strict_types=1);

namespace App\Service;

use App\DataStructure\CalendarConfiguration;
use App\DataStructure\Date;
use App\DataStructure\Duration;
use App\DataStructure\Event;
use App\DataStructure\Period;
use App\Exception\InvalidCalendarException;
use App\Factory\EventFactory;
use App\Service\CalendarAdapter\ICalendarAdapter;
use DateTime;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CalendarService
{
    protected mixed $config;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected EventFactory $eventFactory,
        protected ConfigLoader $configLoader,
        protected string $configPath = '~/.apostolos.yml'
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
            static function (
                ?CalendarConfiguration $carry,
                CalendarConfiguration $calendar
            ) use ($name): ?CalendarConfiguration {
                return $calendar->name === $name ? $calendar : $carry;
            },
            null
        );
    }

    /**
     * @param string[] $days
     * @return Event[]
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws InvalidCalendarException
     */
    public function getEvents(string $url, ?array $days): array
    {
        $icalAdapter = new ICalendarAdapter($this->httpClient, $this->eventFactory, $url);

        $events = $icalAdapter->list();

        if (!is_null($days)) {
            $events = array_filter(
                $events,
                function (Event $event) use ($days): bool {
                    return in_array($event->start->format('Y-m-d'), $days);
                }
            );
        }

        return array_values($events);
    }

    /**
     * @param string[] $days
     *
     * @return Period[]
     *
     * @throws ClientExceptionInterface
     * @throws InvalidCalendarException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getPeriods(string $url, ?array $days): array
    {
        $events = $this->getEvents($url, $days);

        $periods = array_reduce(
            $events,
            /**
             * @param Period[] $acc
             *
             * @return Period[]
             *
             * @throws Exception
             */
            static function (array $acc, Event $event): array {
                $dateEquals = static function (DateTime $a, DateTime $b): bool {
                    return $a->format('Y-m-d') === $b->format('Y-m-d');
                };

                $found = false;

                foreach ($acc as $period) {
                    if ($dateEquals($period->date->toDateTime(), $event->start)) {
                        $period->duration->addMinutes($event->minutes());
                        $found = true;
                    }
                }

                if (!$found) {
                    $acc[] = new Period(
                        Date::fromDateTime($event->start),
                        new Duration($event->minutes())
                    );
                }

                return $acc;
            },
            []
        );

        sort($periods);

        return $periods;
    }
}
