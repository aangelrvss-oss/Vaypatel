# Fingerprints

Every site you build with **scroll-craft** gets one row here, appended after it
ships. The registry exists so your next build can prove it is a different page
rather than a re-skin of one you already made.

This file is **yours**. It starts empty on purpose: the gate is about not
repeating *yourself*, so it has nothing to say until you have built something.

The rules and the gate live in the skill's
`references/uniqueness.md`. Short version:

**A new build must differ from EVERY row below on at least 4 of the 6
dimensions.** Four against each row individually, not four on average across the
table. If a planned build fails, change the plan. Never edit a row to make room
for it.

The six dimensions are: **grammar**, **nav treatment**, **hero device**,
**act-sequence shape**, **close pattern**, **signature move**.

Dimension 6 is free, because a signature move is unique by definition. So the
gate really asks for three more out of the remaining five, and a build that
changes only grammar and world will fail it.

---

## The registry

| Build | Grammar | Nav treatment | Hero device | Act-sequence shape | Close pattern | Signature move | World | Port |
|---|---|---|---|---|---|---|---|---|
| vaypatel (12/10/2026) | Expediente técnico (nueva: láminas que se dibujan + fichas de datos, cortes duros a hoja clara) | Cajetín fijo: logo grande que se reduce, secciones, teléfono y CTA separados por filetes | Alzado por planos: retícula, lámina con foto real anotada, órbita del logo por detrás y por delante, bandeja en primer plano (pin 1.5, parallax) | pin+parallax > flow > reveal > in > kinetic > pin (pico 3.0) > reveal > pan (2.4) > in > parallax > in · 10 tramos · 18 120 px | Hoja de solicitud: formulario real con cajetín, canales a la vista, sin botón magnético | El peinado: seis cables enredados se peinan con el scroll, se embridan y entran cada uno en su equipo del armario, que se enciende | Dibujo técnico (SVG) + fotos reales enmarcadas | PHP + contenido.json |


---

## What is taken

Add a bullet here whenever a build claims something a later build should avoid
reusing: a grammar, a nav treatment, a close pattern, a signature move, an
act-count-and-length band. The shared columns are what the next build inherits
as a constraint, so writing them down is the whole point.

- Gramática "Expediente técnico" (láminas de dibujo técnico + fichas de datos + cortes a hoja clara).
- Navegación en cajetín de plano.
- Movimiento firma "el peinado" (trazados SVG interpolados del enredo al mazo, bridas y equipos que se encienden).
- Cierre con formulario maquetado como hoja de solicitud.

---

## Appending a row

After shipping, add one line to the table and one bullet to **What is taken** if
the build claimed something new. Fill every column. Say what the build shares
with existing rows.

Rows are append-only. A build that has been superseded stays in the table,
because the space it occupies is still occupied.

---

## Worked example

The skill's author kept a registry of twelve builds across eight page grammars.
If you want to see what a filled-in table looks like, and which shapes tend to
collide, read `EXAMPLES.md` in the scroll-craft repository. Treat it as
illustration only: those rows are somebody else's builds and they do **not**
constrain yours.
