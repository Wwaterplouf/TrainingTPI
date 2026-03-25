<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ARUser;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

final class ListsController
{
    public function __construct(private PhpRenderer $view) {}

    private function render(array $data = []): void
    {
        extract($data, EXTR_SKIP);
    }

    public function showForm(Request $request, Response $response)
    {
        return $this->view->render($response, 'users/lostPassword.php', ["email" => "", "username" => '']);
    }
}
