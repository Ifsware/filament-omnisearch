<?php

declare(strict_types=1);

namespace Ifsware\Spotlight;

use Illuminate\Support\Facades\Config;

final class IfswareSpotlightAction
{
    protected string $title = '';

    protected string $subtitle = 'Run this action';

    protected string $icon = 'heroicon-o-bolt';

    /** @var array<int, string> */
    protected array $keywords = [];

    protected ?string $shortcut = null;

    /** @var callable|null */
    protected mixed $handler = null;

    public function __construct(protected readonly string $id) {}

    public static function make(string $id): static
    {
        return new self($id);
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function subtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /** @param array<int, string> $keywords */
    public function keywords(array $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function shortcut(string $shortcut): static
    {
        $this->shortcut = $shortcut;

        return $this;
    }

    public function action(callable $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    public function execute(): void
    {
        if ($this->handler !== null) {
            call_user_func($this->handler);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array{id: string, type: 'action', group: string, title: string, subtitle: string, icon: string, keywords: array<int, string>, shortcut?: string}
     */
    public function toArray(): array
    {
        $data = [
            'id'       => $this->id,
            'type'     => 'action',
            'group'    => Config::string('ifsware-spotlight.groups.actions.label', 'Actions'),
            'title'    => $this->title,
            'subtitle' => $this->subtitle,
            'icon'     => $this->icon,
            'keywords' => $this->keywords,
        ];

        if ($this->shortcut !== null) {
            $data['shortcut'] = $this->shortcut;
        }

        return $data;
    }
}
