<?php

declare(strict_types=1);
use Ifsware\Omnisearch\Scopes\OmnisearchNavigationScope;
use Ifsware\Omnisearch\Scopes\OmnisearchPanelScope;
use Ifsware\Omnisearch\Scopes\OmnisearchResourceScope;

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Omnisearch
    |--------------------------------------------------------------------------
    | Set to false or use OMNISEARCH_ENABLED=false in your .env to disable
    | the omnisearch palette entirely without removing the plugin registration.
    */
    'enabled' => env('OMNISEARCH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Search Scopes
    |--------------------------------------------------------------------------
    | Each scope must implement \Ifsware\Omnisearch\Contracts\OmnisearchScope.
    | Remove or add scopes to control what appears in the palette.
    */
    'scopes' => [
        OmnisearchNavigationScope::class,
        OmnisearchResourceScope::class,
        OmnisearchPanelScope::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Results
    |--------------------------------------------------------------------------
    | Maximum number of results returned per search query across all scopes.
    */
    'max_results' => 50,

    /*
    |--------------------------------------------------------------------------
    | Search Input Placeholder
    |--------------------------------------------------------------------------
    | Leave null to use the translation file (default). Set a string here
    | to override it for all locales.
    */
    'placeholder' => null,

    /*
    |--------------------------------------------------------------------------
    | Keyboard Shortcut
    |--------------------------------------------------------------------------
    | The key combination to open omnisearch. Use "mod" for Cmd on Mac /
    | Ctrl on Windows. Examples: "mod+k", "mod+p", "ctrl+shift+f".
    */
    'shortcut' => 'mod+k',

    /*
    |--------------------------------------------------------------------------
    | Group Labels
    |--------------------------------------------------------------------------
    | Customize the group headings shown in the omnisearch results.
    | Leave null to use the translation file (default).
    */
    'groups' => [
        'navigate' => [
            'label' => null,
            'subtitle' => null,
        ],
        'actions' => [
            'label' => null,
        ],
        'page' => [
            'label' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recent Searches
    |--------------------------------------------------------------------------
    */
    'recent_searches' => [
        'enabled' => true,
        'label' => null,
        'max' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    | Message shown when the palette is open but there is no query and no
    | recent searches. Leave null to use the translation file (default).
    | Suggestions are clickable keywords shown when a search returns no results.
    */
    'empty_state' => [
        'message' => null,
        'suggestions' => ['dashboard', 'users', 'settings'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Footer Brand
    |--------------------------------------------------------------------------
    | Text shown on the right side of the footer. Set to null to hide it,
    | or provide a custom string to replace the default "Filament Omnisearch".
    */
    'footer_brand' => 'Filament Omnisearch',

];
