<?php
/*
 * Panel de administración — seguridad, sesiones y guardado.
 * Este archivo no se abre directamente; lo carga admin/index.php.
 */
require __DIR__ . '/../incluir/comun.php';

const ARCHIVO_CLAVE  = __DIR__ . '/datos/clave.php';     // empieza por una línea que corta PHP: abierto desde la web no muestra nada
const ARCHIVO_INTENTOS = __DIR__ . '/datos/intentos.php';
const CABECERA_PRIVADA = "<?php exit; ?>\n";
const DIR_COPIAS     = __DIR__ . '/datos/copias';
const DIR_GALERIA    = RAIZ . '/img/galeria';
const DIR_FOTOS      = RAIZ . '/img';
const MAX_INTENTOS   = 5;        // fallos seguidos antes de bloquear
const BLOQUEO_SEG    = 900;      // 15 minutos
const SESION_SEG     = 7200;     // la sesión caduca tras 2 horas sin actividad
const MAX_COPIAS     = 20;
const MAX_SUBIDA     = 12 * 1024 * 1024;

/* ---------- sesión ---------- */
function iniciar_sesion(): void {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    session_name('vp_admin');
    session_set_cookie_params(['lifetime' => 0, 'path' => dirname($_SERVER['SCRIPT_NAME']) . '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
    ini_set('session.use_strict_mode', '1');
    session_start();
    if (!empty($_SESSION['ok']) && time() - ($_SESSION['actividad'] ?? 0) > SESION_SEG) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['actividad'] = time();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
}

function csrf_campo(): string {
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrf_ok(): bool {
    return isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function conectado(): bool { return !empty($_SESSION['ok']); }

/* ---------- contraseña ---------- */
/* Datos privados guardados como JSON tras una cabecera PHP que corta la ejecución.
   Se leen como texto (no con include) para que la caché de PHP no sirva una versión antigua. */
function leer_privado(string $ruta): array {
    $t = is_file($ruta) ? (string)file_get_contents($ruta) : '';
    if (strncmp($t, CABECERA_PRIVADA, strlen(CABECERA_PRIVADA)) === 0) $t = substr($t, strlen(CABECERA_PRIVADA));
    $d = json_decode($t, true);
    return is_array($d) ? $d : [];
}

function guardar_privado(string $ruta, array $d): bool {
    return escribir_atomico($ruta, CABECERA_PRIVADA . json_encode($d, JSON_UNESCAPED_SLASHES));
}

function leer_clave(): array {
    $c = leer_privado(ARCHIVO_CLAVE);
    return isset($c['hash']) ? $c : ['hash' => '', 'cambiar' => true];
}

function guardar_clave(string $nueva): bool {
    return guardar_privado(ARCHIVO_CLAVE, ['hash' => password_hash($nueva, PASSWORD_DEFAULT), 'cambiar' => false, 'fecha' => date('c')]);
}

function clave_valida(string $c): ?string {
    if (mb_strlen($c) < 10) return 'La contraseña debe tener al menos 10 caracteres.';
    if (!preg_match('/[A-Za-zÁÉÍÓÚáéíóúÑñ]/u', $c) || !preg_match('/\d/', $c)) return 'Usa letras y números.';
    return null;
}

/* ---------- bloqueo por intentos fallidos ---------- */
function ip_cliente(): string { return $_SERVER['REMOTE_ADDR'] ?? 'desconocida'; }

function leer_intentos(): array { return leer_privado(ARCHIVO_INTENTOS); }

function bloqueado(): int {
    $r = leer_intentos()[hash('sha256', ip_cliente())] ?? null;
    if (!$r || $r['n'] < MAX_INTENTOS) return 0;
    $resta = $r['t'] + BLOQUEO_SEG - time();
    return $resta > 0 ? $resta : 0;
}

function anotar_intento(bool $ok): void {
    $d = leer_intentos();
    $k = hash('sha256', ip_cliente());
    foreach ($d as $kk => $r) if (time() - ($r['t'] ?? 0) > BLOQUEO_SEG * 4) unset($d[$kk]);
    if ($ok) unset($d[$k]);
    else {
        $prev = $d[$k] ?? ['n' => 0, 't' => time()];
        if (time() - $prev['t'] > BLOQUEO_SEG) $prev = ['n' => 0, 't' => time()];
        $d[$k] = ['n' => $prev['n'] + 1, 't' => time()];
    }
    guardar_privado(ARCHIVO_INTENTOS, $d);
}

/* ---------- escritura segura ---------- */
function escribir_atomico(string $ruta, string $txt): bool {
    $tmp = $ruta . '.tmp-' . bin2hex(random_bytes(4));
    if (file_put_contents($tmp, $txt, LOCK_EX) === false) return false;
    if (!rename($tmp, $ruta)) { @unlink($tmp); return false; }
    return true;
}

function guardar_contenido(array $d): bool {
    if (!is_dir(DIR_COPIAS)) @mkdir(DIR_COPIAS, 0750, true);
    if (is_file(ARCHIVO_CONTENIDO)) @copy(ARCHIVO_CONTENIDO, DIR_COPIAS . '/contenido-' . date('Ymd-His') . '.json');
    $copias = glob(DIR_COPIAS . '/contenido-*.json') ?: [];
    rsort($copias);
    foreach (array_slice($copias, MAX_COPIAS) as $vieja) @unlink($vieja);
    $json = json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $json !== false && escribir_atomico(ARCHIVO_CONTENIDO, $json);
}

function copias(): array {
    $c = glob(DIR_COPIAS . '/contenido-*.json') ?: [];
    rsort($c);
    return array_map('basename', $c);
}

/* ---------- limpieza de lo que llega del formulario ---------- */
function txt($v, int $max = 600): string {
    $v = is_string($v) ? $v : '';
    $v = str_replace(["\r\n", "\r", "\0"], ["\n", "\n", ''], $v);
    return mb_substr(trim($v), 0, $max);
}

function lineas($v, int $max = 60): array {
    return array_slice(array_values(array_filter(array_map(fn($l) => txt($l, 160), explode("\n", txt($v, 8000))), 'strlen')), 0, $max);
}

/* Filas de una lista repetible; descarta las que llegan vacías */
function filas(string $campo, array $esquema, int $max = 80): array {
    $in = $_POST[$campo] ?? [];
    if (!is_array($in)) return [];
    $out = [];
    foreach ($in as $fila) {
        if (!is_array($fila)) continue;
        $r = []; $vacia = true;
        foreach ($esquema as $k => $tipo) {
            $v = $fila[$k] ?? '';
            switch ($tipo) {
                case 'int':   $r[$k] = max(0, min(10000000, (int)preg_replace('/\D/', '', (string)$v))); break;
                case 'icono': $r[$k] = icono_valido($v); break;
                case 'cat':   $r[$k] = array_key_exists($v, CATEGORIAS) ? $v : 'cableado'; break;
                case 'tags':  $r[$k] = array_values(array_filter(array_map(fn($t) => txt($t, 40), explode(',', txt($v, 300))), 'strlen')); break;
                case 'foto':  $r[$k] = ruta_foto_valida($v) ?? ''; break;
                case 'corto': $r[$k] = txt($v, 12); break;
                default:      $r[$k] = txt($v, 900);
            }
            if (!in_array($tipo, ['icono', 'cat'], true) && $r[$k] !== '' && $r[$k] !== [] && $r[$k] !== 0) $vacia = false;
        }
        if (!$vacia) $out[] = $r;
        if (count($out) >= $max) break;
    }
    return $out;
}

/* Solo se aceptan rutas a imágenes dentro de img/ que existan de verdad */
function ruta_foto_valida($v): ?string {
    $v = is_string($v) ? $v : '';
    if (!preg_match('#^img/(galeria/)?[A-Za-z0-9._-]+\.(jpe?g|png|webp)$#', $v)) return null;
    return is_file(RAIZ . '/' . $v) ? $v : null;
}

/* ---------- fotos ---------- */
/* Comprueba que el archivo es una imagen real, la vuelve a codificar como JPG
   (eso elimina cualquier contenido incrustado) y la guarda con un nombre generado. */
function procesar_foto(array $f, string $dir, string $prefijo, int $ancho = 1600): array {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $msg = [UPLOAD_ERR_INI_SIZE => 'La foto supera el tamaño que permite el servidor.', UPLOAD_ERR_FORM_SIZE => 'La foto es demasiado grande.', UPLOAD_ERR_NO_FILE => 'No se ha elegido ninguna foto.'];
        return [null, $msg[$f['error'] ?? 4] ?? 'No se ha podido subir la foto.'];
    }
    if (!is_uploaded_file($f['tmp_name']) || $f['size'] > MAX_SUBIDA) return [null, 'La foto supera 12 MB o no es válida.'];
    $info = @getimagesize($f['tmp_name']);
    $tipos = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
    if (!$info || !isset($tipos[$info[2]]) || !function_exists($tipos[$info[2]])) return [null, 'Solo se admiten fotos JPG, PNG o WebP.'];
    if ($info[0] * $info[1] > 40000000) return [null, 'La foto tiene demasiada resolución.'];
    $img = @$tipos[$info[2]]($f['tmp_name']);
    if (!$img) return [null, 'No se ha podido leer la foto.'];
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $o = (@exif_read_data($f['tmp_name'])['Orientation'] ?? 1);
        if ($o == 3) $img = imagerotate($img, 180, 0); elseif ($o == 6) $img = imagerotate($img, -90, 0); elseif ($o == 8) $img = imagerotate($img, 90, 0);
    }
    $w = imagesx($img); $h = imagesy($img);
    if ($w > $ancho) {
        $nh = (int)round($h * $ancho / $w);
        $dst = imagecreatetruecolor($ancho, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $ancho, $nh, $w, $h);
        imagedestroy($img); $img = $dst;
    } elseif ($info[2] !== IMAGETYPE_JPEG) {
        $dst = imagecreatetruecolor($w, $h);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopy($dst, $img, 0, 0, 0, 0, $w, $h);
        imagedestroy($img); $img = $dst;
    }
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $nombre = $prefijo . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.jpg';
    $ok = imagejpeg($img, $dir . '/' . $nombre, 82);
    /* copia en WebP junto a la JPG: la web la sirve a los navegadores que la admiten (más ligera) */
    if ($ok && function_exists('imagewebp')) @imagewebp($img, $dir . '/' . substr($nombre, 0, -4) . '.webp', 78);
    imagedestroy($img);
    return $ok ? [$nombre, null] : [null, 'No se ha podido guardar la foto en el servidor.'];
}

/* Reorganiza $_FILES['x'] de un input múltiple en una lista de archivos */
function archivos(string $campo): array {
    $f = $_FILES[$campo] ?? null;
    if (!$f || !is_array($f['name'])) return $f ? [$f] : [];
    $out = [];
    foreach ($f['name'] as $i => $n) $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    return $out;
}
