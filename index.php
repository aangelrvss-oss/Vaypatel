<?php
/*
 * Vaypatel Proyectos — página principal.
 * Todo el texto sale de contenido.json, que se edita desde /admin/.
 * No hace falta tocar este archivo para cambiar contenidos.
 */
require __DIR__ . '/incluir/comun.php';
$d = cargar_contenido();

$c        = v($d, 'contacto', []);
$tel      = v($d, 'contacto.telefono', '607 23 09 57');
$telLink  = '+34' . preg_replace('/^34/', '', solo_digitos($tel));
$email    = v($d, 'contacto.email', 'a.arribas@vaypatel.com');
$web      = v($d, 'contacto.web', 'www.vaypatel.com');
$dir      = v($d, 'contacto.direccion', '');
$waNum    = solo_digitos(v($d, 'contacto.whatsapp', '34607230957'));
$waMsg    = v($d, 'contacto.whatsapp_mensaje', '');
$waLink   = 'https://wa.me/' . $waNum . ($waMsg !== '' ? '?text=' . rawurlencode($waMsg) : '');
$mailLink = 'mailto:' . $email . '?subject=' . rawurlencode('Consulta desde la web');
$flota    = v($d, 'flota', []);
$dest     = v($d, 'destacada', []);
/* Resultado del formulario cuando se envía sin JavaScript (enviar.php redirige aquí) */
$envio    = $_GET['envio'] ?? '';
$envioMsg = ['ok' => 'Recibido. Te respondemos en menos de 24 horas laborables.', 'error' => 'No se ha podido enviar. Llámanos o escríbenos por email o WhatsApp.', 'datos' => 'Faltan datos o el email no es válido. Revisa el formulario y vuelve a enviarlo.'][$envio] ?? '';
$galeria  = lista($d, 'galeria');
$VISIBLES = 12; // fotos que se ven antes de pulsar "Ver todas"
$vista    = defined('VISTA_PREVIA');

