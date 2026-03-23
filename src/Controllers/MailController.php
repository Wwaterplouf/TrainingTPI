<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ARUser;
use App\Services\MailerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PHPMailer\PHPMailer\Exception as MailException;
use Slim\Views\PhpRenderer;
use Throwable;

final class MailController
{
    public function __construct(private PhpRenderer $view, private MailerService $mailer = new MailerService()) {}

    private function render(array $data = []): void
    {
        extract($data, EXTR_SKIP);
    }

    public function showForm(Request $request, Response $response)
    {
        return $this->view->render($response, 'users/lostPassword.php', ["email" => "", "username" => '']);
    }

    // public function submit(): void
    // {
    //     // 1) Récupère et nettoie les champs
    //     $email = trim((string)($_POST['email'] ?? ''));
    //     $msg = trim((string)($_POST['message'] ?? ''));
    //     $old = ['email' => $email, 'message' => $msg];
    //     $errors = [];
    //     // 2) Validation minimale
    //     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //         $errors[] = 'Adresse e-mail invalide.';
    //     }
    //     if ($msg === '' || mb_strlen($msg) > 2000) {
    //         $errors[] = 'Message vide ou trop long (max 2000 caractères).';
    //     }
    //     if ($errors) {
    //         http_response_code(422);
    //         $this->render([
    //             'flash' => ['type' => 'error', 'text' => implode(' ', $errors)],
    //             'old' => $old,
    //         ]);
    //         return;
    //     }
    //     // 3) Construction du mail (From = compte SMTP ; Reply-To = utilisateur)
    //     $to = getenv('MAIL_FROM') ?: (getenv('SMTP_USER') ?: '');
    //     if ($to === '') {
    //         http_response_code(500);
    //         $this->render([
    //             'flash' => ['type' => 'error', 'text' => 'Configuration SMTP incomplète (MAIL_FROM/SMTP_USER).'],
    //             'old' => $old,
    //         ]);
    //         return;
    //     }
    //     $subject = 'Nouveau message via formulaire';
    //     $html = ''
    //         . '<p><strong>De :</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
    //         . '<p><strong>Message :</strong><br>'
    //         . nl2br(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'))
    //         . '</p>';
    //     // 4) Envoi + gestion d'erreurs
    //     try {
    //         $this->mailer->send($to, 'Atelier', $subject, $html, $email);
    //         $this->render([
    //             'flash' => ['type' => 'success', 'text' => 'Message envoyé.'],
    //             'old' => ['email' => '', 'message' => ''],
    //         ]);
    //     } catch (MailException $e) {
    //         // En prod : loguer $e->getMessage() plutôt que l'afficher
    //         http_response_code(500);
    //         $this->render([
    //             'flash' => ['type' => 'error', 'text' => 'Erreur SMTP : envoi impossible.'],
    //             'old' => $old,
    //         ]);
    //     } catch (Throwable $e) {
    //         http_response_code(500);
    //         $this->render([
    //             'flash' => ['type' => 'error', 'text' => 'Erreur interne : envoi impossible.'],
    //             'old' => $old,
    //         ]);
    //     }
    // }
}
