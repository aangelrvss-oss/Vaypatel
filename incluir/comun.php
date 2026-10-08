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
