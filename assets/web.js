/* Vaypatel Proyectos — interacción de la web pública.
   Módulos: menú móvil · cabecera y WhatsApp · enlace activo · aparición al hacer scroll · visor de fotos · formulario.
   Sin dependencias. Todo el contenido es visible aunque este archivo no llegue a cargarse. */
(function () {
  'use strict';
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var FOCUSABLE = 'a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])';

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
  var grupo = { acreditaciones: 'servicios', instalaciones: 'proyectos', flota: 'empresa' }; // secciones sin enlace propio en la cabecera
  if ('IntersectionObserver' in window) {
    var spy = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        var id = e.target.id, cab = grupo[id] || id;
        navLinks.forEach(function (a) {
          var h = a.getAttribute('href').slice(1);
          a.classList.toggle('active', a.closest('#menu') ? h === id : h === cab);
        });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    ['servicios', 'acreditaciones', 'proyectos', 'instalaciones', 'empresa', 'flota', 'contacto'].forEach(function (id) { var el = document.getElementById(id); if (el) spy.observe(el); });
  }

  /* ---------- aparición al hacer scroll ----------
     Todo es visible por defecto. Solo se oculta lo que está por debajo de la pantalla al cargar. */
  if (!reduce && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.remove('pre'); io.unobserve(e.target); } });
    }, { threshold: 0.1, rootMargin: '0px 0px -5% 0px' });
    /* primero se leen todas las posiciones y después se escriben las clases (evita recalcular el diseño en bucle) */
    var limite = innerHeight * 0.95;
    $$('.reveal').filter(function (el) { return el.getBoundingClientRect().top > limite; })
      .forEach(function (el) { el.classList.add('pre'); io.observe(el); });
  }

  /* ---------- visor de fotos ---------- */
  var lb = $('#lb'), lbCap = $('#lbCap'), lbK = 0, lastFocus = null;
  var lbImg = document.createElement('img');  /* la imagen se crea aquí para no dejar un <img> vacío en el HTML */
  lbImg.id = 'lbImg'; lbImg.alt = '';
  if (lbCap) lbCap.parentNode.insertBefore(lbImg, lbCap);
  var fotos = (window.VP && VP.galeria) || [];
  function mostrar(k) { lbK = (k + fotos.length) % fotos.length; var f = fotos[lbK]; if (!f) return; lbImg.src = f.src; lbImg.alt = f.pie; lbCap.textContent = f.pie; }
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
    $('#lbPrev').addEventListener('click', function () { mostrar(lbK - 1); });
    $('#lbNext').addEventListener('click', function () { mostrar(lbK + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.tagName === 'FIGURE') cerrar(); });
    lb.addEventListener('keydown', function (e) { atrapaFoco(e, lb); });
  }

  addEventListener('keydown', function (e) {
    if (lb && !lb.hidden) {
      if (e.key === 'Escape') cerrar();
      else if (e.key === 'ArrowLeft') mostrar(lbK - 1);
      else if (e.key === 'ArrowRight') mostrar(lbK + 1);
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
