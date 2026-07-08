<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Tests;

use Orchestra\Testbench\TestCase;

abstract class TestbenchTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Filament\FilamentServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            \Ifsware\Spotlight\IfswareSpotlightServiceProvider::class,
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
