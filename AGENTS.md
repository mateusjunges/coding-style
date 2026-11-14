# Coding Style

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `mateusjunges/coding-style`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Quick Commands

- Full validation: `composer test`
- Apply the coding style to the package itself: `composer cs`
- Formatting check: `composer lint:check`
- Rector check: `composer rector:check`
- Static analysis: `composer analyse`
- Pest tests: `composer test:unit`

## Layout

- `presets/` holds the shared Pint and PHPStan configuration that applications reference from `vendor/mateusjunges/coding-style/presets`. Moving or renaming these files breaks every application using the package.
- `src/Rector.php` builds the shared Rector configuration.
- `stubs/` holds the files `coding-style:install` publishes into an application.
- `resources/boost/guidelines/core.blade.php` holds the Laravel Boost guidelines for applications.
- The package dogfoods its own presets through `pint.json`, `phpstan.neon.dist` and `rector.php`.

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
