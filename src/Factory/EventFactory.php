<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataStructure\Event;
use Sabre\VObject\Component\VEvent;

class EventFactory
{
    public function __construct(private DateTimeFactory $dateTimeFactory)
    {
    }

    public function build(VEvent $VEvent): Event
    {
        return new Event(
            $this->dateTimeFactory->build($VEvent->DTSTART),
            $this->dateTimeFactory->build($VEvent->DTEND),
            $VEvent->SUMMARY->getValue()
        );
    }
}
