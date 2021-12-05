<?php

declare(strict_types=1);

namespace App\Service;

use App\Factory\EventFactory;
use InvalidArgumentException;
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

    public function getCalendar(string $name): ?array // @TODO return calendar object
    {
        foreach ($this->config['calendars'] ?? [] as $cal) {
            if ($cal['name'] === $name) {
                return $cal;
            }
        }

        return null;
    }

    /**
     * @return Event[]
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getEvents(string $name, string $year, string $month): array
    {
        $cal = $this->getCalendar($name);

        if (!$cal) {
            throw new InvalidArgumentException("Calendar $name is not configured.");
        }

        $calendar = VObject\Reader::read(
            $this
                ->httpClient
                ->request('GET', $cal['url'])
                ->getContent()
        );

        if (!empty($calendar->validate())) {
            throw new InvalidArgumentException('Calendar seems to be invalid.: ');
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