<?php
/*
 * Esquemas técnicos de los servicios (SVG propio, dibujo de línea).
 * Se eligen por el icono que tiene cada servicio en el panel, así que si se añade o
 * reordena un servicio sigue llevando su esquema. Un icono sin esquema muestra el icono.
 * Son ilustraciones de principio, no planos de ninguna obra concreta.
 *
 * Clases: .k trazo base · .a trazo de acento (señal) · .t etiqueta · .f relleno suave
 * Cada trazo lleva pathLength="1" para dibujarse al entrar en pantalla (ver estilos.css).
 */

function esquema(string $icono): string {
    $L = 'pathLength="1"';
    $svg = [
    /* Cableado estructurado: rack con paneles, troncal horizontal y tomas de puesto */
    'i-net' => <<<SVG
<rect class="k" $L x="18" y="22" width="46" height="112" rx="2"/>
<path class="k" $L d="M18 42h46M18 58h46M18 74h46M18 90h46M18 106h46"/>
<path class="a" $L d="M64 66h28v-14h132"/>
<path class="k" $L d="M120 52v66M160 52v66M200 52v66"/>
<rect class="k" $L x="111" y="118" width="18" height="14" rx="1.5"/><rect class="k" $L x="151" y="118" width="18" height="14" rx="1.5"/><rect class="k" $L x="191" y="118" width="18" height="14" rx="1.5"/>
<path class="k" $L d="M116 124h8M156 124h8M196 124h8"/>
<circle class="dot" cx="64" cy="66" r="3"/>
<text class="t" x="18" y="150">RACK</text><text class="t" x="111" y="150">TOMAS RJ45</text><text class="t" x="110" y="44">TRONCAL</text>
SVG,
    /* Fibra óptica: dos salas técnicas unidas por una troncal con empalme por fusión */
    'i-fiber' => <<<SVG
<rect class="k" $L x="14" y="40" width="40" height="84" rx="2"/><rect class="k" $L x="186" y="40" width="40" height="84" rx="2"/>
<path class="k" $L d="M14 60h40M14 78h40M186 60h40M186 78h40"/>
<path class="a" $L d="M54 70c30 0 34-14 58-14h16c24 0 28 14 58 14"/>
<path class="k" $L d="M54 84c30 0 34 14 58 14h16c24 0 28-14 58-14"/>
<ellipse class="k" $L cx="120" cy="56" rx="12" ry="7"/><path class="k" $L d="M113 52l14 8M127 52l-14 8"/>
<text class="t" x="14" y="142">SALA A</text><text class="t" x="186" y="142">SALA B</text><text class="t" x="98" y="40">FUSIÓN</text><text class="t" x="96" y="118">TRONCAL</text>
SVG,
    /* Electricidad: esquema unifilar, acometida, cuadro general, salidas y SAI */
    'i-bolt' => <<<SVG
<path class="a" $L d="M20 18v34"/><path class="k" $L d="M14 30l6-12 6 12"/>
<rect class="k" $L x="8" y="52" width="24" height="26" rx="1.5"/>
<path class="k" $L d="M32 65h190"/>
<path class="k" $L d="M70 65v22l8 10M70 101v26M120 65v22l8 10M120 101v26M170 65v22l8 10M170 101v26"/>
<path class="k" $L d="M62 127h16M112 127h16M162 127h16"/>
<rect class="k" $L x="196" y="86" width="34" height="40" rx="2"/><path class="a" $L d="M213 65v21"/>
<path class="k" $L d="M204 112h18M204 104h18"/>
<text class="t" x="0" y="96">CGBT</text><text class="t" x="62" y="146">SALIDAS</text><text class="t" x="200" y="146">SAI</text><text class="t" x="34" y="20">ACOMETIDA</text>
SVG,
    /* Seguridad: cámaras IP y control de accesos sobre una red con conmutador PoE */
    'i-shield' => <<<SVG
<rect class="k" $L x="96" y="110" width="48" height="22" rx="2"/><path class="k" $L d="M104 121h4M114 121h4M124 121h4M134 121h4"/>
<path class="a" $L d="M120 110V76H40V52M120 76h80V52M120 76V52"/>
<path class="k" $L d="M28 40l26-8 4 12-26 8zM40 52v-4"/>
<path class="k" $L d="M188 40l26-8 4 12-26 8zM200 52v-4"/>
<rect class="k" $L x="108" y="26" width="24" height="26" rx="2"/><circle class="k" $L cx="120" cy="36" r="4"/><path class="k" $L d="M114 46h12"/>
<text class="t" x="16" y="24">CCTV IP</text><text class="t" x="176" y="24">CCTV IP</text><text class="t" x="98" y="16">ACCESOS</text><text class="t" x="106" y="150">PoE</text>
SVG,
    /* Centro de datos: planta de dos filas de racks con bandeja superior y troncales */
    'i-server' => <<<SVG
<path class="k" $L d="M24 30h192v34H24zM24 96h192v34H24z"/>
<path class="k" $L d="M56 30v34M88 30v34M120 30v34M152 30v34M184 30v34M56 96v34M88 96v34M120 96v34M152 96v34M184 96v34"/>
<path class="a" $L d="M8 80h224"/>
<path class="k" $L d="M40 64v16M104 64v16M168 64v16M72 96V80M136 96V80M200 96V80"/>
<text class="t" x="24" y="22">FILA A</text><text class="t" x="24" y="148">FILA B</text><text class="t" x="168" y="76">BANDEJA</text>
SVG,
    /* Control de clima (BMS): bus de control con controlador, fancoils y sonda */
    'i-climate' => <<<SVG
<rect class="k" $L x="16" y="60" width="40" height="40" rx="2"/><path class="k" $L d="M24 72h24M24 80h16M24 88h20"/>
<path class="a" $L d="M56 80h168"/>
<rect class="k" $L x="84" y="26" width="44" height="28" rx="2"/><circle class="k" $L cx="106" cy="40" r="9"/><path class="k" $L d="M106 54v26"/>
<rect class="k" $L x="150" y="106" width="44" height="28" rx="2"/><circle class="k" $L cx="172" cy="120" r="9"/><path class="k" $L d="M172 80v26"/>
<circle class="k" $L cx="214" cy="56" r="8"/><path class="k" $L d="M214 64v16M211 52h6M214 52v8"/>
<text class="t" x="14" y="116">CONTROL</text><text class="t" x="84" y="18">FANCOIL</text><text class="t" x="150" y="150">FANCOIL</text><text class="t" x="196" y="40">SONDA</text>
SVG,
    ];
    if (!isset($svg[$icono])) {
        return '<svg class="esq esq--icono" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#' . e(icono_valido($icono)) . '"/></svg>';
    }
    return '<svg class="esq" viewBox="0 0 240 160" aria-hidden="true" focusable="false">' . $svg[$icono] . '</svg>';
}
