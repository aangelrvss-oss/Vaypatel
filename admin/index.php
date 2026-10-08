<?php
/*
 * Panel de administración de www.vaypatel.com
 * Permite cambiar textos, obras, clientes, contacto y fotos sin tocar código.
 */
require __DIR__ . '/nucleo.php';
iniciar_sesion();

$aviso = ''; $error = '';
$accion = $_POST['accion'] ?? '';

/* ---------- acciones ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'La sesión ha caducado. Vuelve a intentarlo.';
    } elseif ($accion === 'entrar') {
        if ($espera = bloqueado()) {
            $error = 'Demasiados intentos. Espera ' . ceil($espera / 60) . ' minutos.';
        } else {
            $c = leer_clave();
            $ok = $c['hash'] !== '' && password_verify((string)($_POST['clave'] ?? ''), $c['hash']);
            anotar_intento($ok);
            if ($ok) {
                session_regenerate_id(true);
                $_SESSION['ok'] = 1;
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                header('Location: ./'); exit;
            }
            usleep(400000);
            $error = 'Contraseña incorrecta.';
        }
    } elseif (!conectado()) {
        $error = 'Inicia sesión para continuar.';
    } elseif ($accion === 'salir') {
        $_SESSION = []; session_destroy();
        header('Location: ./'); exit;
    } elseif ($accion === 'cambiar_clave') {
        $c = leer_clave();
        $actual = (string)($_POST['actual'] ?? ''); $n1 = (string)($_POST['nueva'] ?? ''); $n2 = (string)($_POST['repite'] ?? '');
        if (!password_verify($actual, $c['hash'])) $error = 'La contraseña actual no es correcta.';
        elseif ($n1 !== $n2) $error = 'Las dos contraseñas nuevas no coinciden.';
        elseif ($m = clave_valida($n1)) $error = $m;
        elseif (!guardar_clave($n1)) $error = 'No se ha podido guardar. Revisa los permisos de la carpeta admin/datos.';
        else { $aviso = 'Contraseña cambiada.'; session_regenerate_id(true); }
    } elseif ($accion === 'guardar') {
        $d = cargar_contenido();
        $d['portada']['aviso']     = txt($_POST['p_aviso'] ?? '', 120);
        $d['portada']['titular']   = txt($_POST['p_titular'] ?? '', 120);
        $d['portada']['destacado'] = txt($_POST['p_destacado'] ?? '', 120);
        $d['portada']['subtitulo'] = txt($_POST['p_subtitulo'] ?? '', 500);
        $d['portada']['sellos']    = lineas($_POST['p_sellos'] ?? '', 4);
        $d['disciplinas']          = lineas($_POST['disciplinas'] ?? '', 10);
        $d['cifras']               = filas('cifras', ['numero' => 'int', 'prefijo' => 'corto', 'sufijo' => 'corto', 'texto' => 'txt'], 4);
        $d['servicios']['intro']   = txt($_POST['s_intro'] ?? '', 500);
        $d['servicios']['lista']   = filas('servicios', ['icono' => 'icono', 'titulo' => 'txt', 'texto' => 'txt', 'entornos' => 'txt', 'etiquetas' => 'tags'], 12);
        $d['servicios']['certificacion'] = txt($_POST['s_cert'] ?? '', 600);
        $d['acreditaciones']       = filas('acreditaciones', ['icono' => 'icono', 'tipo' => 'txt', 'titulo' => 'txt', 'texto' => 'txt', 'ref' => 'txt'], 10);
        $d['obras']['titulo']      = txt($_POST['o_titulo'] ?? '', 120);
        $d['obras']['intro']       = txt($_POST['o_intro'] ?? '', 500);
        $d['obras']['lista']       = filas('obras', ['icono' => 'icono', 'cliente' => 'txt', 'proyecto' => 'txt', 'tipo' => 'txt', 'lugar' => 'txt', 'cifra' => 'corto', 'cifra_txt' => 'txt', 'texto' => 'txt'], 60);
        $d['obras']['referencias'] = filas('referencias', ['cliente' => 'txt', 'texto' => 'txt'], 40);
        $d['empresa']['titulo']    = txt($_POST['e_titulo'] ?? '', 120);
        $d['empresa']['parrafos']  = array_values(array_filter(array_map(fn($p) => txt($p, 1200), preg_split('/\n\s*\n/', txt($_POST['e_parrafos'] ?? '', 6000))), 'strlen'));
        $d['empresa']['puntos']    = filas('puntos', ['titulo' => 'txt', 'texto' => 'txt'], 8);
        $d['clientes']['titulo']   = txt($_POST['c_titulo'] ?? '', 120);
        $d['clientes']['intro']    = txt($_POST['c_intro'] ?? '', 500);
        $d['clientes']['lista']    = lineas($_POST['c_lista'] ?? '', 40);
        $d['galeria']              = array_values(array_filter(filas('galeria', ['archivo' => 'foto', 'pie' => 'txt', 'categoria' => 'cat'], 200), fn($g) => $g['archivo'] !== ''));
        $enGaleria = array_column($d['galeria'], 'archivo');
        $d['destacada']['titulo']  = txt($_POST['d_titulo'] ?? '', 120);
        $d['destacada']['texto']   = txt($_POST['d_texto'] ?? '', 600);
        $d['destacada']['puntos']  = lineas($_POST['d_puntos'] ?? '', 6);
        $d['destacada']['fotos']   = array_values(array_unique(array_filter([(string)($_POST['d_foto1'] ?? ''), (string)($_POST['d_foto2'] ?? '')], fn($f) => in_array($f, $enGaleria, true))));
        $d['flota']['titulo']      = txt($_POST['f_titulo'] ?? '', 120);
        $d['flota']['intro']       = txt($_POST['f_intro'] ?? '', 600);
        $d['flota']['puntos']      = filas('flota', ['icono' => 'icono', 'titulo' => 'txt', 'texto' => 'txt'], 8);
        $d['flota']['fotos']       = array_values(array_filter(filas('flota_fotos', ['archivo' => 'foto', 'pie' => 'txt'], 4), fn($f) => $f['archivo'] !== ''));
        unset($d['flota']['foto']);
        foreach (['telefono' => 30, 'telefono_oficina' => 30, 'email' => 120, 'web' => 120, 'direccion' => 200, 'horario' => 120, 'whatsapp' => 20, 'whatsapp_mensaje' => 300] as $k => $max) {
            $d['contacto'][$k] = txt($_POST['k_' . $k] ?? '', $max);
        }
        if ($d['contacto']['email'] !== '' && !filter_var($d['contacto']['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'El email de contacto no es válido; no se ha guardado nada.';
        } elseif (guardar_contenido($d)) {
            $aviso = 'Cambios guardados. Ya se ven en la web.';
        } else {
            $error = 'No se ha podido guardar. Revisa los permisos de contenido.json.';
        }
    } elseif ($accion === 'subir_galeria') {
        $d = cargar_contenido(); $n = 0; $fallos = [];
        $cat = array_key_exists($_POST['categoria'] ?? '', CATEGORIAS) ? $_POST['categoria'] : 'cableado';
        $pie = txt($_POST['pie'] ?? '', 160);
        foreach (array_slice(archivos('fotos'), 0, 20) as $f) {
            [$nombre, $err] = procesar_foto($f, DIR_GALERIA, 'obra');
            if ($nombre) { array_unshift($d['galeria'], ['archivo' => 'img/galeria/' . $nombre, 'pie' => $pie !== '' ? $pie : 'Instalación de Vaypatel Proyectos', 'categoria' => $cat]); $n++; }
            else $fallos[] = e($f['name'] ?? '') . ': ' . $err;
        }
        if ($n && !guardar_contenido($d)) $error = 'Las fotos se han subido pero no se ha podido guardar la lista.';
        elseif ($n) $aviso = $n === 1 ? 'Foto añadida al principio de la galería. Revisa su pie de foto.' : "$n fotos añadidas al principio de la galería. Revisa sus pies de foto.";
        if ($fallos) $error = trim($error . ' ' . implode(' · ', $fallos));
    } elseif ($accion === 'subir_flota') {
        $d = cargar_contenido();
        [$nombre, $err] = procesar_foto(archivos('foto')[0] ?? [], RAIZ . '/img/flota', 'flota', 1600);
        if (!$nombre) $error = $err;
        else {
            $fotos = lista($d, 'flota.fotos');
            if (!$fotos && !empty($d['flota']['foto'])) $fotos[] = ['archivo' => $d['flota']['foto'], 'pie' => 'Vehículos de Vaypatel Proyectos'];
            $fotos[] = ['archivo' => 'img/flota/' . $nombre, 'pie' => txt($_POST['pie'] ?? '', 160) ?: 'Vehículo de Vaypatel Proyectos'];
            $d['flota']['fotos'] = array_slice($fotos, -4);
            unset($d['flota']['foto']);
            $aviso = guardar_contenido($d) ? 'Foto añadida a la flota. Revisa su pie de foto.' : '';
            if (!$aviso) $error = 'No se ha podido guardar el cambio.';
        }
    } elseif (in_array($accion, ['subir_portada', 'subir_empresa'], true)) {
        $d = cargar_contenido();
        $destino = ['subir_portada' => 'portada', 'subir_empresa' => 'empresa'][$accion];
        [$nombre, $err] = procesar_foto(archivos('foto')[0] ?? [], DIR_FOTOS, $destino, 2000);
        if (!$nombre) $error = $err;
        else {
            $d[$destino]['foto'] = 'img/' . $nombre;
            $aviso = guardar_contenido($d) ? 'Foto cambiada.' : '';
            if (!$aviso) $error = 'No se ha podido guardar el cambio.';
        }
    } elseif ($accion === 'restaurar') {
        $copia = basename((string)($_POST['copia'] ?? ''));
        if (!in_array($copia, copias(), true)) $error = 'Esa copia no existe.';
        else {
            $d = json_decode((string)file_get_contents(DIR_COPIAS . '/' . $copia), true);
            if (!is_array($d)) $error = 'La copia está dañada.';
            elseif (guardar_contenido($d)) $aviso = 'Se ha recuperado la versión del ' . e(fecha_copia($copia)) . '.';
            else $error = 'No se ha podido recuperar.';
        }
    }
}

function fecha_copia(string $f): string {
    return preg_match('/(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})/', $f, $m) ? "$m[3]/$m[2]/$m[1] a las $m[4]:$m[5]" : $f;
}

/* ---------- piezas de la interfaz ---------- */
function campo(string $label, string $name, $valor, string $tipo = 'text', string $ayuda = ''): string {
    $id = 'f_' . preg_replace('/\W/', '_', $name);
    $h = '<label class="fld" for="' . $id . '"><span>' . e($label) . '</span>';
    if ($tipo === 'area') $h .= '<textarea id="' . $id . '" name="' . e($name) . '" rows="4">' . e($valor) . '</textarea>';
    elseif ($tipo === 'lineas') $h .= '<textarea id="' . $id . '" name="' . e($name) . '" rows="6">' . e(implode("\n", (array)$valor)) . '</textarea>';
    else $h .= '<input id="' . $id . '" type="' . $tipo . '" name="' . e($name) . '" value="' . e($valor) . '">';
    if ($ayuda) $h .= '<small>' . e($ayuda) . '</small>';
    return $h . '</label>';
}

