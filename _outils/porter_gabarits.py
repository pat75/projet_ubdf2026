#!/usr/bin/env python3
"""
Porte les gabarits Savant des books (2011_html_pages_v2/) en vues Blade.

Portage mecanique et reproductible : le HTML est conserve a l'octet pres,
seul le code PHP est adapte. Voir _doc/11_phase4_books.md, lot 4d.

    python3 _outils/porter_gabarits.py            # tous les themes
    python3 _outils/porter_gabarits.py zoom2016   # un theme

La source est lue en lecture seule. Les vues produites vont dans
resources/views/book/themes/<dossier>/ ; elles sont ensuite retouchees a la
main la ou la conversion ne suffit pas, et ces retouches sont marquees
`{{-- PORTAGE: ... --}}` pour survivre a une relecture.
"""
import re
import sys
from pathlib import Path

SOURCE = Path('/Users/pat/Sites_2019/_projet_ubdf_2020/2011_html_pages_v2')
CIBLE = Path(__file__).resolve().parent.parent / 'resources/views/book/themes'

# dossier source -> dossier cible ; '' = racine (theme classique 2010)
DOSSIERS = {
    '': '_racine',
    'base': 'base', 'slide': 'slide', 'pinter': 'pinter',
    'responsive': 'responsive', 'classique2015': 'classique2015',
    'grid2015': 'grid2015', 'zoom2016': 'zoom2016', 'ultra2020': 'ultra2020',
    'non_diffuse': 'non_diffuse',
}

SUPERGLOBALES = [
    # isset() sur une superglobale : doit preceder les remplacements
    # ci-dessous, isset() n'acceptant pas un appel de fonction.
    (r"isset\(\s*\$_COOKIE\[\s*(['\"][^'\"]+['\"])\s*\]\s*\)", r"(request()->cookie(\1) !== null)"),
    (r"isset\(\s*\$_GET\[\s*(['\"][^'\"]+['\"])\s*\]\s*\)", r"request()->query->has(\1)"),
    (r"isset\(\s*\$_SESSION\[\s*(['\"][^'\"]+['\"])\s*\]\s*\)", "false"),
    (r"\$_SERVER\[\s*['\"](SERVER_NAME|HTTP_HOST)['\"]\s*\]", "request()->getHost()"),
    (r"\$_SERVER\[\s*['\"]PHP_SELF['\"]\s*\]", "request()->getPathInfo()"),
    (r"\$_SERVER\[\s*['\"]REQUEST_URI['\"]\s*\]", "request()->getRequestUri()"),
    (r"\$_SERVER\[\s*['\"]DOCUMENT_ROOT['\"]\s*\]", "public_path()"),
    (r"\$_SERVER\[\s*['\"]HTTP_USER_AGENT['\"]\s*\]", "request()->userAgent()"),
    (r"\$_SERVER\[\s*['\"]SERVER_PROTOCOL['\"]\s*\]", "'HTTP/1.1'"),
    (r"\$_COOKIE\[\s*(['\"][^'\"]+['\"])\s*\]", r"request()->cookie(\1)"),
    (r"\$_GET\[\s*(['\"][^'\"]+['\"])\s*\]", r"request()->query(\1)"),
    (r"\$_REQUEST\[\s*(['\"][^'\"]+['\"])\s*\]", r"request()->input(\1)"),
    (r"\$_SESSION\[\s*(['\"][^'\"]+['\"])\s*\]", "null"),
]


BLOC_PHP = re.compile(r'<\?(?:php\b|=)?(.*?)(?:\?>|\Z)', re.S)


def segments_php(texte: str):
    """Positions (debut, fin) du code PHP, hors HTML et JavaScript."""
    texte = re.sub(r'<\?(?!php|=|xml)', '<?php ', texte)
    return [(m.start(1), m.end(1)) for m in BLOC_PHP.finditer(texte)]


