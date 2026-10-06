#!/usr/bin/env bash
#
# Mise a jour de la STRUCTURE de la base de prod (O2switch) d'apres la base
# locale 2026_ubdf (.env.dev). Les donnees de la prod sont conservees.
#
#   ./deploy/mise_a_jour_MySQL.sh          lance la mise a jour, ou reprend la ou elle s'est arretee
#   ./deploy/mise_a_jour_MySQL.sh --etat   ou en est-on ?
#
# Tout se deroule sur O2switch, dans screen (le Mac peut se mettre en veille) :
#   1 sauvegarde complete de la prod   2 comparaison et affichage des modifications
#   3 confirmation                      4 maintenance + application (mode strict)
#   5 controles + remise en ligne
# Chaque etape terminee est notee dans storage/mise_a_jour_MySQL/etat.
# Jamais supprime : table ou colonne absente de la reference (listee).
# Comparaison : deploy/mise_a_jour_MySQL.php. Tutoriel : mise_a_jour_MySQL.txt.
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"
source "$(dirname "$0")/lib-ancien.sh"

W="storage/mise_a_jour_MySQL"                   # dossier de travail sur O2switch
SESSION="mise_a_jour_MySQL"                     # session screen
ENV_DEV="$PROJECT_DIR/.env.dev"
val() { grep -E "^$1=" "$2" | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }

MODE="lancement"
case "${1:-}" in
    --etat)    MODE="etat" ;;
    -h|--aide) sed -n '3,15p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    "")        ;;
    *)         echec "option inconnue : $1 (voir --aide)" ;;
esac

journaliser mise_a_jour_MySQL "$MODE"

# ---------------------------------------------------------------- --etat
if [ "$MODE" = "etat" ]; then
    ssh_run "echo \"etapes      : \$(cat $W/etat 2>/dev/null | tr '\n' ' '; [ -s $W/etat ] || echo aucune)\"; \
        echo \"sauvegarde  : \$(cat $W/sauvegarde 2>/dev/null || echo aucune)\"; \
        echo \"en cours    : \$(screen -ls 2>/dev/null | grep -c $SESSION || true) session(s) screen\"; \
        [ -f $W/rapport.txt ] && tail -1 $W/rapport.txt; true"
    exit 0
fi

# ------------------------------------------------- lancement ou reprise
if ssh_run "screen -ls 2>/dev/null | grep -q '\.$SESSION'"; then
    alerte "Une mise a jour tourne deja : on s'y rattache (Ctrl-A puis D pour se detacher)."
    sur_o2switch "screen -r $SESSION"
    exit 0
fi

ETAT="$(ssh_run "cat $W/etat 2>/dev/null || true")"
if echo "$ETAT" | grep -qx fin || [ -z "$ETAT" ]; then
    ssh_run "rm -rf '$W'"; ETAT=""
fi

ligne
if [ -z "$ETAT" ]; then
    echo "  Mise a jour de la structure de ${GRAS}$(val DB_DATABASE "$PROJECT_DIR/.env.prod")${RAZ} (O2switch)"
    echo "  d'apres la structure de ${GRAS}$(val DB_DATABASE "$ENV_DEV")${RAZ} (local, .env.dev). Donnees conservees."
    echo "  Sauvegarde complete d'abord ; les modifications sont affichees, table par"
    echo "  table, et appliquees seulement apres confirmation."
    ligne
    confirm "Lancer ?" O || exit 0
else
    echo "  Reprise de la mise a jour. Etapes deja faites : $(echo "$ETAT" | tr '\n' ' ')"
    ligne
    confirm "Reprendre ?" O || exit 0
fi

# --- Reference : structure et migrations de la base locale (.env.dev)
etape "Structure de reference : $(val DB_DATABASE "$ENV_DEV") (local)"
TMP="$(mktemp -d)"; chmod 700 "$TMP"; trap 'rm -rf "$TMP"' EXIT
MAMP_BIN="/Applications/MAMP/Library/bin"
printf '[client]\nuser=%s\npassword=%s\nsocket=%s\n' "$(val DB_USERNAME "$ENV_DEV")" \
    "$(val DB_PASSWORD "$ENV_DEV")" "$(val DB_SOCKET "$ENV_DEV")" > "$TMP/local.cnf"
BASE_DEV="$(val DB_DATABASE "$ENV_DEV")"
"$MAMP_BIN/mysqldump" --defaults-extra-file="$TMP/local.cnf" --no-data --skip-comments --skip-add-drop-table \
    "$BASE_DEV" > "$TMP/reference.sql" || echec "structure locale illisible (MAMP demarre ?)"
"$MAMP_BIN/mysql" --defaults-extra-file="$TMP/local.cnf" -N -B -e "select migration from migrations order by id" \
    "$BASE_DEV" > "$TMP/migrations-ref"
