<?php

declare(strict_types=1);

namespace Junges\CodingStyle\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Override;
use stdClass;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\confirm;

#[AsCommand(name: 'coding-style:install')]
final class InstallCommand extends Command
{
    /**
     * The stubs published into the application, keyed by their target path.
     *
     * @var array<string, string>
     */
    private const array FILES = [
        'pint.json' => 'pint.json.stub',
        'rector.php' => 'rector.php.stub',
        'phpstan.neon' => 'phpstan.neon.stub',
        '.editorconfig' => 'editorconfig.stub',
    ];

    /**
     * The composer scripts added to the application.
     *
     * @var array<string, string|list<string>>
     */
    private const array SCRIPTS = [
        'lint' => 'pint --parallel',
        'lint:check' => 'pint --parallel --test',
        'rector' => '@php -d memory_limit=-1 vendor/bin/rector process --ansi',
        'rector:check' => '@php -d memory_limit=-1 vendor/bin/rector process --dry-run --ansi',
        'types:check' => 'phpstan analyse --memory-limit=-1',
        'cs' => ['@rector', '@lint', '@types:check'],
        'cs:check' => ['@rector:check', '@lint:check', '@types:check'],
    ];

    #[Override]
    protected $signature = 'coding-style:install
        {--workflow : Publish a GitHub Actions workflow that checks the coding style}
        {--force : Overwrite existing files and composer scripts}';

    #[Override]
    protected $description = 'Install the Pint, Rector and PHPStan configuration and composer scripts';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        foreach (self::FILES as $target => $stub) {
            $this->publish($stub, $target);
        }

        if ($this->shouldPublishWorkflow()) {
            $this->publish('coding-style.yml.stub', '.github/workflows/coding-style.yml', [
                '{{ php }}' => PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION,
            ]);
        }

        if (! $this->addComposerScripts()) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('Coding style installed. Run [composer cs] to apply it.');

        return self::SUCCESS;
    }

    /**
     * Copy a stub into the application, asking before overwriting an existing file.
     *
     * @param  array<string, string>  $replacements
     */
    private function publish(string $stub, string $target, array $replacements = []): void
    {
        $path = $this->laravel->basePath($target);

        if ($this->files->exists($path) && ! $this->shouldOverwrite($target)) {
            $this->components->twoColumnDetail($target, '<fg=yellow;options=bold>SKIPPED</>');

            return;
        }

        $contents = strtr($this->files->get(__DIR__.'/../../stubs/'.$stub), $replacements);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $contents);

        $this->components->twoColumnDetail($target, '<fg=green;options=bold>DONE</>');
    }

    private function shouldOverwrite(string $target): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        return confirm(label: "The [{$target}] file already exists. Do you want to replace it?", default: true);
    }

    private function shouldPublishWorkflow(): bool
    {
        if ($this->option('workflow')) {
            return true;
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        return confirm(label: 'Would you like to publish a GitHub Actions workflow that checks the coding style?', default: true);
    }

    /**
     * Add the coding style scripts to the application's composer.json file.
     *
     * The file is decoded into objects rather than arrays so that empty
     * objects, such as "allow-plugins": {}, survive the round trip.
     */
    private function addComposerScripts(): bool
    {
        $path = $this->laravel->basePath('composer.json');

        if (! $this->files->exists($path)) {
            $this->components->error('Unable to find the composer.json file.');

            return false;
        }

        /** @var stdClass $composer */
        $composer = json_decode($this->files->get($path), flags: JSON_THROW_ON_ERROR);

        // An empty "scripts": [] decodes to an array rather than an object.
        $scripts = $composer->scripts ?? null;

        if (! $scripts instanceof stdClass) {
            $scripts = new stdClass;
        }

        $skipped = [];

        foreach (self::SCRIPTS as $name => $script) {
            $existing = $scripts->{$name} ?? null;

            if ($existing !== null && (array) $existing !== (array) $script && ! $this->option('force')) {
                $skipped[] = $name;

                continue;
            }

            $scripts->{$name} = $script;
        }

        $composer->scripts = $scripts;

        $this->files->put(
            $path,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL,
        );

        $this->components->twoColumnDetail('composer.json scripts', '<fg=green;options=bold>DONE</>');

        if ($skipped !== []) {
            $this->components->warn(sprintf(
                'Kept the existing [%s] composer scripts. Use --force to replace them.',
                implode(', ', $skipped),
            ));
        }

        return true;
    }
}
