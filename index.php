<?php
/*
 * Vaypatel Proyectos — página principal.
 * Todo el texto sale de contenido.json, que se edita desde /admin/.
 * No hace falta tocar este archivo para cambiar contenidos.
 *
 * Rediseño "Expediente técnico" (ver scrollcraft/builds/vaypatel/BRIEF.md). Recorrido:
 * firma del logo → portada por planos → credenciales → servicios → pausa → el peinado (momento
 * central) → medición y acreditaciones → proyectos → trabajo terminado → empresa y medios → contacto.
 *
 * El movimiento lo hace el motor Scroll-craft (assets/scrollcraft.js, sin modificar) leyendo los
 * atributos data-sc-*; lo propio de esta web (firma, peinado, órbita) está en assets/web.js.
 * Sin JavaScript todo el contenido se ve y el formulario funciona.
 */
require __DIR__ . '/incluir/comun.php';
require __DIR__ . '/incluir/esquemas.php';
require __DIR__ . '/incluir/peine.php';
$d = cargar_contenido();

$tel      = v($d, 'contacto.telefono', '607 23 09 57');
$telLink  = '+34' . preg_replace('/^34/', '', solo_digitos($tel));
$telTxt   = str_replace(' ', "\u{00A0}", $tel); // el número no se parte en dos líneas
$telOf     = v($d, 'contacto.telefono_oficina', '');
$telOfLink = $telOf !== '' ? '+34' . preg_replace('/^34/', '', solo_digitos($telOf)) : '';
$telOfTxt  = str_replace(' ', "\u{00A0}", $telOf);
$email    = v($d, 'contacto.email', 'a.arribas@vaypatel.com');
$dir      = v($d, 'contacto.direccion', '');
$horario  = v($d, 'contacto.horario', '');
$waNum    = solo_digitos(v($d, 'contacto.whatsapp', '34607230957'));
$waMsg    = v($d, 'contacto.whatsapp_mensaje', '');
$waLink   = 'https://wa.me/' . $waNum . ($waMsg !== '' ? '?text=' . rawurlencode($waMsg) : '');
$mailLink = 'mailto:' . $email . '?subject=' . rawurlencode('Consulta desde la web');
$mapLink  = 'https://maps.google.com/?q=' . rawurlencode('Calle Fundición 4 BIS nave 54, 28522 Rivas-Vaciamadrid');
$galeria  = lista($d, 'galeria');
$dest     = v($d, 'destacada', []);
$vista    = defined('VISTA_PREVIA');

/* Resultado del formulario cuando se envía sin JavaScript (enviar.php redirige aquí) */
$envio    = $_GET['envio'] ?? '';
$envioMsg = ['ok' => 'Recibido. Te respondemos en menos de 24 horas laborables.', 'error' => 'No se ha podido enviar. Llámanos o escríbenos por email o WhatsApp.', 'datos' => 'Faltan datos o el email no es válido. Revisa el formulario y vuelve a enviarlo.'][$envio] ?? '';

/* Proyectos: admite el formato actual (cliente, proyecto, tipo, lugar…) y el antiguo (titulo, etiqueta) */
$obras = array_map(function ($o) {
    if (empty($o['cliente']) && !empty($o['titulo'])) {
        $p = array_map('trim', explode('·', $o['titulo'], 2));
        $o['cliente'] = $p[0]; $o['proyecto'] = $p[1] ?? '';
        $o['tipo'] = $o['etiqueta'] ?? '';
    }
    return $o + ['cliente' => '', 'proyecto' => '', 'tipo' => '', 'lugar' => '', 'cifra' => '', 'cifra_txt' => '', 'texto' => '', 'icono' => ''];
}, lista($d, 'obras.lista'));
$OBRAS_DESTACADAS = 6; // las primeras se muestran como ficha; el resto, en lista

/* Fotos del bloque destacado (se abren en el mismo visor que la galería) */
$destK = [];
foreach (array_slice((array)($dest['fotos'] ?? []), 0, 2) as $archivo) {
    foreach ($galeria as $k => $g) if (($g['archivo'] ?? '') === $archivo) { $destK[] = $k; break; }
}

$servicios    = lista($d, 'servicios.lista');
$tituloPagina = 'Vaypatel | Instalación de telecomunicaciones y electricidad en Madrid';
$descripcion  = 'Empresa instaladora de telecomunicación nº 16317: cableado estructurado, fibra óptica, electricidad, seguridad y centros de datos. Madrid y toda España.';
$url          = 'https://www.vaypatel.com/';

/* CSS en línea: es una sola página, así el primer pintado no espera a ninguna hoja externa.
   Orden: tipografías · base del motor Scroll-craft (sin modificar) · estilos de la web. */
$css = str_replace('url(', 'url(assets/fuentes/', (string)@file_get_contents(__DIR__ . '/assets/fuentes/fuentes.css'))
     . (string)@file_get_contents(__DIR__ . '/assets/scrollcraft.css')
     . (string)@file_get_contents(__DIR__ . '/assets/estilos.css');

$heroFoto  = v($d, 'portada.foto', 'img/hero.jpg');
$heroMovil = v($d, 'portada.foto_movil', '');
/* Las llamadas de la lámina describen lo que se ve en la foto de portada original.
   Si se cambia la foto desde el panel, las llamadas no se muestran (dejarían de ser ciertas). */
