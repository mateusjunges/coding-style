# Changelog

All relevant changes to `mateusjunges/coding-style` will be documented here.

## [2026-11-17 v0.1.0](https://github.com/mateusjunges/coding-style/releases/tag/v0.1.0)
* Initial release.
* Add a Pint preset that extends Pint's `laravel` preset with strict types, strict comparisons, final classes, ordered class elements, global namespace imports, multibyte string functions and immutable dates.
* Add a shared Rector configuration, `Junges\CodingStyle\Rector::configure()`, with the dead code, code quality, coding style, type declaration, privatization, instanceof, early return and PHP version sets, skipping the rules that break Laravel applications.
* Add a PHPStan preset at level 7 with Larastan, the Carbon extension, Octane compatibility checks and callable signature checks.
* Add the `coding-style:install` command, which publishes `pint.json`, `rector.php`, `phpstan.neon` and `.editorconfig`, and adds the `cs`, `cs:check`, `lint`, `lint:check`, `rector`, `rector:check` and `types:check` composer scripts.
* Add an optional GitHub Actions workflow that checks the coding style, published with `--workflow`.
* Add an optional `DefaultsServiceProvider` with immutable dates, automatic eager loading, strict models outside production, production safeguards and test guards, published with `--defaults`.
* Add Laravel Boost guidelines, so AI agents follow the coding style.
