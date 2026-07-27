<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\FilamentServiceProvider;
use Ifsware\Omnisearch\OmnisearchServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class TestbenchTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            LivewireServiceProvider::class,
            OmnisearchServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