def dans_php(texte: str, fn) -> str:
    """Applique fn au seul code PHP du texte."""
    morceaux, i = [], 0
    for debut, fin in segments_php(texte):
        morceaux.append(texte[i:debut])
        morceaux.append(fn(texte[debut:fin]))
        i = fin
    morceaux.append(texte[i:])
    return ''.join(morceaux)


PHP7 = [
    'count', 'sizeof', 'in_array', 'array_key_exists', 'array_keys', 'array_values',
    'array_merge', 'array_slice', 'array_flip', 'array_reverse', 'array_search',
    'implode', 'array_pop', 'array_shift', 'reset', 'end', 'key', 'current', 'max', 'min',
]


def fonctions_definies(dossier: Path) -> set:
    """Fonctions PHP definies par le theme — pas celles de son JavaScript."""
    noms = set()
    for f in dossier.glob('*.php'):
        texte = re.sub(r'<\?(?!php|=|xml)', '<?php ', f.read_text(encoding='utf-8'))
        for debut, fin in segments_php(texte):
            code = re.sub(r'/\*.*?\*/', '', texte[debut:fin], flags=re.S)
            code = re.sub(r'(//|#)[^\n]*', '', code)
            noms |= set(re.findall(r'\bfunction\s+([A-Za-z_]\w*)\s*\(', code))
    return noms


def convertir(texte: str, prefixe: str, fonctions: set, dossier: str) -> str:
    # 1. Balises courtes <? -> <?php (sauf <?php, <?=, <?xml).
    texte = re.sub(r'<\?(?!php|=|xml)', '<?php ', texte)

    # 1b. Inclusions relatives d'un fichier du theme (hors loadTemplate).
    texte = re.sub(
        r"\b(include|include_once|require|require_once)\s*\(?\s*'([\w.]+\.php)'\s*\)?",
        lambda m: f"{m.group(1)} \\App\\Services\\Book\\Gabarit::chemin('{dossier}/{m.group(2)}')",
        texte,
    )

    # 2. Inclusions Savant -> gabarit Blade compile, meme portee.
    texte = texte.replace('$this->loadTemplate(', '\\App\\Services\\Book\\Gabarit::chemin(')

    # 3. Contexte : $this -> $b.
    texte = re.sub(r'\$this\b', '$b', texte)

    # 4. gettext _() -> __().
    texte = re.sub(r'(?<![\w>$:\\])_\(', '__(', texte)

    # 5. Superglobales et effets de bord.
    for motif, remplacement in SUPERGLOBALES:
        texte = re.sub(motif, remplacement, texte)
    texte = re.sub(r'\bsetcookie\s*\(', lambda m: '\\App\\Services\\Book\\Gabarit::ignorer(', texte)

    # 6. Fonctions PHP locales au theme : prefixees et gardees (un meme
    #    processus peut rendre plusieurs themes, et plusieurs fois). Le
    #    traitement ne touche que le code PHP : les fonctions JavaScript
    #    homonymes restent intactes.
    def fonctions_php(code: str) -> str:
        for nom in sorted(fonctions, key=len, reverse=True):
            nouveau = f'{prefixe}__{nom}'
            code = re.sub(rf'\bfunction\s+{nom}\s*\(', f"function {nouveau}(", code)
            code = re.sub(rf'(?<![\w>$:]){nom}\s*\(', f'{nouveau}(', code)
        return code

    texte = dans_php(texte, fonctions_php)
    texte = extraire_fonctions(texte, EXTRAITES, {f'{prefixe}__{n}' for n in fonctions})

    # 6c. Fonctions dont le comportement a change entre PHP 7 et PHP 8 sur
    #     null ou scalaire (TypeError au lieu d'un avertissement) : redirigees
    #     vers App\\Services\\Book\\Php7, qui rend la valeur de PHP 7.
    def php7(code: str) -> str:
        return re.sub(
            r'(?<![\w>$:\\])(' + '|'.join(PHP7) + r')\s*\(',
            lambda m: f'\\App\\Services\\Book\\Php7::{m.group(1)}(',
            code,
        )

    texte = dans_php(texte, php7)

    # 6b. Mode edition : le legacy l'activait sur un simple cookie pose cote
    #     client (`us_pr`), donc a la portee de n'importe quel visiteur.
    #     Neutralise : l'edition du book releve de l'espace creatif (phase 5).
    texte = re.sub(
        r'\$b->connection_admin_book\s*=\s*true\s*;',
        '$b->connection_admin_book = false; /* PORTAGE : edition en phase 5, voir _doc/11 */',
        texte,
    )

    # 6d. Chemin des visuels de reglage : la classe legacy `user` le
    #     calculait depuis users_2/ ; le contexte le fournit (rep_pref).
    texte = re.sub(
        r"user::usadmin2011_dir_findname\([^)]*\)\s*\.\s*'/cms_pref/'",
        '$b->rep_pref',
        texte,
    )

    # 6e. phpThumb : dimensions libres dans l'URL, ce qui permettait de
    #     faire fabriquer n'importe quelle image. Les trois tailles des
    #     gabarits deviennent des declinaisons nommees (config/images.php).
    texte = re.sub(
        r"(?:'https?://www\.ultra-book\.com'|\$b->url_abs_site\.'/'|\$b->url_abs_site)\s*\.\s*\$b->phpThumb_path\s*\.\s*"
        r"'/phpThumb\.php\?src=(?:https?://www\.ultra-book\.com)?'\s*\.\s*\$b->rep_img(?:_|550)\s*\.\s*"
        r"(\$\w+\['img_fichier'\])\s*\.\s*'&amp;zc=1&amp;w=(\d+)&amp;h=\d+&amp;q=\d+'",
        lambda m: f"'/books/'.$b->us_dir.'/carre_{m.group(2)}/'.{m.group(1)}",
        texte,
    )

    # 6f. Images par defaut : l'URL de production (sans dossier, 404 depuis
    #     longtemps) devient celle du dossier img_default, qui les contient.
    texte = re.sub(r"https?://www\.ultra-book\.com/(ultra-book_default_\w+\.gif)", r"/img_default/\1", texte)

    # 7. Echappements Blade, sur tout le texte (Blade travaille au niveau du
    #    texte, y compris dans les blocs PHP) : @mot et {{ / {!!.
    texte = re.sub(r'@(?=[A-Za-z_])', '@@', texte)
    texte = texte.replace('{{', '@{{').replace('{!!', '@{!!')

    return texte


