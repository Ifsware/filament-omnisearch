@php
$shortcutDisplay = collect(explode('+', config('spotlight.shortcut', 'mod+k')))
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
    x-on:click="$dispatch('open-ifsware-spotlight')"
    class="ifsware-trigger-sidebar"
    aria-label="Open spotlight search"
>
    <svg class="ifsware-trigger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
    </svg>
    <span class="ifsware-trigger-label">Search...</span>
    <kbd class="ifsware-trigger-kbd">{{ $shortcutDisplay }}</kbd>
</button>
