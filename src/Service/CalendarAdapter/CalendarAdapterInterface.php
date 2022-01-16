<?php

declare(strict_types=1);

namespace App\Service\CalendarAdapter;

use App\DataStructure\Event;
use App\Exception\AdapterDoesNotSupportActionException;

interface CalendarAdapterInterface {
    /**
     * @throws AdapterDoesNotSupportActionException
     * @return Event[]
     */
    public function list(): array;

    /**
     * @throws AdapterDoesNotSupportActionException
     */
    public function create(Event $event): void;

    /**
     * @throws AdapterDoesNotSupportActionException
     */
    public function update(Event $event): void;

    /**
     * @throws AdapterDoesNotSupportActionException
     */
    public function delete(Event $event): void;
}