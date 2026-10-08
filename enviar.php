<?php
/*
 * Formulario de contacto de www.vaypatel.com
 * Recibe los datos del formulario y los envía por email.
 *
 * CONFIGURACIÓN: revisar las dos líneas de abajo.
 *  - DESTINO:   lo toma del email de contacto del panel (con este valor por defecto).
 *  - REMITENTE: debe ser una cuenta del propio dominio (p. ej. web@vaypatel.com),
 *               si no, muchos servidores de correo marcan el mensaje como spam.
 */
// Buzón por defecto. Si en el panel hay un email de contacto válido, se usa ese.
$DESTINO = 'a.arribas@vaypatel.com';
$contenido = json_decode((string)@file_get_contents(__DIR__ . '/contenido.json'), true);
if (is_array($contenido) && filter_var($contenido['contacto']['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
    $DESTINO = $contenido['contacto']['email'];
}
const REMITENTE = 'web@vaypatel.com';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function responder(int $codigo, string $mensaje): void {
    http_response_code($codigo);
    echo json_encode(['ok' => $codigo === 200, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, 'Método no permitido.');
}

// Trampa antispam: campo oculto que una persona nunca rellena.
if (!empty($_POST['web_url'])) {
    responder(200, 'Recibido.');
}

// Límite básico: un envío cada 30 segundos por sesión.
session_start();
if (isset($_SESSION['ultimo_envio']) && time() - $_SESSION['ultimo_envio'] < 30) {
    responder(429, 'Espera unos segundos antes de volver a enviar.');
}

function limpiar(string $campo, int $max): string {
    $v = trim((string)($_POST[$campo] ?? ''));
    $v = str_replace(["\r", "\0"], '', $v);
    return mb_substr($v, 0, $max);
}

$nombre  = limpiar('nombre', 150);
$tel     = limpiar('tel', 40);
$email   = limpiar('email', 150);
$tipo    = limpiar('tipo', 80);
$mensaje = limpiar('msg', 5000);
$acepta  = !empty($_POST['consentimiento']);

if ($nombre === '' || $email === '' || $mensaje === '') {
    responder(400, 'Faltan el nombre, el email o la descripción de la obra.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(400, 'El email no parece válido.');
}
if (!$acepta) {
    responder(400, 'Es necesario aceptar la política de privacidad.');
}

// Evita inyección de cabeceras en los campos de una línea.
foreach ([$nombre, $tel, $email, $tipo] as $v) {
    if (strpos($v, "\n") !== false) responder(400, 'Datos no válidos.');
}

$asunto = 'Consulta web: ' . ($tipo !== '' ? $tipo : 'sin especificar');
$cuerpo = "Nueva consulta desde www.vaypatel.com\n"
        . "--------------------------------------\n\n"
        . "Nombre y empresa: $nombre\n"
        . "Teléfono:         " . ($tel !== '' ? $tel : '-') . "\n"
        . "Email:            $email\n"
        . "Tipo de trabajo:  $tipo\n\n"
        . "Descripción:\n$mensaje\n\n"
        . "--------------------------------------\n"
        . "Enviado el " . date('d/m/Y H:i') . " · Privacidad aceptada\n";

$cabeceras = implode("\r\n", [
    'From: Web Vaypatel <' . REMITENTE . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
]);

$ok = mail($DESTINO, '=?UTF-8?B?' . base64_encode($asunto) . '?=', $cuerpo, $cabeceras, '-f' . REMITENTE);

if (!$ok) {
    responder(500, 'No se ha podido enviar. Escríbenos a ' . $DESTINO . ' o llámanos al 607 23 09 57.');
}

$_SESSION['ultimo_envio'] = time();
responder(200, 'Recibido. Te respondemos en menos de 24 horas laborables.');
