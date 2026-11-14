<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Validation\Rules\Password;

/**
 * Render the published stub into a namespace of its own and load it once.
 */
function defaultsProvider(): string
{
    $class = 'CodingStyleFixtures\\DefaultsServiceProvider';

    if (! class_exists($class, false)) {
        $path = sys_get_temp_dir().'/coding-style-defaults-'.bin2hex(random_bytes(6)).'.php';

        file_put_contents($path, strtr(
            (string) file_get_contents(__DIR__.'/../../stubs/DefaultsServiceProvider.php.stub'),
            ['{{ namespace }}' => 'CodingStyleFixtures'],
        ));

        require $path;

        unlink($path);
    }

    return $class;
}

function prohibitsDestructiveCommands(): bool
{
    return (bool) new ReflectionProperty(WipeCommand::class, 'prohibitedFromRunning')->getValue();
}

afterEach(function (): void {
    Model::shouldBeStrict(false);
    Model::automaticallyEagerLoadRelationships(false);
    Date::useDefault();
    DB::prohibitDestructiveCommands(false);
    Sleep::fake(false);
    Password::$defaultCallback = null;
});

it('applies the defaults outside production', function (): void {
    $this->app->register(defaultsProvider());

    expect(now())->toBeInstanceOf(CarbonImmutable::class)
        ->and(Model::isAutomaticallyEagerLoadingRelationships())->toBeTrue()
        ->and(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(prohibitsDestructiveCommands())->toBeFalse()
        ->and(Password::default()->appliedRules()['min'])->toBe(8)
        ->and(url('/'))->toStartWith('http://');
});

it('guards tests against stray requests and real sleeps', function (): void {
    $this->app->register(defaultsProvider());

    Sleep::for(5)->seconds();

    Sleep::assertSleptTimes(1);

    expect(Http::preventingStrayRequests())->toBeTrue();
});

it('applies the production defaults in production', function (): void {
    $this->app['env'] = 'production';

    $this->app->register(defaultsProvider());

    expect(Model::preventsLazyLoading())->toBeFalse()
        ->and(Model::preventsAccessingMissingAttributes())->toBeFalse()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeFalse()
        ->and(Model::isAutomaticallyEagerLoadingRelationships())->toBeTrue()
        ->and(prohibitsDestructiveCommands())->toBeTrue()
        ->and(Password::default()->appliedRules())->toMatchArray([
            'min' => 12,
            'mixedCase' => true,
            'letters' => true,
            'numbers' => true,
            'symbols' => true,
            'uncompromised' => true,
        ])
        ->and(url('/'))->toStartWith('https://')
        ->and(Http::preventingStrayRequests())->toBeFalse();
});
