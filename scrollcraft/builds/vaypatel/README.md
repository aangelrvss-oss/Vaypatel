# Rediseño "Expediente técnico" · vaypatel.com

Carpeta de trabajo del rediseño hecho con Scroll-craft. **No se publica** (la bloquea
`scrollcraft/.htaccess`; en Nginx, ver INSTRUCCIONES-INFORMATICO.txt).

- `BRIEF.md`: auditoría de partida, decisiones, gramática de página, curva emocional, momento
  central, partitura de dispositivos, contrato de planos de la portada y firma del logo.
- `VERIFICACION.md`: qué se ha probado, con qué resultado y qué queda pendiente.
- `evidencia/`: capturas comprimidas (antes y ahora, firma del logo, peinado, hojas de contactos
  del arnés de Scroll-craft) y los informes JSON del arnés.
- `herramientas/logo_partial.py`: regenera `incluir/logo.php` a partir de `img/logo.svg` sin
  alterar ningún trazado. Uso:

      python3 scrollcraft/builds/vaypatel/herramientas/logo_partial.py img/logo.svg incluir/logo.php

  Si el SVG nuevo no tiene la misma estructura (10 trazados), el script se detiene sin escribir.

## Vista previa en un solo archivo (para el móvil)

Genera un HTML autocontenido (imágenes, fuentes y JS dentro, unos 2,7 MB) en modo vista previa:
el formulario no envía y la página lleva `noindex`.

    php -r 'define("VISTA_PREVIA", true); include "index.php";' > /tmp/render.html
    python3 scrollcraft/builds/vaypatel/herramientas/vista_previa.py /tmp/render.html . vaypatel-vista-previa.html

## Ver la web en local

    php -S 127.0.0.1:4600 -t .
    # abrir http://127.0.0.1:4600/

Para volver a ver la firma del logo, borrar la clave `vp-firma` del almacenamiento local del
navegador (o abrir una ventana privada).

## Comparar con la versión anterior

La versión anterior es la rama `claude/wizardly-albattani-1k7wgl` (commit `9cfe4ae`), que es lo
publicado hoy en www.vaypatel.com:

    git fetch origin claude/wizardly-albattani-1k7wgl
    git worktree add ../vaypatel-antes origin/claude/wizardly-albattani-1k7wgl
    php -S 127.0.0.1:4601 -t ../vaypatel-antes
    # antes: http://127.0.0.1:4601/   ·   ahora: http://127.0.0.1:4600/
