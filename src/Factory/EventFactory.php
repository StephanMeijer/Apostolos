<?php

namespace App\Factory;

use App\DataStructure\Event;
use DateTime;
use Sabre\VObject\Component\VEvent;

class EventFactory {
    public function build(VEvent $VEvent): Event
    {
        return new Event(
            DateTime::createFromImmutable($VEvent->DTSTART->getDateTime()),
            DateTime::createFromImmutable($VEvent->DTEND->getDateTime()),
            (string) $VEvent->SUMMARY
        );
    }
}