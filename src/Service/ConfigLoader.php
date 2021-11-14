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

class ConfigLoader {
    protected mixed $config;

    public function load(string $path): mixed
    {
        return Yaml::parseFile(str_replace('~', getenv('HOME'), $path));
    }
}