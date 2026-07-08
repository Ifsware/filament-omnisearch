<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch;

use Illuminate\Support\Facades\Config;

final class OmnisearchAction
{
    use \Ifsware\Omnisearch\Concerns\TransConfig;
    protected string $title = '';

    protected ?string $titleTransKey = null;

    /** @var array<string, bool|float|int|string|null> */
    protected array $titleTransParams = [];

    protected string $subtitle = '';

    protected ?string $subtitleTransKey = null;

    /** @var array<string, bool|float|int|string|null> */
    protected array $subtitleTransParams = [];

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
        $this->titleTransKey = null;

        return $this;
    }

    /** @param array<string, bool|float|int|string|null> $params */
    public function transTitle(string $key, array $params = []): static
    {
        $this->titleTransKey = $key;
        $this->titleTransParams = $params;

        return $this;
    }

    public function subtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;
        $this->subtitleTransKey = null;

        return $this;
    }

    /** @param array<string, bool|float|int|string|null> $params */
    public function transSubtitle(string $key, array $params = []): static
    {
        $this->subtitleTransKey = $key;
        $this->subtitleTransParams = $params;

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
        $title = $this->titleTransKey !== null
            ? $this->trans($this->titleTransKey, $this->titleTransParams)
            : $this->title;

        $subtitle = $this->subtitleTransKey !== null
            ? $this->trans($this->subtitleTransKey, $this->subtitleTransParams)
            : (filled($this->subtitle) ? $this->subtitle : $this->trans('omnisearch::omnisearch.run_this_action'));

        $data = [
            'id'       => $this->id,
            'type'     => 'action',
            'group'    => $this->transConfig('omnisearch.groups.actions.label', 'omnisearch::omnisearch.actions'),
            'title'    => $title,
            'subtitle' => $subtitle,
            'icon'     => $this->icon,
            'keywords' => $this->keywords,
        ];

        if ($this->shortcut !== null) {
            $data['shortcut'] = $this->shortcut;
        }

        return $data;
    }
}
