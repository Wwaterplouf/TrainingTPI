<?php

namespace App\Controllers;

// Import des classes nécessaires
use Exception;
use App\Models\ARUser;
use App\Models\ARRole;
use App\Models\Alert;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

/**
 * Contrôleur responsable de la gestion des utilisateurs :
 * - authentification (login / logoff)
 * - création, modification, suppression des utilisateurs
 * - affichage de la liste et du détail des utilisateurs.
 */
class UsersController
{
    /**
     * Constructeur du contrôleur.
     *
     * Injection du moteur de rendu PhpRenderer pour générer les vues.
     * Cette dépendance est généralement fournie par le conteneur d'injection
     * de dépendances (DI container) configuré dans Slim.
     *
     * @param PhpRenderer $view Moteur de templates utilisé pour rendre les vues PHP.
     */
    public function __construct(private PhpRenderer $view)
    {
    }

    /**
     * Affiche le formulaire de login.
     *
     * @param Request  $request  Requête HTTP entrante (non utilisée ici).
     * @param Response $response Réponse HTTP à renvoyer au client.
     * @param array    $args     Paramètres de route (non utilisés ici).
     *
     * @return Response Réponse HTTP contenant la vue du formulaire de login.
     */
    public function formLoginShow(Request $request, Response $response, $args): Response
    {
        // Affiche simplement la vue /login.php sans données supplémentaires.
        return $this->view->render($response, '/login.php');
    }

    /**
     * Traite la soumission du formulaire de login.
     *
     * Récupère les informations envoyées par le formulaire (username et password),
     * vérifie les identifiants via le modèle User, puis redirige soit vers la page
     * d'accueil (succès), soit vers la page de login (échec) en ajoutant un message
     * flash dans le système d'alertes.
     *
     * @param Request  $request  Requête HTTP contenant les données du formulaire.
     * @param Response $response Réponse HTTP à renvoyer au client.
     *
     * @return Response Réponse HTTP redirigeant l'utilisateur.
     */
    public function formLoginPost(Request $request, Response $response): Response
    {
        // Récupération des données envoyées par le formulaire (POST, JSON, etc.).
        // getParsedBody() retourne un tableau associatif avec les clés du formulaire.
        $body = $request->getParsedBody();

        // Récupération du username :
        // - ?? '' fournit une chaîne vide si la clé n'existe pas (évite les notices),
        // - trim() supprime les espaces au début et à la fin,
        // - FILTER_SANITIZE_SPECIAL_CHARS neutralise les caractères spéciaux
        //   pour un éventuel affichage (protection XSS côté vue).
        $username = filter_var(
            trim($body['username'] ?? ''),
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        // Récupération du password :
        // - ?? '' pour éviter un undefined index,
        // - trim() pour enlever les espaces accidentels (optionnel),
        // - FILTER_UNSAFE_RAW indique que la chaîne n'est pas modifiée
        //   (important : on ne veut pas altérer le mot de passe avant le hash/verify).
        $password = filter_var(
            trim($body['password'] ?? ''),
            FILTER_UNSAFE_RAW
        );

        // Vérification des identifiants via le modèle User.
        // User::isValid($username, $password) est supposée :
        // - chercher l'utilisateur en base à partir du username,
        // - vérifier le mot de passe avec password_verify() sur le hash stocké,
        // - stocker les informations utiles dans $_SESSION['user_connected'] en cas de succès.
        if (ARUser::isValid($username, $password)) {

            // Ajout d'un message de succès dans le système d'alertes (flash messages).
            Alert::add('success', 'L\'utilisateur a été identifié');

            // Redirection vers la page d'accueil en cas de succès (302 = redirection temporaire).
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }

        // Si l'authentification échoue :
        // Ajout d'un message d'erreur dans le système d'alertes.
        Alert::add('danger', 'Username et/ou password inconnus !');

        // Redirection vers la page de login pour une nouvelle tentative.
        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302); // 302 pour une redirection temporaire
    }

