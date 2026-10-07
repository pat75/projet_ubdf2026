#!/usr/bin/env bash
#
# Transfert et conversion de la base MySQL : ancienne prod -> O2switch (ultra-book.pro).
#
#   ./deploy/transfert_mySQL.sh adminpat@163.172.103.215:/home/adminpat/db_ultra_book_2019_AAAA-MM-JJ.sql.gz
#        lance tout le transfert a partir du dump fait a la main (voir transfert_mySQL.txt)
#   ./deploy/transfert_mySQL.sh              reprend la ou le transfert s'est arrete
#   ./deploy/transfert_mySQL.sh --etat       ou en est-on ?
#   ./deploy/transfert_mySQL.sh --nettoyer   fin de parcours : retire le dump et les tables legacy
#
# Tout se deroule sur O2switch, dans screen (le Mac peut se mettre en veille) :
#   1 recuperation du dump   2 controle du dump      3 maintenance + sauvegarde de la prod
#   4 import legacy          5 integrite AVANT        6 structure (reference : 2026_ubdf local)
#   7 conversion             8 integrite APRES        9 remise en ligne + rapport
# Chaque etape terminee est notee dans storage/transfert_mySQL/etat : une
# relance saute les etapes faites, et la conversion repart au dernier paquet.
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"
source "$(dirname "$0")/lib-ancien.sh"

DATA_SQL="${DATA_SQL:-data_sql}"
W="storage/transfert_mySQL"                     # dossier de travail sur O2switch
SESSION="transfert_mySQL"                       # session screen

# Seules tables qu'un dump legacy a le droit de creer ou d'ecraser.
LEGACY_TABLES='^(inc_|ub2_|bn_|df2_|nl_|wp_import$)'

# Tables videes puis rechargees depuis l'ancienne prod (decisions du
# 02/10/2026, meme liste que MigrateLegacyCommand::truncateTargets()).
# Toutes les autres tables 2026 sont gardees et recomptees ligne a ligne.
VIDEES="messages conversations visit_stats invoices book_articles book_sections media galleries
book_settings selection_user users billing_profiles visitor_book_visits visitors memo_partages
memo_books referrals data_exports page_images subscription_reminders user_password_resets
campaign_sends promo_codes marketing_offers newsletter_mails"
# Tables techniques : ni comptees ni comparees.
TECHNIQUES="migrations cache cache_locks sessions jobs job_batches failed_jobs"

ENV_PROD="$PROJECT_DIR/.env.prod"
ENV_DEV="$PROJECT_DIR/.env.dev"
val() { grep -E "^$1=" "$2" | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }

definir_env() {
    if grep -qE "^$1=" "$ENV_PROD"; then
        sed -i '' -E "s#^$1=.*#$1=$2#" "$ENV_PROD"
    else
        printf '%s=%s\n' "$1" "$2" >> "$ENV_PROD"
    fi
}

envoyer_env() {
    rsync -a -e "$SSH_CMD" "$ENV_PROD" "$REMOTE:$REMOTE_PATH/.env.prod"
    caches > /dev/null
    ok ".env.prod envoye, caches reconstruits"
}

LIEN=""; MODE="reprise"
case "${1:-}" in
    --etat)     MODE="etat" ;;
    --nettoyer) MODE="nettoyer" ;;
    -h|--aide)  sed -n '3,17p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    "")         ;;
    -*)         echec "option inconnue : $1 (voir --aide)" ;;
    *)          LIEN="$1"; MODE="lancement" ;;
esac

journaliser transfert_mySQL "$MODE"

# ---------------------------------------------------------------- --etat
if [ "$MODE" = "etat" ]; then
    ssh_run "echo \"source      : \$(cat $W/source 2>/dev/null || echo aucune)\"; \
        echo \"etapes      : \$(cat $W/etat 2>/dev/null | tr '\n' ' '; [ -s $W/etat ] || echo aucune)\"; \
        echo \"dump        : \$(ls -lh $DATA_SQL/*.gz 2>/dev/null | awk '{print \$5, \$9}' || echo aucun)\"; \
        echo \"sauvegarde  : \$(cat $W/sauvegarde 2>/dev/null || echo aucune)\"; \
        echo \"en cours    : \$(screen -ls 2>/dev/null | grep -c $SESSION || true) session(s) screen\"; \
        [ -f $W/conversion.log ] && echo \"conversion  : \$(grep -o 'paquet [0-9]*.*us_id [0-9]*' $W/conversion.log | tail -1)\"; \
        df -h ~ | tail -1"
    exit 0
