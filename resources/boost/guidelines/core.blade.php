## Coding style

This application follows the coding style shipped by `junges/coding-style`, enforced with Pint, Rector and PHPStan (Larastan).

- After changing PHP files, run `composer cs`. It applies Rector refactorings, formats the code with Pint and runs PHPStan. Use `composer cs:check` to verify without changing files.
- Every PHP file starts with `declare(strict_types=1);`.
- Import classes, functions and constants with `use` statements instead of referencing them with a leading backslash.
- Use strict comparisons (`===`, `!==`), `mb_*` string functions and `DateTimeImmutable` (or `CarbonImmutable`) instead of `DateTime`.
- Never edit the shared rules in `vendor/junges/coding-style`. Project specific changes belong in `pint.json`, `rector.php` and `phpstan.neon`.
