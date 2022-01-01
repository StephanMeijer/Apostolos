<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    protected mixed $config;

    public function load(string $path): mixed
    {
        return Yaml::parseFile(str_replace('~', getenv('HOME'), $path));
    }
}
