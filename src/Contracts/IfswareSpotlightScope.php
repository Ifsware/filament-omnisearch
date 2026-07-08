<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Contracts;

interface IfswareSpotlightScope
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array{
     *     id: string,
     *     type: 'url'|'modal'|'action',
     *     group: string,
     *     title: string,
     *     subtitle: string,
     *     icon: string,
     *     keywords: array<int, string>,
     *     url?: string,
     *     modalId?: string,
     *     action?: callable,
     *     shortcut?: string,
     * }>
     */
    public function getItems(string $query, array $context): array;

    public function isActive(): bool;
}