$llamadas  = $heroFoto === 'img/hero.jpg' && in_array($heroMovil, ['', 'img/hero-movil.jpg'], true);
$ver = fn($f) => @filemtime(__DIR__ . '/' . $f);
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($tituloPagina) ?></title>
<meta name="description" content="<?= e($descripcion) ?>">
<link rel="canonical" href="<?= $url ?>">
<meta name="theme-color" content="#04070B">
<meta property="og:type" content="website">
<meta property="og:locale" content="es_ES">
<meta property="og:site_name" content="Vaypatel Proyectos">
<meta property="og:url" content="<?= $url ?>">
<meta property="og:title" content="Vaypatel Proyectos · Telecomunicaciones, electricidad y seguridad">
<meta property="og:description" content="<?= e($descripcion) ?>">
<meta property="og:image" content="<?= $url ?>img/compartir.jpg">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Logotipo de Vaypatel · Telecomunicaciones y electricidad">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Vaypatel Proyectos · Telecomunicaciones, electricidad y seguridad">
<meta name="twitter:description" content="<?= e($descripcion) ?>">
<meta name="twitter:image" content="<?= $url ?>img/compartir.jpg">
<link rel="icon" type="image/svg+xml" href="img/favicon.svg">
<link rel="icon" type="image/png" href="img/favicon.png">
<link rel="apple-touch-icon" href="img/apple-touch-icon.png">
<link rel="preload" href="assets/fuentes/chakra-petch-700.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fuentes/barlow-300.woff2" as="font" type="font/woff2" crossorigin>
<?php if ($heroMovil && ($w = webp_de($heroMovil))): ?><link rel="preload" as="image" href="<?= e($w) ?>" type="image/webp" media="(max-width: 760px)" fetchpriority="high">
<?php endif; ?>
<?php if ($w = webp_de($heroFoto)): ?><link rel="preload" as="image" href="<?= e($w) ?>" type="image/webp"<?= $heroMovil ? ' media="(min-width: 761px)"' : '' ?> fetchpriority="high">
<?php endif; ?>
<style><?= $css ?></style>
<script>
/* Antes del primer pintado: decide si se reproduce la firma del logo y prepara los actos.
   - firma: primera visita en 7 días, arriba del todo y sin ancla en la dirección.
   - firma-corta: visitas siguientes; solo la órbita del logo se traza en la cabecera.
   - rm: movimiento reducido; sin firma y sin tramos fijados.
   Si el motor no llega a cargar en 4 s, "sin-motor" muestra todo el contenido tal cual. */
(function (h) {
  h.classList.add('js');
  var rm = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches, visto = 0;
  try { visto = +localStorage.getItem('vp-firma') || 0; } catch (e) {}
  if (rm) h.classList.add('rm');
  else if (Date.now() - visto > 6048e5 && !location.hash && !location.search) h.classList.add('firma');
  else h.classList.add('firma-corta');
  setTimeout(function () { if (!h.classList.contains('sc-ready')) h.classList.add('sin-motor'); }, 4000);
})(document.documentElement);
</script>
<script type="application/ld+json"><?= json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    '@id' => $url . '#empresa',
    'name' => 'Vaypatel Proyectos',
    'legalName' => 'Vaypatel Proyectos S.L.',
    'url' => $url,
    'logo' => $url . 'img/logo.png',
    'image' => $url . 'img/compartir.jpg',
    'description' => 'Empresa instaladora de telecomunicaciones, electricidad y seguridad: cableado estructurado, fibra óptica, centros de datos, control de accesos y cableado BMS.',
    'telephone' => $telLink,
    'contactPoint' => $telOfLink ? [['@type' => 'ContactPoint', 'telephone' => $telOfLink, 'contactType' => 'oficina', 'areaServed' => 'ES', 'availableLanguage' => 'es']] : null,
    'email' => $email,
    'taxID' => 'B88306642',
    'foundingDate' => '2019-01-31',
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Calle Fundición 4 BIS, nave 54', 'postalCode' => '28522', 'addressLocality' => 'Rivas-Vaciamadrid', 'addressRegion' => 'Madrid', 'addressCountry' => 'ES'],
    'areaServed' => [['@type' => 'AdministrativeArea', 'name' => 'Comunidad de Madrid'], ['@type' => 'Country', 'name' => 'España']],
    'knowsAbout' => array_values(array_filter(array_map(fn($s) => $s['titulo'] ?? '', $servicios))),
    'hasOfferCatalog' => ['@type' => 'OfferCatalog', 'name' => 'Servicios de instalación', 'itemListElement' => array_map(fn($s) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $s['titulo'] ?? '', 'description' => $s['texto'] ?? '']], $servicios)],
], fn($x) => $x !== null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body>
<a class="skip" href="#contenido">Saltar al contenido</a>
<?php include __DIR__ . '/incluir/iconos.php'; ?>
<div class="sc-grain" aria-hidden="true"></div>
<div class="firma-velo" aria-hidden="true"></div>

<header id="nav" class="nav" data-sc-shot-fixed>
  <div class="navbar">
    <a class="brand" href="#top" aria-label="Vaypatel Proyectos, inicio"><?php include __DIR__ . '/incluir/logo.php'; ?></a>
    <nav class="navlinks" aria-label="Principal">
      <a href="#servicios">Servicios</a>
      <a href="#proyectos">Proyectos</a>
      <a href="#empresa">Empresa</a>
      <a href="#contacto">Contacto</a>
    </nav>
    <div class="navend">
      <a class="navcall" href="tel:<?= e($telLink) ?>" aria-label="Llamar al <?= e($tel) ?>"><svg class="i" aria-hidden="true"><use href="#i-phone"/></svg><span class="t"><?= e($telTxt) ?></span></a>
      <a class="btn btn-main btn-sm navcta" href="#contacto">Pedir presupuesto</a>
      <button class="burger" id="burger" type="button" aria-expanded="false" aria-controls="menu" aria-label="Abrir menú"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
