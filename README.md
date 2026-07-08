# Filament Omnisearch

An omnisearch palette plugin for [Filament v5](https://filamentphp.com).

## Features

- **Keyboard-driven** — Open with `⌘K` / `Ctrl+K`, navigate with arrow keys, execute with Enter
- **Built-in Scopes** — Navigation, Resources, Panels, and Actions out of the box
- **Extensible** — Add custom scopes by implementing `OmnisearchScope`
- **Record Preview** — Side panel showing resource field details when a result is active
- **Recent Searches** — Persisted per-browser with configurable toggle
- **Page Actions** — Auto-detect Create/List actions from any Filament resource page
- **Dark Mode** — Follows Filament's theme automatically
- **Fuzzy Search** — Smart matching with keyword support

## Requirements

- PHP 8.2+
- Laravel 11+
- Filament v5
- Livewire v4

## Installation

```bash
composer require ifsware/filament-omnisearch
```

Register the plugin in your Filament panel provider:

```php
use Ifsware\Omnisearch\OmnisearchPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            OmnisearchPlugin::make(),
        ]);
}
```

Publish the config file:

```bash
php artisan vendor:publish --tag="omnisearch-config"
```

---

## What Works Out of the Box

After installation and registration, opening the omnisearch palette (`⌘K` / `Ctrl+K`) gives you four built-in scopes automatically.

### 1. Navigation Scope

Searches all visible navigation items registered in the current panel. No configuration needed — every item in your sidebar is immediately searchable.

### 2. Resource Scope

Searches **records** across all resources that have Filament's global search enabled. A resource is searchable only when it declares at least one of:

- `$recordTitleAttribute` — the column used as the result title
- `getGlobalSearchResultDetails()` — method override returning fields shown in the subtitle and preview panel
- A custom `getGlobalSearchResults()` implementation

**Minimal example** — make a resource record searchable:

```php
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // Required: tells Filament which column is the record "name"
    protected static ?string $recordTitleAttribute = 'name';
}
```

**With detail fields shown in the preview panel:**

```php
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    // These fields appear in the side preview when a result is highlighted
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
        ];
    }
}
```

> If `$recordTitleAttribute` is not set and `getGlobalSearchResultDetails()` is not implemented, the resource will not appear in omnisearch results.

### 3. Panel Scope

Lists other Filament panels the authenticated user has access to. Useful in multi-panel applications (e.g. admin + customer portal). Results appear even without a search query, so users can switch panels from the palette.

### 4. Action Scope

Shows global actions registered on the plugin (see [Global Actions](#global-actions) below). Actions appear without a search query so they are always discoverable.

---

## Global Actions

Register application-level actions on the plugin using `OmnisearchAction`. Actions appear in the **Actions** group and can run any PHP callable.

```php
use Ifsware\Omnisearch\OmnisearchPlugin;
use Ifsware\Omnisearch\OmnisearchAction;

OmnisearchPlugin::make()
    ->actions([
        OmnisearchAction::make('clear-cache')
            ->title('Clear Application Cache')
            ->subtitle('Run artisan cache:clear')
            ->icon('heroicon-o-trash')
            ->keywords(['cache', 'clear', 'flush'])
            ->shortcut('mod+shift+c')
            ->action(fn () => \Artisan::call('cache:clear')),

        OmnisearchAction::make('go-profile')
            ->title('My Profile')
            ->subtitle('Open your profile settings')
            ->icon('heroicon-o-user')
            ->keywords(['profile', 'account', 'settings']),
    ]);
```

`OmnisearchAction` fluent API:

| Method | Description |
|--------|-------------|
| `title(string)` | Display title in the palette |
| `subtitle(string)` | Secondary text below the title |
| `icon(string)` | Heroicon name, e.g. `heroicon-o-bolt` |
| `keywords(array)` | Extra words used for fuzzy matching |
| `shortcut(string)` | Optional keyboard shortcut label shown in the result |
| `action(callable)` | PHP callable invoked when the action is executed |

---

## Page Actions

Add `HasOmnisearchPageActions` to a resource page to automatically expose **Create** and **List** actions in the palette while the user is on that page.

```php
use Ifsware\Omnisearch\Concerns\HasOmnisearchPageActions;

class EditUser extends EditRecord
{
    use HasOmnisearchPageActions;

    protected static string $resource = UserResource::class;
}
```

When this page is open, omnisearch will show:

- **Create User** — links to the resource create page (if it exists)
- **Users** — links back to the resource index (if it exists)

### Adding Custom Page Actions

Override `getOmnisearchActions()` to inject page-specific actions alongside the auto-detected ones:

```php
class EditUser extends EditRecord
{
    use HasOmnisearchPageActions;

    protected static string $resource = UserResource::class;

    protected function getOmnisearchActions(): array
    {
        return [
            [
                'id'       => 'page.send-welcome',
                'type'     => 'action',
                'group'    => 'Page',
                'title'    => 'Send Welcome Email',
                'subtitle' => 'Dispatch a welcome email to this user',
                'icon'     => 'heroicon-o-envelope',
                'keywords' => ['email', 'welcome', 'send'],
            ],
        ];
    }
}
```

---

## Custom Scopes

Implement `OmnisearchScope` to add any results you want. Each item must have a `type` of `url`, `action`, or `modal`.

### URL item — navigates to a page

```php
use Ifsware\Omnisearch\Contracts\OmnisearchScope;

final class SettingsScope implements OmnisearchScope
{
    public function isActive(): bool
    {
        return true; // return false to disable this scope conditionally
    }

    public function getItems(string $query, array $context): array
    {
        return [
            [
                'id'       => 'settings.general',
                'type'     => 'url',
                'group'    => 'Settings',
                'title'    => 'General Settings',
                'subtitle' => 'Open the general settings page',
                'icon'     => 'heroicon-o-cog-6-tooth',
                'keywords' => ['settings', 'general', 'config'],
                'url'      => '/admin/settings',
            ],
        ];
    }
}
```

### Action item — runs a PHP callable server-side

```php
[
    'id'       => 'actions.export',
    'type'     => 'action',
    'group'    => 'Actions',
    'title'    => 'Export Users',
    'subtitle' => 'Download all users as CSV',
    'icon'     => 'heroicon-o-arrow-down-tray',
    'keywords' => ['export', 'csv', 'download'],
    'action'   => fn () => \Artisan::call('export:users'),
]
```

### Modal item — opens a Livewire modal

```php
[
    'id'       => 'modal.invite',
    'type'     => 'modal',
    'group'    => 'Actions',
    'title'    => 'Invite User',
    'subtitle' => 'Open the invite user dialog',
    'icon'     => 'heroicon-o-user-plus',
    'keywords' => ['invite', 'user', 'add'],
    'modalId'  => 'invite-user-modal',
]
```

### Register the scope

Add it to `config/omnisearch.php`:

```php
'scopes' => [
    \Ifsware\Omnisearch\Scopes\OmnisearchNavigationScope::class,
    \Ifsware\Omnisearch\Scopes\OmnisearchResourceScope::class,
    \Ifsware\Omnisearch\Scopes\OmnisearchPanelScope::class,
    \Ifsware\Omnisearch\Scopes\OmnisearchActionScope::class,
    \App\Omnisearch\SettingsScope::class, // your custom scope
],
```

---

## Configuration

```php
// config/omnisearch.php

return [
    // Set to false or OMNISEARCH_ENABLED=false in .env to disable entirely
    'enabled' => env('OMNISEARCH_ENABLED', true),

    // Active scopes — order determines result order in the palette
    'scopes' => [
        \Ifsware\Omnisearch\Scopes\OmnisearchNavigationScope::class,
        \Ifsware\Omnisearch\Scopes\OmnisearchResourceScope::class,
        \Ifsware\Omnisearch\Scopes\OmnisearchPanelScope::class,
        \Ifsware\Omnisearch\Scopes\OmnisearchActionScope::class,
    ],

    // Maximum number of results returned across all scopes
    'max_results' => 50,

    // Keyboard shortcut to open the palette (mod = Cmd on Mac, Ctrl on Windows/Linux)
    'shortcut' => 'mod+k',

    'placeholder' => 'Search commands, pages, resources...',

    // Group labels shown as section headers in the palette
    'groups' => [
        'navigate' => ['label' => 'Navigate', 'subtitle' => 'Navigate to this page'],
        'actions'  => ['label' => 'Actions'],
        'page'     => ['label' => 'Page'],
    ],

    'recent_searches' => [
        'enabled' => true,
        'label'   => 'Recent',
        'max'     => 5,         // maximum number of recent searches to show
    ],

    'empty_state' => [
        'message'     => 'No recent searches',
        'suggestions' => ['dashboard', 'users', 'settings'],
    ],
];
```

### Plugin options

```php
OmnisearchPlugin::make()
    // Pass false to keep Filament's native global search bar visible
    ->disableDefaultGlobalSearch(false)
    ->actions([/* OmnisearchAction instances */]);
```

---

## Keyboard Shortcuts

| Key | Action |
|-----|--------|
| `⌘K` / `Ctrl+K` | Open omnisearch |
| `Escape` | Close omnisearch |
| `↑` / `↓` | Navigate results |
| `↵` | Execute selected result |

---

## Testing

```bash
composer test
```

## License

MIT
