<?php

declare(strict_types=1);

namespace App\Routes;

use App\Controllers\MailController;
use App\Controllers\SiteController;
use App\Controllers\UsersController;
use App\Services\MailerService;
use Slim\App;
use Slim\Views\PhpRenderer;

final class Web
{
    public static function register(App $app, PhpRenderer $view): void
    {

        $app->group('/auth', function ($group): void {
            $group->get('/login', [UsersController::class, 'formLoginShow'])->setName('auth.login');
            $group->post('/login', [UsersController::class, 'formLoginPost'])->setName('auth.login.post');
            $group->get('/logout', [UsersController::class, 'logOff'])->setName('auth.logout');
            $group->get('/passwordlost', [MailController::class, 'showForm'])->setName('auth.resetform');
            $group->get('/register[/{id}]', [UsersController::class, 'formShow'])->setName('auth.register');
            $group->post('/register', [UsersController::class, 'formPost'])->setName('auth.register.post');
        });

        $app->group('/users', function ($group): void {
            $group->get('[/]', [UsersController::class, 'showAll'])->setName('admin.users.index');
            $group->get('/view/{id}', [UsersController::class, 'showOne'])->setName('users.view');
            $group->get('/save/{id}', [UsersController::class, 'showOne'])->setName('users.save');
            $group->get('/delete/{id}', [UsersController::class, 'delete'])->setName('users.delete');
            $group->get('/resetpassword/{hash}', [UsersController::class, 'showResetPasswordForm'])->setName('users.resetpassword');
        });

        $app->post('/sendmail', [UsersController::class, 'sendMailResetPassword'])->setName('users.sendmail');
        $app->post('/newuserpassword', [UsersController::class, 'updateUserPasswordFromReset'])->setName('users.newuserpassword');

        $app->get('/', [SiteController::class, 'redirectToHome'])->setName('home');
        $app->get('/home[/{page}]', [SiteController::class, 'home'])->setName('home');
    }
}
