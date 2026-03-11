<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Views\PhpRenderer;
use Throwable;

/**
 * Gestionnaire d'erreurs global pour Slim.
 */
final class ErrorHandler
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param PhpRenderer $view
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private PhpRenderer $view
    ) {
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return Response
     */
    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $response = $this->responseFactory->createResponse();

        $status = 500;
        $template = '500.php';
        $data = [
            'pagetitle' => 'Erreur serveur',
            'message' => null,
            'file'    => null,
            'line'    => null,
            'trace'   => null,
            'debug'   => $displayErrorDetails,
        ];

        if ($exception instanceof HttpNotFoundException) {
            $status = 404;
            $template = '404.php';
            $data['pagetitle'] = 'Page introuvable';

            if ($displayErrorDetails) {
                $data['message'] = $exception->getMessage();
            }
        }
        elseif ($exception instanceof HttpForbiddenException || $exception instanceof HttpUnauthorizedException) {
            $status = 403;
            $template = '403.php';
            $data['pagetitle'] = 'Accès interdit';

            if ($displayErrorDetails) {
                $data['message'] = $exception->getMessage();
            }
        }
        else {
            if ($displayErrorDetails) {
                $data['message'] = $exception->getMessage();
                $data['file']    = $exception->getFile();
                $data['line']    = $exception->getLine();
                $data['trace']   = $exception->getTraceAsString();
            }
        }

        if ($logErrors) {
            $log = sprintf(
                "[%s] %s: %s in %s:%d\n",
                date('Y-m-d H:i:s'),
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );

            if ($logErrorDetails) {
                $log .= $exception->getTraceAsString() . "\n";
            }

            error_log($log);
        }

        return $this->view->render(
            $response->withStatus($status),
            $template,
            $data
        );
    }
}
