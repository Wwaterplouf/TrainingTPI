<?php

declare(strict_types=1);

return [
    // Routes accessibles sans être connecté
    'public' => [
        'home',
        'message',
        'auth.login',
        'auth.login.post',
        'auth.register',
        'auth.register.post',
        'auth.logout',
        'auth.resetform',
        'users.resetpassword',
        'users.resetpassword.post',
        'users.sendmail',
    ],

    // Règles RBAC basées sur le préfixe du nom de route
    // IMPORTANT : l'ordre compte. Mettre les règles les plus spécifiques en premier si besoin.
    'rules' => [
        'users.' => ['users.view', 'lists'],
        'admin.' => ['admin'],

        // Routes "métier" : pas de rôle spécifique, mais nécessite d'être connecté
        // (donc pas de règle ici : login requis par le middleware, autorisé pour tout utilisateur connecté)
        // 'tasks.' => null,
    ],
];
