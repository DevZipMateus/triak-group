<?php
// Recebe o formulário de contato e encaminha por e-mail via SMTP.
// Não grava nada em disco ou banco de dados — só envia e descarta.

declare(strict_types=1);

require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

header('Content-Type: application/json; charset=utf-8');

function respond(bool $ok, string $code, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(['ok' => $ok, 'code' => $code]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'method_not_allowed', 405);
}

// Honeypot: campo invisível para humanos. Se vier preenchido, é bot.
if (!empty($_POST['website'] ?? '')) {
    respond(true, 'ok'); // finge sucesso para não dar dica ao bot
}

$name    = trim((string) ($_POST['name'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));

// Remove quebras de linha de todos os campos (evita header injection).
$name    = preg_replace('/[\r\n]+/', ' ', $name);
$phone   = preg_replace('/[\r\n]+/', ' ', $phone);
$email   = preg_replace('/[\r\n]+/', ' ', $email);
$subject = preg_replace('/[\r\n]+/', ' ', $subject);

if ($name === '' || $phone === '' || $email === '' || $subject === '') {
    respond(false, 'missing_fields', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'invalid_email', 422);
}

$config = require __DIR__ . '/mail-config.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->Port       = $config['smtp_port'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($config['smtp_user'], 'Site Triak Group');
    $mail->addAddress($config['mail_to'], $config['mail_to_name']);
    $mail->addReplyTo($email, $name);

    $mail->Subject = 'Novo contato pelo site — Triak Group';
    $mail->Body =
        "Novo contato recebido pelo formulário do site:\n\n" .
        "Nome: {$name}\n" .
        "Telefone: {$phone}\n" .
        "E-mail: {$email}\n" .
        "Assunto de interesse: {$subject}\n";

    $mail->send();

    respond(true, 'ok');
} catch (PHPMailerException $e) {
    respond(false, 'send_failed', 502);
}