    /**
     * Déconnecte l'utilisateur courant.
     *
     * Supprime les informations de session liées à l'utilisateur connecté,
     * ajoute un message de confirmation, puis redirige vers la page d'accueil.
     *
     * @param Request  $request  Requête HTTP entrante.
     * @param Response $response Réponse HTTP à renvoyer au client.
     *
     * @return Response Réponse HTTP redirigeant vers la page d'accueil.
     */
    public function logOff(Request $request, Response $response): Response
    {
        // Suppression des informations de session indiquant qu'un utilisateur est connecté.
        // (On pourrait aussi appeler session_destroy() selon les besoins.)
        unset($_SESSION['user_connected']);

        // Message d'information indiquant que l'utilisateur a été déconnecté.
        Alert::add('success', 'Utilisateur déconnecté');

        // Redirection vers la page d'accueil.
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302); // 302 pour une redirection temporaire
    }

    /**
     * Affiche le formulaire de création ou de modification d'utilisateur.
     *
     * Si un ID est présent dans les paramètres, on est en mode "update" et
     * les données de l'utilisateur sont chargées. Sinon, on est en mode "create".
     *
     * @param Request  $request  Requête HTTP entrante.
     * @param Response $response Réponse HTTP à renvoyer au client.
     * @param array    $args     Paramètres de route, notamment 'id' pour l'utilisateur.
     *
     * @return Response Réponse HTTP contenant la vue du formulaire utilisateur.
     */
    public function formShow(Request $request, Response $response, $args): Response
    {
        // Récupération de l'ID passé dans l'URL (s'il existe).
        $id = $args['id'] ?? null;

        // Instanciation par défaut d'un nouvel utilisateur (utilisé en mode création).
        $user = new ARUser();

        if ($id) {
            // Mode update : on souhaite modifier un utilisateur existant.

            // Validation / filtrage de l'ID pour s'assurer qu'il s'agit bien d'un entier.
            $id = filter_var($args['id'], FILTER_VALIDATE_INT);

            // Récupération de l'utilisateur existant en base.
            $user = ARUser::findById($id);

            // On ne veut pas afficher le mot de passe dans le formulaire (sécurité + UX),
            // donc on écrase la valeur avec une chaîne vide.
            $user->password = '';

            // Données destinées éventuellement au layout global (ex: titre de page).
            // (Ici, $dataLayout est préparé mais pas encore utilisé dans render.)
            $dataLayout = ['title' => 'Modifier'];

            // Données spécifiques au formulaire (ex: mode d'affichage).
            $dataDetail = [
                'mode' => 'Modifier'
            ];
        } else {
            // Mode create : on souhaite ajouter un nouvel utilisateur.

            $dataLayout = ['title' => 'Ajouter'];
            $dataDetail = [
                'mode' => 'Ajouter'
            ];
        }

        // Dans tous les cas, on passe l'objet $user à la vue.
        $dataDetail['user'] = $user;
        $dataDetail['allRoles'] = \App\Models\ARRole::findAll();

        // Rendu de la vue du formulaire utilisateur.
        return $this->view->render($response, '/users/form.php', $dataDetail);
    }

    /**
     * Traite la soumission du formulaire de création / modification d'utilisateur.
     *
     * Règles principales :
     * - le username est obligatoire et doit être unique (sauf pour l'utilisateur en cours d'édition) ;
     * - le mot de passe doit contenir au minimum 8 caractères et au moins un caractère spécial ;
     * - en création, le mot de passe est obligatoire ;
     * - en modification, le mot de passe est optionnel, mais doit respecter les règles s'il est fourni.
     *
     * En cas d'erreur de validation, le formulaire est ré-affiché avec les messages d'erreur
     * et les données déjà saisies. En cas de succès, l'utilisateur est créé ou mis à jour,
     * un message de succès est ajouté, et une redirection est effectuée.
     *
     * @param Request  $request  Requête HTTP contenant les données du formulaire.
     * @param Response $response Réponse HTTP à renvoyer au client.
     *
     * @return Response Réponse HTTP redirigeant en fonction de l'action (create / update)
     *                  ou contenant la vue du formulaire en cas d'erreur.
     */
    public function formPost(Request $request, Response $response): Response
    {
        // Récupération des données POST (ou autre format parsé) sous forme de tableau associatif.
        $data = $request->getParsedBody() ?? [];

        // Mode : create ou update (présence d'un ID indique un update).
        $id = $data['id'] ?? null;
        $mode = $id ? 'Modifier' : 'Ajouter';

        // Récupération / normalisation des champs du formulaire.
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $selectedRoles = $data['roles'] ?? [];  // Array of role IDs

        // Tableau des erreurs, indexé par nom de champ,
        // chaque entrée contenant un tableau de messages.
        $errors = [];

        // --- Validation username ---
        if ($username === '') {
            $errors['username'][] = "Le nom d'utilisateur est obligatoire.";
        } else {
            // Unicité du username (en tenant compte du cas update) :
            // on cherche un éventuel autre utilisateur portant le même nom.
            $existing = ARUser::findByUsername($username);

            if ($existing) {
                // Si on est en création, ou si l'ID retourné diffère de celui en cours d'édition,
                // alors le username est déjà pris par un autre compte.
                if (!$id || (int) $existing->id !== (int) $id) {
                    $errors['username'][] = "Ce nom d'utilisateur est déjà utilisé.";
                }
            }
        }

        // --- Validation mot de passe ---
        $passwordPattern = '/^(?=.*[^a-zA-Z0-9]).{8,}$/';

        if (!$id) {
            // CREATE : mot de passe obligatoire.
            if ($password === '') {
                $errors['password'][] = "Le mot de passe est obligatoire.";
            } elseif (!preg_match($passwordPattern, $password)) {
                $errors['password'][] = "Le mot de passe doit contenir au moins 8 caractères dont au moins un caractère spécial.";
            }
        } else {
            // UPDATE : mot de passe optionnel, mais s'il est saisi il doit respecter les règles.
            if ($password !== '' && !preg_match($passwordPattern, $password)) {
                $errors['password'][] = "Le mot de passe doit contenir au moins 8 caractères dont au moins un caractère spécial.";
            }
        }

        // --- En cas d'erreurs : réafficher le formulaire ---
        if (!empty($errors)) {
            // On recrée un objet User "virtuel" pour re-remplir le formulaire
            // avec les données déjà saisies (username).
            $user = new ARUser();
            if ($id) {
                $user->id = (int) $id;
            }
            $user->username = $username;

            return $this->view->render($response, '/users/form.php', [
                'mode' => $mode,
                'user' => $user,
                'errors' => $errors,
                'selectedRoles' => $selectedRoles,
                'allRoles' => \App\Models\ARRole::findAll(),
            ]);
        }

        // ---------------------------------------------------------------------
        // A partir d'ici : aucune erreur de validation, on peut appeler le modèle.
        // ---------------------------------------------------------------------

        // Pour les redirections en cas d'erreur technique SQL
        $referer = $request->getHeaderLine('Referer') ?: '/users';

        if ($id) {
            // UPDATE : récupération de l'utilisateur existant.
            $user = ARUser::findById((int) $id);
            if (!$user) {
                Alert::add('danger', "L'utilisateur demandé est introuvable.");
                return $response
                    ->withHeader('Location', '/')
                    ->withStatus(302);
            }

            // Mise à jour des champs.
            $user->username = $username;

            // Si un nouveau mot de passe est fourni, on le hash ici AVANT l'update transactionnelle
            if ($password !== '') {
                $user->password = password_hash($password, PASSWORD_DEFAULT);
            }
            // Si $password === '', on conserve le hash existant déjà présent dans $user->password.

            try {
                $user->update();   // Transaction interne dans le modèle

                // Gérer les rôles via pivot
                $allRoles = \App\Models\ARRole::findAll();
                foreach ($allRoles as $role) {
                    if (in_array($role->id, $selectedRoles)) {
                        $user->assignRole($role);
                    } else {
                        $user->removeRole($role);
                    }
                }

                Alert::add('success', "L'utilisateur a été modifié. Merci de vous reconnecter pour prendre en compte les modifications.");

                return $response
                    ->withHeader('Location', '/')
                    ->withStatus(302);
            } catch (\Throwable $e) {
                Alert::add('danger', "Une erreur est survenue lors de la mise à jour de l'utilisateur.");
                // Optionnel : logger $e->getMessage()
                return $response
                    ->withHeader('Location', $referer)
                    ->withStatus(302);
            }
        }

        // CREATE : création d'une nouvelle instance User.
        $user = new ARUser();
        $user->username = $username;
        $user->password = $password;  // Hash fait dans User::create()

        try {
            $user->create();   // Transaction interne dans le modèle

            // Assigner les rôles
            foreach ($selectedRoles as $roleId) {
                $role = \App\Models\ARRole::findById($roleId);
                if ($role) {
                    $user->assignRole($role);
                }
            }

            Alert::add('success', "L'utilisateur a été ajouté. Vous pouvez vous connecter.");

            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        } catch (\Throwable $e) {
            Alert::add('danger', "Une erreur est survenue lors de la création de l'utilisateur.");
            // Optionnel : logger $e->getMessage()
            return $response
                ->withHeader('Location', $referer)
                ->withStatus(302);
        }
    }

    /**
     * Affiche la liste de tous les utilisateurs.
     *
     * Accessible uniquement aux administrateurs. En cas d'accès non autorisé,
     * l'utilisateur est redirigé vers la page précédente (ou la racine).
     *
     * @param Request  $request  Requête HTTP entrante.
     * @param Response $response Réponse HTTP à renvoyer au client.
     *
     * @return Response Réponse HTTP contenant la vue de la liste des utilisateurs
     *                  ou une redirection en cas d'accès refusé.
     */
    public function showAll(Request $request, Response $response): Response
    {
        // Vérification des droits : uniquement pour les administrateurs.
        // On s'appuie sur les données stockées dans $_SESSION['user_connected'].
        // RBAC: Vérification via rôles
        if (!(isset($_SESSION['user_connected']) && in_array('admin', $_SESSION['user_connected']['roles']))) {
            Alert::add('danger', 'Vous n\'êtes pas autorisé à accéder à cette page');

            // Récupération du header Referer pour revenir à la page précédente.
            // Si Referer est vide, on renvoie vers la racine du site.
            $referer = $request->getHeaderLine('Referer') ?: '/';

            return $response->withHeader('Location', $referer)->withStatus(302);
        }

        // Récupération de l'ensemble des utilisateurs en base.
        $users = ARUser::findAll();

        // Préparation des données à passer à la vue.
        $dataDetail = ['users' => $users];

        // Rendu de la vue affichant la liste des utilisateurs.
        return $this->view->render($response, '/users/index.php', $dataDetail);
    }

    /**
     * Affiche le détail d'un utilisateur.
     *
     * Accessible uniquement aux administrateurs, ou à l'utilisateur lui-même.
     * En cas d'accès non autorisé, redirige vers la page précédente (ou la racine).
     *
     * @param Request  $request  Requête HTTP entrante.
     * @param Response $response Réponse HTTP à renvoyer au client.
     * @param array    $args     Paramètres de route, notamment 'id'.
     *
     * @return Response Réponse HTTP contenant la vue du détail utilisateur
     *                  ou une redirection en cas d'accès refusé.
     */
    public function showOne(Request $request, Response $response, $args): Response
    {
        // Validation / filtrage de l'ID passé dans l'URL.
        $id = filter_var($args['id'], FILTER_VALIDATE_INT);

        // Contrôle d'accès :
        // - soit l'utilisateur connecté est administrateur,
        // - soit il s'agit du propriétaire de la fiche (id identique).
        if (
            !(isset($_SESSION['user_connected']) &&
                (in_array('admin', $_SESSION['user_connected']['roles']) || $_SESSION['user_connected']['id'] == $id))
        ) {

            Alert::add('danger', 'Vous n\'êtes pas autorisé à accéder à cette page');

            // Retour à la page précédente ou à la racine.
            $referer = $request->getHeaderLine('Referer') ?: '/';

            return $response->withHeader('Location', $referer)->withStatus(302);
        }

        // Récupération des données de l'utilisateur à afficher.
        $dataDetail = ['user' => ARUser::findById($id)];

        // Rendu de la vue de détail utilisateur.
        return $this->view->render($response, '/users/view.php', $dataDetail);
    }

    //`:~debutdelete~:`
    /**
     * Supprime un utilisateur.
     *
     * Accessible uniquement aux administrateurs. Tente de supprimer l'utilisateur
     * correspondant à l'ID fourni. En cas d'erreur (exception), un message est
     * ajouté dans le système d'alertes. Redirige ensuite vers la liste des utilisateurs.
     *
     * @param Request  $request  Requête HTTP entrante.
     * @param Response $response Réponse HTTP à renvoyer au client.
     * @param array    $args     Paramètres de route, notamment 'id'.
     *
     * @return Response Réponse HTTP redirigeant vers la liste des utilisateurs
     *                  ou vers la page précédente en cas d'accès refusé.
     */
    public function delete(Request $request, Response $response, $args): Response
    {
        // Vérification des droits : suppression uniquement pour les administrateurs.
        if (!(isset($_SESSION['user_connected']) && in_array('admin', $_SESSION['user_connected']['roles']))) {

            Alert::add('danger', "Vous n'êtes pas autorisé à effectuer cette opération.");

            $referer = $request->getHeaderLine('Referer') ?: '/';
            return $response->withHeader('Location', $referer)->withStatus(302);
        }

        // Validation / filtrage de l'ID passé dans l'URL.
        $id = filter_var($args['id'], FILTER_VALIDATE_INT);

        // Récupération de l'utilisateur à supprimer.
        $user = ARUser::findById($id);

        try {
            // Suppression transactionnelle : tâches + utilisateur,
            // avec contrôle du "dernier administrateur".
            ARUser::deleteAndTasks($id);

            Alert::add('success', "L'utilisateur et ses tâches associées ont été supprimés.");
        } catch (\RuntimeException $e) {
            // Erreurs métier (par ex. "dernier admin", utilisateur introuvable, etc.)
            Alert::add('danger', $e->getMessage());
        } catch (\Throwable $e) {
            // Erreurs techniques (PDO, autre exception non prévue).
            Alert::add(
                'danger',
                "Une erreur est survenue lors de la suppression de l'utilisateur."
            );
            // Optionnel : logger $e->getMessage() pour diagnostic.
        }

        // Redirection vers la liste des utilisateurs après la suppression (ou tentative).
        return $response->withHeader('Location', '/users')->withStatus(302); // 302 pour une redirection temporaire
    }
    //`:~finddelete~:`
}
