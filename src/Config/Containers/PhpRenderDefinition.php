<?php

declare(strict_types=1);

namespace App\Config\Containers;

use DI\ContainerBuilder;
use Slim\Views\PhpRenderer;

/**
 * Définition du service PhpRenderer pour le conteneur DI.
 */
class PhpRenderDefinition
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return void
     */
    public function __invoke(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions([
            PhpRenderer::class => static function () {
                $view = new PhpRenderer(__DIR__ . '/../../Views');
                $view->setLayout('layout.php');
                return $view;
            },
        ]);
    }
}
