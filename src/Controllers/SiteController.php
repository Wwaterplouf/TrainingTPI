<?php

namespace App\Controllers;

// Import des classes nécessaires

use App\Models\ARAnime;
use GuzzleHttp;
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

    public function redirectToHome(Request $request, Response $response)
    {
        return $response->withStatus(302)
                        ->withheader('Location', '/home');
    }

    /**
     * Affiche la page d'accueil du site.
     *
     * @param Request  $request  Requête HTTP entrante.
     * @param Response $response Réponse HTTP à renvoyer au client.
     *
     * @return Response Réponse HTTP contenant la vue de la page d'accueil.
     */
    public function home(Request $request, Response $response, array $args): Response
    {
        // $query = '
        //         query ($page: Int, $perPage: Int, $search: String) {
        //         Page(page: $page, perPage: $perPage) {
        //             pageInfo {
        //                 total
        //                 currentPage
        //                 lastPage
        //                 hasNextPage
        //                 perPage
        //             }
        //             media(type: ANIME, search: $search, sort: POPULARITY_DESC) {
        //             id
        //             title {
        //                 romaji
        //                 english
        //             }
        //             siteUrl
        //             isAdult
        //             coverImage {
        //                 extraLarge
        //                 large
        //                 medium
        //             }
        //             season
        //             seasonYear
        //             endDate {
        //                 year
        //                 month
        //             }
        //             startDate {
        //                 year
        //                 month
        //             }
        //             genres
        //             volumes
        //             episodes
        //             chapters
        //             description
        //             }
        //         }
        // }';

        $query = '
                query ($page: Int, $perPage: Int) {
                Page(page: $page, perPage: $perPage) {
                    pageInfo {
                        total
                        currentPage
                        lastPage
                        hasNextPage
                        perPage
                    }
                    media(type: ANIME, sort: TRENDING_DESC) {
                    id
                    title {
                        romaji
                        english
                    }
                    siteUrl
                    isAdult
                    coverImage {
                        extraLarge
                        large
                        medium
                    }
                    season
                    status
                    seasonYear
                    endDate {
                        year
                        month
                    }
                    startDate {
                        year
                        month
                    }
                    genres
                    volumes
                    episodes
                    description
                    }
                }
        }';

        $page = filter_var($args['page'] ?? 1, FILTER_VALIDATE_INT);

        $variables = [
            'page' => $page && $page >= 1 ? $page : 1,
            'perPage' => 20,
            'search' => $args['search'] ?? 'Fate',
        ];

        $http = new GuzzleHttp\Client();
        $guzzleResponse = $http->post('https://graphql.anilist.co', [
            'json' => [
                'query' => $query,
                'variables' => $variables,
            ],
        ]);

        $datas = json_decode((string) $guzzleResponse->getBody(), true);

        $paginationInfos = [
            'total' => $datas["data"]["Page"]["pageInfo"]["total"],
            'currentPage' => $datas["data"]["Page"]["pageInfo"]["currentPage"],
            'previousPage' => $datas["data"]["Page"]["pageInfo"]["currentPage"] == 1 ? 'disabled' : '',
            'nextPage' => $datas["data"]["Page"]["pageInfo"]["hasNextPage"] ? '' : 'disabled',
            'link1' => '',
            'link2' => '',
            'link3' => '',
            'active' => ''
        ];

        $pageOffset = 0;
        $paginationInfos["active"] = 2;

        if ($paginationInfos["currentPage"] == 1)
        {
            $pageOffset = 1;
            $paginationInfos["active"] = 1;
        }
        else if ($paginationInfos["nextPage"] == 'disabled')
        {
            $pageOffset = -2;
            $paginationInfos["active"] = 3;
        }

        for ($i = 1; $i <= min($paginationInfos['total'], 3); $i++)
        {
            $paginationInfos['link' . $i] = $paginationInfos["currentPage"] + $i - 2 + $pageOffset;
        }
        
        $animesToPrint = [];
        for($i = 0; $i < $variables["perPage"]; $i++)
        {
            $anime = $datas["data"]["Page"]["media"][$i];
            if ($anime["isAdult"] === false) {
                $endDate = null;
                $timestamp = strtotime($anime["endDate"]["year"] . '-' . $anime["endDate"]["month"] . '-01');
                if ($timestamp !== false)
                {
                    $endDate = date("F Y", $timestamp);
                }
                $animesToPrint += [ $i =>
                    new ARAnime([
                        "anilist_id" => $anime["id"],
                        "englishTitle" => $anime["title"]["english"],
                        "originalTitle" => $anime["title"]["romaji"],
                        "siteUrl" => $anime["siteUrl"],
                        "description" => $anime["description"],
                        "largeCover" => $anime["coverImage"]["extraLarge"],
                        "status" => $anime["status"],
                        "startDate" => date("F Y", strtotime($anime["startDate"]["year"] . '-' . $anime["startDate"]["month"] . '-01')),
                        "endDate" => $endDate,
                        "season" => $anime["season"],
                        "seasonYear" => $anime["seasonYear"],
                        "episodes" => $anime["episodes"],
                        "genres" => $anime["genres"],
                    ])
                ];
            }
        }

        return $this->view->render($response, 'home.php', ['animes' => $animesToPrint, 'pagination' => $paginationInfos]); // Rend la vue home.php
    }
}
