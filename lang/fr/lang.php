<?php

return [
    'plugin' => [
        'name' => 'Skin',
        'description' => 'Améliore l\'interface de l\'administration Winter CMS avec des menus déroulants, des fils d\'arianne gérant les sous-pages, des boutons précédent et suivant sur les formulaires ainsi que des boutons à droite du fil d\'arianne pour accéder aux sous-pages depuis les listes afin d\'éviter d\'encombrer la navigation avec les pages peu utilisées.',
    ],
    'general' => [
        'deleted_at' => 'Supprimé le :datetime',
        'dark_theme' => 'Thème sombre',
    ],
    'fields' => [
        'skin' => [
            'label' => 'Skin',
            'enhanced' => 'Classique amélioré',
            'modern' => 'Moderne (expérimental)',
        ],
        'theme' => [
            'label' => 'Thème',
            'light' => 'Clair',
            'dark' => 'Sombre',
            'cupcake' => 'Cupcake',
        ],
    ],
    'list' => [
        'manage_items' => 'Gérer les :items',
    ],
    'navigation' => [
        'previous' => 'Précédent',
        'next' => 'Suivant',
    ],
    'permissions' => [
        'administrate' => 'Administration',
    ],
    'settings' => [
        'name' => 'Skin',
        'description' => 'Gérer les paramètres du Skin',
    ],
];
