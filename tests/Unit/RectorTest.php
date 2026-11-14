<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Junges\CodingStyle\Rector;
use Rector\Configuration\RectorConfigBuilder;

beforeEach(function (): void {
    $this->basePath = sys_get_temp_dir().'/coding-style-'.bin2hex(random_bytes(6));

    mkdir($this->basePath.'/app', recursive: true);
    mkdir($this->basePath.'/routes');
    mkdir($this->basePath.'/tests');
});

afterEach(function (): void {
    (new Filesystem)->deleteDirectory($this->basePath);
});

it('only processes the application directories that exist', function (): void {
    expect(Rector::paths($this->basePath))->toBe([
        $this->basePath.'/app',
        $this->basePath.'/routes',
        $this->basePath.'/tests',
    ]);
});

it('returns a builder the application can keep configuring', function (): void {
    expect(Rector::configure($this->basePath)->withSkip([$this->basePath.'/tests']))
        ->toBeInstanceOf(RectorConfigBuilder::class);
});
