<?php

declare(strict_types=1);

namespace Junges\CodingStyle;

use Illuminate\Support\ServiceProvider;
use Junges\CodingStyle\Console\InstallCommand;

final class CodingStyleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }
}
