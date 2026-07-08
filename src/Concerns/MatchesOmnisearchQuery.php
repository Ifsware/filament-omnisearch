<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Concerns;

use Ifsware\Omnisearch\OmnisearchFuzzyMatcher;

trait MatchesOmnisearchQuery
{
    /**
     * @param  array{title: string, subtitle: string, keywords: array<int, string>}  $item
     */
    protected function matchesQuery(array $item, string $query): bool
    {
        $haystack = implode(' ', array_filter([
            $item['title'],
            $item['subtitle'],
            implode(' ', $item['keywords']),
        ]));

        return OmnisearchFuzzyMatcher::matches($query, $haystack);
    }
}
