<?php

declare(strict_types=1);

namespace App\Routes;

use App\Controllers\UsersController;
use App\Controllers\SiteController;
use Slim\App;
use Slim\Views\PhpRenderer;


final class Web
{
    public static function register(App $app, PhpRenderer $view): void
    {
        $app->get('/', [SiteController::class, 'home'])->setName('home');
        $app->get('/login', [UsersController::class, 'formLoginShow'])->setName('auth.login');
        $app->post('/login', [UsersController::class, 'formLoginPost'])->setName('auth.login.post');
        $app->get('/logout', [UsersController::class, 'logOff'])->setName('auth.logout');
        $app->get('/lostPassword', UsersController::class, 'resetPassword')->setName('auth.reset');

        $app->group('/users', function ($group): void {
                    $group->get('[/]', [UsersController::class, 'showAll'])->setName('admin.users.index');
                    $group->get('/view/{id}', [UsersController::class, 'showOne'])->setName('admin.users.view');
                    $group->get('/delete/{id}', [UsersController::class, 'delete'])->setName('admin.users.delete');
                    $group->get('/register[/{id}]', [UsersController::class, 'formShow'])->setName('auth.register');
                    $group->post('/register', [UsersController::class, 'formPost'])->setName('auth.register.post');
                });
    }
}