<?php
/* Funciones compartidas por la web pública y el panel de administración. */

const RAIZ = __DIR__ . '/..';
const ARCHIVO_CONTENIDO = RAIZ . '/contenido.json';

/* Iconos disponibles para servicios, acreditaciones y obras: id => nombre visible en el panel */
const ICONOS = [
    'i-net'      => 'Red / cableado',
    'i-fiber'    => 'Fibra óptica',
    'i-bolt'     => 'Electricidad',
    'i-shield'   => 'Seguridad',
    'i-server'   => 'Centro de datos',
    'i-climate'  => 'Clima / BMS',
    'i-camera'   => 'Cámaras',
    'i-building' => 'Edificio',
    'i-star'     => 'Destacado',
    'i-check'    => 'Certificación',
    'i-award'    => 'Distintivo',
    'i-gov'      => 'Registro oficial',
    'i-warranty' => 'Garantía',
    'i-wave'     => 'Medición',
    'i-van'      => 'Vehículo',
    'i-box'      => 'Material',
    'i-route'    => 'Ruta / cobertura',
    'i-tools'    => 'Herramienta',
    'i-team'     => 'Equipo',
    'i-clock'    => 'Horario',
];

/* Categorías de la galería: id => texto del filtro */
const CATEGORIAS = [
    'cableado'     => 'Cableado',
    'fibra'        => 'Fibra y CPD',
    'electricidad' => 'Electricidad',
];

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cargar_contenido(): array {
    $txt = @file_get_contents(ARCHIVO_CONTENIDO);
    $d = $txt !== false ? json_decode($txt, true) : null;
    return is_array($d) ? $d : [];
}

/* Lectura segura de claves anidadas: v($d, 'portada.titular', '') */
function v(array $d, string $ruta, $defecto = '') {
    foreach (explode('.', $ruta) as $k) {
        if (!is_array($d) || !array_key_exists($k, $d)) return $defecto;
        $d = $d[$k];
    }
    return $d;
}

function lista(array $d, string $ruta): array {
    $x = v($d, $ruta, []);
    return is_array($x) ? array_values($x) : [];
}

function icono_valido($id): string {
    return array_key_exists((string)$id, ICONOS) ? (string)$id : 'i-net';
}

function numero_es(int $n): string {
    return number_format($n, 0, ',', '.');
}

function solo_digitos(string $s): string {
    return preg_replace('/\D+/', '', $s);
}

/* ---------- imágenes ---------- */

/* Versión WebP junto a la foto (misma ruta, extensión .webp), si existe */
function webp_de(string $ruta): ?string {
    $w = preg_replace('/\.(jpe?g|png)$/i', '.webp', $ruta);
    return ($w !== $ruta && is_file(RAIZ . '/' . $w)) ? $w : null;
}

/* Tamaño real de una imagen del sitio, para width/height (evita saltos de maquetación) */
function medidas(string $ruta): array {
    static $cache = [];
    if (!isset($cache[$ruta])) {
        $i = is_file(RAIZ . '/' . $ruta) ? @getimagesize(RAIZ . '/' . $ruta) : false;
        $cache[$ruta] = $i ? [$i[0], $i[1]] : [0, 0];
    }
    return $cache[$ruta];
}

/* <picture> con WebP (y variante de 480 px si existe) y la foto original como respaldo.
   $o: class, sizes, lazy (true por defecto), prioridad (fetchpriority=high) */
function foto(string $src, string $alt, array $o = []): string {
    [$w, $h] = medidas($src);
    $img = '<img src="' . e($src) . '" alt="' . e($alt) . '"'
         . ($w ? ' width="' . $w . '" height="' . $h . '"' : '')
         . (!empty($o['class']) ? ' class="' . e($o['class']) . '"' : '')
         . (($o['lazy'] ?? true) ? ' loading="lazy"' : '')
         . (!empty($o['prioridad']) ? ' fetchpriority="high"' : '')
         . ' decoding="async">';
    $webp = webp_de($src);
    if (!$webp) return $img;
    $peq = preg_replace('/\.webp$/', '-480.webp', $webp);
    $srcset = is_file(RAIZ . '/' . $peq) && $w > 480 ? e($peq) . ' 480w, ' . e($webp) . ' ' . $w . 'w' : e($webp);
    $sizes = strpos($srcset, ' 480w') !== false ? ' sizes="' . e($o['sizes'] ?? '100vw') . '"' : '';
    return '<picture><source type="image/webp" srcset="' . $srcset . '"' . $sizes . '>' . $img . '</picture>';
}
