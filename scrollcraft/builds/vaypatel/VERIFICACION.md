# Verificación · rediseño vaypatel.com (12/10/2026)

Todo lo de este documento se ha ejecutado de verdad sobre el `index.php` real servido con
`php -S` (PHP 8.3.6) y Chromium 141 (Playwright 1.56, `/opt/pw-browsers/chromium-1194`).
Las capturas de esta carpeta (`evidencia/`) son una selección comprimida de esas pruebas.

## Entorno y limitaciones

- vaypatel.com está bloqueado por la política de red de esta sesión. La web publicada se leyó
  con Firecrawl (HTML, texto, captura completa). Las fotos y el SVG del logo salen del
  repositorio, rama `claude/wizardly-albattani-1k7wgl`, que es lo publicado: el texto visible
  coincide línea a línea (366 líneas) y la captura de escritorio mide igual (1440 × 11 382 px).
- No se ha probado en un teléfono real (iOS/Android), ni en Safari o Firefox: solo Chromium.
- No se ha probado en el servidor real (Apache/LiteSpeed/Nginx de producción) ni el envío real
  por Microsoft 365: el correo se sustituyó por un `sendmail` falso que guarda el mensaje.
- Las métricas de rendimiento son de laboratorio (servidor local, CPU ×4 y red tipo 4G
  emulados). No son Core Web Vitals de usuarios reales.
- La comprobación de sensaciones (feel check) la hizo el mismo autor, así que no es "en frío".

## Arnés de Scroll-craft (`scripts/shoot.mjs`), versión final

| Perfil | Alto de página | Tramo muerto | Contraste sobre fondo compuesto | Consola / peticiones fallidas |
|---|---|---|---|---|
| Escritorio 1440 × 900 | 18 120 px (20,1 pantallas) | ninguno | todas las líneas ≥ 4,5:1 en su peor fotograma | 0 / 0 |
| Móvil 390 × 844 | 22 520 px | ninguno | ≥ 4,5:1 | 0 / 0 |
| Compacto 360 × 640 | 21 506 px | ninguno | ≥ 4,5:1 | 0 / 0 |
| Movimiento reducido 1440 × 900 | 15 518 px | ninguno | ≥ 4,5:1 | 0 / 0 |

Hojas de contactos: `evidencia/arnes-*.jpg`; informes completos: `evidencia/arnes-*.json`.

**Fallos encontrados por el camino y su corrección** (el resultado anterior queda superado por la
tabla de arriba):

1. La portada no se fijaba: `.hero-stage{position:relative}` anulaba el `sticky` del motor (aviso
   del propio motor). Corregido con `:where()`.
2. La órbita del logo no rodeaba la lámina: el `svg{max-width:100%}` del motor la encogía. Corregido
   con `max-width:none` en los planos.
3. El titular tocaba la lámina a 1440 px y la órbita la cruzaba en diagonal. Nuevo tamaño de `h1`,
   órbita con radio vertical mayor y composición apilada hasta 1023 px.
4. Las etiquetas de los equipos del armario se salían de su hueco. Etiquetas más cortas y una
   versión propia para móvil.
5. En acreditaciones, la referencia (`nowrap`) estrechaba el texto hasta partir palabra por
   palabra. La referencia pasa debajo del texto.
6. Llamadas de la foto de portada solapadas entre sí y con la órbita. Quedan dos, arriba, en dos
   alturas; en el teléfono, una.
7. Contraste 2,03:1 con movimiento reducido: era una línea pasando bajo la cabecera fija medida
   sobre el logo. Se marca la cabecera como cromo fijo (`data-sc-shot-fixed`) y desaparece.
8. Con movimiento reducido el catálogo de proyectos quedaba fijado 2,4 pantallas sin moverse (el
   arnés no lo detecta). Con movimiento reducido ahora es una cuadrícula en flujo normal.
9. Artefacto en la firma del logo: un punto azul en la punta derecha de la órbita en los primeros
   fotogramas (el remate redondeado del siguiente guion). `stroke-dasharray: 1 2`.
10. LCP retrasado por la firma: el titular y la foto estaban a opacidad 0 bajo el velo. Ahora están
   pintados desde el primer fotograma y solo se asienta su posición.

## Pruebas funcionales (`lab/func.mjs`), 29/29 correctas

