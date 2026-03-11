<?php

declare(strict_types=1);

namespace App\Config\Containers;

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;

/**
 * Définition HTTP pour le conteneur.
 */
class HttpDefinition
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return void
     */
    public function __invoke(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions([
            ResponseFactoryInterface::class => static fn () => AppFactory::determineResponseFactory(),
        ]);
    }
}
