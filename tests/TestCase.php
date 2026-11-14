<?php

declare(strict_types=1);

namespace Junges\CodingStyle\Tests;

use Junges\CodingStyle\CodingStyleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CodingStyleServiceProvider::class,
        ];
    }
}