function select_opciones(array $ops, $actual): string {
    $h = '';
    foreach ($ops as $k => $t) $h .= '<option value="' . e($k) . '"' . ((string)$k === (string)$actual ? ' selected' : '') . '>' . e($t) . '</option>';
    return $h;
}

/* Editor de listas: cada fila con sus campos, botones para subir, bajar y quitar, y plantilla para añadir */
function editor(string $name, array $campos, array $filas, string $nombreFila, string $añadir = ''): string {
    $fila = function ($i, $f) use ($name, $campos, $nombreFila) {
        $h = '<div class="row"><div class="row-h"><b>' . e($nombreFila) . '</b><span class="row-b">'
           . '<button type="button" data-mv="-1" aria-label="Subir">↑</button><button type="button" data-mv="1" aria-label="Bajar">↓</button>'
           . '<button type="button" data-rm aria-label="Quitar" class="rm">Quitar</button></span></div><div class="row-f">';
        foreach ($campos as $k => [$label, $tipo]) {
            $n = $name . '[' . $i . '][' . $k . ']'; $v = $f[$k] ?? '';
            if ($tipo === 'icono') $h .= '<label class="fld s"><span>' . e($label) . '</span><select name="' . e($n) . '">' . select_opciones(ICONOS, $v) . '</select></label>';
            elseif ($tipo === 'cat') $h .= '<label class="fld s"><span>' . e($label) . '</span><select name="' . e($n) . '">' . select_opciones(CATEGORIAS, $v) . '</select></label>';
            elseif ($tipo === 'foto') $h .= '<input type="hidden" name="' . e($n) . '" value="' . e($v) . '"><img class="thumb" src="../' . e($v) . '" alt="" loading="lazy">';
            elseif ($tipo === 'area') $h .= '<label class="fld w"><span>' . e($label) . '</span><textarea name="' . e($n) . '" rows="3">' . e($v) . '</textarea></label>';
            elseif ($tipo === 'tags') $h .= '<label class="fld w"><span>' . e($label) . '</span><input name="' . e($n) . '" value="' . e(implode(', ', (array)$v)) . '"><small>Separadas por comas</small></label>';
            else $h .= '<label class="fld' . ($tipo === 'corto' ? ' xs' : ($tipo === 'int' ? ' s' : ' w')) . '"><span>' . e($label) . '</span><input name="' . e($n) . '" value="' . e($v) . '"' . ($tipo === 'int' ? ' inputmode="numeric"' : '') . '></label>';
        }
        return $h . '</div></div>';
    };
    $h = '<div class="list" data-list="' . e($name) . '">';
    foreach (array_values($filas) as $i => $f) $h .= $fila($i, $f);
    $h .= '</div>';
    if ($añadir !== '') $h .= '<template data-tpl="' . e($name) . '">' . $fila('__N__', []) . '</template><button type="button" class="add" data-add="' . e($name) . '">+ ' . e($añadir) . '</button>';
    return $h;
}

