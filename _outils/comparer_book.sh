#!/bin/bash
# Compare les pages d'un book entre la production et le portage local.
#   _outils/comparer_book.sh <login> [dossier_de_travail]
# Pages : accueil, portfolio, actualites, contact, et la premiere galerie
# (-p) et la premiere page (-r…-c…) liees depuis l'accueil de production.
L=$1; W=${2:-/tmp/comparer_book}/$L${UA:+-mobile}; mkdir -p "$W"
# UA=<agent> pour comparer une version mobile.
C=(-sk ${UA:+-A "$UA"})
PROD="https://$L.ultra-book.com"; PORT="https://$L.ubdf2026.ultra-book.name"
O=$(cd "$(dirname "$0")" && pwd)
curl "${C[@]}" "$PROD/" -o "$W/accueil.prod"
pages="/ /portfolio /actualites /news /contact"
p=$(grep -o 'href="[^"]*-pi\?[0-9]\+"' "$W/accueil.prod" | head -1 | sed 's/href="//;s/"//;s#^/##'); [ -n "$p" ] && pages="$pages /$p"
r=$(grep -o 'href="[^"]*-r[0-9]\+-c[0-9]\+"' "$W/accueil.prod" | head -1 | sed 's/href="//;s/"//;s#^/##'); [ -n "$r" ] && pages="$pages /$r"
for page in $pages; do
  n=$(echo "$page" | tr '/' '_'); [ "$n" = "_" ] && n=_accueil
  cp=$(curl "${C[@]}" -o "$W/$n.prod" -w "%{http_code}" "$PROD$page"); cl=$(curl "${C[@]}" -o "$W/$n.port" -w "%{http_code}" "$PORT$page")
  printf "%-44s prod %s port %s  " "$page" "$cp" "$cl"
  if [ "$cp" = 200 ] && [ "$cl" = 200 ]; then
    s=$(python3 "$O/comparer_structure.py" "$W/$n.prod" "$W/$n.port" 0 | head -1 | sed 's/.*similarite /struct /')
    c=$(python3 "$O/comparer_contenu.py" "$W/$n.prod" "$W/$n.port" 0 | head -2 | sed 's/.*similarite /texte /;s/visuels : /visuels /;s/ — memes fichiers : /=/' | tr '\n' ' ')
    echo "$s | $c"
  else echo; fi
done
