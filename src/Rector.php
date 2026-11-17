<?php

declare(strict_types=1);

namespace Junges\CodingStyle;

use Rector\Config\RectorConfig;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Configuration\RectorConfigBuilder;
use Rector\DeadCode\Rector\Property\RemoveDefaultValueFromAssignedPropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;

final class Rector
{
    /**
     * The directories Rector processes in a Laravel application, when present.
     *
     * @var list<string>
     */
    private const array PATHS = [
        'app',
        'bootstrap',
        'config',
        'database',
        'routes',
        'tests',
    ];

    /**
     * Build the Rector configuration for the application at the given path.
     *
     * The returned builder can be chained further, for example to skip
     * additional rules or to process a different set of paths.
     */
    public static function configure(string $basePath): RectorConfigBuilder
    {
        return RectorConfig::configure()
            ->withPaths(self::paths($basePath))
            ->withSkip([
                $basePath.'/bootstrap/cache',
                $basePath.'/database/migrations',

                // Treats strings as nullable and adds unnecessary casts.
                NullToStrictStringFuncCallArgRector::class,

                // Turns route actions into (new Controller())->show(...).
                ArrayToFirstClassCallableRector::class,

                // Breaks the magic of properties such as protected array $validationRules = [].
                PrivatizeFinalClassPropertyRector::class,

                // Getters are sometimes required, for example by an interface.
                PrivatizeLocalGetterToPropertyRector::class,

                PrivatizeFinalClassMethodRector::class,

                // unserialize() fills missing properties from their declared defaults without calling the
                // constructor. Both rules move or drop those defaults, so an old queued job or cached object
                // missing the key would be left with an uninitialized property.
                ClassPropertyAssignToConstructorPromotionRector::class,
                RemoveDefaultValueFromAssignedPropertyRector::class,

                // Splitting `use HasFactory, Notifiable;` drops its `@use HasFactory<UserFactory>` docblock,
                // which PHPStan needs for the factory's generic type.
                SeparateMultiUseImportsRector::class,
            ])
            ->withPreparedSets(
                deadCode: true,
                codeQuality: true,
                codingStyle: true,
                typeDeclarations: true,
                privatization: true,
                instanceOf: true,
                earlyReturn: true,
            )
            ->withPhpSets()
            // Import the class names Rector adds, such as return types, instead of writing them fully qualified.
            // Docblocks are left as written, so fully qualified names in PHPDoc types stay untouched.
            ->withImportNames(importDocBlockNames: false, importShortClasses: false)
            ->withCache($basePath.'/storage/framework/cache/rector');
    }

    /**
     * Resolve the directories that exist in the application.
     *
     * @return list<string>
     */
    public static function paths(string $basePath): array
    {
        return array_values(array_filter(
            array_map(fn (string $path): string => $basePath.'/'.$path, self::PATHS),
            is_dir(...),
        ));
    }
}
