<?php

return [
    'plugin' => [
        'name' => 'Skin',
        'description' => 'Enhances the Winter CMS admin interface with dropdown menus, breadcrumbs that handle subpages, previous and next buttons on forms, as well as buttons on the right side of the breadcrumb to access subpages from lists in order to avoid cluttering the navigation with rarely used pages.',
    ],
    'general' => [
        'deleted_at' => 'Deleted at :datetime',
        'dark_theme' => 'Dark theme',
    ],
    'fields' => [
        'skin' => [
            'label' => 'Skin',
            'enhanced' => 'Enhanced classic',
            'modern' => 'Modern (experimental)',
        ],
        'theme' => [
            'label' => 'Theme',
            'light' => 'Light',
            'dark' => 'Dark',
            'cupcake' => 'Cupcake',
        ],
    ],
    'list' => [
        'manage_items' => 'Manage :items',
    ],
    'navigation' => [
        'previous' => 'Previous',
        'next' => 'Next',
    ],
    'permissions' => [
        'administrate' => 'Administration',
    ],
    'settings' => [
        'name' => 'Skin',
        'description' => 'Manage Skin settings',
    ],
];
