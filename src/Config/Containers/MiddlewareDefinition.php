<?php

declare(strict_types=1);

namespace App\Config\Containers;

use App\Middlewares\SessionMiddleware;
use App\Middlewares\ViewGlobalsMiddleware;
use DI\ContainerBuilder;
use Slim\Views\PhpRenderer;

/**
 * Définitions de services pour les middlewares de l'application.
 */
class MiddlewareDefinition
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return void
     */
    public function __invoke(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions([
            ViewGlobalsMiddleware::class => static function ($c) {
                return new ViewGlobalsMiddleware(
                    $c->get(PhpRenderer::class)
                );
            },
            SessionMiddleware::class => static fn () => new SessionMiddleware(),
        ]);
    }
}