<script>
/* Firma del logo: el logo de la cabecera empieza en el centro de la pantalla y aterriza en su sitio.
   Aquí solo se mide dónde está su sitio; la animación es CSS (estilos.css, "firma"). Cualquier tecla,
   rueda o toque la termina al instante. */
(function (h) {
  if (!h.classList.contains('firma')) return;
  var lg = document.querySelector('.brand .lg'), r = lg && lg.getBoundingClientRect();
  var base = lg && parseFloat(getComputedStyle(lg).width);
  if (!r || !r.width || !base) { h.classList.remove('firma'); return; }
  /* la cabecera escala el logo desde su contenedor: el desplazamiento se expresa en su escala */
  var sp = r.width / base, w = Math.min(innerWidth * (innerWidth < 700 ? 0.84 : 0.56), 680);
  h.style.setProperty('--fsl', (w / r.width).toFixed(3));
  h.style.setProperty('--fxl', ((innerWidth / 2 - (r.left + r.width / 2)) / sp).toFixed(1) + 'px');
  h.style.setProperty('--fyl', ((innerHeight * 0.46 - (r.top + r.height / 2)) / sp).toFixed(1) + 'px');
  function fin() {
    if (h.classList.contains('firma-fin')) return;
    h.classList.add('firma-fin');
    ['keydown', 'wheel', 'touchstart', 'pointerdown'].forEach(function (t) { removeEventListener(t, fin, true); });
  }
  ['keydown', 'wheel', 'touchstart', 'pointerdown'].forEach(function (t) { addEventListener(t, fin, { capture: true, passive: true }); });
  setTimeout(fin, 2700); // después de la última animación de la portada (2,54 s)
  try { localStorage.setItem('vp-firma', String(Date.now())); } catch (e) {}
})(document.documentElement);
</script>

<div id="menu" class="menu" role="dialog" aria-modal="true" aria-label="Menú" hidden>
  <nav aria-label="Secciones">
    <a href="#servicios">Servicios</a>
    <a href="#acreditaciones">Acreditaciones</a>
    <a href="#proyectos">Proyectos</a>
    <a href="#instalaciones">Trabajo terminado</a>
    <a href="#empresa">Empresa y medios</a>
    <a href="#contacto">Contacto</a>
  </nav>
  <a class="btn btn-main" href="#contacto">Pedir presupuesto</a>
  <div class="mfoot">
    <a class="mcall" href="tel:<?= e($telLink) ?>"><svg class="i" aria-hidden="true"><use href="#i-phone"/></svg><?= e($telTxt) ?></a>
    <?php if ($telOf): ?><a class="mcall" href="tel:<?= e($telOfLink) ?>"><svg class="i" aria-hidden="true"><use href="#i-building"/></svg><?= e($telOfTxt) ?></a><?php endif; ?>
    <a class="mcall" href="<?= e($mailLink) ?>"><svg class="i" aria-hidden="true"><use href="#i-mail"/></svg>Email</a>
    <a class="mcall mwa" href="<?= e($waLink) ?>" target="_blank" rel="noopener"><svg class="i" aria-hidden="true"><use href="#i-wa"/></svg>WhatsApp</a>
  </div>
</div>

<main id="contenido">

<!-- 1 · PORTADA. Alzado por planos: retícula de fondo, lámina con la foto real, órbita del logo
     que la rodea por detrás y por delante, y bandeja de cable en primer plano. -->
<section id="top" class="hero" data-sc-act="pin" data-sc-span="1.5" data-movil="flow" data-movil-hasta="1023" aria-labelledby="h1">
  <div class="hero-stage" data-sc-stage>
    <div class="pl pl-far" data-sc-parallax="-0.4" aria-hidden="true">
      <svg class="riser" viewBox="0 0 600 800" focusable="false">
        <path d="M60 760V80h480v680M60 620h480M60 480h480M60 340h480M60 200h480"/>
        <path class="rz" d="M292 760V60M308 760V60"/>
        <path d="M292 690H120M308 690h300M292 550H140M308 550h260M292 410H110M308 410h300M292 270H150M308 270h250"/>
        <path d="M120 684v12M140 544v12M110 404v12M150 264v12M560 404v12M568 544v12"/>
        <rect x="250" y="700" width="100" height="56"/><path d="M250 714h100M250 728h100M250 742h100"/>
        <path class="dim" d="M572 80v680M566 80h12M566 760h12"/>
      </svg>
    </div>
    <div class="pl pl-glow" aria-hidden="true"></div>

    <div class="pl pl-obj" data-sc-parallax="-0.6" aria-hidden="true">
      <div class="obj">
        <svg class="orb orb-back" viewBox="0 0 1000 600" focusable="false"><g transform="rotate(-12 500 300)">
          <path class="orb-hair" d="M30 300A470 250 0 0 1 970 300"/>
        </g></svg>
        <div class="plate">
          <picture>
            <?php if ($heroMovil): ?>
            <?php if ($w = webp_de($heroMovil)): ?><source media="(max-width: 760px)" type="image/webp" srcset="<?= e($w) ?>"><?php endif; ?>
            <source media="(max-width: 760px)" srcset="<?= e($heroMovil) ?>">
            <?php endif; ?>
            <?php if ($w = webp_de($heroFoto)): ?><source type="image/webp" srcset="<?= e($w) ?>"><?php endif; ?>
            <img class="plate-img" src="<?= e($heroFoto) ?>" alt="" fetchpriority="high" decoding="async">
          </picture>
          <span class="reg reg-tl"></span><span class="reg reg-tr"></span><span class="reg reg-bl"></span><span class="reg reg-br"></span>
          <?php if ($llamadas): ?>
          <span class="co co-t co-1"><i></i><b>Mazo recogido con velcro</b></span>
          <span class="co co-t co-2"><i></i><b>Puertos etiquetados</b></span>
          <?php endif; ?>
        </div>
        <svg class="orb orb-front" viewBox="0 0 1000 600" focusable="false"><g transform="rotate(-12 500 300)">
          <path class="orb-cres" d="M30 300A470 262 0 0 0 970 300A470 240 0 0 1 30 300Z"/>
          <path class="orb-cian" pathLength="1" d="M52 306A452 232 0 0 0 520 534"/>
          <path class="orb-track" d="M30 300A470 251 0 0 0 970 300"/>
          <circle class="orb-dot" cx="224" cy="503" r="13"/>
        </g></svg>
      </div>
    </div>

    <div class="pl pl-near" data-sc-parallax="1.6" aria-hidden="true">
      <svg class="tray" viewBox="0 0 900 320" focusable="false">
        <path class="tr-rail" d="M0 250 900 70M0 316 900 136"/>
        <path class="tr-wire" d="M20 246v66M80 234v66M140 222v66M200 210v66M260 198v66M320 186v66M380 174v66M440 162v66M500 150v66M560 138v66M620 126v66M680 114v66M740 102v66M800 90v66M860 78v66"/>
        <path class="tr-cable" d="M0 280C220 236 420 196 900 104M0 288C240 244 470 200 900 112M0 296C250 252 500 206 900 120M0 272C230 228 440 190 900 96"/>
      </svg>
    </div>

    <div class="hero-copy">
      <p class="eyebrow mono"><svg class="i" aria-hidden="true"><use href="#i-gov"/></svg><span><?= e(v($d, 'portada.aviso')) ?></span></p>
      <h1 id="h1"><?= e(v($d, 'portada.titular')) ?> <em><?= e(v($d, 'portada.destacado')) ?></em></h1>
      <p class="lede"><?= e(v($d, 'portada.subtitulo')) ?></p>
      <div class="actions">
        <a class="btn btn-main" href="#contacto">Pedir presupuesto <svg class="i" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        <a class="btn btn-ghost" href="#proyectos">Ver proyectos</a>
      </div>
    </div>
  </div>
