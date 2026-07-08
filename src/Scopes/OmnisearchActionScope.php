<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Scopes;

use Ifsware\Omnisearch\Concerns\MatchesOmnisearchQuery;
use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Ifsware\Omnisearch\OmnisearchManager;

final class OmnisearchActionScope implements OmnisearchScope
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
        $actions = [];

        foreach (app(OmnisearchManager::class)->getActions() as $action) {
            $actions[] = $action->toArray();
        }

        if ($query === '') {
            return $actions;
        }

        return array_values(array_filter(
            $actions,
            fn (array $item): bool => $this->matchesQuery($item, $query),
        ));
    }
}
