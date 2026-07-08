<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch;

final class OmnisearchManager
{
    /** @var array<string, OmnisearchAction> */
    protected array $actions = [];

    public function registerAction(OmnisearchAction $action): void
    {
        $this->actions[$action->getId()] = $action;
    }

    /** @param array<int, OmnisearchAction> $actions */
    public function registerActions(array $actions): void
    {
        foreach ($actions as $action) {
            $this->registerAction($action);
        }
    }

    /** @return array<string, OmnisearchAction> */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function findAction(string $id): ?OmnisearchAction
    {
        return $this->actions[$id] ?? null;
    }
}
