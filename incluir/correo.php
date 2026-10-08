<?php
/*
 * Envío de correo del formulario.
 *
 * Tres modos, según admin/datos/correo.php (si no existe, se usa "mail"):
 *  - "graph": envía a través de Microsoft 365 con la API Microsoft Graph (OAuth por
 *             HTTPS, sin contraseñas de buzón). El correo sale del propio Microsoft 365,
 *             así que pasa SPF, DKIM y DMARC sin tocar los DNS. Recomendado.
 *  - "smtp":  servidor SMTP externo con usuario y contraseña (p. ej. Microsoft 365:
 *             smtp.office365.com, puerto 587, STARTTLS). Sin dependencias externas.
 *  - "mail":  función mail() de PHP del servidor.
 *
 * admin/datos/correo.php (no está en el repositorio; ver INSTRUCCIONES-INFORMATICO.txt):
 *   <?php exit; ?>
 *   {"modo":"graph","tenant":"<id del inquilino>","cliente":"<id de la aplicación>",
 *    "secreto":"<secreto de cliente>","buzon":"web@vaypatel.com"}
 * o bien
 *   {"modo":"smtp","host":"smtp.office365.com","puerto":587,"cifrado":"starttls",
 *    "usuario":"web@vaypatel.com","clave":"<contraseña>","remitente":"web@vaypatel.com"}
 */

const ARCHIVO_CORREO = __DIR__ . '/../admin/datos/correo.php';

/* Configuración guardada tras una cabecera PHP que corta la ejecución (como clave.php) */
function config_correo(): array {
    $t = is_file(ARCHIVO_CORREO) ? (string)file_get_contents(ARCHIVO_CORREO) : '';
    $t = preg_replace('/^<\?php exit; \?>\s*/', '', $t);
    $c = json_decode($t, true);
    return is_array($c) ? $c : ['modo' => 'mail'];
}

/* Envía un mensaje de texto. Devuelve [ok, detalle del error para el registro]. */
function enviar_correo(string $destino, string $asunto, string $cuerpo, string $responderA, string $nombre, string $remitente): array {
    $c = config_correo();
    if (($c['modo'] ?? 'mail') === 'graph') return enviar_graph($c, $destino, $asunto, $cuerpo, $responderA, $nombre);
    if (($c['modo'] ?? 'mail') === 'smtp') return enviar_smtp($c, $destino, $asunto, $cuerpo, $responderA, $nombre);

    $cabeceras = implode("\r\n", [
        'From: Web Vaypatel <' . $remitente . '>',
        'Reply-To: ' . $responderA,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-Mailer: Web Vaypatel',
    ]);
    $ok = mail($destino, '=?UTF-8?B?' . base64_encode($asunto) . '?=', $cuerpo, $cabeceras, '-f' . $remitente);
    return [$ok, $ok ? '' : 'mail() ha devuelto false'];
}

/* Petición HTTPS con cURL. Devuelve [código HTTP, cuerpo]. */
function peticion_https(string $url, array $cabeceras, string $datos): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $datos, CURLOPT_HTTPHEADER => $cabeceras,
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $r = curl_exec($ch);
    $codigo = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return [$codigo, $r === false ? 'cURL: ' . $error : (string)$r];
}

function enviar_graph(array $c, string $destino, string $asunto, string $cuerpo, string $responderA, string $nombre): array {
    foreach (['tenant', 'cliente', 'secreto', 'buzon'] as $k) {
        if (empty($c[$k])) return [false, "Graph: falta \"$k\" en admin/datos/correo.php"];
    }
    if (!function_exists('curl_init')) return [false, 'Graph: el servidor no tiene la extensión cURL de PHP'];
    $login = rtrim($c['login_url'] ?? 'https://login.microsoftonline.com', '/');   // solo cambia en pruebas
    $graph = rtrim($c['graph_url'] ?? 'https://graph.microsoft.com', '/');

    /* 1. Token de la aplicación (flujo client credentials) */
    [$codigo, $r] = peticion_https($login . '/' . rawurlencode($c['tenant']) . '/oauth2/v2.0/token',
        ['Content-Type: application/x-www-form-urlencoded'],
        http_build_query(['client_id' => $c['cliente'], 'client_secret' => $c['secreto'], 'scope' => 'https://graph.microsoft.com/.default', 'grant_type' => 'client_credentials']));
    $token = json_decode($r, true)['access_token'] ?? '';
    if ($codigo !== 200 || $token === '') return [false, "Graph: no se ha obtenido el token (HTTP $codigo): " . mb_substr($r, 0, 300)];

    /* 2. Envío desde el buzón configurado; "responder" va al cliente que escribe */
    $mensaje = ['message' => [
        'subject' => $asunto,
        'body' => ['contentType' => 'Text', 'content' => $cuerpo],
        'toRecipients' => [['emailAddress' => ['address' => $destino]]],
        'replyTo' => [['emailAddress' => ['address' => $responderA, 'name' => $nombre]]],
    ], 'saveToSentItems' => false];
    [$codigo, $r] = peticion_https($graph . '/v1.0/users/' . str_replace('%40', '@', rawurlencode($c['buzon'])) . '/sendMail',
        ['Authorization: Bearer ' . $token, 'Content-Type: application/json'],
        json_encode($mensaje, JSON_UNESCAPED_UNICODE));
    return $codigo === 202 ? [true, ''] : [false, "Graph: envío rechazado (HTTP $codigo): " . mb_substr($r, 0, 300)];
}

