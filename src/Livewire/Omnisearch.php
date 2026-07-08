<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Livewire;

use Filament\Facades\Filament;
use Ifsware\Omnisearch\Concerns\HasOmnisearchScopes;
use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Ifsware\Omnisearch\OmnisearchManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

final class Omnisearch extends Component
{
    use HasOmnisearchScopes;

    public string $search = '';

    public function executeAction(string $id): void
    {
        app(OmnisearchManager::class)->findAction($id)?->execute();
    }

    public function render(): View
    {
        $query = mb_trim($this->search);
        $context = [
            'panelId' => Filament::getCurrentPanel()?->getId(),
            'user' => auth()->user(),
        ];

        $this->scopes = [];

        foreach (Config::array('omnisearch.scopes', []) as $scope) {
            if (is_string($scope) && is_a($scope, OmnisearchScope::class, true)) {
                $this->scopes[] = $scope;
            }
        }

        $allItems = $this->getScopedItems($query, $context);

        // Separate panels and actions for top bar
        $panels = [];
        $actions = [];
        $items = [];

        foreach ($allItems as $item) {
            if (str_starts_with($item['id'], 'panel.')) {
                $panels[] = $item;
            } elseif ($item['type'] === 'action' && $query === '') {
                $actions[] = $item;
            } else {
                $items[] = $item;
            }
        }

        return view()->make('omnisearch::livewire.omnisearch', [
            'actions' => $actions,
            'items' => $items,
            'panels' => $panels,
            'search' => $query,
            'hasMore' => false,
        ]);
    }
}
