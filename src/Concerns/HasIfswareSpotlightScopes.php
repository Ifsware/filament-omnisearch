<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Concerns;

use Ifsware\Spotlight\Contracts\IfswareSpotlightScope;
use Ifsware\Spotlight\IfswareFuzzyMatcher;

trait HasIfswareSpotlightScopes
{
    /** @var array<int, class-string<IfswareSpotlightScope>> */
    protected array $scopes = [];

    /** @param class-string<IfswareSpotlightScope> $scope */
    public function registerScope(string $scope): static
    {
        if (! in_array($scope, $this->scopes, true)) {
            $this->scopes[] = $scope;
        }

        return $this;
    }

    /** @return array<int, class-string<IfswareSpotlightScope>> */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array{
     *     id: string,
     *     type: 'url'|'modal'|'action',
     *     group: string,
     *     title: string,
     *     subtitle: string,
     *     icon: string,
     *     keywords: array<int, string>,
     *     url?: string,
     *     modalId?: string,
     *     action?: callable,
     *     shortcut?: string,
     * }>
     */
    public function getScopedItems(string $query, array $context): array
    {
        $items = [];

        foreach ($this->scopes as $scopeClass) {
            $scope = app($scopeClass);

            if (! $scope instanceof IfswareSpotlightScope || ! $scope->isActive()) {
                continue;
            }

            $items = array_merge($items, $scope->getItems($query, $context));
        }

        if ($query === '' || $items === []) {
            return $items;
        }

        // Score and sort by relevance
        $scored = array_map(function (array $item) use ($query): array {
            $haystack = implode(' ', [
                $item['title'],
                $item['subtitle'],
                implode(' ', $item['keywords']),
            ]);

            $item['_score'] = IfswareFuzzyMatcher::score($query, $haystack);

            return $item;
        }, $items);

        usort($scored, fn (array $a, array $b): int => $b['_score'] <=> $a['_score']);

        return array_map(function (array $item): array {
            unset($item['_score']);

            return $item;
        }, $scored);
    }
}
