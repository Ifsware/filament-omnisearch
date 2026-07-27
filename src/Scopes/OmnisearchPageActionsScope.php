<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Scopes;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Ifsware\Omnisearch\Concerns\HasOmnisearchPageActions;
use Ifsware\Omnisearch\Concerns\MatchesOmnisearchQuery;
use Ifsware\Omnisearch\Concerns\TransConfig;
use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Illuminate\Support\Facades\Blade;
use Throwable;

final class OmnisearchPageActionsScope implements OmnisearchScope
{
    use MatchesOmnisearchQuery;
    use TransConfig;

    public function isActive(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array{id: string, type: 'url'|'modal'|'action', group: string, title: string, subtitle: string, icon: string, keywords: array<int, string>, url?: string, modalId?: string, action?: callable, shortcut?: string}>
     */
    public function getItems(string $query, array $context): array
    {
        if ($query === '') {
            return [];
        }

        $group = $this->transConfig('omnisearch.groups.page.label', 'omnisearch::omnisearch.page');
        $items = [];

        foreach (Filament::getResources() as $resourceClass) {
            if (! is_a($resourceClass, Resource::class, true)) {
                continue;
            }

            if (! $this->resourceHasPageActions($resourceClass)) {
                continue;
            }

            try {
                $items[] = [
                    'id' => 'page.index.'.$resourceClass::getSlug(),
                    'type' => 'url',
                    'group' => $group,
                    'title' => $resourceClass::getPluralModelLabel(),
                    'subtitle' => __('omnisearch::omnisearch.go_to_list', ['resource' => strtolower($resourceClass::getPluralModelLabel())]),
                    'icon' => 'heroicon-o-list-bullet',
                    'keywords' => ['list', 'index', strtolower($resourceClass::getPluralModelLabel()), strtolower($resourceClass::getModelLabel())],
                    'url' => $resourceClass::getUrl('index'),
                    'iconHtml' => rescue(fn (): string => Blade::render('<x-heroicon-o-list-bullet style="width:20px;height:20px" />'), ''),
                ];
            } catch (Throwable) {
            }

            try {
                $items[] = [
                    'id' => 'page.create.'.$resourceClass::getSlug(),
                    'type' => 'url',
                    'group' => $group,
                    'title' => __('omnisearch::omnisearch.create_resource', ['resource' => $resourceClass::getModelLabel()]),
                    'subtitle' => __('omnisearch::omnisearch.add_new_resource', ['resource' => strtolower($resourceClass::getModelLabel())]),
                    'icon' => 'heroicon-o-plus-circle',
                    'keywords' => ['create', 'new', 'add', strtolower($resourceClass::getModelLabel())],
                    'url' => $resourceClass::getUrl('create'),
                    'iconHtml' => rescue(fn (): string => Blade::render('<x-heroicon-o-plus-circle style="width:20px;height:20px" />'), ''),
                ];
            } catch (Throwable) {
            }

            // Index custom actions defined in getOmnisearchActions() on each page
            try {
                foreach ($resourceClass::getPages() as $route => $registration) {
                    $pageClass = $registration->getPage();

                    if (! in_array(HasOmnisearchPageActions::class, class_uses_recursive($pageClass), true)) {
                        continue;
                    }

                    foreach ($this->resolvePageCustomActions($pageClass) as $action) {
                        $id = $action['id'] ?? null;

                        if (! is_string($id) || $id === '') {
                            continue;
                        }

                        $action['id'] = 'page.'.$route.'.'.$resourceClass::getSlug().'.'.$id;
                        $action['group'] = empty($action['group']) ? $group : $action['group'];

                        $items[] = $action;
                    }
                }
            } catch (Throwable) {
            }
        }

        /** @var list<array{id: string, type: 'url'|'modal'|'action', group: string, title: string, subtitle: string, icon: string, keywords: array<int, string>, url?: string, modalId?: string, action?: callable, shortcut?: string}> $items */
        return array_values(array_filter(
            $items,
            fn (array $item): bool => $this->matchesQuery($item, $query),
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolvePageCustomActions(string $pageClass): array
    {
        $page = app($pageClass);

        if (! is_object($page) || ! method_exists($page, 'getOmnisearchCustomActions')) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $result */
        $result = $page->getOmnisearchCustomActions();

        return $result;
    }

    /** @param class-string<resource> $resourceClass */
    private function resourceHasPageActions(string $resourceClass): bool
    {
        try {
            foreach ($resourceClass::getPages() as $registration) {
                $pageClass = $registration->getPage();

                if (in_array(HasOmnisearchPageActions::class, class_uses_recursive($pageClass), true)) {
                    return true;
                }
            }
        } catch (Throwable) {
        }

        return false;
    }
}
