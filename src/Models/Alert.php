<?php

namespace App\Models;

/**
 * Classe Alert
 * ------------
 * Gestion des messages d'alerte (flash messages) stockés en session.
 *
 * Principe :
 *  - On empile les messages dans $_SESSION['alerts'] via Alert::add().
 *  - On les récupère et on les efface avec Alert::get().
 *  - On peut tester leur présence avec Alert::hasAlerts().
 *  - Alert::displayHtml() génère le HTML prêt à être affiché dans la vue.
 *
 * Typiquement, les messages sont rendus dans le layout (ex: layout.php),
 * afin d'apparaître sur toutes les pages après une redirection.
 */
class Alert
{
    /**
     * Ajoute une alerte à la session.
     *
     * @param string $mode Type de l'alerte (ex. 'success', 'danger', 'info', 'warning').
     *                     Peut correspondre directement aux classes Bootstrap
     *                     (alert-success, alert-danger, etc.).
     * @param string $msg  Message à afficher à l'utilisateur.
     *
     * @return void
     */
    public static function add($mode, $msg): void
    {
        // Si aucune alerte n'est encore stockée, on initialise le tableau.
        if (!isset($_SESSION['alerts'])) {
            $_SESSION['alerts'] = [];
        }

        // On ajoute un nouvel élément au tableau des alertes.
        // Chaque alerte est un tableau associatif ['mode' => ..., 'msg' => ...].
        $_SESSION['alerts'][] = ['mode' => $mode, 'msg' => $msg];
    }

    /**
     * Récupère toutes les alertes stockées en session.
     *
     * Important : les alertes sont supprimées de la session après lecture
     * (comportement de "flash messages" : affichées une seule fois).
     *
     * @return array Liste des alertes sous forme de tableaux associatifs.
     */
    public static function get(): array
    {
        // S'il n'y a pas encore de clé 'alerts', on retourne un tableau vide.
        if (!isset($_SESSION['alerts'])) {
            return [];
        }

        // On copie les alertes présentes en session...
        $alerts = $_SESSION['alerts'];

        // ... puis on les supprime pour éviter qu'elles ne s'affichent plusieurs fois.
        unset($_SESSION['alerts']);

        return $alerts;
    }

    /**
     * Indique s'il existe au moins une alerte dans la session.
     *
     * @return bool true s'il y a des alertes, false sinon.
     */
    public static function hasAlerts(): bool
    {
        // Retourne true si la clé 'alerts' existe et n'est pas vide.
        // (Attention : si la clé n'existe pas, PHP émet un notice ; on pourrait
        // aussi écrire !empty($_SESSION['alerts'] ?? []) pour être plus robuste.)
        return !empty($_SESSION['alerts']);
    }

    /**
     * Génère le HTML des alertes prêtes à être affichées.
     *
     * Cette méthode :
     *  - récupère les alertes via Alert::get(),
     *  - construit un bloc <div> par alerte avec les classes CSS voulues,
     *  - renvoie la chaîne HTML complète.
     *
     * Typiquement appelée dans le layout :
     *   echo \App\Models\Alert::displayHtml();
     *
     * @return string HTML des alertes à afficher.
     */
    public static function displayHtml(): string
    {
        $html = '';

        // On récupère les alertes (et on les efface de la session).
        $alerts = Alert::get();

        if (!empty($alerts)) {
            // Conteneur global des alertes (utile pour le style).
            $html .= '<div class="alerts">';

            // Pour chaque alerte, on génère un bloc <div>.
            foreach ($alerts as $alert) {
                // Classe CSS basée sur le mode : ex. alert-success, alert-danger, etc.
                $html .= '<div class="alert alert-' . $alert['mode'] . ' shadow">';
                // Contenu du message.
                $html .= $alert['msg'];
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }
}
