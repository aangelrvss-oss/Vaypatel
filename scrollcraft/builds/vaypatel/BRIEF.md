# BRIEF · vaypatel.com, rediseño con Scroll-craft

> **Escrito por Claude bajo delegación creativa explícita** (encargo "MASTER PROMPT —
> VAYPATEL.COM PREMIUM REDESIGN", 12/10/2026). No hay entrevista: las respuestas
> salen del encargo, de la web publicada y del repositorio. Lo que es **evidencia**
> se marca [E]; lo que es **decisión de autor**, [D].

## 0. Auditoría de partida (resumen)

- [E] Repositorio `aangelrvss-oss/Vaypatel`: `main` solo tenía un README. La web real
  vive en la rama `claude/wizardly-albattani-1k7wgl` (commit `9cfe4ae`): PHP sin base de
  datos, `index.php` pinta `contenido.json`, panel propio en `/admin/`, formulario
  `enviar.php` (Microsoft 365 por SMTP o Graph), 404 propia, aviso legal y privacidad.
- [E] La web publicada coincide con esa rama: el texto visible es idéntico línea a línea
  (366 líneas) y la captura completa de escritorio mide lo mismo (1440 × 11 382 px).
- [E] Identidad: fondo `#04070B`, azul del logo `#00A0E3`, azul acero `#1D6E8F`,
  papel `#EEF4F8`, hoja clara `#E9F0F4`. Chakra Petch (titulares), Barlow (texto),
  IBM Plex Mono (datos y etiquetas). Logo vectorial: letras VAYPA en blanco, TEL en
  acero, una órbita acero, una media luna azul y un punto azul sobre la órbita.
- [E] Reglas del propio cliente (LEEME.txt): solo datos reales; **los proyectos no
  llevan fotos**; las fotos de galería son técnicas y genéricas; sin personas
  reconocibles. El cliente pidió "logo más grande al abrir" (commit `3d33cfb`).
- [E] Diagnóstico visual: web correcta pero de plantilla. Rejillas de tarjetas
  idénticas (6 servicios, 4 acreditaciones, 6 proyectos), etiqueta sobre cada título,
  portada plana (una foto + velo + un arco decorativo), 18 300 px de scroll en móvil.
- [E] Fotos reales disponibles: hero 1200 × 675, empresa 960 × 1280, 23 de galería,
  2 de flota rotulada, Fluke. Tomadas con móvil: sirven como documentación enmarcada,
  no como fondo a sangre en pantallas grandes.

## 1. Las ocho preguntas

1. **Ambiente en pocas palabras.** [D] Precisión, oficio, orden, sala técnica a media
   luz. Referencias: un plano de instalaciones con su cajetín; un rack bien peinado; el
   informe de certificación de un Fluke.
2. **Recorrido.** [D] Quién es y qué instala → por qué creerle (cifras reales y
   empresas) → qué instala exactamente → el momento: todo llega al mismo armario y se
   entrega ordenado → cómo lo demuestra (medición y acreditaciones) → dónde lo ha hecho
   (proyectos) → cómo queda (fotos reales) → quién lo hace (equipo y medios propios) →
   pedir presupuesto.
3. **Curva de energía.** [D] Tranquila y segura al abrir; sube en servicios; silencio
   antes del pico; pico en "el peinado"; vuelve a la calma documental; cierre firme.
4. **Sentimiento por tramo y momento a recordar.** Ver §4 y §5.
5. **Algo que no haga ninguna otra web.** [D] Que el visitante peine con el scroll una
   maraña de cables hasta dejarla como un rack terminado, cada cable en su puerto.
6. **Distancia del premium-minimal.** [D] Editorial técnico y denso en datos, no
   minimalismo de lujo. Oscuro porque la marca ya es oscura, con hojas claras donde el
   contenido es "documento".
7. **¿Un mundo continuo o escenas?** [D] Escenas. Una página de capítulos con cortes
   de fondo, no un vuelo de cámara.
8. **Recursos.** [E] Logo SVG, tipografías propias, 23 fotos de galería, flota, Fluke,
   foto de portada y de empresa. Nada de vídeo. [D] No se genera imagen ni vídeo de pago:
   el mundo es **dibujo técnico** (worlds.md §8) hecho en SVG, y las fotos reales se
   muestran enmarcadas como documentación.

## 2. Lo que debe creer el visitante y lo que debe hacer

- Creer: *"Si se lo encargo a Vaypatel, me lo entregan terminado, ordenado, medido y
  documentado por su propio equipo."* (Todo eso está afirmado en la web actual.)
- Hacer: **Pedir presupuesto**. Una sola etiqueta en toda la página. Teléfono y
  WhatsApp como vías secundarias, sin renombrar el CTA.

## 3. Gramática de página

**Nueva gramática: "Expediente técnico".** Las otras siete no encajan:

| Gramática | Por qué no |
|---|---|
| Filmic one-shot | No hay metraje ni un argumento único de consumo; prohíbe la navegación por secciones que un cliente B2B necesita |
| Chaptered editorial | Prohíbe la barra fija; aquí el teléfono y el CTA deben estar siempre a mano |
| Live surface | No hay producto de software que operar |
| Continuous world | No es un viaje por un lugar; exige vuelo de cámara continuo |
| Typographic poster | El contenido es denso en datos verificables, no una frase |
| Gallery / catalog | Solo una parte (proyectos) es catálogo |
| Split stage | El antes/después vale para una escena, no para la página entera |
| Rhythmic cutlist | Ritmo de marca de consumo, impropio de una instaladora B2B |

**Definición.**
- *Encaja con:* servicios técnicos B2B cuya confianza nace de datos comprobables
  (registros, normas, equipos de medida, cantidades instaladas).
- *El scroll se siente como:* leer un expediente de obra que se dibuja y se anota solo:
  láminas de dibujo técnico que se trazan y fichas de datos que se rellenan.
- *Navegación:* barra fija tipo **cajetín**: logo, nombre de la sección en la que estás
  (sin números), teléfono y "Pedir presupuesto". Grande al abrir, compacta al bajar.
- *Hero:* un **alzado por planos**: retícula de plano, la foto real enmarcada como lámina
  anotada, la órbita del logo rodeándola por delante y por detrás, y una bandeja de
  cable en primer plano.
- *Secuencia:* alterna láminas de dibujo (pinned, el trazo avanza con el scroll) y
  fichas de datos (flujo normal, compactas).
- *Cierre:* **hoja de solicitud**: el formulario es el final, maquetado como una hoja de
  pedido; no hay botón magnético ni foco que siga al ratón.
- *Prohíbe:* fotografía a sangre bajo texto (las fotos siempre van enmarcadas y con pie);
  vídeo scrub; `spotlight` y `magnet`; `drift` en degradado (los fondos cambian con
  corte limpio entre plano oscuro y hoja clara); contadores; kinetic por caracteres;
  numeración de secciones; etiqueta sobre cada título.
- *Se apoya en:* trazo SVG gobernado por `--sc-p` (propio de la página), `pin`, `pan`,
  `parallax` solo en el hero, `reveal` en cambios de estado, `kinetic` por líneas una vez.

## 4. Curva emocional (escrita antes de los actos)

| # | Tramo | Emoción | Qué la provoca |
|---|---|---|---|
| 0 | Firma | Expectación breve | La órbita del logo se tiende, el punto la recorre y las letras se conectan |
| 1 | Hero | Confianza | El alzado con profundidad real: la lámina, la órbita y la bandeja se separan al bajar |
| 2 | Credenciales | Reconocimiento | Cifras reales acotadas con cotas de plano y las empresas para las que han trabajado |
| 3 | Servicios | Comprensión | Seis esquemas originales que se trazan solos, uno por sistema |
| 4 | Pausa | Anticipación (silencio autorado) | Una sola frase en dos líneas sobre fondo vacío |
| 5 | **Pico** | **Asombro y alivio** | **El peinado**: seis cables enredados se ordenan, se embridan y entran cada uno en su puerto, que se enciende |
| 6 | Método | Seguridad | Hoja clara de documento: medición Fluke y acreditaciones con sus números reales |
| 7 | Proyectos | Amplitud | Las obras recorren la pantalla en horizontal como fichas de catálogo; después, el listado completo |
| 8 | Trabajo terminado | Evidencia | Fotos reales enmarcadas, filtros y visor |
| 9 | Empresa y medios | Cercanía | Hoja clara: equipo propio, nave en Rivas, furgonetas rotuladas |
| 10 | Contacto | Resolución | La hoja de solicitud, con todos los canales a la vista |

No hay dos tramos contiguos con la misma emoción.

## 5. El pico

> *"Bajas y una maraña de cables se va peinando sola hasta quedar como un rack
> terminado: cada cable entra en su puerto y se enciende."*

- Vive en el tramo 5 (`#metodo-peinado`), el de mayor recorrido de la página
  (`data-sc-span="3"` en escritorio, frente a 2.4 del siguiente más largo; en la primera
  versión eran 3.4 y 2.6 y se recortaron tras la verificación).
- Lo precede un tramo de silencio (4), que es intencionado y no scroll muerto.
- Es conceptual, no una instalación real: es un diagrama y no lleva foto ni cliente.
- Su texto sale de afirmaciones que ya están en la web: equipo propio, cableado
  organizado por colores, mazos con velcro, enlaces medidos y documentados.

**Frase para contarlo:** *"Es la web donde peinas un rack con el scroll."*

**Movimiento firma (bespoke):** el peinado. Interpolación de seis trazados SVG desde
un estado enredado a un mazo paralelo, con bridas que aparecen en los puntos de
peinado y puertos que se encienden en secuencia. Escrito en `assets/web.js`, leyendo
`--sc-p` del acto. El motor no se toca.

## 6. Partitura (dispositivo por tramo)

| Tramo | Dispositivo | Por qué este |
|---|---|---|
| 0 Firma | Animación de entrada propia (CSS + SVG del logo) | Es la marca, no el scroll |
| 1 Hero | `pin` corto (1.5) + **parallax** por planos desde `--sc-p` | La profundidad se percibe por velocidad diferencial |
| 2 Credenciales | **flow + in** con cotas que se trazan | Datos: compacto, sin secuestrar el scroll |
| 3 Servicios | **reveal** (el esquema se dibuja) + in | Cada esquema "pasa a existir": cambio de estado |
| 4 Pausa | **kinetic** por líneas, flow | Una frase que se monta sola: la pausa antes del pico |
| 5 Pico | **pin** (3.0) + peinado bespoke | Argumento visual: el marco se queda, el estado avanza |
| 6 Método | flow + in (hoja clara, corte de fondo) | Documento: se lee, no se mira |
| 7 Proyectos | **pan** (2.4) | Lateral se lee como amplitud y catálogo |
| 8 Trabajo terminado | flow + in | Evidencia fotográfica, interacción de filtros y visor |
| 9 Empresa y medios | flow + parallax suave en la foto | Cercanía sin espectáculo |
| 10 Contacto | flow, final que se sostiene | El cierre es un formulario real |

Familias distintas: parallax, flow/in, reveal, kinetic, pin, pan (6). Ninguna se
repite en dos tramos seguidos. Cero `scrub`. Recorrido total de los actos fijados en
escritorio: 1.5 + 3.0 + 2.4 = 6.9 alturas de pantalla, más las secciones en flujo. En móvil la
portada fluye (no se fija) y con movimiento reducido no se fija nada.

## 7. Contrato de planos del hero

| Plano | Recurso | Movimiento | Regla de contacto u oclusión |
|---|---|---|---|
| Fondo | Retícula de plano y esquema de montantes de un edificio (SVG) | El que menos se desplaza (−0.35×) | Nunca compite con el titular: 8–12 % de opacidad |
| Medio | Foto real `img/hero.jpg` enmarcada como lámina, con cotas y llamadas que describen lo que se ve | Sube y escala algo (−0.6×, 1 → 1.04) | Se queda dentro de su marco; las llamadas viajan con ella |
| Foco | La órbita del logo (su forma real, ampliada) rodeando la lámina | Gira unos grados y el punto avanza sobre ella | La mitad trasera queda **detrás** de la lámina y la delantera **delante**: oclusión real |
| Primer plano | Bandeja de rejilla con mazo de cable (dibujo de línea, más grande y algo difuminada) | La que más se desplaza (+1.2×) | Entra por abajo a la derecha y no tapa el CTA |
| Atmósfera | Luz azul muy tenue y grano | Lenta e independiente | Solo separa planos; nada de velo a pantalla completa |
| Texto y controles | `h1`, subtítulo, 2 botones, sellos (HTML) | Fijo a 1× | Siempre legible; el CTA se ve sin bajar |

- Apertura: el titular a la izquierda y la lámina anotada a la derecha, abrazada por la
  órbita.
- Punto medio: la bandeja ha pasado por delante, la lámina sube y la órbita gira; el
  punto ha recorrido la mitad del arco.
- Salida: la órbita se cierra sobre la lámina y la escena entrega a la franja de
  credenciales con corte limpio.
- Móvil: el titular arriba (por encima de todo), la lámina debajo con `hero-movil.jpg`
  recortada en vertical, la órbita más cerrada, la bandeja más pequeña; pin más corto.
- Movimiento reducido: la misma composición estática con sus planos (la profundidad se
  mantiene por oclusión y escala), sin espacio de scroll extra.

## 8. Firma de logo

- Se construye con **los trazados reales de `img/logo.svg`**, sin redibujar la marca.
- Secuencia (≈1,8 s la primera visita):
  1. Fondo `#04070B`. La órbita acero se "tiende" desde su extremo izquierdo: una
     máscara recorre su línea media, así que lo que aparece es su relleno original.
  2. El punto azul viaja por delante de la máscara y se detiene en su posición exacta.
  3. La media luna azul se traza en el mismo sentido, más rápida.
  4. Las letras se "conexionan": cada una sube 6 px desde una línea base, de izquierda a
     derecha (VAYPA en blanco, TEL en acero), con 45 ms de escalonado.
  5. El subtítulo TELECOMUNICACIONES Y ELECTRICIDAD aparece con un barrido horizontal.
  6. El logo vuela a su sitio en la cabecera (FLIP medido) mientras el velo se abre sobre
     el hero, que ya está compuesto debajo.
- No bloquea: la capa no recibe eventos, y cualquier tecla, rueda, toque o clic la
  termina al instante. El `h1` y el contenido están en el HTML desde el principio.
- Visitas siguientes (7 días, `localStorage`): sin capa; el logo de la cabecera solo
  traza su órbita en 0,6 s.
- `prefers-reduced-motion`, sin JS o si falla: no hay capa; el logo aparece directamente.
- Sin saltos de maquetación: la capa es `position: fixed` y la cabecera reserva su
  tamaño desde el primer pintado.

## 9. Recursos, dependencias y riesgos

- Recursos nuevos: solo SVG propio (esquemas, peinado, retícula, bandeja). Coste 0 €.
  Sin generación de imagen ni vídeo, sin servicios ni claves.
- Dependencia nueva: el motor Scroll-craft (MIT), copiado **sin modificar** en
  `assets/scrollcraft.js` y `assets/scrollcraft.css`. Sin frameworks ni CDN.
- Riesgos:
  - El panel `/admin/` debe seguir funcionando: se respetan todas las claves de
    `contenido.json` y `index.php` sigue leyéndolas.
  - El contador del motor formatea con coma ("5,200"): no se usa; cifras estáticas.
  - Las fotos son de móvil: siempre enmarcadas, nunca a sangre.
  - El pico en móvil debe recomponerse en vertical, no encogerse.
  - El servidor real (Apache, LiteSpeed o Nginx) no se puede probar aquí: se prueba con
    `php -S`, con el envío de correo sustituido por un registro local.

## 10. Plan de verificación

- Servir con `php -S` el `index.php` real.
- `shoot.mjs` de Scroll-craft: escritorio 1440 × 900, móvil 390 × 844, compacto
  360 × 640 y movimiento reducido. Leer las hojas de contactos.
- Script propio: fotogramas de la firma del logo, estados del hero y del pico, overflow
  del rail de proyectos, desbordamiento horizontal, consola y recursos fallidos, orden
  del foco, menú móvil, visor y filtros, formulario (validación, envío AJAX real contra
  `enviar.php` con correo simulado, y envío sin JavaScript).
- Comparar antes y después con las capturas de la versión actual.

## Comprobación de sensaciones (tras construir)

Recorrida la página y leídas las hojas de contactos del arnés, la curva percibida coincide con la
prevista tramo a tramo. No fue "en frío": la hizo el mismo autor. El pico es el mayor cambio visual
y el tramo más largo; lo precede la pausa; el cierre se sostiene. Lo que cambió: la página se hacía
larga y se recortaron el peinado (3.4 → 3.0), el catálogo (2.6 → 2.4) y la pausa (88 → 74 svh).

## Silencio autorado

El tramo 4 (la pausa) es una pantalla casi vacía con una sola frase. Es intencionado.
