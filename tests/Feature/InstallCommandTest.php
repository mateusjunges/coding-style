<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

beforeEach(function (): void {
    $this->basePath = sys_get_temp_dir().'/coding-style-'.bin2hex(random_bytes(6));

    mkdir($this->basePath);

    file_put_contents($this->basePath.'/composer.json', <<<'JSON'
        {
            "name": "laravel/laravel",
            "require": {
                "php": "^8.3"
            },
            "extra": {
                "laravel": {
                    "dont-discover": []
                }
            },
            "config": {
                "allow-plugins": {}
            },
            "scripts": {
                "test": [
                    "@php artisan test"
                ]
            }
        }
        JSON);

    $this->app->setBasePath($this->basePath);
});

afterEach(function (): void {
    (new Filesystem)->deleteDirectory($this->basePath);
});

/**
 * @return array<string, mixed>
 */
function composerJson(string $basePath): array
{
    return json_decode((string) file_get_contents($basePath.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
}

it('publishes the configuration files', function (): void {
    $this->artisan('coding-style:install', ['--no-interaction' => true])->assertSuccessful();

    expect($this->basePath.'/pint.json')->toBeFile()
        ->and(json_decode((string) file_get_contents($this->basePath.'/pint.json'), true))
        ->toBe(['extend' => 'vendor/junges/coding-style/presets/pint.json'])
        ->and(file_get_contents($this->basePath.'/rector.php'))->toContain('Rector::configure(__DIR__)')
        ->and(file_get_contents($this->basePath.'/phpstan.neon'))->toContain('vendor/junges/coding-style/presets/phpstan.neon')
        ->and($this->basePath.'/.editorconfig')->toBeFile()
        ->and($this->basePath.'/.github/workflows/coding-style.yml')->not->toBeFile();
});

it('adds the composer scripts and keeps the rest of composer.json intact', function (): void {
    $this->artisan('coding-style:install', ['--no-interaction' => true])->assertSuccessful();

    $composer = composerJson($this->basePath);

    expect($composer['scripts'])
        ->toMatchArray([
            'test' => ['@php artisan test'],
            'lint' => 'pint --parallel',
            'lint:check' => 'pint --parallel --test',
            'types:check' => 'phpstan analyse --memory-limit=-1',
            'cs' => ['@rector', '@lint', '@types:check'],
            'cs:check' => ['@rector:check', '@lint:check', '@types:check'],
        ])
        ->toHaveKeys(['rector', 'rector:check']);

    expect((string) file_get_contents($this->basePath.'/composer.json'))
        ->toContain('"allow-plugins": {}')
        ->toContain('"dont-discover": []');
});

it('keeps existing files when running without interaction', function (): void {
    file_put_contents($this->basePath.'/pint.json', '{"preset": "psr12"}');

    $this->artisan('coding-style:install', ['--no-interaction' => true])
        ->expectsOutputToContain('SKIPPED')
        ->assertSuccessful();

    expect(file_get_contents($this->basePath.'/pint.json'))->toBe('{"preset": "psr12"}');
});

it('asks before replacing an existing file', function (bool $answer, string $expected): void {
    file_put_contents($this->basePath.'/pint.json', '{"preset": "psr12"}');

    $this->artisan('coding-style:install')
        ->expectsConfirmation('The [pint.json] file already exists. Do you want to replace it?', $answer ? 'yes' : 'no')
        ->expectsConfirmation('Would you like to publish a GitHub Actions workflow that checks the coding style?', 'no')
        ->assertSuccessful();

    expect(file_get_contents($this->basePath.'/pint.json'))->toContain($expected);
})->with([
    'replaced' => [true, 'vendor/junges/coding-style/presets/pint.json'],
    'kept' => [false, 'psr12'],
]);

it('replaces existing files with the force option', function (): void {
    file_put_contents($this->basePath.'/pint.json', '{"preset": "psr12"}');

    $this->artisan('coding-style:install', ['--force' => true, '--no-interaction' => true])->assertSuccessful();

    expect(file_get_contents($this->basePath.'/pint.json'))->toContain('vendor/junges/coding-style/presets/pint.json');
});

it('keeps different existing composer scripts unless forced', function (): void {
    $composer = composerJson($this->basePath);
    $composer['scripts']['lint'] = ['pint'];
    $composer['scripts']['lint:check'] = ['pint --parallel --test'];
    file_put_contents($this->basePath.'/composer.json', json_encode($composer));

    $this->artisan('coding-style:install', ['--no-interaction' => true])
        ->expectsOutputToContain('Kept the existing [lint] composer scripts')
        ->assertSuccessful();

    expect(composerJson($this->basePath)['scripts']['lint'])->toBe(['pint']);

    $this->artisan('coding-style:install', ['--force' => true, '--no-interaction' => true])->assertSuccessful();

    expect(composerJson($this->basePath)['scripts']['lint'])->toBe('pint --parallel');
});

it('publishes the GitHub Actions workflow for the running PHP version', function (): void {
    $this->artisan('coding-style:install', ['--workflow' => true, '--no-interaction' => true])->assertSuccessful();

    expect(file_get_contents($this->basePath.'/.github/workflows/coding-style.yml'))
        ->toContain(sprintf("php-version: '%d.%d'", PHP_MAJOR_VERSION, PHP_MINOR_VERSION))
        ->toContain('composer lint:check')
        ->toContain('composer rector:check')
        ->toContain('composer types:check')
        ->not->toContain('{{ php }}');
});

it('fails when the application has no composer.json file', function (): void {
    unlink($this->basePath.'/composer.json');

    $this->artisan('coding-style:install', ['--no-interaction' => true])
        ->expectsOutputToContain('Unable to find the composer.json file.')
        ->assertFailed();
});
