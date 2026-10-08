<?php
/*
 * Envío de correo del formulario.
 *
 * Dos modos, según admin/datos/correo.php (si no existe, se usa "mail"):
 *  - "graph": envía a través de Microsoft 365 con la API Microsoft Graph (OAuth, sin
 *             contraseñas de buzón). El correo sale del propio Microsoft 365, así que
 *             pasa SPF, DKIM y DMARC sin tocar los DNS. Recomendado para vaypatel.com.
 *  - "mail":  función mail() de PHP del servidor (requiere que el SPF del dominio
 *             autorice al servidor; si no, el correo puede acabar en spam).
 *
 * admin/datos/correo.php (no está en el repositorio; ver INSTRUCCIONES-INFORMATICO.txt):
 *   <?php exit; ?>
 *   {"modo":"graph","tenant":"<id del inquilino>","cliente":"<id de la aplicación>",
 *    "secreto":"<secreto de cliente>","buzon":"web@vaypatel.com"}
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
