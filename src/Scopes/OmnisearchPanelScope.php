<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Scopes;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Ifsware\Omnisearch\Concerns\MatchesOmnisearchQuery;
use Ifsware\Omnisearch\Contracts\OmnisearchScope;
use Illuminate\Database\Eloquent\Model;

final class OmnisearchPanelScope implements OmnisearchScope
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
        $currentPanelId = isset($context['panelId']) && is_string($context['panelId'])
            ? $context['panelId']
            : null;

        $panels = [];

        foreach (Filament::getPanels() as $panel) {
            if ($panel->getId() === $currentPanelId || ! $this->userCanAccessPanel($panel)) {
                continue;
            }

            $panels[] = [
                'id'       => 'panel.'.$panel->getId(),
                'type'     => 'url',
                'group'    => 'Go to',
                'title'    => ucfirst($panel->getId()),
                'subtitle' => 'Switch to the '.ucfirst($panel->getId()).' panel',
                'icon'     => 'heroicon-o-squares-2x2',
                'keywords' => [$panel->getId(), 'panel', 'switch'],
                'url'      => '/'.$panel->getPath(),
            ];
        }

        if ($query === '') {
            return $panels;
        }

        return array_values(array_filter(
            $panels,
            fn (array $panel): bool => $this->matchesQuery($panel, $query),
        ));
    }

    private function userCanAccessPanel(Panel $panel): bool
    {
        /** @var Model|null $user */
        $user = auth()->guard($panel->getAuthGuard())->user();

        if (! $user) {
            return false;
        }

        if ($user instanceof FilamentUser) {
            return $user->canAccessPanel($panel);
        }

        return app()->environment('local');
    }
}