- Formulario: vacío, email incorrecto y privacidad sin marcar muestran su error, marcan
  `aria-invalid` y llevan el foco al primer campo con error.
- Envío AJAX real a `enviar.php`: 200 y "Recibido…", el formulario se vacía y el mensaje llega al
  `sendmail` (destino `a.arribas@vaypatel.com`, remitente `web@vaypatel.com`, Reply-To del cliente).
- Segundo envío inmediato: 429 "Espera unos segundos…" (límite de 30 s del servidor).
- Sin JavaScript: todo el contenido animado se ve (38 elementos a opacidad 1), no hay velo, el
  formulario hace POST, vuelve a `/?envio=ok#contacto` con el mensaje y los datos no van en la URL.
- Galería: 6 fotos al entrar, filtro Electricidad (6), "Ver todas" (21), visor con flechas y Escape,
  el foco vuelve a la foto.
- Teclado: los 26 primeros focos (salto al contenido, logo, menú, teléfono, CTA, portada, banda de
  contacto, galería y filtros, contacto) están en pantalla, opacos y con anillo de foco.
- Móvil: menú (abre, enfoca, Escape cierra, el enlace lleva a su sección), lista de proyectos
  plegada en 4 y desplegable, áreas táctiles ≥ 40 px, sin desbordamiento horizontal.
- Si `scrollcraft.js` o `web.js` no cargan: a los 4 s la página muestra todo (`sin-motor`) y el velo
  de la firma desaparece.

## Accesibilidad (axe-core 4.10.2)

0 incidencias en escritorio, móvil y movimiento reducido, tras recorrer toda la página. La única
que había (un `<aside>` dentro de otro punto de referencia, heredado de la versión anterior) se
corrigió.

## SEO (comprobado en el HTML servido)

Mismo `title`, `description`, `canonical`, Open Graph y Twitter. Un solo `h1` y jerarquía de
encabezados ordenada. JSON-LD `LocalBusiness` válido con los 6 servicios. 29 imágenes, todas con
`alt`. Sin `id` duplicados. `robots.txt`, `sitemap.xml`, `aviso-legal.html`, `privacidad.html` y
`404.html` sin cambios. No se afirma ninguna mejora de posicionamiento.

## Rendimiento de laboratorio (`lab/perf.mjs`)

| | LCP (antes del scroll) | CLS | Tareas largas al recorrer la página | Transferido |
|---|---|---|---|---|
| Antes · escritorio | 268 ms | 0,0026 | 1 (73 ms) | 985 kB |
| Ahora · escritorio, 1ª visita | 448 ms | 0 | 2 (209 ms) | 1 002 kB |
| Antes · móvil lento | 1 424 ms | 0,0001 | 8 (482 ms) | 800 kB |
| Ahora · móvil lento, 1ª visita | 1 204 ms | 0 | 11 (569 ms) | 953 kB |
| Ahora · móvil lento, visita repetida | 1 336 ms | 0 | 10 (302 ms) | 953 kB |

HTML: 162 kB (43,6 kB con gzip) frente a 92 kB (21,6 kB con gzip): lleva en línea el logo para la
firma, el CSS del motor y los esquemas SVG. El motor `scrollcraft.js` pesa 62 kB sin comprimir.

## Comprobación de sensaciones

Curva prevista (BRIEF.md §4) frente a lo que se percibe en las hojas de contactos y recorriendo la
página: coinciden tramo a tramo. El peinado es el mayor cambio visual de la hoja y el tramo más
largo (3,0 pantallas frente a 2,4 del siguiente). Lo precede la pausa, que es silencio autorado.
El cierre se sostiene: el formulario y los canales quedan en pantalla. Cambios hechos tras la
comprobación: recorridos más cortos (peinado de 3,4 a 3,0, catálogo de 2,6 a 2,4 y pausa de 88 a
74 svh), porque la primera versión se hacía larga.

## Pendiente

- Probar en iPhone y Android reales (scroll táctil, rendimiento del peinado en gama baja).
- Probar en Safari y Firefox de escritorio.
- Publicar en un entorno de pruebas del hosting real y enviar una consulta de verdad.
- Medir Core Web Vitals reales tras publicar (Search Console o PageSpeed Insights).