fi

# ------------------------------------------------------------ --nettoyer
if [ "$MODE" = "nettoyer" ]; then
    ssh_run "grep -qx fin $W/etat 2>/dev/null" || echec "le transfert n'est pas termine : rien a nettoyer"
    ligne
    echo "  Retire le dump de data_sql/, les tables legacy (inc_*, ub2_*, bn_*, df2_*,"
    echo "  nl_*, wp_import) et DB_LEGACY_* de .env.prod. Les tables 2026 restent."
    echo "  ${ROUGE}Seulement quand « organiser » (transfert des images) a tourne sur${RAZ}"
    echo "  ${ROUGE}toutes les lettres${RAZ} : il lit les tables legacy."
    ligne
    read -r -p "  Taper NETTOYER pour confirmer : " rep
    [ "$rep" = "NETTOYER" ] || { echo "Annule."; exit 0; }
    etape "Sauvegarde, puis suppression des tables legacy"
    ssh_script "'$LEGACY_TABLES' '$DATA_SQL'" <<'DISTANT'
set -euo pipefail
MOTIF="$1"; DATA_SQL="$2"
val() { grep -E "^$1=" .env.prod | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
CNF="$(mktemp)"; chmod 600 "$CNF"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
BASE="$(val DB_DATABASE)"
mkdir -p "$BACKUP_DIR"
S="$BACKUP_DIR/base-$(date +%Y%m%d-%H%M%S)-avant-nettoyage-legacy.sql.gz"
mysqldump --defaults-extra-file="$CNF" --single-transaction --quick --no-tablespaces "$BASE" | gzip > "$S"
gzip -t "$S" && [ "$(stat -c %s "$S")" -gt 1000 ] || { echo "   SAUVEGARDE INVALIDE : arret"; exit 1; }
echo "   sauvegarde : $S"
for t in $(mysql --defaults-extra-file="$CNF" -N -e "select table_name from information_schema.tables where table_schema='$BASE'" | grep -E "$MOTIF" || true); do
    mysql --defaults-extra-file="$CNF" "$BASE" -e "SET FOREIGN_KEY_CHECKS=0; DROP TABLE \`$t\`"
done
rm -f "$DATA_SQL"/*.sql.gz; rmdir "$DATA_SQL" 2>/dev/null || true
echo "   tables legacy et dump retires"
DISTANT
    for v in DB_LEGACY_HOST DB_LEGACY_PORT DB_LEGACY_SOCKET DB_LEGACY_DATABASE DB_LEGACY_USERNAME DB_LEGACY_PASSWORD; do
        sed -i '' -E "/^$v=/d" "$ENV_PROD"
    done
    envoyer_env
    exit 0
fi

# ------------------------------------------------- lancement ou reprise
SOURCE_ACTUELLE="$(ssh_run "cat $W/source 2>/dev/null || true")"
ETAT="$(ssh_run "cat $W/etat 2>/dev/null || true")"

if [ "$MODE" = "lancement" ]; then
    [[ "$LIEN" =~ ^[A-Za-z0-9._-]+@[A-Za-z0-9.-]+:/.+\.sql\.gz$ ]] \
        || echec "lien attendu : utilisateur@serveur:/chemin/du/dump.sql.gz"
    if [ "$LIEN" = "$SOURCE_ACTUELLE" ]; then
        MODE="reprise"
    elif [ -n "$SOURCE_ACTUELLE" ]; then
        alerte "Un transfert precedent existe ($SOURCE_ACTUELLE) : etapes $(echo "$ETAT" | tr '\n' ' ')"
        alerte "Le nouveau dump repart de l'etape 1 (la sauvegarde precedente reste dans ~/backups_deploy/)."
        confirm "Recommencer avec le nouveau dump ?" || exit 0
        ssh_run "rm -rf '$W'"
        ETAT=""
    fi
else
    [ -n "$SOURCE_ACTUELLE" ] || echec "aucun transfert en cours : passer le lien du dump (voir --aide)"
    LIEN="$SOURCE_ACTUELLE"
fi

if echo "$ETAT" | grep -qx fin; then
    ok "Transfert deja termine pour $LIEN. Rapport : $W/rapport.txt"
    exit 0
fi
if ssh_run "screen -ls 2>/dev/null | grep -q '\.$SESSION'"; then
    alerte "Un transfert tourne deja : on s'y rattache (Ctrl-A puis D pour se detacher)."
    sur_o2switch "screen -r $SESSION"
    exit 0
fi

DUMP="$DATA_SQL/$(basename "${LIEN#*:}")"

ligne
if [ -z "$ETAT" ]; then
    echo "  Source     : $LIEN"
    echo "  Cible      : base de prod ${GRAS}$(val DB_DATABASE "$ENV_PROD")${RAZ} (O2switch)"
    echo "  Reference  : structure de ${GRAS}$(val DB_DATABASE "$ENV_DEV")${RAZ} (base locale, .env.dev)"
    echo
    echo "  Le site passe ${ROUGE}en maintenance${RAZ} de l'etape 3 a la fin (2 h 30 a 3 h)."
    echo "  Avant toute modification : sauvegarde complete de la prod, verifiee."
    echo "  ${ROUGE}Videes puis rechargees${RAZ} : comptes, books, visuels, pages, messages, factures,"
    echo "    stats, visiteurs, memos, parrainages, codes promo, offres, abonnes newsletter."
    echo "  ${VERT}Gardees et recomptees${RAZ} : administrateurs, reglages, pages CMS, actualites,"
    echo "    campagnes, categories, selections."
    echo "  Si un controle d'integrite echoue, le site reste en maintenance."
    ligne
    read -r -p "  Taper TRANSFERER pour lancer : " rep
    [ "$rep" = "TRANSFERER" ] || { echo "Annule."; exit 0; }
else
    echo "  Reprise du transfert : $LIEN"
    echo "  Etapes deja faites : $(echo "$ETAT" | tr '\n' ' ')"
    ligne
    confirm "Reprendre ?" O || exit 0
fi

# --- Reference de structure : dump --no-data de la base locale (.env.dev)
etape "Structure de reference : $(val DB_DATABASE "$ENV_DEV") (local)"
TMP="$(mktemp -d)"; chmod 700 "$TMP"; trap 'rm -rf "$TMP"' EXIT
MAMP_BIN="/Applications/MAMP/Library/bin"
LOCAL_CNF="$TMP/local.cnf"
printf '[client]\nuser=%s\npassword=%s\nsocket=%s\n' "$(val DB_USERNAME "$ENV_DEV")" \
    "$(val DB_PASSWORD "$ENV_DEV")" "$(val DB_SOCKET "$ENV_DEV")" > "$LOCAL_CNF"
BASE_DEV="$(val DB_DATABASE "$ENV_DEV")"
"$MAMP_BIN/mysqldump" --defaults-extra-file="$LOCAL_CNF" --no-data --skip-comments \
    --skip-add-drop-table "$BASE_DEV" > "$TMP/structure.brut" \
    || echec "dump de structure impossible (MAMP demarre ?)"
sed -E 's/ AUTO_INCREMENT=[0-9]+//' "$TMP/structure.brut" > "$TMP/structure.sql"
"$MAMP_BIN/mysql" --defaults-extra-file="$LOCAL_CNF" -N -B -e \
    "select table_name, column_name, column_type, is_nullable from information_schema.columns
     where table_schema='$BASE_DEV' order by table_name, column_name" \
    | grep -vE "$LEGACY_TABLES" > "$TMP/colonnes-ref.tsv"
ok "$(grep -c '^CREATE TABLE' "$TMP/structure.sql") tables, $(wc -l < "$TMP/colonnes-ref.tsv" | tr -d ' ') colonnes"

# --- Connexion legacy : meme base que la prod, en latin1 (lue par l'analyse et la conversion)
etape "Connexion legacy dans .env.prod"
if [ "$(val DB_LEGACY_DATABASE "$ENV_PROD")" != "$(val DB_DATABASE "$ENV_PROD")" ]; then
    definir_env DB_LEGACY_HOST "$(val DB_HOST "$ENV_PROD")"
    definir_env DB_LEGACY_PORT "$(val DB_PORT "$ENV_PROD")"
    definir_env DB_LEGACY_SOCKET ""
    definir_env DB_LEGACY_DATABASE "$(val DB_DATABASE "$ENV_PROD")"
    definir_env DB_LEGACY_USERNAME "$(val DB_USERNAME "$ENV_PROD")"
    definir_env DB_LEGACY_PASSWORD "$(val DB_PASSWORD "$ENV_PROD")"
    envoyer_env
else
    ok "deja en place"
fi

# --- Script execute sur O2switch
{
    printf 'SOURCE=%q\n' "$LIEN"
    printf 'DUMP=%q\n' "$DUMP"
    printf 'PHP=%q\n' "$REMOTE_PHP"
    printf 'BACKUP_DIR=%s\n' "$BACKUP_DIR"
    printf 'SSH_ANCIEN=%q\n' "ssh -p $OLD_SSH_PORT -o StrictHostKeyChecking=accept-new -o PubkeyAuthentication=no"
    printf 'MOTIF=%q\n' "$LEGACY_TABLES"
    printf 'VIDEES=%q\n' "$(echo $VIDEES)"
    printf 'TECHNIQUES=%q\n' "$TECHNIQUES"
} > "$TMP/params"

cat > "$TMP/run.sh" <<'RUN'
#!/usr/bin/env bash
# Execute sur O2switch, dans screen, depuis la racine de l'application.
set -uo pipefail
W=storage/transfert_mySQL
source "$W/params"
ETAT="$W/etat"; N=9
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
    echo "Relancer depuis le Mac : ./deploy/transfert_mySQL.sh   (reprend a cette etape)"
    [ -f "$W/sauvegarde" ] && echo "Restaurer si besoin : gunzip -c $(cat "$W/sauvegarde") | mysql -u $(val DB_USERNAME) -p $BASE  (puis php artisan cache:clear)"
    fermer 1
}
# O2switch : mysql est un alias obsolete de mariadb (avertissement a chaque appel).
command -v mariadb > /dev/null && mysql() { mariadb "$@"; }
sql()     { mysql --defaults-extra-file="$CNF" -N -B "$BASE" -e "$1"; }
# Pas de <( ) : /dev/fd n'existe pas sur O2switch.
sauf()    { local a=() m; for m in $1; do a+=(-e "$m"); done; grep -vxF "${a[@]}"; }
maintenance() { "$PHP" artisan tinker --execute="App\Models\Reglage::definir(App\Models\Reglage::MAINTENANCE, $1);" > /dev/null; }
mo()      { echo $(( $1 / 1048576 )); }

# Progression d'un gunzip qui lit son fichier sur l'entree standard.
suivre_lecture() { # pid_lecteur fichier
    local t p; t=$(stat -c %s "$2")
    while kill -0 "$1" 2>/dev/null; do
        p=$(awk '/^pos:/{print $2}' "/proc/$1/fdinfo/0" 2>/dev/null)
        [ -n "$p" ] && printf '\r   %3d %%   %s / %s Mo   ' $(( p * 100 / t )) "$(mo "$p")" "$(mo "$t")"
        sleep 3
    done; echo
}
# Progression d'un fichier qui grossit (sauvegarde).
suivre_taille() { # pid fichier
    while kill -0 "$1" 2>/dev/null; do
        printf '\r   %s Mo ecrits   ' "$(mo "$(stat -c %s "$2" 2>/dev/null || echo 0)")"; sleep 3
    done; echo
}

# Tables 2026 de la prod (hors legacy et techniques), puis celles gardees.
tables_2026()    { sql "select table_name from information_schema.tables where table_schema='$BASE' and table_type='BASE TABLE' order by 1" | grep -vE "$MOTIF" | sauf "$TECHNIQUES"; }
tables_gardees() { tables_2026 | sauf "$VIDEES"; }
compter()        { for t in $1; do printf '%s\t%s\n' "$t" "$(sql "select count(*) from \`$t\`")"; done; }

# Colonnes normalisees (MySQL 5.7 local / MariaDB O2switch : largeur
# d'affichage des entiers et json ignores).
normaliser()    { awk -F'\t' '{ t=tolower($3); t=gensub(/(tinyint|smallint|mediumint|bigint|int)\([0-9]+\)/, "\\1", "g", t);
                    sub(/^json$/, "longtext", t); print $1 "\t" $2 "\t" t "\t" $4 }' | sort; }
colonnes_prod() { sql "select table_name, column_name, column_type, is_nullable from information_schema.columns where table_schema='$BASE'" | grep -vE "$MOTIF" | normaliser; }
dans_ref()      { grep -q "^CREATE TABLE \`$1\` (" "$W/structure.sql"; }
ddl()           { awk -v t="CREATE TABLE \`$1\` (" 'index($0,t)==1{p=1} p{print} p&&/;$/{exit}' "$W/structure.sql"; }

echo "Transfert MySQL — $SOURCE"
echo "Base cible : $BASE    Journal : $W/run.log"

# ───────────────────────────────────────────── 1 recuperation
if ! fait recuperation; then
    bandeau 1 "Recuperation du dump (reprend un fichier coupe)"
    mkdir -p "$(dirname "$DUMP")"
    echo "Mot de passe de l'ANCIEN serveur (${SOURCE%%:*}) :"
    rsync -a --partial --info=progress2 -e "$SSH_ANCIEN" "$SOURCE" "$DUMP" || arret "rsync interrompu"
    gzip -t "$DUMP" 2>/dev/null || arret "dump gzip corrompu"
    fin="$(zcat "$DUMP" | tail -1)"
    case "$fin" in *'Dump completed'*) info "$fin";; *) arret "pas de « Dump completed » : export incomplet, refaire l'etape EXPORTER";; esac
    sha256sum "$DUMP" | cut -c1-16 > "$W/empreinte"
    info "empreinte $(cat "$W/empreinte"), $(du -h "$DUMP" | cut -f1)"
    noter recuperation
fi
[ "$(sha256sum "$DUMP" | cut -c1-16)" = "$(cat "$W/empreinte")" ] || arret "le dump a change depuis la recuperation"

# ───────────────────────────────────────────── 2 controle
if ! fait controle; then
    bandeau 2 "Controle du dump (tables legacy uniquement)"
    zcat "$DUMP" | grep -oE '^CREATE TABLE `[a-z0-9_]+`' | cut -d'`' -f2 > "$W/tables-dump"
    intruses="$(grep -vE "$MOTIF" "$W/tables-dump" || true)"
    [ -z "$intruses" ] || arret "tables hors legacy dans le dump (mauvais fichier) : $intruses"
    info "$(wc -l < "$W/tables-dump") tables, toutes legacy"
    noter controle
fi

# ───────────────────────────────────────────── 3 maintenance + sauvegarde
if ! fait sauvegarde; then
    bandeau 3 "Maintenance, puis sauvegarde complete de $BASE"
    maintenance true || arret "mise en maintenance impossible"
    noter maintenance
    info "site en maintenance"
    mkdir -p "$BACKUP_DIR"
    S="$BACKUP_DIR/base-$(date +%Y%m%d-%H%M%S)-avant-transfert.sql.gz"
    ( mysqldump --defaults-extra-file="$CNF" --single-transaction --quick --no-tablespaces "$BASE" | gzip > "$S" ) &
    pid=$!; suivre_taille "$pid" "$S"; wait "$pid" || arret "sauvegarde en echec, la base n'est pas modifiee"
    gzip -t "$S" && [ "$(stat -c %s "$S")" -gt 1000 ] && zcat "$S" | tail -1 | grep -q 'Dump completed' \
        || arret "SAUVEGARDE INVALIDE ($S), la base n'est pas modifiee"
    echo "$S" > "$W/sauvegarde"
    info "$S ($(du -h "$S" | cut -f1)), verifiee"
    # Lignes des tables gardees, avant toute modification.
    compter "$(tables_gardees)" > "$W/gardees-avant.tsv"
    [ -s "$W/gardees-avant.tsv" ] || arret "aucune table 2026 gardee trouvee : controle impossible"
    info "$(wc -l < "$W/gardees-avant.tsv") tables gardees recomptees"
    noter sauvegarde
fi

# ───────────────────────────────────────────── 4 import legacy
if ! fait import; then
    bandeau 4 "Import des tables legacy dans $BASE (latin1, comme l'export)"
    debut=$(date +%s)
    ( gunzip -c < "$DUMP" | mysql --defaults-extra-file="$CNF" --default-character-set=latin1 "$BASE" ) &
    sub=$!; sleep 1
    suivre_lecture "$(pgrep -P "$sub" -x gunzip || echo "$sub")" "$DUMP"
    wait "$sub" || arret "import en echec (relancer : le dump recree ses tables)"
    info "termine en $(( ($(date +%s) - debut) / 60 )) min"
    noter import
fi

# ───────────────────────────────────────────── 5 integrite AVANT
if ! fait integrite-avant; then
    bandeau 5 "Integrite AVANT conversion"
    sql "show tables" | grep -E "$MOTIF" > "$W/tables-base" || true
    manquantes="$(grep -vxF -f "$W/tables-base" "$W/tables-dump" || true)"
    [ -z "$manquantes" ] || arret "tables du dump absentes de la base : $manquantes"
    info "$(wc -l < "$W/tables-dump") tables legacy presentes"
    compter "$(cat "$W/tables-dump")" > "$W/legacy-lignes.tsv"
    vides="$(awk -F'\t' '$2==0{print $1}' "$W/legacy-lignes.tsv" | tr '\n' ' ')"
    [ -z "$vides" ] || info "tables legacy vides : $vides"
    for t in inc_user ub2_gal_img nl_newsletter; do
        grep -qP "^$t\t[1-9]" "$W/legacy-lignes.tsv" || arret "$t vide ou absente : dump inexploitable"
    done
    sql "select count(*) from inc_user where us_delete='false'" > "$W/vivants"
    info "comptes vivants : $(cat "$W/vivants")   abonnes : $(grep -P '^nl_newsletter\t' "$W/legacy-lignes.tsv" | cut -f2)"
    "$PHP" artisan ubdf:prod:analyser-legacy --rapport="$W/analyse.txt" > /dev/null || arret "analyse en echec"
    grep -E '^(SYNTHESE|! )' "$W/analyse.txt" | sed 's/^/   /'
    noter integrite-avant
fi

# ───────────────────────────────────────────── 6 structure
if ! fait structure; then
    bandeau 6 "Structure : migrations, puis alignement sur 2026_ubdf"
    "$PHP" artisan migrate --force || arret "migrations en echec"
    normaliser < "$W/colonnes-ref.tsv" > "$W/ref.norm"
    colonnes_prod > "$W/prod.norm"
    ecarts="$(diff "$W/ref.norm" "$W/prod.norm" | grep -E '^[<>]' | cut -c3- | cut -f1 | sort -u || true)"
    for t in $ecarts; do
        if ! dans_ref "$t"; then
            info "$t : presente en prod, absente de la reference (laissee telle quelle)"
        elif ! sql "show tables like '$t'" | grep -q .; then
            sql "SET FOREIGN_KEY_CHECKS=0; $(ddl "$t") SET FOREIGN_KEY_CHECKS=1;" || arret "creation de $t impossible"
            info "$t creee selon la reference"
        elif grep -qw "$t" <<< "$VIDEES"; then
            sql "SET FOREIGN_KEY_CHECKS=0; DROP TABLE \`$t\`; $(ddl "$t") SET FOREIGN_KEY_CHECKS=1;" \
                || arret "recreation de $t impossible"
            info "$t recreee selon la reference (table videe et rechargee de toute facon)"
        else
            grep -P "^$t\t" "$W/ref.norm" > "$W/diff-ref.tmp" || true
            grep -P "^$t\t" "$W/prod.norm" > "$W/diff-prod.tmp" || true
            diff "$W/diff-ref.tmp" "$W/diff-prod.tmp" | sed 's/^/      /' || true
            arret "table GARDEE $t differente de la reference : la modifier ferait perdre des donnees. Deployer la migration manquante, puis relancer."
        fi
    done
    colonnes_prod > "$W/prod.norm"
    restants="$(comm -23 "$W/ref.norm" "$W/prod.norm")"
    [ -z "$restants" ] || { echo "$restants" | sed 's/^/      /'; arret "structure toujours differente de la reference"; }
    info "structure identique a la reference ($(cut -f1 "$W/ref.norm" | sort -u | wc -l) tables)"
    noter structure
fi

# ───────────────────────────────────────────── 7 conversion
if ! fait conversion; then
    depuis="$(grep -o 'relance : --depuis=[0-9]*' "$W/conversion.log" 2>/dev/null | tail -1 | cut -d= -f2 || true)"
    if [ -n "$depuis" ]; then
        bandeau 7 "Conversion — REPRISE apres us_id $depuis (rien n'est vide)"
        opts="--depuis=$depuis"
    else
        bandeau 7 "Conversion legacy -> 2026 (environ 1 h 30)"
        opts="--fresh"
    fi
    info "≈ $(( ($(cat "$W/vivants") + 1999) / 2000 )) paquets de 2 000 comptes, puis messagerie, parrainages, codes promo, abonnes"
    "$PHP" artisan ubdf:migrate-legacy --tous --skip-files $opts --rapport="$W/conversion.txt" 2>&1 | tee -a "$W/conversion.log"
    [ "${PIPESTATUS[0]}" -eq 0 ] || arret "conversion interrompue (la relance repart au dernier paquet)"
    noter conversion
fi

# ───────────────────────────────────────────── 8 integrite APRES
if ! fait integrite-apres; then
    bandeau 8 "Integrite APRES conversion"
    ko=0; : > "$W/integrite.txt"
    note() { echo "   $*" | tee -a "$W/integrite.txt"; }

    compter "$(cut -f1 "$W/gardees-avant.tsv")" > "$W/gardees-apres.tsv"
    if [ ! -s "$W/gardees-avant.tsv" ]; then
        ko=1; note "KO  tables gardees : releve AVANT vide (controle non fait ; la sauvegarde permet de comparer a la main)"
    elif diff -q "$W/gardees-avant.tsv" "$W/gardees-apres.tsv" > /dev/null; then
        note "OK  tables gardees : $(wc -l < "$W/gardees-avant.tsv") tables, lignes identiques"
    else
        ko=1; note "KO  tables gardees modifiees :"
        diff "$W/gardees-avant.tsv" "$W/gardees-apres.tsv" | sed 's/^/        /' | tee -a "$W/integrite.txt"
    fi

    vivants=$(cat "$W/vivants")
    users=$(sql "select count(*) from users")
    ecartes=$(grep -o 'non repris) : [0-9]*' "$W/conversion.txt" | grep -o '[0-9]*$' || echo 0)
    attendu=$(( vivants - ecartes ))
    if [ "$users" -eq "$attendu" ]; then
        note "OK  comptes : $users = $vivants vivants - $ecartes logins ecartes"
    else
        ko=1; note "KO  comptes : $users repris, $attendu attendus ($vivants vivants - $ecartes ecartes)"
        sql "select i.us_id, i.us_login from inc_user i left join users u on u.legacy_id = i.us_id
             where i.us_delete = 'false' and u.id is null order by i.us_id" > "$W/comptes-manquants.tsv"
        note "    $(wc -l < "$W/comptes-manquants.tsv") comptes vivants absents de users (liste : $W/comptes-manquants.tsv), dont les ecartes ci-dessus :"
        head -30 "$W/comptes-manquants.tsv" | sed 's/^/        /' | tee -a "$W/integrite.txt"
    fi

    for t in users book_settings galleries media newsletter_mails; do
        n=$(sql "select count(*) from \`$t\`")
        if [ "$n" -gt 0 ]; then note "OK  $t : $n lignes"; else ko=1; note "KO  $t : vide"; fi
    done

    orphelins=0
    sql "select table_name, column_name, referenced_table_name, referenced_column_name from information_schema.key_column_usage
         where table_schema='$BASE' and referenced_table_name is not null" | grep -vE "$MOTIF" > "$W/fk.tsv" || true
    [ -s "$W/fk.tsv" ] || { ko=1; note "KO  cles etrangeres : aucune trouvee, controle impossible"; }
    while IFS=$'\t' read -r t c rt rc; do
        n=$(sql "select count(*) from \`$t\` e left join \`$rt\` p on e.\`$c\` = p.\`$rc\` where e.\`$c\` is not null and p.\`$rc\` is null")
        [ "$n" -eq 0 ] || { orphelins=1; note "KO  $t.$c -> $rt.$rc : $n lignes orphelines"; }
    done < "$W/fk.tsv"
    if [ "$orphelins" -eq 0 ]; then note "OK  cles etrangeres : aucune ligne orpheline"; else ko=1; fi

    colonnes_prod > "$W/prod.norm"
    if [ -z "$(comm -23 "$W/ref.norm" "$W/prod.norm")" ]; then note "OK  structure identique a 2026_ubdf"; else ko=1; note "KO  structure differente de 2026_ubdf"; fi

    if [ "$ko" -ne 0 ]; then
        echo
        echo "!!! Des controles ont echoue (detail : $W/integrite.txt). Le site est EN MAINTENANCE."
        echo "!!! Restaurer : gunzip -c $(cat "$W/sauvegarde") | mysql -u $(val DB_USERNAME) -p $BASE"
        read -r -p "Taper VALIDER pour remettre en ligne malgre ces ecarts (sinon Entree) : " rep
        [ "$rep" = "VALIDER" ] || arret "integrite non validee"
        note "ecarts acceptes le $(date '+%F %T')"
    fi
    noter integrite-apres
fi

# ───────────────────────────────────────────── 9 remise en ligne
bandeau 9 "Remise en ligne et rapport"
"$PHP" artisan cache:clear > /dev/null
maintenance false || arret "fin de maintenance impossible"
{
    echo "TRANSFERT MySQL $(date '+%F %H:%M') — $SOURCE"
    echo "Sauvegarde avant transfert : $(cat "$W/sauvegarde")"
    echo; echo "INTEGRITE AVANT"; grep -E '^(SYNTHESE|! )' "$W/analyse.txt"
    echo; echo "INTEGRITE APRES"; cat "$W/integrite.txt"
    echo; cat "$W/conversion.txt"
} > "$W/rapport.txt"
cat "$W/rapport.txt"
noter fin
echo; echo "Site remis en ligne. Rapport : $W/rapport.txt"
fermer 0
RUN

etape "Envoi vers O2switch, lancement dans screen"
printf '%s\n' "$LIEN" > "$TMP/source"
ssh_run "mkdir -p '$W'"
rsync -a -e "$SSH_CMD" "$TMP/params" "$TMP/run.sh" "$TMP/structure.sql" "$TMP/colonnes-ref.tsv" "$TMP/source" \
    "$REMOTE:$REMOTE_PATH/$W/"
ok "dossier de travail : $REMOTE_PATH/$W"

alerte "Ctrl-A puis D : se detacher. Revenir : ./deploy/transfert_mySQL.sh"
# Toujours attache : le journal (tee) masque le terminal, lancer_screen detacherait.
sur_o2switch "screen -S $SESSION bash -c 'bash $W/run.sh 2>&1 | tee -a $W/run.log'"
rsync -a -e "$SSH_CMD" "$REMOTE:$REMOTE_PATH/$W/rapport.txt" "$JOURNAL_DIR/transfert_mySQL-rapport-$(date +%Y%m%d-%H%M).txt" 2>/dev/null \
    && ok "rapport copie dans deploy/logs/" || true
