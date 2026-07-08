<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Tests;

use Ifsware\Spotlight\Contracts\IfswareSpotlightScope;
use Ifsware\Spotlight\Livewire\IfswareSpotlight;

final class SpotlightScopeTest extends TestbenchTestCase
{

    public function test_scope_returns_items(): void
    {
        $scope = new class implements IfswareSpotlightScope {
            public function isActive(): bool
            {
                return true;
            }

            public function getItems(string $query, array $context): array
            {
                return [
                    [
                        'id' => 'test.item',
                        'type' => 'url',
                        'group' => 'Test',
                        'title' => 'Test Item',
                        'subtitle' => 'Test subtitle',
                        'icon' => 'heroicon-o-bolt',
                        'keywords' => ['test'],
                        'url' => '/test',
                    ],
                ];
            }
        };

        $items = $scope->getItems('', []);

        $this->assertCount(1, $items);
        $this->assertSame('Test Item', $items[0]['title']);
    }

    public function test_component_uses_scopes(): void
    {
        $component = new IfswareSpotlight();
        $component->mount();
        $component->registerScope(\Ifsware\Spotlight\Scopes\IfswarePanelScope::class);

        $items = $component->getScopedItems('', []);

        $this->assertNotEmpty($items);
    }

    public function test_empty_search_returns_actions_only(): void
    {
        config(['spotlight.scopes' => [
            \Ifsware\Spotlight\Scopes\IfswarePanelScope::class,
            \Ifsware\Spotlight\Scopes\IfswareActionScope::class,
        ]]);

        $component = new IfswareSpotlight();
        $component->mount();

        $html = $component->render()->render();

        $this->assertStringContainsString('Go to', $html);
        $this->assertStringContainsString('No recent searches', $html);
    }
}
