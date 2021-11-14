<?php

namespace App\Service;

use InvalidArgumentException;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Factory\EventFactory;
use App\DataStructure\Event;

class CalendarService {
    protected mixed $config;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected EventFactory $eventFactory,
        protected ConfigLoader $configLoader,
        protected string $configPath = '~/.apostolos.yml'
    ) {
        $this->config = $this->configLoader->load($configPath);
    }

    public function getCalendarUrl(string $name): string
    {
        return $this->config['calendars'][$name];
    }

    /**
     * @return Event[]
     * 
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getEvents(string $name): array
    {
        $calendar = VObject\Reader::read(
            $this
                ->httpClient
                ->request('GET', $this->getCalendarUrl($name))
                ->getContent()
        );

        if (!empty($calendar->validate())) {
            throw new InvalidArgumentException('Calendar seems to be invalid.');
        }

        // @TODO refactor into factory and test
        $events = array_map(
            function (VEvent $event): Event {
                return $this->eventFactory->build($event);
            },
            $calendar->select("VEVENT")
        );

        return $events;
    }
}