ok "$(grep -c '^CREATE TABLE' "$TMP/reference.sql") tables, $(awk 'END{print NR}' "$TMP/migrations-ref") migrations"

printf 'PHP=%q\nBACKUP_DIR=%s\n' "$REMOTE_PHP" "$BACKUP_DIR" > "$TMP/params"

# --- Script execute sur O2switch
cat > "$TMP/run.sh" <<'RUN'
#!/usr/bin/env bash
# Execute sur O2switch, dans screen, depuis la racine de l'application.
set -uo pipefail
W=storage/mise_a_jour_MySQL
source "$W/params"
ETAT="$W/etat"; N=5
val() { grep -E "^$1=" .env.prod | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
CNF="$(mktemp)"; chmod 600 "$CNF"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
BASE="$(val DB_DATABASE)"

fait()    { grep -qx "$1" "$ETAT" 2>/dev/null; }
noter()   { fait "$1" || echo "$1" >> "$ETAT"; }
bandeau() { echo; echo "════ [$1/$N] $2   ($(date +%H:%M:%S))"; }
info()    { echo "   $*"; }
fermer()  { echo; read -r -p "Entree pour fermer. " _; exit "${1:-0}"; }
arret()   {
    echo; echo "!!! ARRET : $*"
    fait maintenance && echo "!!! Le site RESTE EN MAINTENANCE."
    echo "Relancer depuis le Mac : ./deploy/mise_a_jour_MySQL.sh   (reprend a cette etape)"
    [ -f "$W/sauvegarde" ] && echo "Restaurer si besoin : gunzip -c $(cat "$W/sauvegarde") | mysql -u $(val DB_USERNAME) -p $BASE  (puis php artisan cache:clear)"
    fermer 1
}
sql()     { mariadb --defaults-extra-file="$CNF" -N -B "$BASE" "$@"; }
maintenance() { "$PHP" artisan tinker --execute="App\Models\Reglage::definir(App\Models\Reglage::MAINTENANCE, $1);" > /dev/null; }
structure_prod() { mariadb-dump --defaults-extra-file="$CNF" --no-data --skip-comments --skip-add-drop-table "$BASE" 2>/dev/null > "$W/prod.sql"; }
# Lignes de chaque table : « table<TAB>nombre ».
compter() {
    sql -e "select concat('select ''', table_name, ''', count(*) from \`', table_name, '\`;') from information_schema.tables
            where table_schema=database() and table_type='BASE TABLE' order by 1" | sql > "$1"
}

echo "Mise a jour de structure — base $BASE    Journal : $W/run.log"

# ───────────────────────────────────────────── 1 sauvegarde
if ! fait sauvegarde; then
    bandeau 1 "Sauvegarde complete de $BASE"
    mkdir -p "$BACKUP_DIR"
    S="$BACKUP_DIR/base-$(date +%Y%m%d-%H%M%S)-avant-mise-a-jour-structure.sql.gz"
    ( mariadb-dump --defaults-extra-file="$CNF" --single-transaction --quick --no-tablespaces --routines --triggers "$BASE" 2>/dev/null | gzip > "$S" ) &
    pid=$!
    while kill -0 "$pid" 2>/dev/null; do printf '\r   %s Mo ecrits   ' $(( $(stat -c %s "$S" 2>/dev/null || echo 0) / 1048576 )); sleep 2; done; echo
    wait "$pid" || arret "sauvegarde en echec : rien n'est modifie"
    gzip -t "$S" && [ "$(stat -c %s "$S")" -gt 1000 ] && zcat "$S" | tail -1 | grep -q 'Dump completed' \
        || arret "SAUVEGARDE INVALIDE ($S) : rien n'est modifie"
    echo "$S" > "$W/sauvegarde"
    info "$S ($(du -h "$S" | cut -f1)), verifiee"
    noter sauvegarde
fi

# ───────────────────────────────────────────── 2 comparaison
# Toujours refaite : apres un arret, elle repart de l'etat reel de la prod.
bandeau 2 "Comparaison des structures (reference : 2026_ubdf local)"
structure_prod || arret "structure de la prod illisible"
"$PHP" "$W/mise_a_jour_MySQL.php" "$W/reference.sql" "$W/prod.sql" "$W/plan.sql" > "$W/rapport.txt"
code=$?
[ "$code" -eq 0 ] || [ "$code" -eq 10 ] || arret "comparaison en echec"
sql -e "select migration from migrations" > "$W/migrations-prod"
grep -vxF -f "$W/migrations-prod" "$W/migrations-ref" > "$W/migrations-a-noter" || true
echo
sed 's/^/   /' "$W/rapport.txt"
if [ -s "$W/migrations-a-noter" ]; then
    echo; echo "   MIGRATIONS NOTEES COMME JOUEES EN PROD (sinon « migrate » les rejouerait)"
    sed 's/^/     /' "$W/migrations-a-noter"
fi
if [ "$code" -eq 0 ] && [ ! -s "$W/migrations-a-noter" ]; then
    fait maintenance && { maintenance false; info "site remis en ligne"; }
    noter fin
    echo; echo "Rien a faire : la structure de la prod est deja celle de 2026_ubdf."
    fermer 0
fi

# ───────────────────────────────────────────── 3 confirmation
bandeau 3 "Confirmation"
info "Sauvegarde : $(cat "$W/sauvegarde")"
info "Le site passe en maintenance le temps de l'application (quelques minutes)."
info "Mode strict : une valeur qui ne tiendrait plus dans sa colonne fait ECHOUER"
info "la modification au lieu d'etre coupee."
echo
read -r -p "Taper MAJ pour appliquer (sinon Entree) : " rep
if [ "$rep" != "MAJ" ]; then
    fait maintenance && maintenance false
    noter fin
    echo "Annule : seule la sauvegarde a ete faite."; fermer 0
fi

# ───────────────────────────────────────────── 4 application
bandeau 4 "Maintenance et application"
maintenance true || arret "mise en maintenance impossible"
noter maintenance
info "site en maintenance"
# Lignes de reference prises a la premiere application seulement (une reprise garde les comptes d'origine).
[ -s "$W/lignes-avant.tsv" ] || compter "$W/lignes-avant.tsv"
[ -s "$W/lignes-avant.tsv" ] || arret "comptage des lignes impossible : rien n'est applique"
info "$(awk 'END{print NR}' "$W/lignes-avant.tsv") tables recomptees"
{
    echo "SET SESSION sql_mode='STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION';"
    cat "$W/plan.sql"
    if [ -s "$W/migrations-a-noter" ]; then
        echo "SELECT '   migrations notees' AS '';"
        echo "SET @lot := (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);"
        while read -r m; do echo "INSERT INTO migrations (migration, batch) VALUES ('$m', @lot);"; done < "$W/migrations-a-noter"
    fi
} > "$W/appliquer.sql"
sql < "$W/appliquer.sql" || arret "instruction refusee (ci-dessus). Les precedentes sont appliquees ; mode strict : aucune donnee coupee. Corriger (valeur trop longue, doublon sur un index unique...), puis relancer : la comparaison repart de l'etat reel."
noter application

# ───────────────────────────────────────────── 5 controles
bandeau 5 "Controles et remise en ligne"
compter "$W/lignes-apres.tsv"
ko=0
# Les tables creees par la mise a jour n'existaient pas avant : on compare les autres.
if diff "$W/lignes-avant.tsv" <(grep -F -f <(cut -f1 "$W/lignes-avant.tsv" | sed 's/$/\t/') "$W/lignes-apres.tsv") > "$W/ecart"; then
    info "OK  lignes identiques dans les $(awk 'END{print NR}' "$W/lignes-avant.tsv") tables"
else
    ko=1; info "KO  lignes differentes :"; sed 's/^/      /' "$W/ecart"
fi
structure_prod
if "$PHP" "$W/mise_a_jour_MySQL.php" "$W/reference.sql" "$W/prod.sql" "$W/reste.sql" > "$W/reste.txt"; then
    info "OK  structure identique a 2026_ubdf"
else
    ko=1; info "KO  structure encore differente :"; sed 's/^/      /' "$W/reste.txt"
fi
[ "$ko" -eq 0 ] || arret "controles en echec"

"$PHP" artisan cache:clear > /dev/null
maintenance false || arret "fin de maintenance impossible"
echo "Mise a jour appliquee le $(date '+%F %T'), lignes et structure controlees." >> "$W/rapport.txt"
noter fin
echo; echo "Site remis en ligne. Plan applique : $W/rapport.txt"
fermer 0
RUN

etape "Envoi vers O2switch, lancement dans screen"
ssh_run "mkdir -p '$W'"
rsync -a -e "$SSH_CMD" "$TMP/params" "$TMP/run.sh" "$TMP/reference.sql" "$TMP/migrations-ref" \
    "$SCRIPT_DIR/mise_a_jour_MySQL.php" "$REMOTE:$REMOTE_PATH/$W/"
ok "dossier de travail : $REMOTE_PATH/$W"

alerte "Ctrl-A puis D : se detacher. Revenir : ./deploy/mise_a_jour_MySQL.sh"
# Toujours attache : le journal (tee) masque le terminal, lancer_screen detacherait.
sur_o2switch "screen -S $SESSION bash -c 'bash $W/run.sh 2>&1 | tee -a $W/run.log'"
rsync -a -e "$SSH_CMD" "$REMOTE:$REMOTE_PATH/$W/rapport.txt" "$JOURNAL_DIR/mise_a_jour_MySQL-rapport-$(date +%Y%m%d-%H%M).txt" 2>/dev/null \
    && ok "rapport copie dans deploy/logs/" || true
