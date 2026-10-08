/* Panel: añadir, ordenar y quitar filas; aviso de cambios sin guardar. */
(function () {
  'use strict';
  var form = document.getElementById('principal');
  var bar = document.querySelector('.savebar'), estado = document.getElementById('dirty');
  var sucio = false, n = 0;

  function marcar() {
    if (sucio) return;
    sucio = true;
    if (bar) bar.classList.add('on');
    if (estado) estado.textContent = 'Hay cambios sin guardar';
  }

  if (form) {
    form.addEventListener('input', marcar);
    form.addEventListener('change', marcar);
    form.addEventListener('submit', function () { sucio = false; });

    form.addEventListener('click', function (e) {
      var b = e.target.closest('button');
      if (!b) return;
      var row = b.closest('.row');
      if (b.hasAttribute('data-mv') && row) {
        var dir = +b.getAttribute('data-mv');
        var otra = dir < 0 ? row.previousElementSibling : row.nextElementSibling;
        if (otra) { dir < 0 ? row.parentNode.insertBefore(row, otra) : row.parentNode.insertBefore(otra, row); marcar(); b.focus(); }
      } else if (b.hasAttribute('data-rm') && row) {
        if (row.getAttribute('data-confirmar') === '1') { row.remove(); marcar(); return; }
        row.setAttribute('data-confirmar', '1');
        b.textContent = '¿Quitar?';
        setTimeout(function () { if (row.isConnected) { row.removeAttribute('data-confirmar'); b.textContent = 'Quitar'; } }, 3000);
      } else if (b.hasAttribute('data-add')) {
        var nombre = b.getAttribute('data-add');
        var tpl = form.querySelector('template[data-tpl="' + nombre + '"]');
        var list = form.querySelector('.list[data-list="' + nombre + '"]');
        if (!tpl || !list) return;
        var html = tpl.innerHTML.replace(/__N__/g, 'n' + Date.now() + (n++));
        list.insertAdjacentHTML('beforeend', html);
        var nueva = list.lastElementChild, campo = nueva && nueva.querySelector('input,textarea');
        if (campo) campo.focus();
        marcar();
      }
    });
  }

  /* Subir fotos recarga el panel: avisa si hay cambios sin guardar */
  document.querySelectorAll('form[data-upload]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (sucio && !window.confirm('Tienes cambios sin guardar en el panel. Si subes ahora la foto, se perderán. ¿Continuar?')) e.preventDefault();
      else sucio = false;
    });
  });
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault(); else sucio = false; });
  });

  window.addEventListener('beforeunload', function (e) { if (sucio) { e.preventDefault(); e.returnValue = ''; } });
})();
