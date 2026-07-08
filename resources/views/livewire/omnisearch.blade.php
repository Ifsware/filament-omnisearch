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

<div
    x-data="{
        open: false,
        activeIndex: 0,
        activePreview: null,
        searchQuery: '',
        shortcutKey: @js(config('omnisearch.shortcut', 'mod+k')),
        recentSearchesEnabled: @js(config('omnisearch.recent_searches.enabled', true)),
        pageActions: [],
        recentSearches: JSON.parse(localStorage.getItem('omnisearch-recent') || '[]'),
        updatePreview() {
            if (!this.searchQuery.trim()) {
                this.activePreview = null
                return
            }
            const el = this.$refs.omnisearchContent?.querySelector(`[data-command-index='${this.activeIndex}']`)
            const cmd = el ? JSON.parse(el.dataset.command ?? 'null') : null
            this.activePreview = cmd?.preview ?? null
        },
        addRecentSearch(term) {
            if (!this.recentSearchesEnabled || !term.trim()) return
            this.recentSearches = [term, ...this.recentSearches.filter(s => s !== term)].slice(0, @js(config('omnisearch.recent_searches.max', 5)))
            localStorage.setItem('omnisearch-recent', JSON.stringify(this.recentSearches))
        },
        removeRecentSearch(term) {
            this.recentSearches = this.recentSearches.filter(s => s !== term)
            localStorage.setItem('omnisearch-recent', JSON.stringify(this.recentSearches))
        },
        clearRecentSearches() {
            this.recentSearches = []
            localStorage.removeItem('omnisearch-recent')
        },
        applyRecentSearch(term) {
            $wire.set('search', term)
            this.$nextTick(() => this.$refs.input.focus())
        },
        matchesShortcut(event) {
            const parts = this.shortcutKey.toLowerCase().split('+')
            const key = parts[parts.length - 1]
            if (event.key.toLowerCase() !== key) return false
            if (parts.includes('mod') && !event.metaKey && !event.ctrlKey) return false
            if (parts.includes('ctrl') && !event.ctrlKey) return false
            if (parts.includes('meta') && !event.metaKey) return false
            if (parts.includes('shift') && !event.shiftKey) return false
            if (parts.includes('alt') && !event.altKey) return false
            return true
        },
        handleKeydown(event) {
            if (this.matchesShortcut(event)) { event.preventDefault(); this.openPalette() }
        },
        openPalette() {
            this.open = true
            this.activeIndex = 0
            this.activePreview = null
            this.$nextTick(() => {
                this.$refs.input.focus()
                this.$refs.input.select()
            })
        },
        closePalette(resetSearch = true) {
            this.open = false
            this.activeIndex = 0
            this.activePreview = null
            this.searchQuery = ''
            if (resetSearch) {
                $wire.set('search', '')
            }
        },
        setActiveIndex(index) {
            this.activeIndex = index
            this.updatePreview()
        },
        moveSelection(direction) {
            const total = this.$refs.omnisearchContent?.querySelectorAll('[data-command-index]').length ?? 0
            if (!total) return
            this.activeIndex = (this.activeIndex + direction + total) % total
            this.$nextTick(() => {
                this.$refs.omnisearchContent
                    ?.querySelector(`[data-command-index='${this.activeIndex}']`)
                    ?.scrollIntoView({ block: 'nearest' })
            })
        },
        execute(command) {
            if (!command) return
            const term = this.$refs.input?.value?.trim()
            if (command.type === 'modal') {
                this.closePalette(false)
                requestAnimationFrame(() => {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: command.modalId } }))
                    $wire.set('search', '')
                })
                return
            }
            if (command.type === 'action') {
                if (term) this.addRecentSearch(term)
                this.closePalette()
                $wire.executeAction(command.id)
                return
            }
            if (term) this.addRecentSearch(term)
            this.closePalette()
            if (!command.url) return
            const destination = new URL(command.url, window.location.origin)
            if (window.Livewire?.navigate && destination.origin === window.location.origin) {
                window.Livewire.navigate(`${destination.pathname}${destination.search}${destination.hash}`)
                return
            }
            window.location.href = destination.toString()
        },
        executeActiveCommand() {
            const command = JSON.parse(
                this.$refs.omnisearchContent
                    ?.querySelector(`[data-command-index='${this.activeIndex}']`)
                    ?.dataset.command ?? 'null'
            )
            this.execute(command)
        },
    }"
    x-on:keydown.window="handleKeydown($event)"
    x-on:keydown.window.prevent.escape="open ? closePalette() : null"
    x-on:keydown.window.prevent.arrow-down="open ? moveSelection(1) : null"
    x-on:keydown.window.prevent.arrow-up="open ? moveSelection(-1) : null"
    x-on:keydown.window.enter="if (open) { $event.preventDefault(); executeActiveCommand() }"
    x-on:open-omnisearch.window="openPalette()"
    x-on:omnisearch-page-actions.window="pageActions = $event.detail.actions ?? []"
    x-on:livewire:navigated.window="closePalette(); pageActions = []"
    x-on:livewire:updated.window="$nextTick(() => updatePreview())"
    x-effect="document.body.style.overflow = open ? 'hidden' : ''; updatePreview()"
