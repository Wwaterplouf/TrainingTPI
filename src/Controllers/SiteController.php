<?php

namespace App\Controllers;

// Import des classes nécessaires
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

/**
 * Contrôleur principal du site.
 *
 * Gère les pages "générales" (par exemple la page d'accueil),
 * qui ne sont pas directement liées à une ressource métier
 * spécifique (utilisateur, tâche, etc.).
 */
class SiteController
{
    /**
     * Constructeur du contrôleur.
     *
     * Injection du moteur de rendu PhpRenderer pour générer les vues.
     *
     * @param PhpRenderer $view Moteur de templates utilisé pour rendre les vues PHP.
     */
    public function __construct(private PhpRenderer $view)
    {
    }

    /**
     * Affiche la page d'accueil du site.
     *
     * @param Request  $request  Requête HTTP entrante.
     * @param Response $response Réponse HTTP à renvoyer au client.
     *
     * @return Response Réponse HTTP contenant la vue de la page d'accueil.
     */
    public function home(Request $request, Response $response): Response
    {
        // Rendu de la vue 'home.php' sans données supplémentaires.
        // PhpRenderer se charge d'inclure le template et d'écrire dans la réponse.
        return $this->view->render($response, 'home.php'); // Rend la vue home.php
    }
}
