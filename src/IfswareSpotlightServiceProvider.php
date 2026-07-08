<?php

declare(strict_types=1);

namespace Ifsware\Spotlight;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Ifsware\Spotlight\Livewire\IfswareSpotlight;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class IfswareSpotlightServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('ifsware-spotlight')
            ->hasConfigFile('ifsware-spotlight')
            ->hasViews('spotlight');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(IfswareSpotlightManager::class);
    }

    public function packageBooted(): void
    {
        Livewire::component('ifsware-spotlight', IfswareSpotlight::class);

        FilamentAsset::register([
            Css::make('ifsware-spotlight', __DIR__.'/../resources/dist/spotlight.css'),
        ]);
    }
}
