<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Scopes;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Ifsware\Omnisearch\Concerns\HasOmnisearchPageActions;
use Ifsware\Omnisearch\Concerns\MatchesOmnisearchQuery;
use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Throwable;

final class OmnisearchPageActionsScope implements OmnisearchScope
{
    use MatchesOmnisearchQuery;

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

        $group = Config::string('omnisearch.groups.page.label', 'Page');
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
                    'id'       => 'page.index.'.$resourceClass::getSlug(),
                    'type'     => 'url',
                    'group'    => $group,
                    'title'    => $resourceClass::getPluralModelLabel(),
                    'subtitle' => 'Go to the '.strtolower($resourceClass::getPluralModelLabel()).' list',
                    'icon'     => 'heroicon-o-list-bullet',
                    'keywords' => ['list', 'index', strtolower($resourceClass::getPluralModelLabel()), strtolower($resourceClass::getModelLabel())],
                    'url'      => $resourceClass::getUrl('index'),
                    'iconHtml' => rescue(fn (): string => Blade::render('<x-heroicon-o-list-bullet style="width:20px;height:20px" />'), ''),
                ];
            } catch (Throwable) {
            }

            try {
                $items[] = [
                    'id'       => 'page.create.'.$resourceClass::getSlug(),
                    'type'     => 'url',
                    'group'    => $group,
                    'title'    => 'Create '.$resourceClass::getModelLabel(),
                    'subtitle' => 'Add a new '.strtolower($resourceClass::getModelLabel()),
                    'icon'     => 'heroicon-o-plus-circle',
                    'keywords' => ['create', 'new', 'add', strtolower($resourceClass::getModelLabel())],
                    'url'      => $resourceClass::getUrl('create'),
                    'iconHtml' => rescue(fn (): string => Blade::render('<x-heroicon-o-plus-circle style="width:20px;height:20px" />'), ''),
                ];
            } catch (Throwable) {
            }
        }

        return array_values(array_filter(
            $items,
            fn (array $item): bool => $this->matchesQuery($item, $query),
        ));
    }

    /** @param class-string<Resource> $resourceClass */
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
