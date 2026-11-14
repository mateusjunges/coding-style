## Coding style

This application follows the coding style shipped by `mateusjunges/coding-style`, enforced with Pint, Rector and PHPStan (Larastan).

- After changing PHP files, run `composer cs`. It applies Rector refactorings, formats the code with Pint and runs PHPStan. Use `composer cs:check` to verify without changing files.
- Every PHP file starts with `declare(strict_types=1);`.
- Classes are `final` unless they are abstract. Pint adds the keyword automatically. When a concrete class needs to be extended, move the shared code into an `abstract` parent that both concrete classes extend. A `@final` docblock tag does not help there, because PHPStan reports extending a `@final` class.
- Bind dependencies that tests mock to an interface. When a class has to be mocked and an interface is not worth it, add a `@final` tag to its docblock so Pint leaves it non-final and Mockery can mock it.
- Import classes, functions and constants with `use` statements instead of referencing them with a leading backslash.
- Use strict comparisons (`===`, `!==`), `mb_*` string functions and `DateTimeImmutable` (or `CarbonImmutable`) instead of `DateTime`.
- When `app/Providers/DefaultsServiceProvider.php` exists, the application runs with these defaults:
    - Dates are `CarbonImmutable`, so keep the result of a date change (`$date = $date->addDay()`).
    - Outside production, models throw on lazy loading, on reading an attribute that wasn't selected, and on mass assigning an attribute that isn't fillable. Select every attribute the code reads, and eager load relationships outside collections.
    - In tests, HTTP requests that weren't faked throw and sleeps are faked. Fake every HTTP call with `Http::fake()`.
- Never edit the shared rules in `vendor/mateusjunges/coding-style`. Project specific changes belong in `pint.json`, `rector.php` and `phpstan.neon`.
