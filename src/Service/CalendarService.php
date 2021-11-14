<?php

namespace App\Service;

use InvalidArgumentException;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CalendarService {
    protected mixed $config;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected string $configPath = '~/.apostolos.yml'
    ) {
        $configPath = str_replace('~', getenv('HOME'), $configPath);

        $this->config = Yaml::parseFile($configPath);
    }

    public function getCalendarUrl(string $name): string
    {
        return $this->config['calendars'][$name];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCalendar(string $name): VObject\Document
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

        return $calendar;
    }
}