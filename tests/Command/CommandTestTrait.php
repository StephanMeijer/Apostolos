<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Service\ConfigLoader;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait CommandTestTrait {
    protected function loadFromFixtures(string $filename): string
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $filename);
    }

    protected function httpResponseFromFixtures(string $filename): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($this->loadFromFixtures($filename));

        return $response;
    }

    protected function configLoaderLoadingFixture(string $filename): ConfigLoader
    {
        $realConfigLoader = new ConfigLoader();
        $configLoader = $this->createMock(ConfigLoader::class);
        $configLoader
            ->expects($this->once())
            ->method('load')
            ->with('~/.apostolos.yml')
            ->willReturn(
                $realConfigLoader->load(__DIR__.'/Fixtures/' . $filename)
            );

        return $configLoader;
    }

    protected function httpClientRespondingFixtures(array $params): HttpClientInterface
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects($this->exactly(count($params)))
            ->method('request')
            ->withConsecutive(
                ...array_map(
                    function(array $param) {
                        return [ 'GET', $param['url'] ];
                    },
                    $params
                )
            )
            ->willReturnOnConsecutiveCalls(
                ...array_map(
                    function (array $param) {
                        return $this->httpResponseFromFixtures($param['fixture']);
                    },
                    $params
                )
            );

        return $httpClient;
    }
}