$clave = leer_clave();
$vista = !conectado() ? 'login' : ((!empty($clave['cambiar']) || ($_GET['ver'] ?? '') === 'clave') ? 'clave' : 'panel');
$d = cargar_contenido();
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Panel · Vaypatel</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="stylesheet" href="panel.css">
</head>
<body class="<?= $vista ?>">

<?php if ($vista === 'login'): ?>
<main class="login-box">
  <img src="../img/logo.svg" alt="Vaypatel" class="logo">
  <h1>Panel de la web</h1>
  <?php if ($error): ?><p class="msg err" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post">
    <?= csrf_campo() ?><input type="hidden" name="accion" value="entrar">
    <label class="fld"><span>Contraseña</span><input type="password" name="clave" autocomplete="current-password" required autofocus></label>
    <button class="btn">Entrar</button>
  </form>
  <a class="back" href="../">← Volver a la web</a>
</main>

<?php elseif ($vista === 'clave'): ?>
<main class="login-box">
  <img src="../img/logo.svg" alt="Vaypatel" class="logo">
  <h1><?= !empty($clave['cambiar']) ? 'Elige tu contraseña' : 'Cambiar contraseña' ?></h1>
  <?php if (!empty($clave['cambiar'])): ?><p class="hint">Es el primer acceso. Sustituye la contraseña provisional por una tuya antes de continuar.</p><?php endif; ?>
  <?php if ($error): ?><p class="msg err" role="alert"><?= e($error) ?></p><?php endif; ?>
  <?php if ($aviso): ?><p class="msg ok" role="status"><?= e($aviso) ?> <a href="./">Ir al panel</a></p><?php endif; ?>
  <form method="post">
    <?= csrf_campo() ?><input type="hidden" name="accion" value="cambiar_clave">
    <label class="fld"><span>Contraseña actual</span><input type="password" name="actual" autocomplete="current-password" required></label>
    <label class="fld"><span>Nueva contraseña</span><input type="password" name="nueva" autocomplete="new-password" minlength="10" required><small>Al menos 10 caracteres, con letras y números.</small></label>
    <label class="fld"><span>Repite la nueva</span><input type="password" name="repite" autocomplete="new-password" minlength="10" required></label>
    <button class="btn">Guardar contraseña</button>
  </form>
  <?php if (empty($clave['cambiar'])): ?><a class="back" href="./">← Volver al panel</a><?php endif; ?>
