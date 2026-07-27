<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Tests\Stubs;

use Ifsware\Omnisearch\Contracts\OmnisearchScope;

final class PreviewScopeStub implements OmnisearchScope
{
    public function isActive(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array{id: string, type: 'url'|'modal'|'action', group: string, title: string, subtitle: string, icon: string, keywords: array<int, string>, url?: string, modalId?: string, action?: callable, shortcut?: string, preview?: array{title: string, fields: array<int, array{label: string, value: string}>}}>
     */
    public function getItems(string $query, array $context): array
    {
        return [
            [
                'id' => 'preview.test',
                'type' => 'url',
                'group' => 'PreviewTest',
                'title' => 'Preview Item',
                'subtitle' => 'Has preview data',
                'icon' => 'heroicon-o-bolt',
                'keywords' => ['preview'],
                'url' => '/preview',
                'preview' => [
                    'title' => 'Preview Title',
                    'fields' => [
                        ['label' => 'Email', 'value' => 'test@example.com'],
                    ],
                ],
            ],
        ];
    }
}
