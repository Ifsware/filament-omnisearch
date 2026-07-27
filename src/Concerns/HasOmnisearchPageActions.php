<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Concerns;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Blade;
use Throwable;

/**
 * Add this trait to any Filament resource page to expose its actions in omnisearch.
 *
 * Usage:
 *   class ManageBarbershops extends ManageRecords
 *   {
 *       use HasOmnisearchPageActions;
 *   }
 *
 * Override getOmnisearchActions() to add custom page-specific actions.
 *
 * @phpstan-require-extends Page
 */
trait HasOmnisearchPageActions
{
    use TransConfig;

    public function bootedHasOmnisearchPageActions(): void
    {
        $this->dispatch('omnisearch-page-actions', actions: $this->resolveOmnisearchPageActions());
    }

    /**
     * Override to add custom page-specific actions.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getOmnisearchActions(): array
    {
        return [];
    }

    /**
     * Public accessor so global scopes can index custom actions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOmnisearchCustomActions(): array
    {
        return $this->getOmnisearchActions();
    }

    /** @return class-string<resource> */
    private function getOmnisearchResource(): string
    {
        return static::getResource();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveOmnisearchPageActions(): array
    {
        $group = $this->transConfig('omnisearch.groups.page.label', 'omnisearch::omnisearch.page');
        $resource = $this->getOmnisearchResource();
        $auto = [];

        // Auto-detect "Create" action
        try {
            $auto[] = $this->buildPageAction(
                id: 'page.create',
                group: $group,
                title: __('omnisearch::omnisearch.create_resource', ['resource' => $resource::getModelLabel()]),
                subtitle: __('omnisearch::omnisearch.add_new_resource', ['resource' => strtolower($resource::getModelLabel())]),
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
                subtitle: __('omnisearch::omnisearch.go_to_list', ['resource' => strtolower($resource::getPluralModelLabel())]),
                icon: 'heroicon-o-list-bullet',
                keywords: ['list', 'index', 'back', strtolower($resource::getPluralModelLabel())],
                url: $resource::getUrl('index'),
            );
        } catch (Throwable) {
            // Resource does not have an index page
        }

        return array_merge($auto, $this->getOmnisearchActions());
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
