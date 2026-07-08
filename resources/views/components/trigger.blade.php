@php
$shortcutDisplay = collect(explode('+', config('omnisearch.shortcut', 'mod+k')))
    ->map(fn ($p) => match (strtolower(trim($p))) {
        'mod' => '⌘',
        'ctrl' => 'Ctrl',
        'meta' => '⌘',
        'shift' => '⇧',
        'alt' => '⌥',
        default => strtoupper(trim($p)),
    })
    ->implode('');
@endphp

<button
    type="button"
    x-on:click="$dispatch('open-omnisearch')"
    class="omnisearch-trigger"
    aria-label="{{ __('omnisearch::omnisearch.trigger_aria_label') }}"
>
    <svg class="omnisearch-trigger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
    </svg>
    <span class="omnisearch-trigger-label">{{ __('omnisearch::omnisearch.trigger_label') }}</span>
    <kbd class="omnisearch-trigger-kbd">{{ $shortcutDisplay }}</kbd>
</button>
