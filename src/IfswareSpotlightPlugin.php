<?php

declare(strict_types=1);

namespace Ifsware\Spotlight;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;

final class IfswareSpotlightPlugin implements Plugin
{
    protected bool $disableDefaultGlobalSearch = true;

    /** @var array<int, IfswareSpotlightAction> */
    protected array $actions = [];

    public static function make(): static
    {
        return app(self::class);
    }

    public function getId(): string
    {
        return 'ifsware-spotlight';
    }

    /** @param array<int, IfswareSpotlightAction> $actions */
    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function disableDefaultGlobalSearch(bool $disable = true): static
    {
        $this->disableDefaultGlobalSearch = $disable;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if (! config('ifsware-spotlight.enabled', true)) {
            return;
        }

        if ($this->disableDefaultGlobalSearch) {
            $panel->globalSearch(false);
        }

        $panel->renderHook(
            PanelsRenderHook::BODY_START,
            fn (): View => view()->make('spotlight::components.mount'),
        );

        $panel->renderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            function (): string|View {
                if (! Filament::hasTopbar()) {
                    return '';
                }

                return view()->make('spotlight::components.trigger');
            },
        );

        $panel->renderHook(
            PanelsRenderHook::SIDEBAR_NAV_START,
            function (): string|View {
                if (Filament::hasTopbar()) {
                    return '';
                }

                return view()->make('spotlight::components.trigger-sidebar');
            },
        );
    }

    public function boot(Panel $panel): void
    {
        if ($this->actions !== []) {
            app(IfswareSpotlightManager::class)->registerActions($this->actions);
        }
    }
}
