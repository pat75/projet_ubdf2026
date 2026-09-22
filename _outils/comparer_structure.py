#!/usr/bin/env python3
"""
Compare la structure de deux pages HTML (production legacy / portage).

Reduit chaque page a la suite de ses balises avec id et classes, ignore le
texte et les valeurs d'attributs variables (src, href, style), puis affiche
les differences. Sert a verifier qu'un gabarit porte produit le meme DOM
que le legacy.

    python3 _outils/comparer_structure.py prod.html port.html
"""
import difflib
import re
import sys
from html.parser import HTMLParser


class Squelette(HTMLParser):
    IGNORES = {'script', 'style', 'meta', 'link', 'br', 'noscript'}

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.lignes, self.profondeur, self.dans = [], 0, None

    def handle_starttag(self, tag, attrs):
        if self.dans:
            return
        if tag in ('script', 'style'):
            self.dans = tag
        if tag in self.IGNORES:
            return
        a = dict(attrs)
        ident = f"#{a['id']}" if a.get('id') else ''
        classes = '.'.join(sorted(c for c in (a.get('class') or '').split() if c))
        self.lignes.append('  ' * min(self.profondeur, 30) + tag + ident + (('.' + classes) if classes else ''))
        if tag not in ('img', 'input', 'hr', 'source', 'area', 'col', 'embed', 'param', 'track', 'wbr'):
            self.profondeur += 1

    def handle_endtag(self, tag):
        if self.dans:
            if tag == self.dans:
                self.dans = None
            return
        if tag in self.IGNORES:
            return
        self.profondeur = max(0, self.profondeur - 1)


def squelette(chemin):
    p = Squelette()
    p.feed(open(chemin, encoding='utf-8', errors='replace').read())
    return p.lignes


if __name__ == '__main__':
    a, b = squelette(sys.argv[1]), squelette(sys.argv[2])
    diff = list(difflib.unified_diff(a, b, 'production', 'portage', n=1, lineterm=''))
    ratio = difflib.SequenceMatcher(None, a, b).ratio()
    print(f'balises : production {len(a)}, portage {len(b)} — similarite {ratio:.1%}')
    print('\n'.join(diff[:int(sys.argv[3]) if len(sys.argv) > 3 else 60]))
