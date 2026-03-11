<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class MailerService
{
    /**
     * Envoie un email via SMTP en utilisant la configuration chargée depuis .env.
     *
     * @param string $to Adresse du destinataire.
     * @param string $toName Nom affiché du destinataire.
     * @param string $subject Sujet du message (sans retours à la ligne).
     * @param string $htmlBody Corps HTML du message.
     * @param string $replyTo Adresse de réponse (optionnelle).
     *
     * Étapes principales :
     * 1) Validation simple des entrées (email, subject).
     * 2) Configuration SMTP (host, port, chiffrement, auth).
     * 3) Construction du message (from, to, reply-to, html/text).
     * 4) Envoi effectif via PHPMailer.
     *
     * Lève une exception si la configuration est invalide ou si l'envoi échoue.
     * @throws Exception
     */
    public function send(
        string $to = getenv(""),
        string $toName,
        string $subject,
        string $htmlBody,
        string $replyTo = ''
    ): void {
        // --- Validation minimale côté service ---
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Destinataire invalide');
        }
        // Empêche l'injection d'en-têtes via CR/LF dans subject
        if (preg_match("/[\r\n]/", $subject)) {
            throw new Exception('Sujet invalide');
        }
        $mail = new PHPMailer(true);
        // Encodage pour bien gérer les accents
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        // --- SMTP : lecture de la config depuis .env ---
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->Port = (int)(getenv('SMTP_PORT') ?: 465);
        // Choix du chiffrement selon la config
        $enc = strtolower((string)(getenv('SMTP_ENCRYPTION') ?: 'ssl'));
        $mail->SMTPSecure = ($enc === 'ssl')
            ? PHPMailer::ENCRYPTION_SMTPS // 465
            : PHPMailer::ENCRYPTION_STARTTLS; // 587
        // Authentification SMTP
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USER') ?: '';
        $mail->Password = getenv('SMTP_PASS') ?: '';
        // --- Message ---
        // Expéditeur: MAIL_FROM ou compte SMTP par défaut
        $fromEmail = getenv('MAIL_FROM') ?: $mail->Username;
        $fromName = getenv('MAIL_FROM_NAME') ?: 'PHP Sendmail';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to, $toName);
        // Reply-To pour formulaire de contact
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }
        // Corps du message
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        // Envoi effectif
        $mail->send();
    }
}
