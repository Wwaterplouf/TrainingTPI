<?php

declare(strict_types=1);

use App\Models\Alert;

// -----------------------------------------------------------------------------
// Données session
// -----------------------------------------------------------------------------
$user = $_SESSION['user_connected'] ?? null;

$userRoles = $user['roles'] ?? [];
if (!is_array($userRoles)) {
    $userRoles = [];
}

$basePath = $_SERVER['BASE_URL'] ?? str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? '');

// -----------------------------------------------------------------------------
// RBAC (même fichier que le middleware)
// Ajuster le chemin si ton layout n’est pas au même endroit.
// -----------------------------------------------------------------------------
$rbacPath = __DIR__ . '/../src/Config/rbac.php';
$rbac = ['public' => [], 'rules' => []];

if (is_file($rbacPath) && is_readable($rbacPath)) {
    $loaded = require $rbacPath;
    if (is_array($loaded)) {
        $rbac = $loaded;
    }
}

$rbacRules = $rbac['rules'] ?? [];
if (!is_array($rbacRules)) {
    $rbacRules = [];
}

// -----------------------------------------------------------------------------
// Helpers RBAC pour la navigation
// -----------------------------------------------------------------------------
$requiredRolesForRoute = static function (string $routeName) use ($rbacRules): ?array {
    if ($routeName === '') {
        return null;
    }

    // IMPORTANT : l’ordre compte (les plus spécifiques en premier dans rbac.php)
    foreach ($rbacRules as $prefix => $roles) {
        $prefix = (string) $prefix;
        if ($prefix !== '' && str_starts_with($routeName, $prefix)) {
            return is_array($roles) ? $roles : (array) $roles;
        }
    }

    return null; // pas de règle => visible pour tout user connecté
};

$hasAnyRole = static function (array $userRoles, array $requiredRoles): bool {
    foreach ($requiredRoles as $r) {
        if (in_array($r, $userRoles, true)) {
            return true;
        }
    }
    return false;
};

/**
 * Politique simple :
 * - si pas connecté : pas de menu privé
 * - si route sans règle RBAC : visible pour tout connecté
 * - si route avec règle : visible si user a un rôle requis
 */
$canSeeRoute = static function (?array $user, array $userRoles, string $routeName) use ($requiredRolesForRoute, $hasAnyRole): bool {
    if ($user === null) {
        return false;
    }

    $required = $requiredRolesForRoute($routeName);
    if ($required === null) {
        return true;
    }

    return $hasAnyRole($userRoles, $required);
};

// -----------------------------------------------------------------------------
// Déclaration du menu (source unique)
// -----------------------------------------------------------------------------
$menu = [
    [
        'label' => 'Mes Listes',
        'items' => [
            ['label' => 'Voir', 'url' => $basePath . '/categories', 'route' => 'lists.view'],
            ['label' => 'Ajouter', 'url' => $basePath . '/categories/form', 'route' => 'lists.form'],
        ],
    ],
    [
        'label' => 'Admin',
        'items' => [
            ['label' => 'Utilisateurs', 'url' => $basePath . '/users', 'route' => 'admin.users.index'],
            ['label' => 'Listes', 'url' => $basePath . '/users/register', 'route' => 'admin.lists.index'],
        ],
    ],
    // [
    //     'label' => 'Profil',
    //     'items' => [
    //         ['label' => 'Voir', 'url' => $basePath . '/tasks', 'route' => 'users.view'],
    //         ['label' => 'Se déconnecter', 'url' => $basePath . '/tasks/form', 'route' => 'auth.logout'],
    //     ],
    // ],
];

// -----------------------------------------------------------------------------
// Helpers d’affichage rôles
// -----------------------------------------------------------------------------
$rolesLabels = array_map(
    static fn($r) => htmlspecialchars((string) $r, ENT_QUOTES, 'UTF-8'),
    $userRoles
);

$rolesDisplay = !empty($rolesLabels)
    ? '<small class="text-muted ms-2">(' . implode(', ', $rolesLabels) . ')</small>'
    : '<small class="text-muted ms-2">(no role)</small>';

?>
<!DOCTYPE html>
<html lang="en" class="data-bs-theme=<?= $_SESSION["theme"] ?? "dark" ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="imgs/logo-bird.png" type="image/x-icon">
    <link href="css/style.css" rel="stylesheet" />
    <title>Training TPI</title>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">
                <img src="<?= htmlspecialchars($basePath . '/imgs/logo-bird.gif', ENT_QUOTES, 'UTF-8') ?>" width="50" alt="Logo" style="border-radius: 3rem;"/>
                Anitroy
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <!-- Menu gauche (adaptatif RBAC) -->
                <ul class="navbar-nav me-auto">
                    <?php if ($user !== null): ?>
                        <?php foreach ($menu as $section): ?>
                            <?php
                            $visibleItems = array_values(array_filter(
                                $section['items'],
                                static function ($item) use ($canSeeRoute, $user, $userRoles) {
                                    return $canSeeRoute($user, $userRoles, (string)($item['route'] ?? ''));
                                }
                            ));

                            if (empty($visibleItems)) {
                                continue;
                            }
                            ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= htmlspecialchars((string)$section['label'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($visibleItems as $item): ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= htmlspecialchars((string)$item['url'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars((string)$item['label'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <!-- Menu droite (connexion / profil) -->
                <ul class="navbar-nav ms-auto">
                    <?php if ($user !== null): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Bienvenue <?= htmlspecialchars((string)($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                <?= $rolesDisplay ?>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li>
                                    <span class="dropdown-item-text">
                                        <strong>Rôles :</strong>
                                        <?= !empty($rolesLabels) ? implode(', ', $rolesLabels) : '—' ?>
                                    </span>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath . '/auth/logout', ENT_QUOTES, 'UTF-8') ?>">Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/auth/login', ENT_QUOTES, 'UTF-8') ?>">Connexion</a>
                        </li>
                    <?php endif; ?>
                </ul>

            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row my-3">
            <div class="col">
                <?= Alert::displayHtml() ?>
            </div>
        </div>
    </div>

    <?= isset($content) ? $content : '' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>