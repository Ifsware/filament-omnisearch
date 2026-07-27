<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Scopes;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Support\Icons\Heroicon;
use Ifsware\Omnisearch\Concerns\MatchesOmnisearchQuery;
use Ifsware\Omnisearch\Concerns\TransConfig;
use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Illuminate\Support\Str;

final class OmnisearchNavigationScope implements OmnisearchScope
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

        $items = [];

        foreach (Filament::getNavigation() as $group) {
            $rawItems = $group->getItems();
            $groupItems = is_array($rawItems) ? $rawItems : $rawItems->toArray();

            foreach ($groupItems as $item) {
                if (! $item instanceof NavigationItem) {
                    continue;
                }

                if (! $item->isVisible() || ! filled($item->getUrl())) {
                    continue;
                }

                $items[] = [
                    'id' => 'nav.'.Str::slug($item->getLabel()),
                    'type' => 'url',
                    'group' => $this->transConfig('omnisearch.groups.navigate.label', 'omnisearch::omnisearch.navigate'),
                    'title' => $item->getLabel(),
                    'subtitle' => $this->resolveSubtitle($item),
                    'icon' => $this->resolveIcon($item),
                    'keywords' => array_values(array_filter([$item->getLabel(), $this->resolveGroupLabel($item)])),
                    'url' => $item->getUrl(),
                ];
            }
        }

        return array_values(array_filter(
            $items,
            fn (array $item): bool => $this->matchesQuery($item, $query),
        ));
    }

    private function resolveIcon(NavigationItem $item): string
    {
        $icon = $item->getIcon();

        if ($icon instanceof Heroicon) {
            return 'heroicon-'.$icon->value;
        }

        if ($icon instanceof BackedEnum) {
            return (string) $icon->value;
        }

        if (is_string($icon)) {
            return $icon;
        }

        return 'heroicon-o-link';
    }

    private function resolveGroupLabel(NavigationItem $item): string
    {
        $group = $item->getGroup();

        if ($group instanceof \UnitEnum) {
            if (method_exists($group, 'getLabel')) {
                $label = $group->getLabel();

                return is_string($label) ? $label : $group->name;
            }

            return $group->name;
        }

        return is_string($group) ? $group : '';
    }

    private function resolveSubtitle(NavigationItem $item): string
    {
        $group = $this->resolveGroupLabel($item);

        return filled($group) ? $group : $this->transConfig('omnisearch.groups.navigate.subtitle', 'omnisearch::omnisearch.navigate_subtitle');
    }
}
