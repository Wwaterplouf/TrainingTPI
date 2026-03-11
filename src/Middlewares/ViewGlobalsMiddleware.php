<?php

declare(strict_types=1);
// Active le typage strict pour renforcer la fiabilité du code
// (les types des paramètres et valeurs de retour sont vérifiés).

namespace App\Middlewares;
// Namespace de la classe pour l'organisation du projet et l'autoloading via Composer.

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\PhpRenderer;

/**
 * Middleware pour ajouter des variables globales aux vues.
 */
final class ViewGlobalsMiddleware implements MiddlewareInterface
{
    /**
     * @param PhpRenderer $view
     */
    public function __construct(
        private PhpRenderer $view,
    ) {
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $parser = $routeContext->getRouteParser();

        $current = $route
            ? ($route->getName() ?? '')
            : trim($request->getUri()->getPath(), '/');

        return $handler->handle($request);
    }
}