</section>

<!-- 2 · CREDENCIALES. Sellos, cifras reales acotadas como en un plano y empresas. -->
<section id="cifras" class="cred" aria-label="Experiencia en cifras y empresas">
  <div class="wrap">
    <ul class="sellos" data-sc-in data-sc-stagger="50">
      <?php $ics = ['i-award', 'i-warranty', 'i-wave']; foreach (lista($d, 'portada.sellos') as $k => $s): ?>
      <li><svg class="i" aria-hidden="true"><use href="#<?= $ics[$k % 3] ?>"/></svg><?= e($s) ?></li>
      <?php endforeach; ?>
      <li><svg class="i" aria-hidden="true"><use href="#i-route"/></svg>Madrid y toda España</li>
    </ul>
    <dl class="cifras" data-sc-in data-sc-stagger="80">
      <?php foreach (lista($d, 'cifras') as $k => $f): ?>
      <div class="cifra">
        <svg class="cota" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path pathLength="1" d="M1 1v10M199 1v10M1 6h198"/></svg>
        <dt><?= e($f['texto'] ?? '') ?></dt>
        <dd><?php if (!empty($f['prefijo'])): ?><i><?= e($f['prefijo']) ?></i><?php endif; ?><?= numero_es((int)($f['numero'] ?? 0)) ?><?php if (!empty($f['sufijo'])): ?><i><?= e($f['sufijo']) ?></i><?php endif; ?></dd>
      </div>
      <?php endforeach; ?>
    </dl>
    <?php $cl = lista($d, 'clientes.lista'); if ($cl): ?>
    <div class="clientes" data-sc-act="flow">
      <div class="clientes-h">
        <h2><?= e(v($d, 'clientes.titulo')) ?></h2>
        <p><?= e(v($d, 'clientes.intro')) ?></p>
      </div>
      <ul class="clientes-l" data-sc-reveal="left" data-sc-reveal-at="0.16 0.46"><?php foreach ($cl as $n): ?><li><?= e($n) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <?php $dis = lista($d, 'disciplinas'); if ($dis): ?>
    <ul class="disc" aria-label="Disciplinas"><?php foreach ($dis as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </div>
</section>

<!-- 3 · SERVICIOS. Cada servicio con su esquema de principio, que se traza al entrar. -->
<section id="servicios" class="svcs" aria-labelledby="h-servicios">
  <div class="wrap svcs-grid">
    <header class="svcs-head">
      <h2 id="h-servicios">Qué instalamos</h2>
      <p class="sub"><?= e(v($d, 'servicios.intro')) ?></p>
    </header>
    <ul class="svcs-list">
      <?php foreach ($servicios as $k => $s): ?>
      <li class="svc" data-sc-in>
        <div class="svc-esq"><?= esquema((string)($s['icono'] ?? '')) ?></div>
        <div class="svc-tx">
          <h3><?= e($s['titulo'] ?? '') ?></h3>
          <p><?= e($s['texto'] ?? '') ?></p>
          <?php if (!empty($s['entornos'])): ?><p class="svc-env"><span class="mono">Habitual en</span><?= e($s['entornos']) ?></p><?php endif; ?>
          <?php $et = array_filter((array)($s['etiquetas'] ?? [])); if ($et): ?><ul class="chips"><?php foreach ($et as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul><?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 4 · PAUSA. Silencio autorado antes del momento central: una frase y nada más. -->
<section class="pausa" data-sc-act="flow" aria-label="Del encargo a la entrega">
  <div class="wrap">
    <p class="pausa-tx" data-sc-cue="0.22 0.86 0.32 0.24" data-sc-kinetic="lines">Seis sistemas distintos.<br> Un mismo equipo propio, de principio a fin.</p>
  </div>
</section>

<!-- 5 · EL PEINADO. Momento central: seis cables enredados se peinan, se embridan y entran cada uno
     en su equipo, que se enciende. Esquema ilustrativo: no representa una obra concreta. -->
<section id="orden" class="pico" data-sc-act="pin" data-sc-span="3" data-span-m="2.8" aria-labelledby="h-pico">
  <div class="pico-stage" data-sc-stage>
    <div class="pico-copy">
      <h2 id="h-pico">Del enredo al rack terminado</h2>
      <div class="pico-ls">
        <p class="pico-l" data-sc-cue="0 0.36 0">Telecomunicaciones, fibra, electricidad, seguridad, centro de datos y clima: seis sistemas que suelen llegar por separado a la misma sala técnica.</p>
        <p class="pico-l" data-sc-cue="0.32 0.66">Los ejecuta personal propio, coordinado por un responsable de obra que trata con la constructora y el resto de gremios.</p>
        <p class="pico-l" data-sc-cue="0.62 1">Y se entregan como un rack terminado: cableado peinado, etiquetado y cada enlace medido.</p>
      </div>
    </div>
    <figure class="pico-fig">
      <?= peine_svg(false) ?>
      <?= peine_svg(true) ?>
      <figcaption class="mono">Esquema ilustrativo · no representa una obra concreta</figcaption>
    </figure>
  </div>
</section>

<!-- 6 · MÉTODO. Corte a hoja clara: la parte "documento". Medición y acreditaciones con sus números. -->
<section id="metodo" class="metodo" data-sc-act="flow" aria-labelledby="h-metodo">
  <div class="hoja" data-sc-reveal="left" data-sc-reveal-at="0 0.2">
    <div class="wrap metodo-grid">
      <div class="metodo-tx" data-sc-in data-sc-stagger="70">
        <h2 id="h-metodo">Todo se entrega medido y documentado</h2>
        <p><?= e(v($d, 'servicios.certificacion')) ?></p>
        <figure class="fluke"><?= foto('img/fluke.jpg', 'Equipo de certificación Fluke Networks DSX-5000 con sus accesorios') ?><figcaption class="mono">Certificador Fluke DSX-5000 propio</figcaption></figure>
      </div>
      <div id="acreditaciones" class="acred">
        <h3 class="acred-h">Acreditaciones y medios</h3>
        <p class="acred-sub">Registro oficial, certificaciones de fabricante y equipo de medida propio. Cada ficha indica de qué se trata.</p>
        <ul class="registro">
          <?php foreach (lista($d, 'acreditaciones') as $k => $a): ?>
          <li class="reg-row" data-sc-in>
            <?php if (!empty($a['tipo'])): ?><p class="reg-t mono"><?= e($a['tipo']) ?></p><?php endif; ?>
            <div class="reg-b"><svg class="i" aria-hidden="true"><use href="#<?= icono_valido($a['icono'] ?? '') ?>"/></svg><div>
              <h4><?= e($a['titulo'] ?? '') ?></h4><p><?= e($a['texto'] ?? '') ?></p>
            </div></div>
            <?php if (!empty($a['ref'])): ?><p class="reg-ref mono"><?= e($a['ref']) ?></p><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- 7 · PROYECTOS. Las obras recientes recorren la pantalla como fichas de catálogo (mismo esquema
     para todas, sin fotos); después, el listado completo y las obras de referencia. -->
<section id="proyectos" class="obras" aria-labelledby="h-proyectos">
  <div class="obras-pan" data-sc-act="pan" data-sc-span="2.4">
    <div class="obras-stage" data-sc-stage>
      <div class="rail" data-sc-pan="0.02">
        <div class="rail-lead">
          <h2 id="h-proyectos"><?= e(v($d, 'obras.titulo')) ?></h2>
          <p><?= e(v($d, 'obras.intro')) ?></p>
        </div>
        <?php foreach (array_slice($obras, 0, $OBRAS_DESTACADAS) as $k => $o): ?>
        <article class="ficha" style="--i:<?= $k + 1 ?>">
          <p class="f-tipo mono"><span><?= e($o['tipo']) ?></span><svg class="i" aria-hidden="true"><use href="#<?= icono_valido($o['icono']) ?>"/></svg></p>
          <h3><?= e($o['cliente']) ?><?php if ($o['proyecto'] !== ''): ?> <span><?= e($o['proyecto']) ?></span><?php endif; ?></h3>
          <?php if ($o['cifra'] !== ''): ?><p class="f-cifra"><b><?= e($o['cifra']) ?></b> <?= e($o['cifra_txt']) ?></p><?php endif; ?>
          <p class="f-tx"><?= e($o['texto']) ?></p>
          <?php if ($o['lugar'] !== ''): ?><p class="f-lugar mono"><svg class="i" aria-hidden="true"><use href="#i-pin"/></svg><?= e($o['lugar']) ?></p><?php endif; ?>
        </article>
        <?php endforeach; ?>
        <p class="rail-end mono">Más proyectos y obras de referencia, a continuación</p>
      </div>
    </div>
  </div>

  <div class="wrap obras-mas">
    <?php $resto = array_slice($obras, $OBRAS_DESTACADAS); if ($resto): ?>
    <h3 class="listhead">Más proyectos de 2025 y 2026</h3>
    <ul class="caselist">
      <?php foreach ($resto as $o): ?>
      <li data-sc-in>
        <p class="cl-h"><b><?= e($o['cliente']) ?></b><?php if ($o['proyecto'] !== ''): ?> · <?= e($o['proyecto']) ?><?php endif; ?></p>
        <p class="cl-m mono"><?= e($o['tipo']) ?><?php if ($o['lugar'] !== ''): ?> · <?= e($o['lugar']) ?><?php endif; ?><?php if ($o['cifra'] !== ''): ?> · <?= e($o['cifra'] . ' ' . $o['cifra_txt']) ?><?php endif; ?></p>
        <p class="cl-t"><?= e($o['texto']) ?></p>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php $refs = lista($d, 'obras.referencias'); if ($refs): ?>
    <div class="refs" data-sc-in>
      <h3>Obras de referencia anteriores</h3>
      <p>Despliegues de gran volumen ejecutados antes de 2025.</p>
      <ul><?php foreach ($refs as $r): ?><li><b><?= e($r['cliente'] ?? '') ?></b><span><?= e($r['texto'] ?? '') ?></span></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <div class="cta-band" data-sc-in>
      <div>
        <h2 id="h-cta">¿Tienes un proyecto en marcha? Hablemos.</h2>
        <p>Cuéntanos ubicación, alcance y plazos y te enviamos una propuesta.</p>
      </div>
      <div class="cta-actions">
        <a class="btn btn-main" href="#contacto">Pedir presupuesto <svg class="i" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        <a class="btn btn-ghost" href="tel:<?= e($telLink) ?>"><svg class="i" aria-hidden="true"><use href="#i-phone"/></svg><?= e($telTxt) ?></a>
      </div>
    </div>
  </div>
</section>

<?php if ($galeria): ?>
<!-- 8 · TRABAJO TERMINADO. Fotos reales, técnicas y genéricas, enmarcadas como documentación. -->
<section id="instalaciones" class="inst" aria-labelledby="h-inst">
  <div class="wrap">
    <h2 id="h-inst">Trabajo terminado</h2>
    <?php if ($destK): ?>
    <div class="showcase">
      <div class="sc-tx" data-sc-in data-sc-stagger="60">
        <h3><?= e($dest['titulo'] ?? '') ?></h3>
        <p><?= e($dest['texto'] ?? '') ?></p>
        <?php $dp = array_filter((array)($dest['puntos'] ?? []), 'is_string'); if ($dp): ?><ul class="ticks"><?php foreach ($dp as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul><?php endif; ?>
      </div>
      <?php foreach ($destK as $n => $k): $g = $galeria[$k]; ?>
      <figure class="sc-ph sc-ph-<?= $n ?>" data-sc-in>
        <button type="button" class="ph" data-k="<?= (int)$k ?>" aria-label="Ampliar foto: <?= e($g['pie'] ?? '') ?>"><?= foto($g['archivo'], $g['pie'] ?? '', ['sizes' => '(min-width: 900px) 360px, 45vw']) ?></button>
        <figcaption><?= e($g['pie'] ?? '') ?></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
      /* Cuadrícula: todas las fotos menos las del bloque destacado. Se ven las primeras
         y el resto con "Ver todas"; los filtros por tipo muestran todas las de ese tipo. */
      $resto = array_filter($galeria, fn($g, $k) => !in_array($k, $destK, true) && !empty($g['archivo']), ARRAY_FILTER_USE_BOTH);
      $VISIBLES = 6;
      $cuenta = array_count_values(array_map(fn($g) => $g['categoria'] ?? '', $resto));
    ?>
    <?php if (count($resto) > $VISIBLES && count($cuenta) > 1): ?>
    <div class="filters" role="group" aria-label="Filtrar fotos por tipo">
      <button type="button" data-f="todas" aria-pressed="true">Todas <b><?= count($resto) ?></b></button>
      <?php foreach (CATEGORIAS as $id => $nom): if (empty($cuenta[$id])) continue; ?>
      <button type="button" data-f="<?= e($id) ?>" aria-pressed="false"><?= e($nom) ?> <b><?= (int)$cuenta[$id] ?></b></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <ul class="gal" id="gal">
      <?php $i = 0; foreach ($resto as $k => $g): ?>
      <li data-c="<?= e($g['categoria'] ?? '') ?>"<?= $i++ >= $VISIBLES ? ' data-extra hidden' : '' ?>><figure>
        <button type="button" class="ph" data-k="<?= $k ?>" aria-label="Ampliar foto: <?= e($g['pie'] ?? '') ?>"><?= foto($g['archivo'], $g['pie'] ?? '') ?></button>
        <figcaption><?= e($g['pie'] ?? '') ?></figcaption>
      </figure></li>
      <?php endforeach; ?>
    </ul>
    <?php if (count($resto) > $VISIBLES): ?>
    <div class="galmore"><button type="button" class="btn btn-ghost" id="galMore" aria-controls="gal" aria-expanded="false">Ver todas las fotos (<?= count($resto) ?>)</button></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- 9 · EMPRESA Y MEDIOS. Segunda hoja clara: quién lo hace y con qué. -->
<div class="hoja hoja-empresa">
<section id="empresa" class="empresa" data-sc-act="flow" aria-labelledby="h-empresa">
  <div class="wrap about">
    <div class="about-tx">
      <h2 id="h-empresa"><?= e(v($d, 'empresa.titulo')) ?></h2>
      <div data-sc-in data-sc-stagger="70"><?php foreach (lista($d, 'empresa.parrafos') as $p): ?><p><?= e($p) ?></p><?php endforeach; ?></div>
      <dl class="facts" data-sc-in data-sc-stagger="60">
        <?php foreach (lista($d, 'empresa.puntos') as $k => $p): ?>
        <div><dt><?= e($p['titulo'] ?? '') ?></dt><dd><?= e($p['texto'] ?? '') ?></dd></div>
        <?php endforeach; ?>
      </dl>
    </div>
    <div class="photo"><div class="photo-in" data-sc-parallax="-0.7"><?= foto(v($d, 'empresa.foto', 'img/empresa.jpg'), 'Rack de comunicaciones con paneles de parcheo y fibra etiquetada') ?></div></div>
  </div>
</section>

<?php $flota = v($d, 'flota', []); if ($flota): ?>
<section id="flota" class="flota" aria-labelledby="h-flota">
  <div class="wrap">
    <h2 id="h-flota"><?= e(v($d, 'flota.titulo', 'Medios propios')) ?></h2>
    <p class="sub"><?= e(v($d, 'flota.intro')) ?></p>
    <div class="fleet">
      <?php
        /* Fotos de la flota (se suben desde el panel). Sin fotos, se muestra la ilustración. */
        $flFotos = array_values(array_filter(lista($d, 'flota.fotos'), fn($f) => !empty($f['archivo'])));
        if (!$flFotos && v($d, 'flota.foto')) $flFotos = [['archivo' => v($d, 'flota.foto'), 'pie' => 'Vehículos de Vaypatel Proyectos']];
      ?>
      <?php if ($flFotos): ?>
      <div class="fl-photos">
        <?php foreach ($flFotos as $f): ?>
        <figure data-sc-in><?= foto($f['archivo'], $f['pie'] ?? '', ['sizes' => '(min-width: 980px) 560px, 100vw']) ?><figcaption><?= e($f['pie'] ?? '') ?></figcaption></figure>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="fl-visual">
        <svg class="van" viewBox="0 0 640 340" aria-hidden="true" focusable="false">
          <g class="dim"><path d="M70 46v18M580 46v18M70 55h510"/><path d="m78 51-8 4 8 4M572 51l8 4-8 4"/></g>
          <path class="ground" d="M20 283h600"/>
          <path class="body" d="M70 250V104q0-14 14-14h386q14 0 22 12l48 66 28 8q12 4 12 16v46q0 12-12 12h-48a38 38 0 0 0-76 0H222a38 38 0 0 0-76 0Z"/>
          <path class="cab" d="M434 104h44l42 58h-86Z"/>
          <path class="seam" d="M428 92v156M330 96v150M70 214h510M444 182h22M314 176h-22"/>
          <rect class="lamp" x="566" y="188" width="10" height="9" rx="2"/>
          <path class="orbit-l" d="M108 176c58-60 190-86 262-62"/><circle class="orbit-d" cx="368" cy="113" r="7"/>
          <g class="wheel"><circle cx="184" cy="252" r="30"/><circle cx="184" cy="252" r="11"/></g>
          <g class="wheel"><circle cx="482" cy="252" r="30"/><circle cx="482" cy="252" r="11"/></g>
        </svg>
      </div>
      <?php endif; ?>
      <ul class="fl-points" data-sc-in data-sc-stagger="60">
        <?php foreach (lista($d, 'flota.puntos') as $k => $f): ?>
        <li><svg class="i" aria-hidden="true"><use href="#<?= icono_valido($f['icono'] ?? '') ?>"/></svg><div>
          <h3><?= e($f['titulo'] ?? '') ?></h3><p><?= e($f['texto'] ?? '') ?></p></div></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php endif; ?>
</div>

<!-- 10 · CONTACTO. El cierre es la hoja de solicitud: un formulario real, con todos los canales. -->
<section id="contacto" class="contacto" aria-labelledby="h-contacto">
  <div class="wrap">
    <div class="ct-head">
      <h2 id="h-contacto">Pide presupuesto</h2>
      <p class="sub">Escríbenos con los datos de la instalación y te enviamos una propuesta y un plazo, sin compromiso.</p>
    </div>
    <div class="contact">
      <ul class="cinfo">
        <li><a href="tel:<?= e($telLink) ?>"><svg class="i" aria-hidden="true"><use href="#i-phone"/></svg><span><small>Móvil</small><?= e($telTxt) ?></span></a></li>
        <?php if ($telOf): ?><li><a href="tel:<?= e($telOfLink) ?>"><svg class="i" aria-hidden="true"><use href="#i-building"/></svg><span><small>Oficina</small><?= e($telOfTxt) ?></span></a></li><?php endif; ?>
        <li><a href="<?= e($mailLink) ?>"><svg class="i" aria-hidden="true"><use href="#i-mail"/></svg><span><small>Email</small><?= e($email) ?></span></a></li>
        <li><a class="ci-wa" href="<?= e($waLink) ?>" target="_blank" rel="noopener"><svg class="i" aria-hidden="true"><use href="#i-wa"/></svg><span><small>WhatsApp</small>Escríbenos por WhatsApp</span><svg class="i go" aria-hidden="true"><use href="#i-arrow"/></svg></a></li>
        <li><a href="<?= e($mapLink) ?>" target="_blank" rel="noopener"><svg class="i" aria-hidden="true"><use href="#i-pin"/></svg><span><small>Nave</small><?= e($dir) ?></span></a></li>
        <?php if ($horario): ?><li><div><svg class="i" aria-hidden="true"><use href="#i-clock"/></svg><span><small>Horario de atención</small><?= e($horario) ?></span></div></li><?php endif; ?>
      </ul>
      <form class="quote" id="form" action="enviar.php" method="POST" novalidate>
        <div class="q-cajetin mono" aria-hidden="true"><span>Solicitud de presupuesto</span><span>Respuesta en menos de 24 h laborables</span></div>
        <p class="hp" aria-hidden="true"><label>No rellenar <input type="text" name="web_url" tabindex="-1" autocomplete="off"></label></p>
        <input type="hidden" name="t" value="<?= time() ?>">
        <div class="frow two">
          <div><label class="fl" for="nombre">Nombre y empresa <span aria-hidden="true">*</span></label><input id="nombre" name="nombre" type="text" autocomplete="name" required></div>
          <div><label class="fl" for="email">Email <span aria-hidden="true">*</span></label><input id="email" name="email" type="email" autocomplete="email" required></div>
        </div>
        <div class="frow two">
          <div><label class="fl" for="tel">Teléfono</label><input id="tel" name="tel" type="tel" autocomplete="tel"></div>
          <div><label class="fl" for="tipo">Tipo de trabajo</label>
            <select id="tipo" name="tipo">
              <option>Cableado estructurado</option><option>Fibra óptica</option><option>Electricidad</option>
              <option>Seguridad y control de accesos</option><option>Centro de datos</option><option>Control de clima (BMS)</option>
              <option>Certificación o mantenimiento</option><option>Instalación integral</option><option>Otro</option>
            </select></div>
        </div>
        <div class="frow"><div><label class="fl" for="msg">Descripción de la obra <span aria-hidden="true">*</span></label><textarea id="msg" name="msg" rows="5" placeholder="Ubicación, alcance aproximado, plazos y si hay proyecto redactado" required></textarea></div></div>
        <label class="consent"><input type="checkbox" id="consentimiento" name="consentimiento" required><span>He leído y acepto la <a href="privacidad.html" target="_blank" rel="noopener">política de privacidad</a> y el tratamiento de mis datos para responder a esta consulta.</span></label>
        <button class="btn btn-main btn-block" type="submit"><svg class="i" aria-hidden="true"><use href="#i-send"/></svg> Enviar consulta</button>
        <p class="formnote<?= $envioMsg ? ' on ' . ($envio === 'ok' ? 'ok' : 'err') : '' ?>" id="note" role="status" aria-live="polite"><?= e($envioMsg ?: 'Respondemos en menos de 24 horas laborables. * Campos obligatorios.') ?></p>
      </form>
    </div>
  </div>
</section>

</main>

<footer class="foot">
  <div class="wrap">
    <div class="fgrid">
      <div>
        <img class="flogo" src="img/logo.svg" alt="Vaypatel · Telecomunicaciones y electricidad" width="247" height="110" loading="lazy">
        <p>Instalaciones de telecomunicaciones, electricidad y seguridad. Madrid y toda España.</p>
      </div>
      <nav class="flinks" aria-label="Secciones de la página">
        <a href="#servicios">Servicios</a><a href="#acreditaciones">Acreditaciones</a><a href="#proyectos">Proyectos</a>
        <a href="#instalaciones">Instalaciones</a><a href="#empresa">Empresa</a><a href="#flota">Medios</a><a href="#contacto">Contacto</a>
      </nav>
      <address>
        <a href="tel:<?= e($telLink) ?>"><?= e($telTxt) ?></a> · móvil<br>
        <?php if ($telOf): ?><a href="tel:<?= e($telOfLink) ?>"><?= e($telOfTxt) ?></a> · oficina<br><?php endif; ?>
        <a href="<?= e($mailLink) ?>"><?= e($email) ?></a><br>
        <a href="<?= e($waLink) ?>" target="_blank" rel="noopener">WhatsApp</a><br>
        <?= e($dir) ?><?php if ($horario): ?><br><?= e($horario) ?><?php endif; ?>
      </address>
    </div>
    <div class="legal">
      <span>© <?= date('Y') ?> Vaypatel Proyectos S.L. · CIF B88306642</span>
      <span><a href="aviso-legal.html">Aviso legal</a> · <a href="privacidad.html">Privacidad</a> · Empresa instaladora de telecomunicación nº 16317 · Tipo B</span>
    </div>
  </div>
</footer>

<a class="wa" id="wa" data-sc-shot-fixed href="<?= e($waLink) ?>" target="_blank" rel="noopener" aria-label="Escribir por WhatsApp (se abre en una pestaña nueva)">
  <span class="wa-ic" aria-hidden="true"><svg class="i"><use href="#i-wa"/></svg></span>
  <span class="wa-tx" aria-hidden="true">WhatsApp</span>
</a>

<div class="lightbox" id="lb" role="dialog" aria-modal="true" aria-label="Foto ampliada" hidden>
  <figure>
    <figcaption id="lbCap"></figcaption>
  </figure>
  <button type="button" class="lb-btn lb-prev" id="lbPrev" aria-label="Foto anterior"><svg class="i" aria-hidden="true"><use href="#i-arrow"/></svg></button>
  <button type="button" class="lb-btn lb-next" id="lbNext" aria-label="Foto siguiente"><svg class="i" aria-hidden="true"><use href="#i-arrow"/></svg></button>
  <button type="button" class="lb-btn lb-close" id="lbClose" aria-label="Cerrar">✕</button>
</div>

<script>window.VP = { galeria: <?= json_encode(array_map(fn($g) => ['src' => webp_de($g['archivo'] ?? '') ?? ($g['archivo'] ?? ''), 'pie' => $g['pie'] ?? ''], $galeria), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>, email: <?= json_encode($email, JSON_HEX_TAG) ?>, vistaPrevia: <?= $vista ? 'true' : 'false' ?> };</script>
<script src="assets/scrollcraft.js?v=<?= $ver('assets/scrollcraft.js') ?>" defer></script>
<script src="assets/web.js?v=<?= $ver('assets/web.js') ?>" defer></script>
</body>
</html>
