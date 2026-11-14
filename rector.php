<?php

declare(strict_types=1);

use Junges\CodingStyle\Rector;

return Rector::configure(__DIR__)
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withCache(__DIR__.'/build/rector');
