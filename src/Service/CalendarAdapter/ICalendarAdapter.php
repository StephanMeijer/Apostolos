<?php

declare(strict_types=1);

namespace App\Service\CalendarAdapter;

use App\DataStructure\AdapterAction;
use App\DataStructure\Event;
use App\Exception\AdapterDoesNotSupportActionException;
use App\Exception\InvalidCalendarException;
use App\Factory\EventFactory;
use Sabre\VObject;
use Sabre\VObject\Component\VEvent;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ICalendarAdapter implements CalendarAdapterInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected EventFactory $eventFactory,

        // @TODO better loading of parameters
        protected string $url
    ) {
    }

    /**
     * @return Event[]
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws InvalidCalendarException
     */
    public function list(): array
    {
        $calendar = VObject\Reader::read(
            $this
                ->httpClient
                ->request('GET', $this->url)
                ->getContent()
        );

        if (!empty($calendar->validate())) {
            throw new InvalidCalendarException('Calendar seems to be invalid.: ');
        }

        return array_map(
            function (VEvent $event): Event {
                return $this->eventFactory->buildFromVEvent($event);
            },
            $calendar->select('VEVENT')
        );
    }

    /**
     * @throws AdapterDoesNotSupportActionException
     */
    public function create(Event $event): void
    {
        throw new AdapterDoesNotSupportActionException(AdapterAction::CREATE);
    }

    /**
     * @throws AdapterDoesNotSupportActionException
     */
    public function update(Event $event): void
    {
        throw new AdapterDoesNotSupportActionException(AdapterAction::UPDATE);
    }

    /**
     * @throws AdapterDoesNotSupportActionException
     */
    public function delete(Event $event): void
    {
        throw new AdapterDoesNotSupportActionException(AdapterAction::DELETE);
    }
}
