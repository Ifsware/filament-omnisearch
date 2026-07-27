<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Tests\Stubs;

use Ifsware\Omnisearch\Contracts\OmnisearchScope;

final class TestScopeStub implements OmnisearchScope
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
        return [
            [
                'id' => 'stub.item',
                'type' => 'url',
                'group' => 'Stub',
                'title' => 'Stub Item',
                'subtitle' => 'A stub item for testing',
                'icon' => 'heroicon-o-bolt',
                'keywords' => ['stub', 'test'],
                'url' => '/stub',
            ],
        ];
    }
}
