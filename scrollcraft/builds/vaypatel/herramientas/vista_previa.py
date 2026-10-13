# Convierte el index.php renderizado en un único HTML autocontenido para verlo en el móvil.
import re, sys, base64, os, mimetypes
src, raiz, out = sys.argv[1], sys.argv[2], sys.argv[3]
h = open(src, encoding='utf-8').read()
MIME = {'.webp': 'image/webp', '.jpg': 'image/jpeg', '.png': 'image/png', '.svg': 'image/svg+xml', '.woff2': 'font/woff2'}
cache = {}
def data(p):
    p = p.split('?')[0]
    if p not in cache:
        b = open(os.path.join(raiz, p), 'rb').read()
        cache[p] = 'data:%s;base64,%s' % (MIME[os.path.splitext(p)[1]], base64.b64encode(b).decode())
    return cache[p]

# 1. <picture>: una sola imagen por foto (la WebP); se conservan solo las fuentes con media (portada móvil)
def pic(m):
    blk = m.group(0)
    sources = re.findall(r'<source [^>]*>', blk)
    img = re.search(r'<img [^>]*>', blk).group(0)
    webp = None; keep = []
    for s in sources:
        ss = re.search(r'srcset="([^"]+)"', s).group(1)
        url = ss.split(',')[-1].strip().split(' ')[0]   # la mayor
        if 'media=' in s:
            if 'image/webp' in s: keep.append('<source media="%s" srcset="%s">' % (re.search(r'media="([^"]+)"', s).group(1), url))
        elif 'image/webp' in s: webp = url
    if webp: img = re.sub(r'src="[^"]+"', 'src="%s"' % webp, img, count=1)
    return '<picture>' + ''.join(keep) + img + '</picture>'
h = re.sub(r'<picture>.*?</picture>', pic, h, flags=re.S)

# 2. fuera: precargas (no aplican con datos en línea)
h = re.sub(r'<link rel="preload"[^>]*>\s*', '', h)
h = re.sub(r'<link rel="apple-touch-icon"[^>]*>\s*', '', h)
h = re.sub(r'<link rel="icon" type="image/png"[^>]*>\s*', '', h)

# 3. scripts en línea, en el mismo orden
def scr(m):
    js = open(os.path.join(raiz, m.group(1).split('?')[0]), encoding='utf-8').read()
    assert '</script' not in js.lower()
    return '<script>\n' + js + '\n</script>'
h = re.sub(r'<script src="(assets/[^"]+)" defer></script>', scr, h)

# 4. la galería del visor reutiliza las imágenes de la página (no se duplican)
h = re.sub(r'"src":"img/galeria/[^"]+"', '"src":""', h)
h = h.replace('<script>\n/* Vaypatel Proyectos — interacción', '<script>(window.VP&&VP.galeria||[]).forEach(function(g,k){var i=document.querySelector(\'.ph[data-k="\'+k+\'"] img\');if(i)g.src=i.getAttribute("src");});</script>\n<script>\n/* Vaypatel Proyectos — interacción', 1)

# 5. el resto de rutas (src, srcset, url() de las fuentes, favicon) → datos en línea
h = re.sub(r'url\((assets/fuentes/[^)]+)\)', lambda m: 'url(%s)' % data(m.group(1)), h)
h = re.sub(r'(src|href)="((?:img|assets)/[^"]+)"', lambda m: '%s="%s"' % (m.group(1), data(m.group(2))), h)
h = re.sub(r'srcset="((?:img|assets)/[^" ,]+)[^"]*"', lambda m: 'srcset="%s"' % data(m.group(1)), h)
h = re.sub(r' sizes="[^"]*"', '', h)

# 6. páginas legales: las publicadas
for f in ('aviso-legal.html', 'privacidad.html'):
    h = h.replace('href="%s"' % f, 'href="https://www.vaypatel.com/%s"' % f)

# 7. marcar la copia como vista previa
h = h.replace('<title>', '<meta name="robots" content="noindex, nofollow">\n<title>Vista previa · ', 1)

restos = re.findall(r'(?:src|href|srcset)="(?:img|assets)/[^"]*"', h)
assert not restos, restos[:5]
open(out, 'w', encoding='utf-8').write(h)
print('ok', len(h.encode()) // 1024, 'kB ·', len(cache), 'recursos en línea')
