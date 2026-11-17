<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;

class ContactController extends Controller
{
    public function submit(): void
    {
        $data = [
            'name'    => trim((string)($_POST['name'] ?? '')),
            'email'   => trim((string)($_POST['email'] ?? '')),
            'phone'   => trim((string)($_POST['phone'] ?? '')),
            'subject' => trim((string)($_POST['subject'] ?? '')),
            'message' => trim((string)($_POST['message'] ?? '')),
        ];

        $v = new Validator($data);
        $ok = $v->validate([
            'name'    => 'required|minLength:3|maxLength:120',
            'email'   => 'required|email|maxLength:180',
            'subject' => 'required|minLength:5|maxLength:200',
            'message' => 'required|minLength:10|maxLength:4000',
        ]);

        if (!$ok) {
            $this->withErrors($v->errors(), '/contacto');
        }

        // Guardar mensaje localmente en archivo de texto (sin depender de BD)
        $logDir = dirname(__DIR__, 2) . '/storage';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        $entry = [
            'date' => date('Y-m-d H:i:s'),
            'ip'   => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'data' => $data,
        ];
        @file_put_contents(
            $logDir . '/contact_messages.log',
            json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );

        // Opcional: enviar correo (si php mail está habilitado)
        $to = app_setting('contact_emails', 'contacto@amigosporelmundo.org');
        if ($to !== '' && function_exists('mail')) {
            $to = explode('|', $to)[0] ?? $to;
            $subject = '[AMIGOS POR EL MUNDO] Contacto Web: ' . $data['subject'];
            $body = "Nombre: {$data['name']}\r\nEmail: {$data['email']}\r\nTeléfono: {$data['phone']}\r\n\r\nMensaje:\r\n{$data['message']}";
            $headers = "From: {$data['name']} <{$data['email']}>\r\nReply-To: {$data['email']}\r\nContent-Type: text/plain; charset=UTF-8";
            @mail(trim($to), $subject, $body, $headers);
        }

        Session::flash('success', '¡Tu mensaje ha sido enviado! Te responderemos a la brevedad.');
        redirect('/contacto');
    }
}
