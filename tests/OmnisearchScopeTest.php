<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Tests;

use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Ifsware\Omnisearch\Livewire\Omnisearch;
use Ifsware\Omnisearch\OmnisearchAction;
use Ifsware\Omnisearch\OmnisearchManager;

final class OmnisearchScopeTest extends TestbenchTestCase
{

    public function test_scope_returns_items(): void
    {
        $scope = new class implements OmnisearchScope {
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
        $component = new Omnisearch();
        $component->registerScope(\Ifsware\Omnisearch\Tests\Stubs\TestScopeStub::class);

        $items = $component->getScopedItems('', []);

        $this->assertNotEmpty($items);
        $this->assertSame('Stub Item', $items[0]['title']);
    }

    public function test_empty_search_returns_actions_only(): void
    {
        app(OmnisearchManager::class)->registerAction(
            OmnisearchAction::make('test-action')
                ->title('Test Action')
                ->subtitle('Run test action')
                ->icon('heroicon-o-bolt')
                ->keywords(['test'])
        );

        config(['omnisearch.scopes' => [
            \Ifsware\Omnisearch\Scopes\OmnisearchActionScope::class,
        ]]);

        $component = new Omnisearch();
        $html = $component->render()->render();

        $this->assertStringContainsString('Test Action', $html);
        $this->assertStringContainsString('No recent searches', $html);
    }
}