/* ---------- SMTP con autenticación (STARTTLS en 587 o SSL en 465) ---------- */

/* Lee una respuesta SMTP (puede ocupar varias líneas) y devuelve [código, texto] */
function smtp_leer($f): array {
    $texto = '';
    while (($l = fgets($f, 1024)) !== false) {
        $texto .= $l;
        if (strlen($l) < 4 || $l[3] !== '-') break;
    }
    return [(int)substr($texto, 0, 3), trim($texto)];
}

/* Envía un comando y comprueba que la respuesta empieza por el código esperado */
function smtp_orden($f, ?string $orden, int $esperado, string $paso): string {
    if ($orden !== null) fwrite($f, $orden . "\r\n");
    [$codigo, $texto] = smtp_leer($f);
    if ($codigo !== $esperado) throw new RuntimeException("SMTP $paso: esperaba $esperado y llegó \"" . mb_substr($texto, 0, 200) . '"');
    return $texto;
}

function cabecera_utf8(string $t): string {
    return preg_match('/[^\x20-\x7E]/', $t) ? '=?UTF-8?B?' . base64_encode($t) . '?=' : $t;
}

/* Nombre para From/Reply-To: entre comillas si es ASCII (puede llevar comas), codificado si no */
function nombre_cabecera(string $n): string {
    $n = str_replace(["\r", "\n"], '', $n);
    return preg_match('/[^\x20-\x7E]/', $n) ? '=?UTF-8?B?' . base64_encode($n) . '?=' : '"' . addcslashes($n, '"\\') . '"';
}

function enviar_smtp(array $c, string $destino, string $asunto, string $cuerpo, string $responderA, string $nombre): array {
    foreach (['host', 'usuario', 'clave'] as $k) {
        if (empty($c[$k])) return [false, "SMTP: falta \"$k\" en admin/datos/correo.php"];
    }
    $cifrado   = strtolower($c['cifrado'] ?? 'starttls');            // starttls | ssl
    $puerto    = (int)($c['puerto'] ?? ($cifrado === 'ssl' ? 465 : 587));
    $remitente = $c['remitente'] ?? $c['usuario'];
    $tls = ['peer_name' => $c['host'], 'verify_peer' => true, 'verify_peer_name' => true];
    if (!empty($c['ca_file'])) $tls['cafile'] = $c['ca_file'];       // solo si el servidor usa una CA propia
    $ctx = stream_context_create(['ssl' => $tls]);
    $destinoSocket = ($cifrado === 'ssl' ? 'ssl://' : 'tcp://') . $c['host'] . ':' . $puerto;

    $f = @stream_socket_client($destinoSocket, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$f) return [false, "SMTP: no se puede conectar a $destinoSocket ($errstr)"];
    stream_set_timeout($f, 20);
    try {
        $equipo = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['SERVER_NAME'] ?? '') ?: 'vaypatel.com';
        smtp_orden($f, null, 220, 'saludo');
        $ehlo = smtp_orden($f, 'EHLO ' . $equipo, 250, 'EHLO');
        if ($cifrado !== 'ssl') {
            smtp_orden($f, 'STARTTLS', 220, 'STARTTLS');
            if (!stream_socket_enable_crypto($f, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                throw new RuntimeException('SMTP: no se ha podido activar TLS');
            }
            $ehlo = smtp_orden($f, 'EHLO ' . $equipo, 250, 'EHLO tras TLS');
        }
        if (stripos($ehlo, 'LOGIN') !== false) {
            smtp_orden($f, 'AUTH LOGIN', 334, 'AUTH');
            smtp_orden($f, base64_encode($c['usuario']), 334, 'usuario');
            smtp_orden($f, base64_encode($c['clave']), 235, 'contraseña');
        } else {
            smtp_orden($f, 'AUTH PLAIN ' . base64_encode("\0" . $c['usuario'] . "\0" . $c['clave']), 235, 'AUTH PLAIN');
        }
        smtp_orden($f, 'MAIL FROM:<' . $remitente . '>', 250, 'MAIL FROM');
        smtp_orden($f, 'RCPT TO:<' . $destino . '>', 250, 'RCPT TO');
        smtp_orden($f, 'DATA', 354, 'DATA');
        $dominio = substr(strrchr($remitente, '@') ?: '@vaypatel.com', 1);
        $mensaje = implode("\r\n", [
            'Date: ' . date('r'),
            'From: Web Vaypatel <' . $remitente . '>',
            'To: <' . $destino . '>',
            'Reply-To: ' . nombre_cabecera($nombre) . ' <' . $responderA . '>',
            'Subject: ' . cabecera_utf8($asunto),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $dominio . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'X-Mailer: Web Vaypatel',
            '',
            rtrim(chunk_split(base64_encode($cuerpo), 76, "\r\n")),   // en base64 no hay líneas que empiecen por "."
            '.',
        ]);
        smtp_orden($f, $mensaje, 250, 'envío');
        @fwrite($f, "QUIT\r\n");
        fclose($f);
        return [true, ''];
    } catch (RuntimeException $e) {
        @fwrite($f, "QUIT\r\n");
        fclose($f);
        return [false, $e->getMessage()];
    }
}