EXTRAITES = []


def fin_de_bloc(texte: str, j: int) -> int:
    """
    Position apres l'accolade qui ferme le bloc ouvert juste avant j.

    Le corps d'une fonction peut sortir du PHP (`?> <div>… <?php`) : les
    accolades du HTML ou du JavaScript ainsi traverse ne comptent pas.
    """
    profondeur = 1
    while j < len(texte) and profondeur:
        c = texte[j]
        if texte.startswith('?>', j):
            suite = re.compile(r'<\?(php\b|=)?').search(texte, j + 2)
            if not suite:
                return len(texte)
            if suite.group(1) == '=':
                fin = texte.find('?>', suite.end())
                j = fin if fin != -1 else len(texte)
                continue
            j = suite.end()
            continue
        if texte.startswith('//', j) or (c == '#' and texte[j - 1] in ' \t\n'):
            fin = texte.find('\n', j)
            j = fin if fin != -1 else len(texte)
            continue
        if texte.startswith('/*', j):
            fin = texte.find('*/', j + 2)
            j = fin + 2 if fin != -1 else len(texte)
            continue
        if c in '\'"':
            fin = texte.find(c, j + 1)
            while fin != -1 and texte[fin - 1] == '\\':
                fin = texte.find(c, fin + 1)
            j = (fin if fin != -1 else len(texte) - 1) + 1
            continue
        profondeur += (c == '{') - (c == '}')
        j += 1
    return j


