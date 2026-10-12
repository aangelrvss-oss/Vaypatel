/* Vaypatel Proyectos — interacción de la web pública.
   Módulos: motor Scroll-craft (montaje) · menú móvil · cabecera y WhatsApp · enlace activo
   · portada (órbita y puntero) · el peinado · galería · visor de fotos · formulario.
   Sin dependencias además del motor (assets/scrollcraft.js, que no se modifica).
   Todo el contenido es visible aunque este archivo no llegue a cargarse. */
(function () {
  'use strict';
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches;
  var movilMQ = window.matchMedia && matchMedia('(max-width: 760px)');
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var clamp01 = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  var easeIO = function (t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; };
  var FOCUSABLE = 'a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])';

  /* ---------- motor Scroll-craft ----------
     Cada tramo se describe en el HTML (data-sc-*). Antes de montar se ajusta lo que cambia por
     dispositivo: en móvil la portada fluye (data-movil) y el peinado usa su propio recorrido
     (data-span-m); con movimiento reducido no se fija nada. Si el ancho cruza el corte de móvil,
     se desmonta y se vuelve a montar con la configuración del nuevo ancho. */
  var motor = null, acts = $$('[data-sc-act]'), original = acts.map(function (a) {
    return { el: a, act: a.getAttribute('data-sc-act'), span: a.getAttribute('data-sc-span') };
  });
  function configurar() {
    var movil = movilMQ && movilMQ.matches;
    original.forEach(function (o) {
      var act = o.act, span = o.span;
      var hasta = +o.el.getAttribute('data-movil-hasta') || 760;
      if (o.el.hasAttribute('data-movil') && matchMedia('(max-width: ' + hasta + 'px)').matches) act = o.el.getAttribute('data-movil');
      if (movil && o.el.hasAttribute('data-span-m')) span = o.el.getAttribute('data-span-m');
      // con movimiento reducido no se fija nada: portada, peinado y catálogo de proyectos fluyen
      if (reduce && (o.el.id === 'top' || o.el.id === 'orden' || act === 'pan')) act = 'flow';
      o.el.setAttribute('data-sc-act', act);
      if (span) o.el.setAttribute('data-sc-span', span);
    });
  }
  function montar() {
    if (!window.ScrollCraft) return;
    configurar();
    motor = ScrollCraft.mount(document.body);
    document.documentElement.classList.remove('sin-motor'); // si el motor llegó tarde, vuelve el movimiento
  }
  function remontar() {
    if (motor) {
      motor.destroy();
      /* el motor deja estilos en línea en los tramos fijados: se limpian antes de volver a montar */
      original.forEach(function (o) {
        o.el.style.height = ''; o.el.classList.remove('sc-act--pinned');
        var st = $('[data-sc-stage]', o.el); if (st) st.classList.remove('sc-stage');
      });
      $$('[data-sc-parallax],[data-sc-pan],[data-sc-reveal]').forEach(function (el) { el.style.transform = ''; el.style.clipPath = ''; });
    }
    montar();
    pintarPortada(true); peinar(true);
  }
  montar();
  [movilMQ, window.matchMedia && matchMedia('(max-width: 1023px)')].forEach(function (mq) {
    if (!mq) return;
    var cambio = function () { remontar(); };
    if (mq.addEventListener) mq.addEventListener('change', cambio); else if (mq.addListener) mq.addListener(cambio);
  });
  function progreso(el) { var v = parseFloat(el.style.getPropertyValue('--sc-p')); return isNaN(v) ? 0 : v; }

  /* Teclado: si el foco entra en un bloque que aún no ha aparecido, aparece ya (no se queda en opacidad 0) */
  document.addEventListener('focusin', function (e) {
    var t = e.target.closest && e.target.closest('[data-sc-in]:not(.sc-in)');
    while (t) {
      t.classList.add('sc-in');
      if (t.hasAttribute('data-sc-stagger')) Array.prototype.forEach.call(t.children, function (k) { k.classList.add('sc-in'); });
      t = t.parentElement && t.parentElement.closest('[data-sc-in]:not(.sc-in)');
    }
  });

  /* Mantiene el foco dentro de un diálogo abierto */
  function atrapaFoco(e, caja) {
    if (e.key !== 'Tab') return;
    var f = $$(FOCUSABLE, caja).filter(function (el) { return el.offsetParent !== null; });
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  /* ---------- menú móvil ---------- */
  var burger = $('#burger'), menu = $('#menu');
  function setMenu(open) {
    if (!menu || !burger) return;
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    burger.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    document.body.classList.toggle('locked', open);
    if (open) {
      menu.hidden = false;
      requestAnimationFrame(function () { menu.classList.add('open'); var a = $('a', menu); if (a) a.focus(); });
    } else {
      menu.classList.remove('open');
      setTimeout(function () { if (!menu.classList.contains('open')) menu.hidden = true; }, reduce ? 0 : 250);
    }
  }
  if (burger && menu) {
    burger.addEventListener('click', function () {
      var open = burger.getAttribute('aria-expanded') !== 'true';
      setMenu(open);
      if (!open) burger.focus();
    });
    $$('a', menu).forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    /* el botón de cerrar está en la cabecera, por encima del menú: el foco circula entre ambos */
    menu.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      var f = $$(FOCUSABLE, menu), last = f[f.length - 1];
      if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); burger.focus(); }
      else if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); burger.focus(); }
    });
    burger.addEventListener('keydown', function (e) {
      if (e.key === 'Tab' && burger.getAttribute('aria-expanded') === 'true') {
        e.preventDefault(); var f = $$(FOCUSABLE, menu); (e.shiftKey ? f[f.length - 1] : f[0]).focus();
      }
    });
    addEventListener('resize', function () { if (innerWidth >= 1000 && !menu.hidden) setMenu(false); });
  }

  /* ---------- portada: órbita y puntero ----------
     El punto recorre la mitad delantera de la órbita y el trazo azul se completa según el avance
     del tramo. Con ratón, los planos se inclinan muy poco hacia el puntero (cada uno a su escala). */
  var hero = $('#top'), track = $('.orb-track'), dot = $('.orb-dot'), cian = $('.orb-cian');
  var trackLen = track && track.getTotalLength ? track.getTotalLength() : 0;
  var heroP = -1, mx = 0, my = 0, tmx = 0, tmy = 0;
  function pintarPortada(forzar) {
    if (!hero || !trackLen) return;
    var p = reduce ? 0.4 : progreso(hero);
    if (!forzar && Math.abs(p - heroP) < 0.0008) return;
    heroP = p;
    var pt = track.getPointAtLength(trackLen * (0.14 + 0.6 * p));
    dot.setAttribute('cx', pt.x.toFixed(1)); dot.setAttribute('cy', pt.y.toFixed(1));
    if (cian) cian.style.strokeDashoffset = (1 - (0.42 + 0.58 * clamp01(p * 1.4))).toFixed(4);
  }
  if (hero && fine && !reduce) {
    hero.addEventListener('pointermove', function (e) {
      tmx = (e.clientX / innerWidth) * 2 - 1; tmy = (e.clientY / innerHeight) * 2 - 1;
    }, { passive: true });
    hero.addEventListener('pointerleave', function () { tmx = 0; tmy = 0; });
  }

  /* ---------- el peinado ----------
     Cada cable tiene en el HTML su trazado final (d) y el enredado (data-from) con los mismos
     comandos. Aquí se interpola número a número: el "peine" barre de izquierda a derecha (cada
     punto empieza un poco después que el anterior) y cada cable un poco después que el de encima.
     Después se cierran las bridas y se encienden los equipos uno a uno. */
  var pico = $('#orden'), peines = [], picoP = -1;
  var NUM = /-?\d*\.?\d+/g;
  if (pico) {
    $$('.peine', pico).forEach(function (svg) {
      var cables = $$('.cable', svg).map(function (c) {
        var d = c.getAttribute('d'), from = c.getAttribute('data-from') || d;
        return { el: c, k: +c.getAttribute('data-k'), partes: d.split(NUM), a: from.match(NUM).map(Number), b: d.match(NUM).map(Number) };
      });
      peines.push({ svg: svg, cables: cables, bridas: $$('.brida', svg), equipos: $$('.rk-u', svg) });
    });
  }
  function visible(svg) { return getComputedStyle(svg).display !== 'none'; }
  function peinar(forzar) {
    if (!pico || reduce) return;
    var p = progreso(pico);
    if (!forzar && Math.abs(p - picoP) < 0.0006) return;
    picoP = p;
    peines.forEach(function (P) {
      if (!visible(P.svg)) return;
      P.cables.forEach(function (c) {
        var out = c.partes[0], n = c.a.length;
        for (var i = 0; i < n; i++) {
          var j = i >> 1; // punto (cada punto son dos números: x, y)
          var t = easeIO(clamp01((p - 0.05 - 0.03 * c.k - 0.15 * j / 9) / 0.4));
          out += (c.a[i] + (c.b[i] - c.a[i]) * t).toFixed(1) + c.partes[i + 1];
        }
        c.el.setAttribute('d', out);
      });
      P.bridas.forEach(function (b, i) {
        var s = clamp01((p - (0.6 + 0.045 * i)) / 0.07);
        b.style.opacity = s.toFixed(3);
        b.style.transform = 'scale(' + (0.9 + 0.1 * s).toFixed(3) + ')';
      });
      P.equipos.forEach(function (u, k) { u.classList.toggle('on', p >= 0.73 + 0.035 * k); });
    });
  }

  /* Un solo bucle para la portada y el peinado, activo solo mientras se ven */
  var vivos = { portada: false, pico: false }, bucle = 0;
  function frame() {
    bucle = 0;
    if (vivos.portada) {
      pintarPortada(false);
      if (fine && !reduce) {
        mx += (tmx - mx) * 0.08; my += (tmy - my) * 0.08;
        if (Math.abs(tmx - mx) > 0.001 || Math.abs(tmy - my) > 0.001) {
          hero.style.setProperty('--mx', mx.toFixed(3)); hero.style.setProperty('--my', my.toFixed(3));
        }
      }
    }
    if (vivos.pico) peinar(false);
    if (vivos.portada || vivos.pico) bucle = requestAnimationFrame(frame);
  }
  if ('IntersectionObserver' in window && (hero || pico)) {
    var vis = new IntersectionObserver(function (es) {
      es.forEach(function (e) { vivos[e.target === hero ? 'portada' : 'pico'] = e.isIntersecting; });
      if (!bucle && (vivos.portada || vivos.pico)) bucle = requestAnimationFrame(frame);
    }, { rootMargin: '20% 0px' });
    if (hero) vis.observe(hero);
    if (pico && !reduce) vis.observe(pico);
  }
  pintarPortada(true); peinar(true);

  /* ---------- cabecera fija y botón de WhatsApp (un solo manejador de scroll) ---------- */
  var nav = $('#nav'), wa = $('#wa'), contacto = $('#contacto'), ticking = false;
  function paint() {
    ticking = false;
    var y = scrollY, vh = innerHeight;
    if (nav) nav.classList.toggle('stuck', y > 30);
    /* WhatsApp aparece al dejar atrás la portada y se retira en contacto, donde ya está a mano */
    if (wa) {
      var enContacto = contacto && contacto.getBoundingClientRect().top < vh * 0.8;
      wa.classList.toggle('show', y > vh * 0.5 && !enContacto);
    }
  }
  function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(paint); } }
  addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- enlace activo según la sección visible ---------- */
  var navLinks = $$('.navlinks a, #menu nav a');
  // secciones sin enlace propio en la cabecera
  var grupo = { orden: 'servicios', metodo: 'servicios', instalaciones: 'proyectos', flota: 'empresa' };
  var grupoMenu = { orden: 'servicios', metodo: 'acreditaciones', flota: 'empresa' };
  if ('IntersectionObserver' in window) {
    var spy = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        var id = e.target.id, cab = grupo[id] || id, men = grupoMenu[id] || id;
        navLinks.forEach(function (a) {
          var h = a.getAttribute('href').slice(1);
          a.classList.toggle('active', a.closest('#menu') ? h === men : h === cab);
        });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    ['servicios', 'orden', 'metodo', 'proyectos', 'instalaciones', 'empresa', 'flota', 'contacto'].forEach(function (id) { var el = document.getElementById(id); if (el) spy.observe(el); });
  }

  /* ---------- galería: filtros por tipo y "ver todas" ---------- */
  var galeria = $('#gal'), figs = galeria ? $$('li', galeria) : [], mas = $('#galMore'), filtro = 'todas', abierta = false;
  function aplicarFiltro() {
    figs.forEach(function (li) {
      li.hidden = filtro === 'todas' ? (!abierta && li.hasAttribute('data-extra')) : li.getAttribute('data-c') !== filtro;
    });
    if (mas) {
      mas.parentNode.hidden = abierta || filtro !== 'todas';
      mas.setAttribute('aria-expanded', abierta ? 'true' : 'false');
    }
    /* la galería cambia de alto: el motor vuelve a medir los tramos que vienen detrás */
    if (motor) motor.layout();
  }
  $$('.filters button').forEach(function (b) {
    b.addEventListener('click', function () {
      filtro = b.getAttribute('data-f');
      $$('.filters button').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      aplicarFiltro();
    });
  });
  if (mas) mas.addEventListener('click', function () {
    var primeraOculta = figs.filter(function (li) { return li.hidden; })[0];
    abierta = true; aplicarFiltro();
    /* el foco pasa a la primera foto nueva, para seguir con el teclado */
    if (primeraOculta) { var b = $('.ph', primeraOculta); if (b) b.focus({ preventScroll: true }); }
  });

  /* ---------- proyectos: en el teléfono se ven los 4 primeros de la lista y el resto con un botón ----------
     Todos están en el HTML (los lee Google y se ven sin JavaScript); solo se pliegan en pantallas estrechas. */
  var lista = $('.caselist'), items = lista ? $$('li', lista) : [], VISIBLES_M = 4;
  if (lista && items.length > VISIBLES_M + 2 && movilMQ && movilMQ.matches) {
    items.slice(VISIBLES_M).forEach(function (li) { li.hidden = true; });
    var bt = document.createElement('button');
    bt.type = 'button'; bt.className = 'btn btn-ghost btn-block caselist-mas';
    bt.setAttribute('aria-expanded', 'false');
    bt.textContent = 'Ver todos los proyectos (' + items.length + ')';
    lista.parentNode.insertBefore(bt, lista.nextSibling);
    bt.addEventListener('click', function () {
      items.forEach(function (li) { li.hidden = false; });
      bt.remove();
      var a = items[VISIBLES_M]; if (a) { a.tabIndex = -1; a.focus({ preventScroll: true }); }
      if (motor) motor.layout();
    });
  }

  /* ---------- visor de fotos ---------- */
  var lb = $('#lb'), lbCap = $('#lbCap'), lbK = 0, lastFocus = null;
  var lbImg = document.createElement('img');  /* la imagen se crea aquí para no dejar un <img> vacío en el HTML */
  lbImg.id = 'lbImg'; lbImg.alt = '';
  if (lbCap) lbCap.parentNode.insertBefore(lbImg, lbCap);
  var fotos = (window.VP && VP.galeria) || [];
  function mostrar(k) { lbK = k; var f = fotos[k]; if (!f) return; lbImg.src = f.src; lbImg.alt = f.pie; lbCap.textContent = f.pie; }
  /* anterior / siguiente: recorre las fotos que se ven en la página, en su orden */
  function mover(dir) {
    var v = $$('.ph[data-k]').filter(function (b) { return b.offsetParent !== null; }).map(function (b) { return +b.getAttribute('data-k'); });
    if (!v.length) return;
    var i = v.indexOf(lbK); if (i < 0) i = 0;
    mostrar(v[(i + dir + v.length) % v.length]);
  }
  function abrir(k) {
    lastFocus = document.activeElement; mostrar(k); lb.hidden = false; document.body.classList.add('locked');
    requestAnimationFrame(function () { lb.classList.add('open'); $('#lbClose').focus(); });
  }
  function cerrar() {
    lb.classList.remove('open'); document.body.classList.remove('locked');
    setTimeout(function () { lb.hidden = true; }, reduce ? 0 : 250);
    if (lastFocus) lastFocus.focus();
  }
  if (lb && fotos.length) {
    document.addEventListener('click', function (e) { var b = e.target.closest('.ph[data-k]'); if (b) abrir(+b.getAttribute('data-k')); });
    $('#lbClose').addEventListener('click', cerrar);
    $('#lbPrev').addEventListener('click', function () { mover(-1); });
    $('#lbNext').addEventListener('click', function () { mover(1); });
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.tagName === 'FIGURE') cerrar(); });
    lb.addEventListener('keydown', function (e) { atrapaFoco(e, lb); });
  }

  addEventListener('keydown', function (e) {
    if (lb && !lb.hidden) {
      if (e.key === 'Escape') cerrar();
      else if (e.key === 'ArrowLeft') mover(-1);
      else if (e.key === 'ArrowRight') mover(1);
      return;
    }
    if (e.key === 'Escape' && menu && !menu.hidden) { setMenu(false); burger.focus(); }
  });

  /* ---------- formulario de presupuesto ---------- */
  var form = $('#form');
  if (form) {
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var note = $('#note'), n = form.nombre.value.trim(), em = form.email.value.trim(), m = form.msg.value.trim();
      function aviso(t, tipo) { note.textContent = t; note.className = 'formnote' + (tipo ? ' on ' + tipo : ''); }
      function marca(campo, mal) { campo.setAttribute('aria-invalid', mal ? 'true' : 'false'); campo.classList.toggle('bad', mal); }
      var malos = [];
      [[form.nombre, !n], [form.email, !em || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)], [form.msg, !m], [form.consentimiento, !form.consentimiento.checked]]
        .forEach(function (c) { marca(c[0], c[1]); if (c[1]) malos.push(c[0]); });
      if (malos.length) {
        malos[0].focus();
        if (!n || !em || !m) return aviso('Faltan el nombre, el email o la descripción de la obra.', 'err');
        if (malos[0] === form.email) return aviso('Revisa el email: no parece válido.', 'err');
        return aviso('Es necesario aceptar la política de privacidad.', 'err');
      }
      if (window.VP && VP.vistaPrevia) return aviso('Vista previa: el envío funcionará cuando la web esté publicada en el servidor.', 'err');
      var btn = form.querySelector('button[type=submit]'); btn.disabled = true; form.classList.add('sending'); aviso('Enviando…');
      var asunto = 'Consulta web: ' + form.tipo.value;
      var cuerpo = 'Nombre y empresa: ' + n + '\nTeléfono: ' + form.tel.value + '\nEmail: ' + em + '\nTipo de trabajo: ' + form.tipo.value + '\n\n' + m;
      fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' } })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
          aviso((res.j && res.j.mensaje) || 'Recibido.', res.ok ? 'ok' : 'err');
          if (res.ok) form.reset();
        })
        .catch(function () {
          /* sin respuesta válida del servidor: se ofrece el correo con la consulta ya escrita */
          location.href = 'mailto:' + ((window.VP && VP.email) || '') + '?subject=' + encodeURIComponent(asunto) + '&body=' + encodeURIComponent(cuerpo);
          aviso('No se ha podido enviar desde la web; abrimos tu programa de correo con la consulta ya escrita.', 'err');
        })
        .then(function () { btn.disabled = false; form.classList.remove('sending'); });
    });
    form.addEventListener('input', function (e) {
      if (e.target.getAttribute('aria-invalid') === 'true') { e.target.setAttribute('aria-invalid', 'false'); e.target.classList.remove('bad'); }
    });
  }
})();