</main>

<?php else: ?>
<header class="top">
  <img src="../img/logo.svg" alt="Vaypatel" class="logo">
  <nav>
    <a href="../" target="_blank" rel="noopener">Ver la web ↗</a>
    <a href="?ver=clave">Contraseña</a>
    <form method="post"><?= csrf_campo() ?><button name="accion" value="salir">Salir</button></form>
  </nav>
</header>
<nav class="tabs" aria-label="Secciones">
  <a href="#portada">Portada</a><a href="#cifras">Cifras</a><a href="#servicios">Servicios</a><a href="#acreditaciones">Acreditaciones</a>
  <a href="#obras">Proyectos</a><a href="#galeria">Fotos</a><a href="#destacada">Destacado</a><a href="#empresa">Empresa</a><a href="#clientes">Clientes</a><a href="#flota">Flota</a><a href="#contacto">Contacto</a><a href="#copias">Copias</a>
</nav>
<main class="wrap">
  <?php if ($aviso): ?><p class="msg ok" role="status"><?= e($aviso) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="msg err" role="alert"><?= $error ?></p><?php endif; ?>

  <form method="post" id="principal">
    <?= csrf_campo() ?><input type="hidden" name="accion" value="guardar">

    <section id="portada" class="card">
      <h2>Portada</h2>
      <?= campo('Línea superior', 'p_aviso', v($d, 'portada.aviso')) ?>
      <?= campo('Titular (en blanco)', 'p_titular', v($d, 'portada.titular')) ?>
      <?= campo('Titular (en azul)', 'p_destacado', v($d, 'portada.destacado')) ?>
      <?= campo('Texto bajo el titular', 'p_subtitulo', v($d, 'portada.subtitulo'), 'area') ?>
      <?= campo('Sellos bajo los botones', 'p_sellos', lista($d, 'portada.sellos'), 'lineas', 'Uno por línea, máximo 3.') ?>
      <?= campo('Banda de disciplinas', 'disciplinas', lista($d, 'disciplinas'), 'lineas', 'Las palabras de la tira que se desliza bajo la portada. Una por línea.') ?>
    </section>

    <section id="cifras" class="card">
      <h2>Cifras</h2>
      <p class="hint">Los cuatro números bajo la portada. En "Antes" o "Después" puedes poner un símbolo, como + o %.</p>
      <?= editor('cifras', ['numero' => ['Número', 'int'], 'prefijo' => ['Antes', 'corto'], 'sufijo' => ['Después', 'corto'], 'texto' => ['Texto', 'txt']], lista($d, 'cifras'), 'Cifra') ?>
    </section>

    <section id="servicios" class="card">
      <h2>Servicios</h2>
      <?= campo('Texto de introducción', 's_intro', v($d, 'servicios.intro'), 'area') ?>
      <?= editor('servicios', ['icono' => ['Icono', 'icono'], 'titulo' => ['Título', 'txt'], 'texto' => ['Qué hacemos', 'area'], 'entornos' => ['Habitual en', 'txt'], 'etiquetas' => ['Capacidades', 'tags']], lista($d, 'servicios.lista'), 'Servicio', 'Añadir servicio') ?>
      <?= campo('Texto de "Todo se entrega medido y documentado"', 's_cert', v($d, 'servicios.certificacion'), 'area') ?>
    </section>

    <section id="acreditaciones" class="card">
      <h2>Acreditaciones</h2>
      <p class="hint">En "Tipo" indica qué es exactamente: Registro oficial, Certificación de fabricante, Equipo y método de medida…</p>
      <?= editor('acreditaciones', ['icono' => ['Icono', 'icono'], 'tipo' => ['Tipo', 'txt'], 'titulo' => ['Título', 'txt'], 'texto' => ['Descripción', 'area'], 'ref' => ['Referencia', 'txt']], lista($d, 'acreditaciones'), 'Acreditación', 'Añadir acreditación') ?>
    </section>

    <section id="obras" class="card">
      <h2>Obras</h2>
      <?= campo('Título de la sección', 'o_titulo', v($d, 'obras.titulo')) ?>
      <?= campo('Introducción', 'o_intro', v($d, 'obras.intro'), 'area') ?>
      <h3>Proyectos recientes</h3>
      <p class="hint">Se muestran en este orden: los 6 primeros como ficha destacada y el resto en lista. Pon una cifra solo si es un dato real del proyecto (por ejemplo "1.500" y "puntos de datos"). No se muestran fotos en los proyectos.</p>
      <?= editor('obras', ['icono' => ['Icono', 'icono'], 'cliente' => ['Cliente', 'txt'], 'proyecto' => ['Proyecto / edificio', 'txt'], 'tipo' => ['Tipo de trabajo', 'txt'], 'lugar' => ['Ubicación', 'txt'], 'cifra' => ['Cifra', 'corto'], 'cifra_txt' => ['Unidad de la cifra', 'txt'], 'texto' => ['Alcance', 'area']], array_map(function ($o) {
          if (empty($o['cliente']) && !empty($o['titulo'])) { $p = array_map('trim', explode('·', $o['titulo'], 2)); $o['cliente'] = $p[0]; $o['proyecto'] = $p[1] ?? ''; $o['tipo'] = $o['etiqueta'] ?? ''; }
          return $o;
      }, lista($d, 'obras.lista')), 'Proyecto', 'Añadir proyecto') ?>
      <h3>Obras de referencia anteriores</h3>
      <?= editor('referencias', ['cliente' => ['Cliente', 'txt'], 'texto' => ['Descripción', 'txt']], lista($d, 'obras.referencias'), 'Referencia', 'Añadir referencia') ?>
    </section>

    <section id="galeria" class="card">
      <h2>Fotos de la galería</h2>
      <p class="hint">Fotos técnicas genéricas: no indiques cliente ni obra en el pie salvo que sea seguro. Las dos fotos elegidas en "Destacado" se muestran en grande; del resto, las 6 primeras se ven al entrar y las demás al pulsar "Ver todas las fotos" (también se pueden filtrar por categoría). Para añadir fotos usa el recuadro de abajo.</p>
      <?= editor('galeria', ['archivo' => ['Foto', 'foto'], 'pie' => ['Pie de foto', 'txt'], 'categoria' => ['Categoría', 'cat']], lista($d, 'galeria'), 'Foto') ?>
    </section>

    <section id="destacada" class="card">
      <h2>Bloque destacado de Instalaciones</h2>
      <p class="hint">Dos fotos grandes de la galería con un texto, encima de los filtros. Elige "Ninguna" en las dos para ocultar el bloque.</p>
      <?= campo('Título', 'd_titulo', v($d, 'destacada.titulo')) ?>
      <?= campo('Texto', 'd_texto', v($d, 'destacada.texto'), 'area') ?>
      <?= campo('Puntos', 'd_puntos', lista($d, 'destacada.puntos'), 'lineas', 'Uno por línea, máximo 6.') ?>
      <?php $opsFotos = ['' => 'Ninguna']; foreach (lista($d, 'galeria') as $g) $opsFotos[$g['archivo']] = ($g['pie'] ?? '') . ' · ' . basename($g['archivo']); $df = lista($d, 'destacada.fotos'); ?>
      <label class="fld"><span>Foto 1</span><select name="d_foto1"><?= select_opciones($opsFotos, $df[0] ?? '') ?></select></label>
      <label class="fld"><span>Foto 2</span><select name="d_foto2"><?= select_opciones($opsFotos, $df[1] ?? '') ?></select></label>
    </section>

    <section id="empresa" class="card">
      <h2>Empresa</h2>
      <?= campo('Título', 'e_titulo', v($d, 'empresa.titulo')) ?>
      <?= campo('Texto', 'e_parrafos', implode("\n\n", lista($d, 'empresa.parrafos')), 'area', 'Deja una línea en blanco entre párrafos.') ?>
      <h3>Puntos destacados</h3>
      <?= editor('puntos', ['titulo' => ['Título', 'txt'], 'texto' => ['Texto', 'txt']], lista($d, 'empresa.puntos'), 'Punto', 'Añadir punto') ?>
    </section>

    <section id="clientes" class="card">
      <h2>Clientes</h2>
      <?= campo('Título', 'c_titulo', v($d, 'clientes.titulo')) ?>
      <?= campo('Introducción', 'c_intro', v($d, 'clientes.intro'), 'area') ?>
      <?= campo('Clientes', 'c_lista', lista($d, 'clientes.lista'), 'lineas', 'Uno por línea. Queda mejor un número múltiplo de 4.') ?>
    </section>

    <section id="flota" class="card">
      <h2>Flota de vehículos</h2>
      <?= campo('Título', 'f_titulo', v($d, 'flota.titulo')) ?>
      <?= campo('Introducción', 'f_intro', v($d, 'flota.intro'), 'area') ?>
      <h3>Puntos</h3>
      <?= editor('flota', ['icono' => ['Icono', 'icono'], 'titulo' => ['Título', 'txt'], 'texto' => ['Descripción', 'area']], lista($d, 'flota.puntos'), 'Punto', 'Añadir punto') ?>
      <h3>Fotos de la flota</h3>
      <p class="hint">Hasta 4 fotos. Quítalas todas para volver a mostrar la ilustración. Para añadir una, usa "Fotos principales", más abajo.</p>
      <?= editor('flota_fotos', ['archivo' => ['Foto', 'foto'], 'pie' => ['Pie de foto', 'txt']], lista($d, 'flota.fotos'), 'Foto') ?>
    </section>

    <section id="contacto" class="card">
      <h2>Contacto</h2>
      <?= campo('Teléfono móvil', 'k_telefono', v($d, 'contacto.telefono'), 'tel') ?>
      <?= campo('Teléfono de oficina', 'k_telefono_oficina', v($d, 'contacto.telefono_oficina'), 'tel', 'Déjalo vacío para no mostrarlo.') ?>
      <?= campo('Email', 'k_email', v($d, 'contacto.email'), 'email', 'Se muestra en la web y es el buzón donde llegan las consultas del formulario.') ?>
      <?= campo('Web', 'k_web', v($d, 'contacto.web')) ?>
      <?= campo('Dirección', 'k_direccion', v($d, 'contacto.direccion')) ?>
      <?= campo('Horario de atención', 'k_horario', v($d, 'contacto.horario'), 'text', 'Se muestra en Contacto y en el pie.') ?>
      <?= campo('WhatsApp (con prefijo, sin espacios)', 'k_whatsapp', v($d, 'contacto.whatsapp'), 'text', 'Ejemplo: 34607230957') ?>
      <?= campo('Mensaje predefinido de WhatsApp', 'k_whatsapp_mensaje', v($d, 'contacto.whatsapp_mensaje'), 'area') ?>
    </section>

    <div class="savebar"><span id="dirty">Sin cambios</span><button class="btn">Guardar cambios</button></div>
  </form>

  <section class="card" id="subir">
    <h2>Añadir fotos a la galería</h2>
    <p class="hint">Guarda antes los cambios de arriba: subir fotos recarga el panel. Se aceptan JPG, PNG o WebP de hasta 12 MB; se reducen automáticamente.</p>
    <form method="post" enctype="multipart/form-data" data-upload>
      <?= csrf_campo() ?><input type="hidden" name="accion" value="subir_galeria">
      <label class="fld"><span>Fotos</span><input type="file" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
      <label class="fld"><span>Pie de foto</span><input name="pie" placeholder="Por ejemplo: Rack de planta con cableado Cat 6A"></label>
      <label class="fld s"><span>Categoría</span><select name="categoria"><?= select_opciones(CATEGORIAS, 'cableado') ?></select></label>
      <button class="btn">Subir fotos</button>
    </form>
  </section>

  <section class="card">
    <h2>Fotos principales</h2>
    <div class="two">
      <form method="post" enctype="multipart/form-data" data-upload>
        <?= csrf_campo() ?><input type="hidden" name="accion" value="subir_portada">
        <img class="big" src="../<?= e(v($d, 'portada.foto', 'img/hero.jpg')) ?>" alt="Foto de portada actual">
        <label class="fld"><span>Cambiar foto de portada</span><input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required><small>Mejor horizontal y oscura, de al menos 1600 px de ancho.</small></label>
        <button class="btn sec">Cambiar portada</button>
      </form>
      <form method="post" enctype="multipart/form-data" data-upload>
        <?= csrf_campo() ?><input type="hidden" name="accion" value="subir_empresa">
        <img class="big" src="../<?= e(v($d, 'empresa.foto', 'img/empresa.jpg')) ?>" alt="Foto de empresa actual">
        <label class="fld"><span>Cambiar foto de "Nuestros profesionales"</span><input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required><small>Mejor vertical. Ideal: el equipo trabajando.</small></label>
        <button class="btn sec">Cambiar foto</button>
      </form>
      <form method="post" enctype="multipart/form-data" data-upload>
        <?= csrf_campo() ?><input type="hidden" name="accion" value="subir_flota">
        <label class="fld"><span>Añadir foto de la flota</span><input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required><small>Horizontal, con el vehículo de lado. Se añade al final de las fotos de la flota.</small></label>
        <label class="fld"><span>Pie de foto</span><input name="pie" placeholder="Por ejemplo: Furgoneta rotulada junto a la nave"></label>
        <button class="btn sec">Añadir foto de la flota</button>
      </form>
    </div>
  </section>

  <section class="card" id="copias">
    <h2>Copias de seguridad</h2>
    <p class="hint">Cada vez que guardas se hace una copia de la versión anterior. Se conservan las <?= MAX_COPIAS ?> últimas.</p>
    <?php $cs = copias(); if (!$cs): ?><p>Todavía no hay copias.</p><?php else: ?>
    <ul class="copias">
      <?php foreach ($cs as $cp): ?>
      <li><span><?= e(fecha_copia($cp)) ?></span>
        <form method="post" data-confirm="¿Recuperar la versión del <?= e(fecha_copia($cp)) ?>? Lo actual quedará guardado como copia."><?= csrf_campo() ?><input type="hidden" name="accion" value="restaurar"><input type="hidden" name="copia" value="<?= e($cp) ?>"><button class="btn sec">Recuperar</button></form></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>
</main>
<script src="panel.js"></script>
<?php endif; ?>
</body>
</html>
