## Coding style

This application follows the coding style shipped by `junges/coding-style`, enforced with Pint, Rector and PHPStan (Larastan).

- After changing PHP files, run `composer cs`. It applies Rector refactorings, formats the code with Pint and runs PHPStan. Use `composer cs:check` to verify without changing files.
- Every PHP file starts with `declare(strict_types=1);`.
- Classes are `final` unless they are abstract. Pint adds the keyword automatically, so a class that needs to be extended must become `abstract`, and a dependency that tests need to mock should be bound to an interface. Only when neither is possible, add a `@final` tag to the class docblock so Pint leaves it non-final.
- Import classes, functions and constants with `use` statements instead of referencing them with a leading backslash.
- Use strict comparisons (`===`, `!==`), `mb_*` string functions and `DateTimeImmutable` (or `CarbonImmutable`) instead of `DateTime`.
- Never edit the shared rules in `vendor/junges/coding-style`. Project specific changes belong in `pint.json`, `rector.php` and `phpstan.neon`.