def extraire_fonctions(code: str, sortie: list, noms: set) -> str:
    """
    Deplace les definitions de fonctions hors du gabarit.

    PHP « remonte » une fonction definie sans condition au niveau d'un
    fichier : les gabarits en profitent et l'appellent avant sa definition.
    Envelopper la definition dans un `if (! function_exists())` la rend
    conditionnelle et casse ce comportement. Les fonctions sont donc
    rassemblees dans <dossier>/_fonctions.php, charge avant tout rendu.
    """
    if not noms:
        return code
    motif = re.compile(r'\bfunction\s+(' + '|'.join(map(re.escape, noms)) + r')\s*\([^)]*\)\s*\{')
    morceaux, i = [], 0
    for m in motif.finditer(code):
        if m.start() < i:
            continue
        fin = fin_de_bloc(code, m.end())
        morceaux.append(code[i:m.start()])
        sortie.append((m.group(1), code[m.start():fin]))
        i = fin
    morceaux.append(code[i:])
    return ''.join(morceaux)


def fermer_gardes(texte: str) -> str:
    """Ajoute l'accolade fermante des gardes function_exists."""
    sortie, i = [], 0
    ouverture = re.compile(r"if \(! function_exists\('\w+'\)\) \{ function \w+\([^)]*\) \{")
    while True:
        m = ouverture.search(texte, i)
        if not m:
            sortie.append(texte[i:])
            break
        sortie.append(texte[i:m.end()])
        j, profondeur = m.end(), 1
        while j < len(texte) and profondeur:
            c = texte[j]
            # Commentaires : une apostrophe dans « d'apres » ne doit pas
            # ouvrir une chaine.
            if texte.startswith('//', j) or (c == '#' and texte[j - 1] in ' \t\n'):
                fin = texte.find('\n', j)
                j = fin if fin != -1 else len(texte)
                continue
            if texte.startswith('/*', j):
                fin = texte.find('*/', j + 2)
                j = fin + 2 if fin != -1 else len(texte)
                continue
            if c in '\'"':
                fin = texte.find(c, j + 1)
                while fin != -1 and texte[fin - 1] == '\\':
                    fin = texte.find(c, fin + 1)
                j = (fin if fin != -1 else len(texte) - 1) + 1
                continue
            profondeur += (c == '{') - (c == '}')
            j += 1
        sortie.append(texte[m.end():j] + ' }')
        i = j
    return ''.join(sortie)


def porter(dossier_source: str):
    src = SOURCE / dossier_source if dossier_source else SOURCE
    dst = CIBLE / DOSSIERS[dossier_source]
    dst.mkdir(parents=True, exist_ok=True)
    prefixe = DOSSIERS[dossier_source].lstrip('_')
    fonctions = fonctions_definies(src)

    EXTRAITES.clear()
    n = 0
    for f in sorted(src.glob('*.php')):
        if ' ' in f.name or f.name.startswith('.'):
            continue
        nom = re.sub(r'(\.tlp)?\.php$', '', f.name)
        entete = f"{{{{-- Porte depuis 2011_html_pages_v2/{(dossier_source + '/') if dossier_source else ''}{f.name} (_outils/porter_gabarits.py) --}}}}\n"
        (dst / f'{nom}.blade.php').write_text(entete + convertir(f.read_text(encoding='utf-8'), prefixe, fonctions, DOSSIERS[dossier_source]), encoding='utf-8')
        n += 1
    vues = {}
    for nom, corps in EXTRAITES:
        vues.setdefault(nom, corps)   # une definition par nom : la premiere
    contenu = "<?php\n\n// Fonctions des gabarits de ce theme, extraites par _outils/porter_gabarits.py.\n// Chargees par App\\Services\\Book\\Gabarit avant tout rendu.\n\n"
    for nom, corps in vues.items():
        contenu += f"if (! function_exists('{nom}')) {{\n{corps}\n}}\n\n"
    (dst / '_fonctions.php').write_text(contenu, encoding='utf-8')
    print(f'{DOSSIERS[dossier_source]:14} {n:3} gabarits, fonctions prefixees: {sorted(fonctions)}')


if __name__ == '__main__':
    demandes = sys.argv[1:] or list(DOSSIERS)
    for d in demandes:
        porter('' if d in ('_racine', 'racine') else d)
