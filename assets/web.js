/* Vaypatel Proyectos — interacción de la web pública.
   Todo el movimiento ligado al scroll pasa por un único manejador sincronizado con el repintado. */
(function () {
  'use strict';
  var doc = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var clamp = function (v, a, b) { return v < a ? a : v > b ? b : v; };
  var smooth = function (a, b, v) { var t = clamp((v - a) / (b - a), 0, 1); return t * t * (3 - 2 * t); };

  /* ---------- raíl de unidades de rack ---------- */
  var rail = $('#rail');
  if (rail) for (var u = 42; u >= 1; u--) { var r = document.createElement('div'); r.className = 'u'; r.textContent = (u < 10 ? '0' : '') + u; rail.appendChild(r); }

  /* ---------- menú móvil ---------- */
  var burger = $('#burger'), menu = $('#menu');
  function setMenu(open) {
    menu.classList.toggle('open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    burger.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    document.body.style.overflow = open ? 'hidden' : '';
    $$('a', menu).forEach(function (a, i) { a.style.transitionDelay = open ? (0.05 + i * 0.045) + 's' : '0s'; });
  }
  if (burger && menu) {
    burger.addEventListener('click', function () { setMenu(!menu.classList.contains('open')); });
    $$('a', menu).forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
  }

  /* ---------- enlace activo según la sección visible ---------- */
  var navLinks = $$('.navlinks a, #menu a[href^="#"]');
  if ('IntersectionObserver' in window) {
    var spy = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        navLinks.forEach(function (a) { a.classList.toggle('active', a.getAttribute('href') === '#' + e.target.id); });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    ['servicios', 'certificaciones', 'obras', 'instalaciones', 'empresa', 'flota', 'contacto'].forEach(function (id) { var el = document.getElementById(id); if (el) spy.observe(el); });
  }

  /* ---------- contadores ---------- */
  function countUp(el) {
    var to = +el.getAttribute('data-to') || 0, t0 = null, dur = 1400;
    if (reduce) return;
    requestAnimationFrame(function step(t) {
      if (!t0) t0 = t;
      var p = Math.min((t - t0) / dur, 1);
      el.textContent = Math.floor(to * (1 - Math.pow(1 - p, 3))).toLocaleString('es-ES');
      if (p < 1) requestAnimationFrame(step); else el.textContent = to.toLocaleString('es-ES');
    });
  }

  /* ---------- aparición al hacer scroll ----------
     Todo es visible por defecto. Solo se oculta lo que está por debajo de la pantalla al cargar,
     y se muestra en cuanto entra en vista. */
  var io = null;
  if (!reduce && 'IntersectionObserver' in window) {
    /* un elemento recortado del todo no cuenta como visible, así que se vigila su contenedor */
    var vigila = new Map();
    io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        (vigila.get(e.target) || []).forEach(function (el) {
          el.classList.remove('pre');
          $$('.count', el).forEach(countUp);
        });
        vigila.delete(e.target);
        io.unobserve(e.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
    var limite = innerHeight * 0.94;
    $$('.reveal, .clip, .sechead').forEach(function (el) {
      if (el.closest('[hidden]')) return;
      if (el.getBoundingClientRect().top <= limite) return;
      var t = el.classList.contains('clip') ? el.parentElement : el;
      el.classList.add('pre');
      if (!vigila.has(t)) { vigila.set(t, []); io.observe(t); }
      vigila.get(t).push(el);
    });
  }

  /* ---------- portada: órbita, foto y titular ligados al scroll ---------- */
  var hero = $('#top'), heroImg = $('#heroImg'), heroDim = $('#heroDim'), cue = $('#cue');
  var orbit = $('#orbit'), thick = $('#orbitThick'), thin = $('#orbitThin'), dot = $('#orbitDot'), glow = $('#orbitGlow');
  var Lthick = thick ? thick.getTotalLength() : 0, Lthin = thin ? thin.getTotalLength() : 0;
  function setArc(path, L, frac) { path.style.strokeDasharray = (L * frac) + ' ' + L; }
  function placeDot(t) {
    var pt = thick.getPointAtLength(Lthick * t);
    dot.setAttribute('cx', pt.x); dot.setAttribute('cy', pt.y);
    glow.setAttribute('cx', pt.x); glow.setAttribute('cy', pt.y);
  }
  var intro = { thick: 0, thin: 0 }, introDone = reduce;

  /* El texto de la portada queda quieto: solo la foto y la órbita reaccionan, con suavidad */
  function heroFrame(p) {
    if (heroImg) heroImg.style.transform = 'translate3d(0,' + (p * 12) + '%,0) scale(' + (1.06 + p * 0.04) + ')';
    if (heroDim) heroDim.style.opacity = (p * 0.5).toFixed(3);
    if (orbit) orbit.style.transform = 'translate3d(0,' + (p * 10) + 'vh,0) rotate(' + (p * 8) + 'deg)';
    if (thick) {
      setArc(thick, Lthick, 0.58 * (introDone ? 1 : intro.thick));
      setArc(thin, Lthin, 0.42 * (introDone ? 1 : intro.thin));
      placeDot((innerWidth < 760 ? 0.21 : 0.31) - p * 0.08);
    }
    if (cue) cue.style.opacity = (1 - p * 5).toFixed(3);
  }

  /* ---------- manejador único de scroll ---------- */
  var nav = $('#nav'), bar = $('#progress'), railDot = $('#railDot'), mesh = $('#bgfx .mesh'), bglow = $('#bgfx .glow');
  var foto = $('#empresaFoto img'), fotoBox = $('#empresaFoto'), ticking = false;
  var wa = $('#wa'), contacto = $('#contacto');

  function paint() {
    ticking = false;
    var y = scrollY, vh = innerHeight, max = doc.scrollHeight - vh, p = max > 0 ? y / max : 0;
    nav.classList.toggle('stuck', y > 40);
    /* WhatsApp: aparece al dejar atrás la portada y se retira en contacto, donde ya está a mano */
    if (wa) {
      var enContacto = contacto && contacto.getBoundingClientRect().top < vh * 0.75;
      wa.classList.toggle('show', y > vh * 0.6 && !enContacto);
    }
    if (bar) bar.style.transform = 'scaleX(' + p + ')';
    if (railDot) railDot.style.transform = 'translateY(' + (p * (vh - 4)) + 'px)';
    if (reduce) return;
    if (mesh) mesh.style.transform = 'translate3d(' + (-p * 90) + 'px,' + (-y * 0.06) + 'px,0)';
    if (bglow) bglow.style.transform = 'translate3d(-50%,' + (y * 0.42 + vh * 0.15) + 'px,0) scale(' + (1 + p * 0.5) + ')';
    if (hero && y < hero.offsetHeight + 50) heroFrame(clamp(y / hero.offsetHeight, 0, 1));
    if (foto && fotoBox) {
      var fr = fotoBox.getBoundingClientRect();
      if (fr.bottom > 0 && fr.top < vh) foto.style.transform = 'translate3d(0,' + ((fr.top + fr.height / 2 - vh / 2) * -0.07) + 'px,0)';
    }
  }
  function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(paint); } }
  function onResize() { onScroll(); }

  addEventListener('scroll', onScroll, { passive: true });
  addEventListener('resize', onResize);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(onResize);

  /* ---------- entrada al cargar ---------- */
  function startIntro() {
    doc.classList.add('ready');
    if (reduce || !thick) { introDone = true; paint(); return; }
    var t0 = null, dur = 1500;
    requestAnimationFrame(function step(t) {
      if (!t0) t0 = t;
      var k = Math.min((t - t0) / dur, 1), e = 1 - Math.pow(1 - k, 3);
      intro.thick = e; intro.thin = smooth(0.15, 1, k);
      if (k < 1) { if (scrollY < innerHeight) heroFrame(clamp(scrollY / Math.max(1, hero.offsetHeight), 0, 1)); requestAnimationFrame(step); }
      else { introDone = true; paint(); }
    });
  }
  paint();
  requestAnimationFrame(function () { requestAnimationFrame(startIntro); });

  /* ---------- galería: filtros, "ver todas" y visor ---------- */
  var gal = $('#gal'), figs = gal ? $$('figure', gal) : [], more = $('#galMore'), filtro = 'todas', abierta = false;
  function aplicarFiltro() {
    figs.forEach(function (f) {
      var ok = filtro === 'todas' ? (abierta || !f.hasAttribute('data-extra')) : f.getAttribute('data-c') === filtro;
      f.hidden = !ok;
    });
    if (more) more.parentNode.hidden = abierta || filtro !== 'todas';
  }
  $$('.filters button').forEach(function (b) {
    b.addEventListener('click', function () {
      filtro = b.getAttribute('data-f');
      $$('.filters button').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      aplicarFiltro();
    });
  });
  if (more) more.addEventListener('click', function () { abierta = true; aplicarFiltro(); });

  var lb = $('#lb'), lbImg = $('#lbImg'), lbCap = $('#lbCap'), lbK = 0, lastFocus = null;
  var fotos = (window.VP && VP.galeria) || [];
  function visibles() { return figs.filter(function (f) { return !f.hidden; }).map(function (f) { return +$('.ph', f).getAttribute('data-k'); }); }
  function mostrar(k) { lbK = k; var f = fotos[k]; if (!f) return; lbImg.src = f.src; lbImg.alt = f.pie; lbCap.textContent = f.pie; }
  function abrir(k) {
    lastFocus = document.activeElement; mostrar(k); lb.hidden = false;
    requestAnimationFrame(function () { lb.classList.add('open'); }); document.body.style.overflow = 'hidden'; $('#lbClose').focus();
  }
  function cerrar() {
    lb.classList.remove('open'); document.body.style.overflow = '';
    setTimeout(function () { lb.hidden = true; }, 300); if (lastFocus) lastFocus.focus();
  }
  function mover(dir) {
    var v = visibles(), i = v.indexOf(lbK);
    if (i < 0) { v = fotos.map(function (_, k) { return k; }); i = lbK; }  /* foto abierta desde el bloque destacado */
    mostrar(v[(i + dir + v.length) % v.length]);
  }
  if (gal && lb) {
    document.addEventListener('click', function (e) { var b = e.target.closest('.ph[data-k]'); if (b) abrir(+b.getAttribute('data-k')); });
    $('#lbClose').addEventListener('click', cerrar);
    $('#lbPrev').addEventListener('click', function () { mover(-1); });
    $('#lbNext').addEventListener('click', function () { mover(1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) cerrar(); });
  }
  addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { if (lb && !lb.hidden) cerrar(); if (menu && menu.classList.contains('open')) setMenu(false); }
    if (lb && !lb.hidden) { if (e.key === 'ArrowLeft') mover(-1); if (e.key === 'ArrowRight') mover(1); }
  });

  /* ---------- formulario de presupuesto ---------- */
  var form = $('#form');
  if (form) form.addEventListener('submit', function (ev) {
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
  if (form) form.addEventListener('input', function (e) { if (e.target.getAttribute('aria-invalid') === 'true') { e.target.setAttribute('aria-invalid', 'false'); e.target.classList.remove('bad'); } });
})();
