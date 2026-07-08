<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Ifsware\Omnisearch\Livewire\Omnisearch;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class OmnisearchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('omnisearch')
            ->hasConfigFile('omnisearch')
            ->hasViews('omnisearch');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OmnisearchManager::class);
    }

    public function packageBooted(): void
    {
        Livewire::component('omnisearch', Omnisearch::class);

        FilamentAsset::register([
            Css::make('omnisearch', __DIR__.'/../resources/dist/omnisearch.css'),
        ]);
    }
}
