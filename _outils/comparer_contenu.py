#!/usr/bin/env python3
"""Compare le texte visible et les visuels de deux pages (production / portage)."""
import difflib, re, sys
from html.parser import HTMLParser

class Contenu(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.texte, self.imgs, self.dans = [], [], 0
    def handle_starttag(self, tag, attrs):
        if tag in ('script', 'style'): self.dans += 1
        if tag == 'img':
            src = dict(attrs).get('src') or ''
            self.imgs.append(re.sub(r'\?.*$', '', src.rsplit('/', 1)[-1]))
    def handle_endtag(self, tag):
        if tag in ('script', 'style'): self.dans -= 1
    def handle_data(self, d):
        if not self.dans and d.strip(): self.texte.append(' '.join(d.split()))

def lire(c):
    p = Contenu(); p.feed(open(c, encoding='utf-8', errors='replace').read()); return p

a, b = lire(sys.argv[1]), lire(sys.argv[2])
print(f'texte : {len(a.texte)} / {len(b.texte)} fragments — similarite {difflib.SequenceMatcher(None, a.texte, b.texte).ratio():.1%}')
print(f'visuels : {len(a.imgs)} / {len(b.imgs)} — memes fichiers : {len(set(a.imgs) & set(b.imgs))}')
for l in list(difflib.unified_diff(a.texte, b.texte, 'production', 'portage', n=0, lineterm=''))[:int(sys.argv[3]) if len(sys.argv) > 3 else 30]:
    print(l[:160])
manque = [i for i in a.imgs if i not in b.imgs]
if manque: print('visuels absents du portage :', manque[:8])