$titularHtml = e(v($d, 'portada.titular'));
$destacadoHtml = e(v($d, 'portada.destacado'));
$tituloPagina = 'Vaypatel Proyectos · Instalación de telecomunicaciones, electricidad y seguridad en Madrid';
$descripcion = 'Cableado estructurado, fibra óptica, electricidad, centros de datos y seguridad. Empresa instaladora de telecomunicación nº 16317, tipo B. Madrid y toda España.';
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($tituloPagina) ?></title>
<meta name="description" content="<?= e($descripcion) ?>">
<link rel="canonical" href="https://www.vaypatel.com/">
<meta name="theme-color" content="#04070B">
<meta property="og:type" content="website">
<meta property="og:locale" content="es_ES">
<meta property="og:site_name" content="Vaypatel Proyectos">
<meta property="og:url" content="https://www.vaypatel.com/">
<meta property="og:title" content="Vaypatel Proyectos · Telecomunicaciones, electricidad y seguridad">
<meta property="og:description" content="<?= e($descripcion) ?>">
<meta property="og:image" content="https://www.vaypatel.com/img/compartir.jpg">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/svg+xml" href="img/favicon.svg">
<link rel="icon" type="image/png" href="img/favicon.png">
<link rel="apple-touch-icon" href="img/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Barlow:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/estilos.css?v=<?= @filemtime(__DIR__ . '/assets/estilos.css') ?>">
<script>document.documentElement.classList.add('js')</script>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'LocalBusiness', '@id' => 'https://www.vaypatel.com/#empresa',
    'name' => 'Vaypatel Proyectos S.L.', 'url' => 'https://www.vaypatel.com/',
    'logo' => 'https://www.vaypatel.com/img/logo.svg', 'image' => 'https://www.vaypatel.com/img/compartir.jpg',
    'description' => 'Instalación de telecomunicaciones, electricidad y seguridad: cableado estructurado, fibra óptica, centros de datos y control de accesos.',
    'telephone' => '+34 ' . $tel, 'email' => $email, 'taxID' => 'B88306642',
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Calle Fundición 4 BIS, nave 54', 'postalCode' => '28522', 'addressLocality' => 'Rivas-Vaciamadrid', 'addressRegion' => 'Madrid', 'addressCountry' => 'ES'],
    'openingHoursSpecification' => ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '08:00', 'closes' => '20:00'],
    'areaServed' => ['@type' => 'Country', 'name' => 'España'],
    'knowsAbout' => ['Cableado estructurado', 'Fibra óptica', 'Instalaciones eléctricas', 'Centros de datos', 'Control de accesos', 'BMS'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body>

<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
<symbol id="i-phone" viewBox="0 0 24 24"><path d="M6.6 3h3l1.5 4-2 1.4a12 12 0 0 0 5.5 5.5l1.4-2 4 1.5v3a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4.6 5.2 2 2 0 0 1 6.6 3Z"/></symbol>
<symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="m3.5 6.5 8.5 6 8.5-6"/></symbol>
<symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.7 3.8 9S14.5 18.4 12 21c-2.5-2.6-3.8-5.7-3.8-9S9.5 5.6 12 3Z"/></symbol>
<symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.6"/></symbol>
<symbol id="i-net" viewBox="0 0 24 24"><rect x="9" y="3" width="6" height="5" rx="1"/><rect x="2" y="16" width="6" height="5" rx="1"/><rect x="16" y="16" width="6" height="5" rx="1"/><path d="M12 8v4M5 16v-2h14v2"/></symbol>
<symbol id="i-fiber" viewBox="0 0 24 24"><rect x="2.5" y="8" width="8" height="8" rx="1.2"/><path d="M10.5 10.5h3.2v3h-3.2"/><path d="M16.5 9.2a4.2 4.2 0 0 1 0 5.6M19.5 6.6a7.8 7.8 0 0 1 0 10.8"/><path d="M4.8 8V6.2M8.2 8V6.2"/></symbol>
<symbol id="i-climate" viewBox="0 0 24 24"><rect x="8.6" y="2.5" width="6.8" height="12" rx="3.4"/><path d="M12 6v5.5"/><circle cx="12" cy="17.6" r="3.9"/><path d="M17.5 5.5H21M17.5 9H20M17.5 12.5H21"/></symbol>
<symbol id="i-grid" viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="7" height="7" rx="1"/><rect x="13.5" y="3.5" width="7" height="7" rx="1"/><rect x="3.5" y="13.5" width="7" height="7" rx="1"/><rect x="13.5" y="13.5" width="7" height="7" rx="1"/></symbol>
<symbol id="i-team" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16.2 5.4a3.1 3.1 0 0 1 0 5.4M17.5 14.8c2.1.7 3.5 2.6 3.5 5.2"/></symbol>
<symbol id="i-bolt" viewBox="0 0 24 24"><path d="M13 2 4.5 13.5H11L10 22l9-11.7h-6.4L13 2Z"/></symbol>
<symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 5 6v6c0 4.4 2.9 7.9 7 9 4.1-1.1 7-4.6 7-9V6l-7-3Z"/><path d="m9.2 12 2 2 3.6-3.8"/></symbol>
<symbol id="i-server" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="6" rx="1.4"/><rect x="3" y="14" width="18" height="6" rx="1.4"/><path d="M7 7h.01M7 17h.01M11 7h5M11 17h5"/></symbol>
<symbol id="i-check" viewBox="0 0 24 24"><path d="M9 4h6a1 1 0 0 1 1 1v1H8V5a1 1 0 0 1 1-1Z"/><path d="M16 5h2.5A1.5 1.5 0 0 1 20 6.5v13A1.5 1.5 0 0 1 18.5 21h-13A1.5 1.5 0 0 1 4 19.5v-13A1.5 1.5 0 0 1 5.5 5H8"/><path d="m8.5 13.5 2.4 2.4 4.6-5"/></symbol>
<symbol id="i-gov" viewBox="0 0 24 24"><path d="M3 9.5 12 4l9 5.5M5 10v8M9.5 10v8M14.5 10v8M19 10v8M3 21h18"/></symbol>
<symbol id="i-award" viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.5"/><path d="m8.5 13.5-1.5 7 5-2.6 5 2.6-1.5-7"/></symbol>
<symbol id="i-warranty" viewBox="0 0 24 24"><path d="M12 3 5 6v5.5c0 4.3 2.8 8 7 9.5 4.2-1.5 7-5.2 7-9.5V6l-7-3Z"/><path d="M12 8.5v4M12 15.5h.01"/></symbol>
<symbol id="i-wave" viewBox="0 0 24 24"><path d="M2 12h3l2.5-7 4 14 3-9.5L17.5 12H22"/></symbol>
<symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3.5" y="5" width="17" height="16" rx="1.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></symbol>
<symbol id="i-star" viewBox="0 0 24 24"><path d="m12 3.5 2.6 5.4 5.9.8-4.3 4.1 1 5.8-5.2-2.8-5.2 2.8 1-5.8-4.3-4.1 5.9-.8L12 3.5Z"/></symbol>
<symbol id="i-camera" viewBox="0 0 24 24"><path d="M3 8.5h3.5L8 6h8l1.5 2.5H21v11H3v-11Z"/><circle cx="12" cy="13.5" r="3.2"/></symbol>
<symbol id="i-send" viewBox="0 0 24 24"><path d="M21 3 10.5 13.5M21 3l-6.8 18-3.7-7.5L3 9.8 21 3Z"/></symbol>
<symbol id="i-plan" viewBox="0 0 24 24"><path d="M3 5.5 9 3l6 2.5L21 3v15.5L15 21l-6-2.5L3 21V5.5Z"/><path d="M9 3v15.5M15 5.5V21"/></symbol>
<symbol id="i-cert" viewBox="0 0 24 24"><rect x="3.5" y="4" width="17" height="12" rx="1.5"/><path d="M7.5 8h9M7.5 11.5h5M9 20l3-2 3 2v-4H9v4Z"/></symbol>
<symbol id="i-building" viewBox="0 0 24 24"><path d="M4 21V5.5L13 3v18M13 9h7v12M4 21h17M7.5 8h2M7.5 12h2M7.5 16h2M16 13h1.5M16 17h1.5"/></symbol>
<symbol id="i-van" viewBox="0 0 24 24"><path d="M2.5 6.5h11v10h-11zM13.5 9.5h4.2l3.8 3.8v3.2h-8"/><circle cx="6.5" cy="17.5" r="1.9"/><circle cx="17" cy="17.5" r="1.9"/><path d="M16 9.5v3.8h5.5"/></symbol>
<symbol id="i-box" viewBox="0 0 24 24"><path d="m12 3 8 4.2v9.6L12 21l-8-4.2V7.2L12 3Z"/><path d="m4 7.2 8 4.2 8-4.2M12 11.4V21M8 5.1l8 4.2"/></symbol>
<symbol id="i-route" viewBox="0 0 24 24"><circle cx="6" cy="18" r="2.3"/><path d="M18 10.5s4-3.4 4-6.1a4 4 0 0 0-8 0c0 2.7 4 6.1 4 6.1Z"/><circle cx="18" cy="4.6" r=".9"/><path d="M8.3 18H15a3 3 0 0 0 0-6H9a3 3 0 0 1 0-6h2.5"/></symbol>
<symbol id="i-tools" viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0 5 5L21 13l-8 8-3-3 8-8-1.3-1.3Z"/><path d="m3 21 6.5-6.5M5 3l4 4-2 2-4-4 2-2Z"/></symbol>
<symbol id="i-wa" viewBox="0 0 24 24"><path stroke="none" fill="currentColor" d="M12.04 2C6.6 2 2.2 6.4 2.2 11.84c0 1.74.46 3.44 1.32 4.94L2 22l5.36-1.4a9.8 9.8 0 0 0 4.68 1.19h.01c5.43 0 9.84-4.4 9.84-9.84C21.89 6.4 17.48 2 12.04 2Zm0 17.92h-.01a8.2 8.2 0 0 1-4.16-1.14l-.3-.18-3.1.81.83-3.02-.2-.31a8.13 8.13 0 0 1-1.25-4.34c0-4.51 3.68-8.18 8.2-8.18a8.15 8.15 0 0 1 8.18 8.19c0 4.51-3.67 8.17-8.19 8.17Zm4.5-6.12c-.25-.13-1.46-.72-1.68-.8-.23-.08-.39-.13-.56.12-.16.25-.64.8-.78.97-.15.16-.29.18-.53.06-.25-.12-1.04-.38-1.98-1.22-.73-.65-1.23-1.46-1.37-1.7-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.44.12-.15.16-.25.25-.42.08-.16.04-.31-.02-.44-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.42l-.48-.01c-.16 0-.43.06-.65.31-.23.25-.86.84-.86 2.05s.88 2.38 1 2.54c.13.17 1.74 2.65 4.2 3.72.59.25 1.05.4 1.4.52.59.18 1.13.16 1.55.1.47-.07 1.46-.6 1.66-1.18.2-.57.2-1.07.14-1.17-.06-.11-.22-.17-.47-.29Z"/></symbol>
<symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2"/></symbol>
<symbol id="i-handshake" viewBox="0 0 24 24"><path d="m3 12 4-4 4 2 3-3 7 6-3 3"/><path d="m7 12 3 3a1.5 1.5 0 0 0 2-2M10 15l2 2a1.5 1.5 0 0 0 2-2l-1-1M14 16l1 1a1.5 1.5 0 0 0 2-2"/></symbol>
</defs></svg>

<div id="rail" aria-hidden="true"></div>
<div id="railDot" aria-hidden="true"></div>

<nav id="nav" aria-label="Principal">
  <div class="wrap navbar">
    <a class="brand" href="#top" aria-label="Vaypatel Proyectos, inicio"><img src="img/logo.svg" alt="Vaypatel · Telecomunicaciones y electricidad" width="224" height="100"></a>
    <div class="navlinks">
      <a href="#servicios">Servicios</a>
      <a href="#certificaciones">Acreditaciones</a>
      <a href="#obras">Obras</a>
      <a href="#instalaciones">Instalaciones</a>
      <a href="#empresa">Empresa</a>
      <a href="#flota">Flota</a>
      <a href="#contacto">Contacto</a>
    </div>
    <div class="navend">
      <a class="navcall" href="tel:<?= e($telLink) ?>"><svg class="i"><use href="#i-phone"/></svg><span class="t"><?= e($tel) ?></span><span class="sr">Llamar</span></a>
      <button class="burger" id="burger" type="button" aria-expanded="false" aria-controls="menu" aria-label="Abrir menú"><span></span><span></span><span></span></button>
    </div>
  </div>
  <div id="progress" aria-hidden="true"></div>
</nav>

<div id="menu">
  <a href="#servicios"><b>01</b> Servicios</a>
  <a href="#certificaciones"><b>02</b> Acreditaciones</a>
  <a href="#obras"><b>03</b> Obras</a>
  <a href="#instalaciones"><b>04</b> Instalaciones</a>
  <a href="#empresa"><b>05</b> Empresa</a>
  <a href="#flota"><b>06</b> Flota</a>
  <a href="#contacto"><b>07</b> Contacto</a>
  <div class="mfoot">
    <a class="mcall" href="tel:<?= e($telLink) ?>"><svg class="i"><use href="#i-phone"/></svg><?= e($tel) ?></a>
    <a class="mcall" href="<?= e($mailLink) ?>"><svg class="i"><use href="#i-mail"/></svg><?= e($email) ?></a>
    <a class="mcall mwa" href="<?= e($waLink) ?>" target="_blank" rel="noopener"><svg class="i"><use href="#i-wa"/></svg>WhatsApp</a>
  </div>
</div>

<div id="bgfx" aria-hidden="true"><div class="mesh"></div><div class="glow"></div></div>

<header id="top" class="hero">
  <div class="hero-pin">
    <div class="hero-media"><picture>
      <?php if (v($d, 'portada.foto_movil')): ?><source media="(max-width: 760px)" srcset="<?= e(v($d, 'portada.foto_movil')) ?>"><?php endif; ?>
      <img class="hero-img" id="heroImg" src="<?= e(v($d, 'portada.foto', 'img/hero.jpg')) ?>" alt="" fetchpriority="high">
    </picture></div>
    <div class="heroveil"></div>
    <div class="herodim" id="heroDim"></div>
    <div class="scan"></div>

    <svg class="orbit" id="orbit" viewBox="0 0 1000 600" aria-hidden="true">
      <defs>
        <linearGradient id="orbitGrad" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0" stop-color="#00A0E3" stop-opacity="0"/>
          <stop offset=".45" stop-color="#00A0E3"/>
          <stop offset="1" stop-color="#1D6E8F"/>
        </linearGradient>
        <radialGradient id="dotGlow"><stop offset="0" stop-color="#00A0E3" stop-opacity=".55"/><stop offset="1" stop-color="#00A0E3" stop-opacity="0"/></radialGradient>
      </defs>
      <g transform="rotate(-12 520 300)">
        <path class="o-thick" id="orbitThick" d="M 50 300 A 470 175 0 1 1 990 300 A 470 175 0 1 1 50 300"/>
        <path class="o-thin" id="orbitThin" d="M 28 296 A 494 196 0 1 1 1012 296 A 494 196 0 1 1 28 296"/>
        <circle class="o-glow" id="orbitGlow" r="44" cx="520" cy="125"/>
        <circle class="o-dot" id="orbitDot" r="12" cx="520" cy="125"/>
      </g>
    </svg>

    <div class="hero-inner" id="heroInner">
      <p class="eyebrow mono"><svg class="i"><use href="#i-gov"/></svg> <?= e(v($d, 'portada.aviso')) ?></p>
      <h1 id="heroTitle"><?= $titularHtml ?> <em><?= $destacadoHtml ?></em></h1>
      <p class="lede"><?= e(v($d, 'portada.subtitulo')) ?></p>
      <div class="actions">
        <a class="btn btn-main" href="#contacto"><svg class="i"><use href="#i-plan"/></svg> Pedir presupuesto</a>
        <a class="btn btn-ghost" href="#obras"><svg class="i"><use href="#i-building"/></svg> Ver obras</a>
      </div>
      <div class="badges mono">
        <?php $ics = ['i-award', 'i-warranty', 'i-wave']; foreach (lista($d, 'portada.sellos') as $k => $s): ?>
        <span><svg class="i"><use href="#<?= $ics[$k % 3] ?>"/></svg> <?= e($s) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="cue mono" id="cue" aria-hidden="true"><span>Scroll</span><i></i></div>
  </div>
</header>

<?php $dis = lista($d, 'disciplinas'); if ($dis): ?>
<div class="band" aria-label="Disciplinas: <?= e(implode(', ', $dis)) ?>">
  <div class="band-track" aria-hidden="true"><?php for ($x = 0; $x < 4; $x++) foreach ($dis as $t) echo '<span>' . e($t) . '</span>'; ?></div>
</div>
<?php endif; ?>

<section id="cifras" aria-label="Cifras">
  <div class="wrap stats">
    <?php foreach (lista($d, 'cifras') as $k => $f): $n = (int)($f['numero'] ?? 0); ?>
    <div class="stat reveal" style="--d:<?= $k ?>"><div class="n"><?php if (!empty($f['prefijo'])): ?><i><?= e($f['prefijo']) ?></i><?php endif; ?><span class="count" data-to="<?= $n ?>"><?= numero_es($n) ?></span><?php if (!empty($f['sufijo'])): ?><i><?= e($f['sufijo']) ?></i><?php endif; ?></div><p><?= e($f['texto'] ?? '') ?></p></div>
    <?php endforeach; ?>
  </div>
</section>

<section id="servicios">
  <div class="wrap">
    <div class="sechead mono reveal"><svg class="i"><use href="#i-grid"/></svg><span class="idx">01</span><span>Servicios</span></div>
    <h2 class="reveal">Qué hacemos</h2>
    <p class="sub reveal"><?= e(v($d, 'servicios.intro')) ?></p>
    <div class="grid3">
      <?php foreach (lista($d, 'servicios.lista') as $k => $s): ?>
      <article class="card reveal" style="--d:<?= $k % 3 ?>"><svg class="i"><use href="#<?= icono_valido($s['icono'] ?? '') ?>"/></svg>
        <h3><?= e($s['titulo'] ?? '') ?></h3>
        <p><?= e($s['texto'] ?? '') ?></p>
        <?php $et = array_filter((array)($s['etiquetas'] ?? [])); if ($et): ?><ul><?php foreach ($et as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul><?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="certstrip reveal">
      <figure class="clip"><img src="img/fluke.jpg" alt="Equipo de certificación Fluke Networks DSX" loading="lazy"></figure>
      <div class="tx">
        <div><h3>Y todo se entrega certificado</h3><p><?= e(v($d, 'servicios.certificacion')) ?></p></div>
        <a class="btn btn-ghost" href="#certificaciones">Ver acreditaciones</a>
      </div>
    </div>
  </div>
</section>

<section id="certificaciones">
  <div class="wrap">
    <div class="sechead mono reveal"><svg class="i"><use href="#i-cert"/></svg><span class="idx">02</span><span>Certificados y homologaciones</span></div>
    <h2 class="reveal">Acreditaciones</h2>
    <p class="sub reveal">Registro estatal en vigor y homologaciones de fabricante que nos permiten emitir garantía de aplicación sobre lo instalado.</p>
    <div class="certs">
      <?php foreach (lista($d, 'acreditaciones') as $k => $a): ?>
      <div class="cert reveal" style="--d:<?= $k % 2 ?>"><svg class="i"><use href="#<?= icono_valido($a['icono'] ?? '') ?>"/></svg><div>
        <h4><?= e($a['titulo'] ?? '') ?></h4><p><?= e($a['texto'] ?? '') ?></p>
        <?php if (!empty($a['ref'])): ?><span class="ref mono"><?= e($a['ref']) ?></span><?php endif; ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="obras">
  <div class="wrap">
    <div class="sechead mono reveal"><svg class="i"><use href="#i-calendar"/></svg><span class="idx">03</span><span>Obras recientes</span></div>
    <h2 class="reveal"><?= e(v($d, 'obras.titulo')) ?></h2>
    <p class="sub reveal"><?= e(v($d, 'obras.intro')) ?></p>
    <div class="projects">
      <?php foreach (lista($d, 'obras.lista') as $k => $o): ?>
      <article class="prj reveal" style="--d:<?= $k % 2 ?>"><svg class="i"><use href="#<?= icono_valido($o['icono'] ?? '') ?>"/></svg><div>
        <h4><?= e($o['titulo'] ?? '') ?></h4><span class="tag"><?= e($o['etiqueta'] ?? '') ?></span><p><?= e($o['texto'] ?? '') ?></p></div></article>
      <?php endforeach; ?>
    </div>
    <?php $refs = lista($d, 'obras.referencias'); if ($refs): ?>
    <div class="histbox reveal">
      <h3><svg class="i"><use href="#i-building"/></svg> Obras de referencia anteriores</h3>
      <p>Despliegues de gran volumen que siguen siendo nuestra mejor carta de presentación.</p>
      <ul class="histlist">
        <?php foreach ($refs as $r): ?><li><span><b><?= e($r['cliente'] ?? '') ?></b> <?= e($r['texto'] ?? '') ?></span></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</section>

<section id="instalaciones">
  <div class="wrap">
    <div class="sechead mono reveal"><svg class="i"><use href="#i-camera"/></svg><span class="idx">04</span><span>Galería de obra</span></div>
    <h2 class="reveal">Instalaciones</h2>
    <?php
      /* Bloque destacado: dos fotos grandes de la galería con un texto. Se abren en el mismo visor. */
      $destFotos = [];
      foreach (array_slice((array)($dest['fotos'] ?? []), 0, 2) as $archivo) {
        foreach ($galeria as $k => $g) if (($g['archivo'] ?? '') === $archivo) { $destFotos[] = ['k' => $k] + $g; break; }
      }
      if ($destFotos):
    ?>
    <div class="showcase">
      <div class="sc-tx reveal">
        <span class="sc-k mono">Obra terminada</span>
        <h3><?= e($dest['titulo'] ?? '') ?></h3>
        <p><?= e($dest['texto'] ?? '') ?></p>
        <?php $dp = array_filter((array)($dest['puntos'] ?? []), 'is_string'); if ($dp): ?><ul class="sc-list"><?php foreach ($dp as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <a class="btn btn-ghost" href="#contacto"><svg class="i"><use href="#i-plan"/></svg> Quiero algo así</a>
      </div>
      <?php foreach ($destFotos as $i => $f): ?>
      <figure class="sc-ph">
        <button type="button" class="ph clip" style="--d:<?= $i + 1 ?>" data-k="<?= (int)$f['k'] ?>" aria-label="Ampliar: <?= e($f['pie'] ?? '') ?>"><img src="<?= e($f['archivo']) ?>" alt="<?= e($f['pie'] ?? '') ?>" loading="lazy" decoding="async"></button>
        <figcaption class="mono"><?= e($f['pie'] ?? '') ?></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
      $cuenta = array_count_values(array_map(fn($g) => $g['categoria'] ?? '', $galeria));
    ?>
    <div class="galbar reveal">
      <div class="filters" role="group" aria-label="Filtrar fotos">
        <button type="button" data-f="todas" aria-pressed="true">Todas<b><?= count($galeria) ?></b></button>
        <?php foreach (CATEGORIAS as $id => $nom): if (empty($cuenta[$id])) continue; ?>
        <button type="button" data-f="<?= e($id) ?>" aria-pressed="false"><?= e($nom) ?><b><?= (int)$cuenta[$id] ?></b></button>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="gal" id="gal">
      <?php foreach ($galeria as $k => $g): ?>
      <figure data-c="<?= e($g['categoria'] ?? '') ?>"<?= $k >= $VISIBLES ? ' data-extra hidden' : '' ?>>
        <button type="button" class="ph clip" style="--d:<?= $k % 4 ?>" data-k="<?= $k ?>" aria-label="Ampliar: <?= e($g['pie'] ?? '') ?>"><img src="<?= e($g['archivo'] ?? '') ?>" alt="<?= e($g['pie'] ?? '') ?>" loading="lazy" decoding="async"></button>
        <figcaption><?= e($g['pie'] ?? '') ?></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
    <?php if (count($galeria) > $VISIBLES): ?>
    <div class="galmore"><button type="button" class="btn btn-ghost" id="galMore">Ver todas las fotos (<?= count($galeria) ?>)</button></div>
    <?php endif; ?>
  </div>
</section>

<div class="lightbox" id="lb" role="dialog" aria-modal="true" aria-label="Foto ampliada" hidden>
  <button type="button" class="lb-btn lb-close" id="lbClose" aria-label="Cerrar">✕</button>
  <button type="button" class="lb-btn lb-prev" id="lbPrev" aria-label="Foto anterior">←</button>
  <img id="lbImg" alt="">
  <p id="lbCap"></p>
  <button type="button" class="lb-btn lb-next" id="lbNext" aria-label="Foto siguiente">→</button>
</div>

<div class="sheet">
<section id="empresa">
  <div class="wrap about">
    <div>
      <div class="sechead mono reveal"><svg class="i"><use href="#i-team"/></svg><span class="idx">05</span><span>La empresa</span></div>
      <h2 class="reveal"><?= e(v($d, 'empresa.titulo')) ?></h2>
      <?php foreach (lista($d, 'empresa.parrafos') as $p): ?><p class="reveal"><?= e($p) ?></p><?php endforeach; ?>
      <div class="facts">
        <?php foreach (lista($d, 'empresa.puntos') as $k => $p): ?>
        <div class="reveal" style="--d:<?= $k ?>"><b><?= e($p['titulo'] ?? '') ?></b><span><?= e($p['texto'] ?? '') ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="photo clip" id="empresaFoto"><img src="<?= e(v($d, 'empresa.foto', 'img/empresa.jpg')) ?>" alt="Rack de comunicaciones instalado y etiquetado por el equipo de Vaypatel Proyectos" loading="lazy"></div>
  </div>
</section>

<?php $cl = lista($d, 'clientes.lista'); if ($cl): ?>
<section id="clientes">
  <div class="wrap">
    <div class="sechead mono reveal"><svg class="i"><use href="#i-handshake"/></svg><span>Clientes</span></div>
    <h2 class="reveal"><?= e(v($d, 'clientes.titulo')) ?></h2>
    <p class="sub reveal"><?= e(v($d, 'clientes.intro')) ?></p>
    <ul class="clients">
      <?php foreach ($cl as $k => $n): ?><li class="reveal" style="--d:<?= $k % 4 ?>"><?= e($n) ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
</div>

<?php $flPuntos = lista($d, 'flota.puntos'); if ($flota): ?>
<section id="flota">
  <div class="wrap">
    <div class="sechead mono reveal"><svg class="i"><use href="#i-van"/></svg><span class="idx">06</span><span>Medios propios</span></div>
    <h2 class="reveal"><?= e(v($d, 'flota.titulo', 'Flota de vehículos propia')) ?></h2>
    <p class="sub reveal"><?= e(v($d, 'flota.intro')) ?></p>
    <div class="fleet">
      <div class="fl-visual clip<?= v($d, 'flota.foto') ? ' has-photo' : '' ?>">
        <?php if (v($d, 'flota.foto')): ?>
        <img src="<?= e(v($d, 'flota.foto')) ?>" alt="Vehículos de la flota de Vaypatel Proyectos" loading="lazy" decoding="async">
        <?php else: ?>
        <svg class="van" viewBox="0 0 640 340" role="img" aria-label="Ilustración de una furgoneta de la flota">
          <g class="dim" aria-hidden="true">
            <path d="M70 46v18M580 46v18M70 55h510"/><path d="m78 51-8 4 8 4M572 51l8 4-8 4"/>
            <text x="325" y="40" text-anchor="middle">Flota propia · Base en Rivas-Vaciamadrid</text>
          </g>
          <path class="ground" d="M20 283h600"/>
          <path class="body" d="M70 250V104q0-14 14-14h386q14 0 22 12l48 66 28 8q12 4 12 16v46q0 12-12 12h-48a38 38 0 0 0-76 0H222a38 38 0 0 0-76 0Z"/>
          <path class="cab" d="M434 104h44l42 58h-86Z"/>
          <path class="seam" d="M428 92v156M330 96v150M70 214h510"/>
          <path class="seam" d="M444 182h22M314 176h-22"/>
          <rect class="lamp" x="566" y="188" width="10" height="9" rx="2"/>
          <path class="orbit-l" d="M108 176c58-60 190-86 262-62"/>
          <circle class="orbit-d" cx="368" cy="113" r="7"/>
          <g class="wheel"><circle cx="184" cy="252" r="30"/><circle cx="184" cy="252" r="11"/></g>
          <g class="wheel"><circle cx="482" cy="252" r="30"/><circle cx="482" cy="252" r="11"/></g>
        </svg>
        <?php endif; ?>
        <div class="fl-tags mono"><span><svg class="i"><use href="#i-pin"/></svg> Nave propia en Rivas-Vaciamadrid</span><span><svg class="i"><use href="#i-route"/></svg> Toda España</span></div>
      </div>
      <ul class="fl-points">
        <?php foreach ($flPuntos as $k => $f): ?>
        <li class="reveal" style="--d:<?= $k ?>"><svg class="i"><use href="#<?= icono_valido($f['icono'] ?? '') ?>"/></svg><div>
          <h4><?= e($f['titulo'] ?? '') ?></h4><p><?= e($f['texto'] ?? '') ?></p></div></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php endif; ?>

<section id="contacto">
  <div class="wrap">
    <div class="sechead mono reveal"><svg class="i"><use href="#i-mail"/></svg><span class="idx">07</span><span>Contacto</span></div>
    <h2 class="reveal">Pide presupuesto</h2>
    <p class="sub reveal">Escríbenos con los datos de la instalación y te enviamos una propuesta y un plazo, sin compromiso.</p>
    <div class="contact">
      <div class="cinfo reveal">
        <a href="tel:<?= e($telLink) ?>"><svg class="i"><use href="#i-phone"/></svg><span><small>Teléfono</small><?= e($tel) ?></span></a>
        <a href="<?= e($mailLink) ?>"><svg class="i"><use href="#i-mail"/></svg><span><small>Email</small><?= e($email) ?></span></a>
        <a class="ci-wa" href="<?= e($waLink) ?>" target="_blank" rel="noopener"><svg class="i"><use href="#i-wa"/></svg><span><small>WhatsApp · respuesta rápida</small>Escríbenos por WhatsApp</span><b class="go" aria-hidden="true">→</b></a>
        <a href="https://maps.google.com/?q=<?= rawurlencode('Calle Fundición 4 BIS nave 54, 28522 Rivas-Vaciamadrid') ?>" target="_blank" rel="noopener"><svg class="i"><use href="#i-pin"/></svg><span><small>Nave</small><?= e($dir) ?></span></a>
        <?php if ($horario = v($d, 'contacto.horario')): ?><div><svg class="i"><use href="#i-clock"/></svg><span><small>Horario de atención</small><?= e($horario) ?></span></div><?php endif; ?>
      </div>
      <form class="quote reveal" id="form" action="enviar.php" method="POST" novalidate>
        <p class="hp" aria-hidden="true"><label>No rellenar <input name="web_url" tabindex="-1" autocomplete="off"></label></p>
        <div class="frow two">
          <div><label class="fl" for="nombre">Nombre y empresa</label><input id="nombre" name="nombre" autocomplete="name" required></div>
          <div><label class="fl" for="tel">Teléfono</label><input id="tel" name="tel" type="tel" autocomplete="tel"></div>
        </div>
        <div class="frow"><div><label class="fl" for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" required></div></div>
        <div class="frow"><div><label class="fl" for="tipo">Tipo de trabajo</label>
          <select id="tipo" name="tipo">
            <option>Cableado estructurado</option><option>Fibra óptica</option><option>Electricidad</option>
            <option>Seguridad y control de accesos</option><option>Centro de datos</option><option>Control de clima (BMS)</option>
            <option>Certificación o mantenimiento</option><option>Instalación integral</option>
          </select></div></div>
        <div class="frow"><div><label class="fl" for="msg">La obra</label><textarea id="msg" name="msg" placeholder="Ubicación, número de puntos, plazos, si hay proyecto redactado…" required></textarea></div></div>
        <input type="hidden" name="t" value="<?= time() ?>">
        <label class="consent" for="consentimiento"><input type="checkbox" id="consentimiento" name="consentimiento" required><span>He leído y acepto la <a href="privacidad.html" target="_blank">política de privacidad</a> y el tratamiento de mis datos para responder a esta consulta.</span></label>
        <button class="btn btn-main" type="submit"><svg class="i"><use href="#i-send"/></svg> Enviar consulta</button>
        <p class="formnote<?= $envioMsg ? ' on ' . ($envio === 'ok' ? 'ok' : 'err') : '' ?>" id="note" role="status" aria-live="polite"><?= e($envioMsg ?: 'Respondemos en menos de 24 horas laborables.') ?></p>
      </form>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="fgrid">
      <div>
        <img class="flogo" src="img/logo.svg" alt="Vaypatel · Telecomunicaciones y electricidad" width="247" height="110">
        <p>Instalaciones de voz-datos, electricidad<br>y seguridad. Madrid y toda España.</p>
      </div>
      <div class="flinks">
        <a href="#servicios">Servicios</a><a href="#certificaciones">Acreditaciones</a><a href="#obras">Obras</a>
        <a href="#instalaciones">Instalaciones</a><a href="#empresa">Empresa</a><a href="#flota">Flota</a><a href="#contacto">Contacto</a>
      </div>
      <div><p><a href="tel:<?= e($telLink) ?>"><?= e($tel) ?></a><br><a href="<?= e($mailLink) ?>"><?= e($email) ?></a><br><a href="<?= e($waLink) ?>" target="_blank" rel="noopener">WhatsApp</a><br><?= e($dir) ?><?php if (v($d, 'contacto.horario')): ?><br><?= e(v($d, 'contacto.horario')) ?><?php endif; ?></p></div>
    </div>
    <div class="legal">
      <span>© <?= date('Y') ?> Vaypatel Proyectos S.L. · CIF B88306642</span>
      <span><a href="aviso-legal.html">Aviso legal</a> · <a href="privacidad.html">Privacidad</a> · Empresa instaladora nº 16317 · Tipo B</span>
    </div>
  </div>
</footer>

<a class="wa" id="wa" href="<?= e($waLink) ?>" target="_blank" rel="noopener" aria-label="Escribir por WhatsApp">
  <span class="wa-ic"><svg class="i" aria-hidden="true"><use href="#i-wa"/></svg></span>
  <span class="wa-tx"><small>¿Hablamos?</small>WhatsApp</span>
</a>

<script>window.VP = { galeria: <?= json_encode(array_map(fn($g) => ['src' => $g['archivo'] ?? '', 'pie' => $g['pie'] ?? ''], $galeria), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>, email: <?= json_encode($email, JSON_HEX_TAG) ?>, vistaPrevia: <?= $vista ? 'true' : 'false' ?> };</script>
<script src="assets/web.js?v=<?= @filemtime(__DIR__ . '/assets/web.js') ?>" defer></script>
</body>
</html>
