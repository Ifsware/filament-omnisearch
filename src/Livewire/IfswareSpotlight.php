<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Livewire;

use Filament\Facades\Filament;
use Ifsware\Spotlight\Concerns\HasIfswareSpotlightScopes;
use Ifsware\Spotlight\Contracts\IfswareSpotlightScope;
use Ifsware\Spotlight\IfswareSpotlightManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

final class IfswareSpotlight extends Component
{
    use HasIfswareSpotlightScopes;

    public string $search = '';

    public function executeAction(string $id): void
    {
        app(IfswareSpotlightManager::class)->findAction($id)?->execute();
    }

    public function render(): View
    {
        $query = mb_trim($this->search);
        $context = [
            'panelId' => Filament::getCurrentPanel()?->getId(),
            'user' => auth()->user(),
        ];

        $this->scopes = [];

        foreach (Config::array('ifsware-spotlight.scopes', []) as $scope) {
            if (is_string($scope) && is_a($scope, IfswareSpotlightScope::class, true)) {
                $this->scopes[] = $scope;
            }
        }

        $allItems = $this->getScopedItems($query, $context);

        // Separate panels and actions for top bar
        $panels = [];
        $actions = [];
        $items = [];

        foreach ($allItems as $item) {
            if ($item['group'] === 'Go to') {
                $panels[] = $item;
            } elseif ($item['group'] === 'Actions' && $query === '') {
                $actions[] = $item;
            } else {
                $items[] = $item;
            }
        }

        return view()->make('spotlight::livewire.spotlight', [
            'actions' => $actions,
            'items' => $items,
            'panels' => $panels,
            'search' => $query,
            'hasMore' => false,
        ]);
    }
}