>
    {{-- Overlay --}}
    <div
        x-cloak
        x-show="open"
        x-transition.opacity.150ms
        class="omnisearch-overlay"
        x-on:click="closePalette()"
    ></div>

    {{-- Modal --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="omnisearch-enter"
        x-transition:enter-start="omnisearch-enter-start"
        x-transition:enter-end="omnisearch-enter-end"
        x-transition:leave="omnisearch-leave"
        x-transition:leave-start="omnisearch-leave-start"
        x-transition:leave-end="omnisearch-leave-end"
        class="omnisearch-modal"
        :class="{ 'omnisearch-modal--preview': activePreview }"
    >
        <div x-ref="omnisearchContent" class="omnisearch-card">

            {{-- Search Input --}}
            <div class="omnisearch-search-bar">
                <div class="omnisearch-search-wrap">
                    <svg class="omnisearch-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        x-ref="input"
                        wire:model.live.debounce.150ms="search"
                        x-on:input="searchQuery = $event.target.value"
                        type="text"
                        placeholder="{{ config('omnisearch.placeholder', 'Search commands, pages, resources...') }}"
                        class="omnisearch-search-input"
                    />
                    <button type="button" x-on:click="closePalette()" class="omnisearch-search-close">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body: left (main) + right (preview) --}}
            <div class="omnisearch-card-body">

                {{-- Main column --}}
                <div class="omnisearch-card-main">

                    @php($itemIndex = 0)

                    {{-- Top Bar: Panels + Actions --}}
                    @if ($panels !== [] || $actions !== [])
                        <div class="omnisearch-topbar">
                            @if ($panels !== [])
                                <div class="omnisearch-topbar-group">
                                    <span class="omnisearch-topbar-label">Go to</span>
                                    @foreach ($panels as $panel)
                                        <button
                                            type="button"
                                            data-command-index="{{ $itemIndex }}"
                                            data-command='@json($panel)'
                                            x-on:mouseenter="setActiveIndex({{ $itemIndex }})"
                                            x-on:click="execute(JSON.parse($el.dataset.command))"
                                            class="omnisearch-chip"
                                            :class="{ 'active': activeIndex === {{ $itemIndex }} }"
                                        >
                                            <span class="omnisearch-chip-icon">
                                                <x-dynamic-component :component="$panel['icon']" style="width:14px;height:14px" />
                                            </span>
                                            {{ $panel['title'] }}
                                        </button>
                                        @php($itemIndex++)
                                    @endforeach
                                </div>
                            @endif

                            @if ($actions !== [])
                                <div class="omnisearch-topbar-end">
                                    @foreach ($actions as $action)
                                        <button
                                            type="button"
                                            data-command-index="{{ $itemIndex }}"
                                            data-command='@json($action)'
                                            x-on:mouseenter="setActiveIndex({{ $itemIndex }})"
                                            x-on:click="execute(JSON.parse($el.dataset.command))"
                                            class="omnisearch-chip"
                                            :class="{ 'active': activeIndex === {{ $itemIndex }} }"
                                        >
                                            <span class="omnisearch-chip-icon">
                                                <x-dynamic-component :component="$action['icon']" style="width:14px;height:14px" />
                                            </span>
                                            {{ $action['title'] }}
                                        </button>
                                        @php($itemIndex++)
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="omnisearch-divider"></div>
                    @endif

                    {{-- Recent Searches --}}
                    <template x-if="recentSearchesEnabled && !$wire.search && recentSearches.length > 0">
                        <div class="omnisearch-recent">
                            <div class="omnisearch-recent-header">
                                <span class="omnisearch-group-label">{{ config('omnisearch.recent_searches.label', 'Recent') }}</span>
                                <button type="button" x-on:click="clearRecentSearches()" class="omnisearch-recent-clear">Clear</button>
                            </div>
                            <div class="omnisearch-recent-list">
                                <template x-for="term in recentSearches" :key="term">
                                    <div class="omnisearch-recent-item">
                                        <button type="button" x-on:click="applyRecentSearch(term)" class="omnisearch-recent-term">
                                            <svg class="omnisearch-recent-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            <span x-text="term"></span>
                                        </button>
                                        <button type="button" x-on:click="removeRecentSearch(term)" class="omnisearch-recent-remove" aria-label="Remove">
                                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Results --}}
                    <div x-ref="results" class="omnisearch-results">
                        {{-- Page Actions (client-side, from current page) --}}
                        <template x-if="pageActions.length > 0">
                            <div class="omnisearch-groups" style="margin-bottom: 1rem">
                                <section class="omnisearch-group">
                                    <div class="omnisearch-group-label">{{ config('omnisearch.groups.page.label', 'Page') }}</div>
                                    <div class="omnisearch-group-items">
                                        <template x-for="(action, idx) in pageActions" :key="action.id">
                                            <button
                                                type="button"
                                                :data-command-index="idx"
                                                :data-command="JSON.stringify(action)"
                                                x-on:mouseenter="setActiveIndex(idx)"
                                                x-on:click="execute(action)"
                                                class="omnisearch-item"
                                                :class="{ 'active': activeIndex === idx }"
                                            >
                                                <div class="omnisearch-item-icon-wrap">
                                                    <span class="omnisearch-item-icon" x-html="action.iconHtml || ''"></span>
                                                </div>
                                                <div class="omnisearch-item-body">
                                                    <div class="omnisearch-item-title" x-text="action.title"></div>
                                                    <div class="omnisearch-item-subtitle" x-text="action.subtitle"></div>
                                                </div>
                                                <div class="omnisearch-item-meta">
                                                    <svg class="omnisearch-item-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                                    </svg>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </section>
                            </div>
                        </template>

                        @php($groupedItems = collect($items)->groupBy('group'))

                        @if ($groupedItems->isEmpty())
                            <div class="omnisearch-empty">
                                <div class="omnisearch-empty-icon-wrap">
                                    @if (filled($search))
                                        <svg class="omnisearch-empty-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                                            <path d="M15.5 4.8c2 3 1.7 7-1 9.7h0l4.3 4.3-4.3-4.3a7.8 7.8 0 01-9.8 1m-2.2-2.2A7.8 7.8 0 0113.2 2.4M2 18L18 2"/>
                                        </svg>
                                    @else
                                        <svg class="omnisearch-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="omnisearch-empty-body">
                                    @if (filled($search))
                                        <p class="omnisearch-empty-title">No results for &quot;{{ $search }}&quot;</p>
                                        <p class="omnisearch-empty-hint">
                                            Try:
                                            @foreach (config('omnisearch.empty_state.suggestions', ['home', 'issue', 'metrics', 'projects']) as $suggestion)
                                                <button type="button" wire:click="set('search', '{{ $suggestion }}')" class="omnisearch-suggestion">{{ $suggestion }}</button>{{ $loop->last ? '' : ', ' }}
                                            @endforeach
                                        </p>
                                    @else
                                        <p class="omnisearch-empty-title">{{ config('omnisearch.empty_state.message', 'No recent searches') }}</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="omnisearch-groups">
                                @foreach ($groupedItems as $group => $groupItems)
                                    <section class="omnisearch-group">
                                        <div class="omnisearch-group-label">{{ $group }}</div>
                                        <div class="omnisearch-group-items">
                                            @foreach ($groupItems as $item)
                                                <button
                                                    type="button"
                                                    data-command-index="{{ $itemIndex }}"
                                                    data-command='@json($item)'
                                                    x-on:mouseenter="setActiveIndex({{ $itemIndex }})"
                                                    x-on:click="execute(JSON.parse($el.dataset.command))"
                                                    class="omnisearch-item"
                                                    :class="{ 'active': activeIndex === {{ $itemIndex }} }"
                                                >
                                                    <div class="omnisearch-item-icon-wrap">
                                                        <span class="omnisearch-item-icon">
                                                            <x-dynamic-component :component="$item['icon']" style="width:20px;height:20px" />
                                                        </span>
                                                    </div>
                                                    <div class="omnisearch-item-body">
                                                        <div class="omnisearch-item-title">{{ $item['title'] }}</div>
                                                        <div class="omnisearch-item-subtitle">{{ $item['subtitle'] }}</div>
                                                    </div>
                                                    <div class="omnisearch-item-meta">
                                                        @if (filled($item['shortcut'] ?? null))
                                                            <span class="omnisearch-shortcut">{{ $item['shortcut'] }}</span>
                                                        @endif
                                                        <svg class="omnisearch-item-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                                        </svg>
                                                    </div>
                                                </button>
                                                @php($itemIndex++)
                                            @endforeach
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>{{-- /.omnisearch-card-main --}}

                {{-- Preview Panel --}}
                <div class="omnisearch-preview-panel" x-show="activePreview" x-cloak>
                    <div class="omnisearch-preview-header">
                        <div class="omnisearch-preview-eyebrow">Preview</div>
                        <div class="omnisearch-preview-title" x-text="activePreview?.title"></div>
                    </div>
                    <div class="omnisearch-preview-fields">
                        <template x-for="field in (activePreview?.fields ?? [])" :key="field.label">
                            <div class="omnisearch-preview-field">
                                <div class="omnisearch-preview-label" x-text="field.label"></div>
                                <div class="omnisearch-preview-value" x-text="field.value"></div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>{{-- /.omnisearch-card-body --}}

            {{-- Footer --}}
            <div class="omnisearch-footer">
                <div class="omnisearch-footer-shortcuts">
                    <span class="omnisearch-footer-shortcut"><kbd class="omnisearch-kbd">↑↓</kbd> navigate</span>
                    <span class="omnisearch-footer-shortcut"><kbd class="omnisearch-kbd">↵</kbd> open</span>
                    <span class="omnisearch-footer-shortcut"><kbd class="omnisearch-kbd">Esc</kbd> close</span>
                </div>
                <span>Filament Omnisearch</span>
            </div>
        </div>
    </div>
</div>
