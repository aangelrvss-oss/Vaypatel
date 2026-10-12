<?php
/*
 * El peinado: geometría del esquema del momento central de la portada.
 * Seis cables (uno por sistema) que llegan enredados y terminan peinados, cada uno en
 * su equipo del armario. Es un esquema ilustrativo, no una obra concreta.
 *
 * Cada cable tiene dos trazados con exactamente los mismos comandos:
 *   d          estado final (peinado). Es el que se ve sin JavaScript o con movimiento reducido.
 *   data-from  estado inicial (enredado).
 * web.js interpola número a número entre los dos según el avance del scroll.
 */

const PEINE_CABLES = [
    // [equipo en escritorio, equipo en móvil, clase de color, grosor]
    ['Panel de parcheo 6A',  'Panel 6A',   'c-datos', 3],
    ['Bandeja de fibra',     'Fibra',      'c-fibra', 2.2],
    ['Switch PoE seguridad', 'Switch PoE', 'c-seg',   3],
    ['Controlador BMS',      'BMS',        'c-bms',   2.6],
    ['Servidores CPD',       'Servidores', 'c-cpd',   3],
    ['PDU · SAI',            'PDU · SAI',  'c-ele',   4.2],
];

function peine_n(float $v): string { return rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.'); }

/* Trazado de 10 puntos: P0, (c1 c2 P1), (c3 c4 P2), (c5 c6 P3) */
function peine_d(array $p): string {
    $f = fn($i) => peine_n($p[$i][0]) . ' ' . peine_n($p[$i][1]);
    return 'M' . $f(0) . ' C' . $f(1) . ' ' . $f(2) . ' ' . $f(3)
         . ' C' . $f(4) . ' ' . $f(5) . ' ' . $f(6)
         . ' C' . $f(7) . ' ' . $f(8) . ' ' . $f(9);
}

/* Geometría para escritorio (horizontal, viewBox 0 0 960 600) o móvil (vertical, 0 0 400 600) */
function peine_geo(bool $movil): array {
    $out = [];
    foreach (PEINE_CABLES as $k => $c) {
        if (!$movil) {
            $yu = 120 + $k * 72;            // centro de cada equipo en el armario
            $ly = 262 + $k * 15;            // carril del mazo peinado
            $fin = [[-10, $ly], [120, $ly], [250, $ly], [380, $ly], [470, $ly], [520, $ly], [560, $ly], [640, $ly], [640, $yu], [714, $yu]];
            $ini = [
                [-10, 70 + $k * 92],
                [150 + 46 * $k, 540 - 74 * $k], [250 - 22 * $k, 60 + 84 * $k], [380, 120 + (($k * 137) % 360)],
                [470 + 12 * $k, 570 - $k * 64], [500 - 8 * $k, 70 + $k * 58], [560, 190 + (($k * 97) % 320)],
                [660 - 14 * $k, 590 - $k * 46], [540 + 22 * $k, 40 + $k * 36], [600 + ($k % 3) * 28, 540 - $k * 58],
            ];
        } else {
            $yu = 214 + $k * 62;
            $lx = 196 - $k * 13;
            $fin = [[$lx, -10], [$lx, 40], [$lx, 90], [$lx, 140], [$lx, 160], [$lx, $yu - 50], [$lx, $yu - 30], [$lx, $yu - 8], [$lx + 10, $yu], [250, $yu]];
            $ini = [
                [30 + $k * 62, -10],
                [330 - 52 * $k, 70 + 20 * $k], [40 + 48 * $k, 130 - 12 * $k], [60 + (($k * 83) % 280), 150],
                [340 - 40 * $k, 210 + 30 * $k], [30 + 50 * $k, 300 - 20 * $k], [90 + (($k * 61) % 220), 360 + 20 * $k],
                [300 - 30 * $k, 470 - 10 * $k], [40 + 30 * $k, 520 - 30 * $k], [130 + ($k % 3) * 40, 570 - $k * 14],
            ];
        }
        $out[] = ['label' => $c[$movil ? 1 : 0], 'clase' => $c[2], 'w' => $c[3], 'yu' => $yu, 'd' => peine_d($fin), 'from' => peine_d($ini)];
    }
    return $out;
}

/* SVG completo del esquema. $movil cambia la composición, no solo el tamaño. */
function peine_svg(bool $movil): string {
    $g = peine_geo($movil);
    $vb = $movil ? '0 0 400 600' : '0 0 960 600';
    $s = '<svg class="peine ' . ($movil ? 'peine--m' : 'peine--d') . '" viewBox="' . $vb . '" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">';

    // Armario: bastidor, guías y equipos
    if (!$movil) {
        $s .= '<g class="rack"><rect class="rk-frame" x="700" y="64" width="220" height="472" rx="3"/>'
            . '<path class="rk-rail" d="M712 64v472M908 64v472"/>'
            . '<rect class="rk-org" x="664" y="78" width="24" height="444" rx="2"/>';
        foreach ($g as $k => $c) {
            $y = $c['yu'];
            $s .= '<g class="rk-u" data-k="' . $k . '"><rect x="716" y="' . ($y - 20) . '" width="188" height="40" rx="2"/>'
                . '<rect class="rk-port" x="716" y="' . ($y - 6) . '" width="10" height="12" rx="1"/>'
                . '<text class="rk-t" x="736" y="' . ($y + 4) . '">' . e(mb_strtoupper($c['label'])) . '</text>'
                . '<circle class="led" cx="890" cy="' . $y . '" r="4.5"/></g>';
        }
        $s .= '</g>';
        $bridas = [[250, 252, 104], [420, 252, 104], [530, 252, 104]];
    } else {
        $s .= '<g class="rack"><rect class="rk-frame" x="236" y="172" width="156" height="398" rx="3"/>'
            . '<path class="rk-rail" d="M246 172v398M382 172v398"/>';
        foreach ($g as $k => $c) {
            $y = $c['yu'];
            $s .= '<g class="rk-u" data-k="' . $k . '"><rect x="250" y="' . ($y - 20) . '" width="128" height="40" rx="2"/>'
                . '<rect class="rk-port" x="250" y="' . ($y - 6) . '" width="9" height="12" rx="1"/>'
                . '<text class="rk-t" x="264" y="' . ($y + 3) . '">' . e(mb_strtoupper($c['label'])) . '</text>'
                . '<circle class="led" cx="368" cy="' . $y . '" r="4"/></g>';
        }
        $s .= '</g>';
        $bridas = [[122, 46, 92, true], [122, 104, 92, true]];
    }

    // Cables
    $s .= '<g class="cables">';
    foreach ($g as $k => $c) {
        $s .= '<path class="cable ' . $c['clase'] . '" data-k="' . $k . '" stroke-width="' . $c['w'] . '" d="' . $c['d'] . '" data-from="' . $c['from'] . '"/>';
    }
    $s .= '</g>';

    // Bridas de velcro sobre el mazo peinado
    $s .= '<g class="bridas">';
    foreach ($bridas as $i => $b) {
        $s .= empty($b[3])
            ? '<rect class="brida" data-i="' . $i . '" x="' . ($b[0] - 5) . '" y="' . $b[1] . '" width="10" height="' . $b[2] . '" rx="3"/>'
            : '<rect class="brida" data-i="' . $i . '" x="' . $b[0] . '" y="' . ($b[1] - 5) . '" width="' . $b[2] . '" height="10" rx="3"/>';
    }
    $s .= '</g></svg>';
    return $s;
}
