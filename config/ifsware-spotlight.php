<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Spotlight
    |--------------------------------------------------------------------------
    | Set to false or use SPOTLIGHT_ENABLED=false in your .env to disable
    | the spotlight palette entirely without removing the plugin registration.
    */
    'enabled' => env('SPOTLIGHT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Search Scopes
    |--------------------------------------------------------------------------
    | Each scope must implement \Ifsware\Spotlight\Contracts\IfswareSpotlightScope.
    | Remove or add scopes to control what appears in the palette.
    */
    'scopes' => [
        \Ifsware\Spotlight\Scopes\IfswareNavigationScope::class,
        \Ifsware\Spotlight\Scopes\IfswareResourceScope::class,
        \Ifsware\Spotlight\Scopes\IfswarePanelScope::class,
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
    | The key combination to open spotlight. Use "mod" for Cmd on Mac /
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
    | Customize the group headings shown in the spotlight results.
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
