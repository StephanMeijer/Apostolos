<?php

declare(strict_types=1);

namespace App\Tests\Service\CalendarAdapter;

use App\DataStructure\Event;
use App\Exception\AdapterDoesNotSupportActionException;
use App\Factory\EventFactory;
use App\Service\CalendarAdapter\ICalendarAdapter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ICalAdapterTest extends KernelTestCase
{
    // @TODO test list

    /**
     * @dataProvider failingMethodNamesProvider
     */
    public function testMethodsFailing(string $methodName): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $factory = $this->createMock(EventFactory::class);

        $adapter = new ICalendarAdapter($httpClient, $factory, 'some url');

        $this->expectException(AdapterDoesNotSupportActionException::class);

        $adapter->$methodName($this->createMock(Event::class));
    }

    /**
     * @return string[][]
     */
    public function failingMethodNamesProvider(): array
    {
        return [['create'], ['delete'], ['update']];
    }
}
