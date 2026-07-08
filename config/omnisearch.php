<?php

declare(strict_types=1);

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
        \Ifsware\Omnisearch\Scopes\OmnisearchNavigationScope::class,
        \Ifsware\Omnisearch\Scopes\OmnisearchResourceScope::class,
        \Ifsware\Omnisearch\Scopes\OmnisearchPanelScope::class,
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
    */
    /*
    |--------------------------------------------------------------------------
    | Keyboard Shortcut
    |--------------------------------------------------------------------------
    | The key combination to open omnisearch. Use "mod" for Cmd on Mac /
    | Ctrl on Windows. Examples: "mod+k", "mod+p", "ctrl+shift+f".
    */
    'shortcut' => 'mod+k',

    'placeholder' => 'Search commands, pages, resources...',

    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | Group Labels
    |--------------------------------------------------------------------------
    | Customize the group headings shown in the omnisearch results.
    */
    'groups' => [
        'navigate' => [
            'label'    => 'Navigate',
            'subtitle' => 'Navigate to this page',
        ],
        'actions' => [
            'label' => 'Actions',
        ],
        'page' => [
            'label' => 'Page',
        ],
    ],

    'recent_searches' => [
        'enabled' => true,
        'label'   => 'Recent',
        'max'     => 5,
    ],

    'empty_state' => [
        'message'     => 'No recent searches',
        'suggestions' => ['dashboard', 'users', 'settings'],
    ],

];
