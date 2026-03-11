<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Models\Alert;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

final class AccessMiddleware
{
    /** @var array<string,mixed> */
    private array $rbac = [];

    private string $rbacPath;

    private bool $rbacOk = true;

    /**
     * @param array<string,mixed>|null $rbac     Injection (tests)
     * @param string|null             $rbacPath Chemin explicite vers rbac.php
     */
    public function __construct(?array $rbac = null, ?string $rbacPath = null)
    {
        // IMPORTANT : adapter le chemin selon LE projet
        $this->rbacPath = $rbacPath ?? (dirname(__DIR__) . '/Config/rbac.php');

        if ($rbac !== null) {
            $this->rbac = $rbac;
            return;
        }

        if (!is_file($this->rbacPath) || !is_readable($this->rbacPath)) {
            $this->rbacOk = false;
            return;
        }

        $loaded = require $this->rbacPath;

        if (!is_array($loaded)) {
            $this->rbacOk = false;
            return;
        }

        $this->rbac = $loaded;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // ERREUR BLOQUANTE, VISIBLE UTILISATEUR
        if (!$this->rbacOk) {
            error_log('[AccessMiddleware] RBAC manquant/invalide: ' . $this->rbacPath);

            $response = new SlimResponse();
            $response->getBody()->write(
                "Erreur 500 : configuration RBAC manquante ou invalide.\n" .
                "Fichier attendu : {$this->rbacPath}\n"
            );

            return $response
                ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                ->withStatus(500);
        }

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if (!$route) {
            return $handler->handle($request);
        }

        $routeName = (string) $route->getName();

        // 1) Routes publiques : pas d'auth, pas de RBAC
        if ($routeName !== '' && in_array($routeName, $this->rbac['public'] ?? [], true)) {
            return $handler->handle($request);
        }

        // 2) Auth obligatoire
        if (empty($_SESSION['user_connected'])) {
            Alert::add('danger', 'Merci de vous connecter pour accéder à cette page.');
            $parser = $routeContext->getRouteParser();

            return (new SlimResponse())
                ->withHeader('Location', $parser->urlFor('auth.login'))
                ->withStatus(302);
        }

        // 3) RBAC
        $userRoles = $_SESSION['user_connected']['roles'] ?? [];
        if (!is_array($userRoles)) {
            $userRoles = [];
        }

        $requiredRoles = $this->requiredRolesForRoute($routeName, $this->rbac['rules'] ?? []);

        if ($requiredRoles !== null && !$this->hasAnyRole($userRoles, $requiredRoles)) {
            Alert::add('danger', 'Accès refusé : droits insuffisants.');

            return (new SlimResponse())
                ->withHeader('Location', '/')
                ->withStatus(302);
        }

        return $handler->handle($request);
    }

    /**
     * @param string $routeName
     * @param array<string,array<int,string>> $rules
     * @return array<int,string>|null
     */
    private function requiredRolesForRoute(string $routeName, array $rules): ?array
    {
        if ($routeName === '') {
            return null;
        }

        foreach ($rules as $prefix => $roles) {
            $prefix = (string) $prefix;
            if ($prefix !== '' && str_starts_with($routeName, $prefix)) {
                return (array) $roles;
            }
        }

        return null;
    }

    /**
     * @param array<int,string> $userRoles
     * @param array<int,string> $requiredRoles
     */
    private function hasAnyRole(array $userRoles, array $requiredRoles): bool
    {
        foreach ($requiredRoles as $r) {
            if (in_array($r, $userRoles, true)) {
                return true;
            }
        }
        return false;
    }
}
