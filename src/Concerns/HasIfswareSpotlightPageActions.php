<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Concerns;

use Filament\Resources\Resource;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Throwable;

/**
 * Add this trait to any Filament resource page to expose its actions in spotlight.
 *
 * Usage:
 *   class ManageBarbershops extends ManageRecords
 *   {
 *       use HasSpotlightPageActions;
 *   }
 *
 * Override getSpotlightActions() to add custom page-specific actions.
 *
 * @phpstan-require-extends \Filament\Resources\Pages\Page
 */
trait HasIfswareSpotlightPageActions
{
    public function bootedHasIfswareSpotlightPageActions(): void
    {
        $this->dispatch('spotlight-page-actions', actions: $this->resolveSpotlightPageActions());
    }

    /**
     * Override to add custom page-specific actions.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getSpotlightActions(): array
    {
        return [];
    }

    /** @return class-string<Resource> */
    private function getSpotlightResource(): string
    {
        return static::getResource();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveSpotlightPageActions(): array
    {
        $group = Config::string('ifsware-spotlight.groups.page.label', 'Page');
        $resource = $this->getSpotlightResource();
        $auto = [];

        // Auto-detect "Create" action
        try {
            $auto[] = $this->buildPageAction(
                id: 'page.create',
                group: $group,
                title: 'Create '.$resource::getModelLabel(),
                subtitle: 'Add a new '.strtolower($resource::getModelLabel()),
                icon: 'heroicon-o-plus-circle',
                keywords: ['create', 'new', 'add', strtolower($resource::getModelLabel())],
                url: $resource::getUrl('create'),
            );
        } catch (Throwable) {
            // Resource does not have a create page
        }

        // Auto-detect "List" action (useful on edit/view pages)
        try {
            $auto[] = $this->buildPageAction(
                id: 'page.index',
                group: $group,
                title: $resource::getPluralModelLabel(),
                subtitle: 'Back to the '.strtolower($resource::getPluralModelLabel()).' list',
                icon: 'heroicon-o-list-bullet',
                keywords: ['list', 'index', 'back', strtolower($resource::getPluralModelLabel())],
                url: $resource::getUrl('index'),
            );
        } catch (Throwable) {
            // Resource does not have an index page
        }

        return array_merge($auto, $this->getSpotlightActions());
    }

    /**
     * @param  array<int, string>  $keywords
     * @return array<string, mixed>
     */
    private function buildPageAction(
        string $id,
        string $group,
        string $title,
        string $subtitle,
        string $icon,
        array $keywords,
        string $url,
    ): array {
        $iconHtml = rescue(
            fn () => Blade::render("<x-{$icon} style=\"width:20px;height:20px\" />"),
            '',
        );

        return compact('id', 'group', 'title', 'subtitle', 'icon', 'keywords', 'url', 'iconHtml') + ['type' => 'url'];
    }
}
