<img src="assets/icon.svg" width="64" height="64" alt="">

# Coding Style

My coding style for Laravel applications, packaged so a new application gets the same Pint, Rector and PHPStan setup with one command.

The rules live in this package and the application only keeps thin configuration files that point at them. Updating the package updates the coding style of every application that uses it.

## What it ships

| Tool | Shared configuration | File in the application |
| --- | --- | --- |
| [Pint](https://laravel.com/docs/pint) | `presets/pint.json` | `pint.json` extends the preset |
| [Rector](https://getrector.com) | `Junges\CodingStyle\Rector` | `rector.php` calls `Rector::configure(__DIR__)` |
| [PHPStan](https://phpstan.org) with [Larastan](https://github.com/larastan/larastan) | `presets/phpstan.neon` | `phpstan.neon` includes the preset |
| EditorConfig | | `.editorconfig` |
| GitHub Actions (optional) | | `.github/workflows/coding-style.yml` |

Pint, Rector and Larastan are dependencies of this package, so they don't need to be required separately.

It also ships [Laravel Boost](https://github.com/laravel/boost) guidelines, so AI agents working on the application know to run `composer cs` and follow the same rules.

## Installation

```bash
composer require --dev junges/coding-style
php artisan coding-style:install
```

The install command publishes the configuration files and adds these composer scripts:

| Script | What it does |
| --- | --- |
| `composer cs` | Runs Rector, Pint and PHPStan, fixing what can be fixed |
| `composer cs:check` | Same checks without changing any file (useful in CI) |
| `composer lint` / `composer lint:check` | Pint only |
| `composer rector` / `composer rector:check` | Rector only |
| `composer types:check` | PHPStan only |

When a file already exists the command asks before replacing it. Composer scripts with the same name but a different command are kept. Pass `--force` to replace both without asking, and `--workflow` to publish the GitHub Actions workflow without being asked.

After installing, run `composer cs` once to bring the existing code in line.

## Customizing an application

The application files are regular configuration files, so project specific changes go there.

Pint merges `pint.json` on top of the preset, so a rule can be added or turned off:

```json
{
    "extend": "vendor/junges/coding-style/presets/pint.json",
    "rules": {
        "strict_comparison": false
    }
}
```

Lists are merged by position rather than appended. Setting `notPath` in the application replaces the preset's `tests/TestCase.php` entry, so repeat it when you set your own.

`Rector::configure()` returns Rector's configuration builder, so it can be chained:

```php
return Rector::configure(__DIR__)
    ->withSkip([
        SomeRector::class,
    ]);
```

`withPaths()` replaces the default paths (`app`, `bootstrap`, `config`, `database`, `routes` and `tests`), while `withSkip()` adds to the preset's skip list.

PHPStan parameters set in `phpstan.neon` override the preset, for example a different `level`.

## Notes

The PHPStan preset includes the Larastan and Carbon extensions itself. If the application uses `phpstan/extension-installer`, PHPStan reports those files as included twice. Remove the extension installer, or replace the preset include with your own parameters.

Pint only extends configuration files that live inside the project. Installing this package from a symlinked [path repository](https://getcomposer.org/doc/05-repositories.md#path) makes the preset resolve outside the project and Pint refuses it. Use `"options": {"symlink": false}` when testing local changes.

Rector picks the PHP version sets from the `require.php` constraint in the application's `composer.json`, and caches its results in `storage/framework/cache/rector`.

## Developing the package

```bash
composer test
```

This runs PHPStan, Pint, Rector, type coverage and the Pest suite. The package is checked with its own presets.
