# Genera incluir/logo.php a partir de img/logo.svg sin alterar ningún trazado.
import re, sys
src, out = sys.argv[1], sys.argv[2]
s = open(src, encoding='utf-8').read()
paths = re.findall(r'<path ([^>]*?)/>', s, re.S)
assert len(paths) == 10, len(paths)
def attr(p, n):
    m = re.search(n + r'="([^"]*)"', p); return m.group(1) if m else None
P = [(attr(p,'d').strip(), attr(p,'fill'), p) for p in paths]
cres, V, A1, A2, Y, Pp, TEL, sub, orb, dot = P
# TEL: un trazado con subtrazados T, E (3 barras), L. Se separan sin cambiar coordenadas.
subs = [x.strip() for x in re.split(r'(?=M )', TEL[0]) if x.strip()]
# último subtrazado es un "M x y" suelto del original; se descarta (no pinta nada)
subs = [x for x in subs if 'L' in x or 'C' in x]
assert len(subs) == 5, subs
T, E1, E2, E3, L = subs
white, steel = V[1], TEL[1]
letters = [(V[0], white), (A1[0], white), (Y[0], white), (Pp[0], white), (A2[0], white), (T, steel), (E1 + ' ' + E2 + ' ' + E3, steel), (L, steel)]
dot_tr = attr(dot[2], 'transform')
L_ = []
L_.append("<?php /* Logo de Vaypatel en línea, generado desde img/logo.svg sin cambiar ningún trazado.")
L_.append("   Las capas (media luna, letras, órbita, punto y subtítulo) van separadas para la firma de entrada.")
L_.append("   Si se cambia img/logo.svg, hay que volver a generar este archivo (ver scrollcraft/builds/vaypatel/README.md). */ ?>")
L_.append('<svg class="lg" viewBox="228 198 395 176" role="img" aria-labelledby="lg-t" focusable="false"><title id="lg-t">Vaypatel · Telecomunicaciones y electricidad</title>')
L_.append('<defs>')
L_.append('<mask id="lg-m-orb" maskUnits="userSpaceOnUse" x="200" y="160" width="460" height="230"><path class="lg-mp lg-mp-orb" pathLength="1" d="M 234.95 263.65 C 352.85 202.65 710.81 188.07 581.95 310.45" fill="none" stroke="#fff" stroke-width="46" stroke-linecap="round"/></mask>')
L_.append('<mask id="lg-m-cre" maskUnits="userSpaceOnUse" x="200" y="160" width="460" height="230"><path class="lg-mp lg-mp-cre" pathLength="1" d="M 235.8 263.2 L 248 255 C 300 222 405 199 448.8 204.8 C 491 210.2 471 235.6 451.6 257.5" fill="none" stroke="#fff" stroke-width="18" stroke-linecap="round"/></mask>')
L_.append('</defs>')
L_.append('<g mask="url(#lg-m-cre)"><path class="lg-cre" fill-rule="evenodd" fill="%s" d="%s"/></g>' % (cres[1], cres[0]))
L_.append('<g class="lg-letras">')
for i, (d, f) in enumerate(letters):
    L_.append('<path class="lg-l" style="--i:%d" fill-rule="nonzero" fill="%s" d="%s"/>' % (i, f, d))
L_.append('</g>')
L_.append('<path class="lg-sub" fill-rule="nonzero" fill="%s" d="%s"/>' % (sub[1], sub[0]))
L_.append('<g mask="url(#lg-m-orb)"><path class="lg-orb" fill-rule="evenodd" fill="%s" d="%s"/></g>' % (orb[1], orb[0]))
L_.append('<g class="lg-dot"><path fill-rule="evenodd" fill="%s" stroke-width="1.42" stroke-linecap="butt" stroke-linejoin="miter" stroke="rgb(0%%, 0%%, 0%%)" stroke-opacity="1" stroke-miterlimit="22.93" d="%s" transform="%s"/></g>' % (dot[1], dot[0], dot_tr))
L_.append('</svg>')
open(out, 'w', encoding='utf-8').write('\n'.join(L_) + '\n')
print('ok', len('\n'.join(L_)))
