<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Scopes;

use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\DefaultGlobalSearchProvider;
use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

final class OmnisearchResourceScope implements OmnisearchScope
{
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

        try {
            // getGlobalSearchProvider() returns null when globalSearch(false) is set on the panel.
            // We fall back to the default provider so omnisearch can still search resources
            // even when the native global search UI is hidden.
            $provider = Filament::getGlobalSearchProvider()
                ?? app(DefaultGlobalSearchProvider::class);

            $results = $provider->getResults($query);
        } catch (QueryException) {
            return [];
        }

        if (! $results instanceof GlobalSearchResults) {
            return [];
        }

        $items = [];
        $index = 0;

        foreach ($results->getCategories() as $category => $categoryResults) {
            if (! is_iterable($categoryResults)) {
                continue;
            }

            foreach ($categoryResults as $result) {
                if (! $result instanceof GlobalSearchResult) {
                    continue;
                }

                $items[] = [
                    'id' => sprintf('resource.%s.%s', Str::slug((string) $category), $index),
                    'type' => 'url',
                    'group' => (string) $category,
                    'title' => $this->normalizeText($result->title),
                    'subtitle' => $this->formatSubtitle($result),
                    'icon' => 'heroicon-o-magnifying-glass',
                    'keywords' => [(string) $category],
                    'url' => $result->url,
                    'preview' => $this->buildPreview($result),
                ];

                $index++;
            }
        }

        return array_slice($items, 0, Config::integer('omnisearch.max_results', 50));
    }

    private function normalizeText(string|Htmlable $value): string
    {
        if ($value instanceof HtmlString) {
            return mb_trim(strip_tags($value->toHtml()));
        }

        if ($value instanceof Htmlable) {
            return mb_trim(strip_tags($value->toHtml()));
        }

        return mb_trim(strip_tags($value));
    }

    private function formatSubtitle(GlobalSearchResult $result): string
    {
        if ($result->details === []) {
            return __('omnisearch::omnisearch.global_search_result');
        }

        return collect($result->details)
            ->map(fn (string $value, string $label): string => "{$label}: {$value}")
            ->implode(' • ');
    }

    /**
     * @return array{title: string, fields: array<int, array{label: string, value: string}>}|null
     */
    private function buildPreview(GlobalSearchResult $result): ?array
    {
        if ($result->details === []) {
            return null;
        }

        $fields = [];

        foreach ($result->details as $label => $value) {
            $fields[] = ['label' => $label, 'value' => $value];
        }

        return [
            'title' => $this->normalizeText($result->title),
            'fields' => $fields,
        ];
    }
}
