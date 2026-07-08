<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Concerns;

use Ifsware\Spotlight\IfswareFuzzyMatcher;

trait MatchesIfswareSpotlightQuery
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

        return IfswareFuzzyMatcher::matches($query, $haystack);
    }
}
