<?php

use App\Config\Containers\ErrorDefinition;
use App\Config\Containers\HttpDefinition;
use App\Config\Containers\MiddlewareDefinition;
use App\Config\Containers\PhpRenderDefinition;
use App\Middlewares\AccessMiddleware;
use App\Middlewares\ErrorHandler;
use App\Middlewares\ViewGlobalsMiddleware;
use App\Middlewares\SessionMiddleware;
use App\Routes\Web;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

// Activer le chargement automatique des classes
require __DIR__ .'/../../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// Enregistre chaque groupe de définitions
(new PhpRenderDefinition())($containerBuilder);     // PhpRenderer + globals
(new HttpDefinition())($containerBuilder);          // ResponseFactoryInterface
(new MiddlewareDefinition ())($containerBuilder);   // ViewGlobalsMiddleware + SessionMiddleware
(new ErrorDefinition())($containerBuilder);         // ErrorHandler

$container = $containerBuilder->build();
$app = AppFactory::createFromContainer($container);

// --- Middlewares globaux ---
// On ajoute d'abord ceux qui doivent être exécutés le plus tard (ordre inverse)
$app->add($container->get(AccessMiddleware::class));
$app->add($container->get(ViewGlobalsMiddleware::class));
$app->add($container->get(SessionMiddleware::class));

// --- Routage & interception des erreurs ---
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));

// --- Initialisation des routes ---
Web::register($app, $container->get(PhpRenderer::class));

// --- Run ---
$app->run();

