<?php

declare(strict_types=1);

namespace Ifsware\Spotlight;

final class IfswareSpotlightManager
{
    /** @var array<string, IfswareSpotlightAction> */
    protected array $actions = [];

    public function registerAction(IfswareSpotlightAction $action): void
    {
        $this->actions[$action->getId()] = $action;
    }

    /** @param array<int, IfswareSpotlightAction> $actions */
    public function registerActions(array $actions): void
    {
        foreach ($actions as $action) {
            $this->registerAction($action);
        }
    }

    /** @return array<string, IfswareSpotlightAction> */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function findAction(string $id): ?IfswareSpotlightAction
    {
        return $this->actions[$id] ?? null;
    }
}
