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

## Ver la web en local

    php -S 127.0.0.1:4600 -t .
    # abrir http://127.0.0.1:4600/

Para volver a ver la firma del logo, borrar la clave `vp-firma` del almacenamiento local del
navegador (o abrir una ventana privada).

## Comparar con la versión anterior

La versión anterior está en la etiqueta `pre-rediseno-scrollcraft` (y en la rama
`claude/wizardly-albattani-1k7wgl`, que es lo publicado hoy):

    git worktree add ../vaypatel-antes pre-rediseno-scrollcraft
    php -S 127.0.0.1:4601 -t ../vaypatel-antes
