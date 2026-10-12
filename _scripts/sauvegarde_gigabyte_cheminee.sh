#!/bin/bash
# Sauvegarde de la production (O2switch) vers le NUC GIGABYTE (Martel, « cheminee ») :
#   1. dump MySQL de la base de .env.prod dans _bkp_mysql/ (vide avant)
#   2. rsync du site complet, dump compris, avec incrementiel horaire cote NUC
#   3. mail de resume : etat du dump + statistiques rsync
#
# Cron (une fois par nuit, 3 h 15) :
#   15 3 * * * /home/sc4tapa2662/ultra-book.pro/_scripts/sauvegarde_gigabyte_cheminee.sh

set -uo pipefail

SITE=/home/sc4tapa2662/ultra-book.pro
DOSSIER_DUMP="$SITE/_bkp_mysql"
ENV_FICHIER="$SITE/.env.prod"
[ -f "$ENV_FICHIER" ] || ENV_FICHIER="$SITE/.env"

DEST=bkp_ubdf_2026@78.193.33.71:./_base
CLE=~/.ssh/rsa_key_bkp_ubdf_2026
MAIL_DE=log2017@polygun.fr
MAIL_A=log2017@polygun.fr

HORODATAGE=$(date +%d_%m_%y_%H)
JOURNAL=$(mktemp)
trap 'rm -f "$JOURNAL" "${CNF:-}"' EXIT

log() { echo "[$(date '+%d/%m/%Y %H:%M:%S')] $*" >> "$JOURNAL"; }

# Une seule sauvegarde a la fois : un rsync lent ne doit pas en chevaucher un autre.
exec 9>/tmp/sauvegarde_gigabyte_cheminee.lock
if ! flock -n 9; then
    echo "Sauvegarde precedente encore en cours, abandon." \
        | mail -s "[RSYNC] O2switch_UBDF_2026 : DEJA EN COURS" -r "$MAIL_DE" "$MAIL_A"
    exit 1
fi

# Lecture d'une cle du .env sans le sourcer (valeurs avec espaces, $, guillemets).
env_val() {
    grep -E "^$1=" "$ENV_FICHIER" | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'
}

DB_HOST=$(env_val DB_HOST); DB_PORT=$(env_val DB_PORT)
DB_DATABASE=$(env_val DB_DATABASE); DB_USERNAME=$(env_val DB_USERNAME)
DB_PASSWORD=$(env_val DB_PASSWORD)

# ---------------------------------------------------------------- 1. dump MySQL
ETAT_DUMP=ECHEC
DEBUT=$(date +%s)
log "Dump MySQL de « $DB_DATABASE » ($ENV_FICHIER)"

mkdir -p "$DOSSIER_DUMP"
chmod 700 "$DOSSIER_DUMP"
find "$DOSSIER_DUMP" -mindepth 1 -delete
# Garde-fou si le dossier venait a etre servi par Apache.
printf 'Require all denied\n' > "$DOSSIER_DUMP/.htaccess"

# Mot de passe dans un fichier 600, jamais en ligne de commande (visible par ps).
CNF=$(mktemp)
chmod 600 "$CNF"
printf '[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n' \
    "$DB_USERNAME" "$DB_PASSWORD" "${DB_HOST:-localhost}" "${DB_PORT:-3306}" > "$CNF"

DUMP="$DOSSIER_DUMP/${DB_DATABASE}-$(date +%Y%m%d-%H%M).sql.gz"
ERR_DUMP=$(mktemp)

if mysqldump --defaults-extra-file="$CNF" --single-transaction --quick \
        --routines --triggers --events --no-tablespaces --default-character-set=utf8mb4 \
        "$DB_DATABASE" 2>"$ERR_DUMP" | gzip -6 > "$DUMP"; then
    if gzip -t "$DUMP" 2>>"$ERR_DUMP" && zcat "$DUMP" | tail -1 | grep -q "Dump completed"; then
        ETAT_DUMP=OK
    else
        log "Dump incomplet : archive invalide ou fin « Dump completed » absente"
    fi
fi
chmod 600 "$DUMP" 2>/dev/null

[ -s "$ERR_DUMP" ] && log "Erreurs mysqldump :" && cat "$ERR_DUMP" >> "$JOURNAL"
rm -f "$ERR_DUMP" "$CNF"

log "Dump : $ETAT_DUMP — $(du -h "$DUMP" 2>/dev/null | cut -f1) en $(( $(date +%s) - DEBUT )) s — $(basename "$DUMP")"
if [ "$ETAT_DUMP" = OK ]; then
    log "Tables : $(zcat "$DUMP" | grep -c '^CREATE TABLE')"
fi

# ------------------------------------------------------------------- 2. rsync
# Le rsync part meme si le dump echoue : les fichiers (images) restent a
# sauvegarder ; l'echec du dump est signale dans l'objet du mail.
DEBUT=$(date +%s)
log "Rsync $SITE/ -> $DEST"

rsync -alHzcpogrh --stats --delete --partial --append-verify --timeout=360 \
    --exclude='lost+found' \
    --exclude='cache' --exclude='cache*' \
    --exclude='/storage/framework/cache/' \
    --exclude='/storage/framework/views/' \
    --exclude='/storage/framework/sessions/' \
    --exclude='/bootstrap/cache/' \
    --exclude='/node_modules/' \
    --backup --backup-dir="../_incrementiel/$HORODATAGE" \
    -e "ssh -p 8888 -i $CLE -o BatchMode=yes -o ConnectTimeout=30" \
    "$SITE/" "$DEST" >> "$JOURNAL" 2>&1
CODE_RSYNC=$?

# 24 = fichiers disparus pendant le transfert (cache, sessions) : sans gravite.
case $CODE_RSYNC in
    0|24) ETAT_RSYNC=OK ;;
    *) ETAT_RSYNC="ECHEC (code $CODE_RSYNC)" ;;
esac
log "Rsync : $ETAT_RSYNC en $(( $(date +%s) - DEBUT )) s"

# --------------------------------------------------------------------- 3. mail
if [ "$ETAT_DUMP" = OK ] && [ "$ETAT_RSYNC" = OK ]; then
    BILAN=OK
else
    BILAN="ECHEC (MySQL $ETAT_DUMP, rsync $ETAT_RSYNC)"
fi

{
    echo "Sauvegarde ultra-book.pro -> GIGABYTE NUC Martel"
    echo "Bilan : $BILAN"
    echo "MySQL : $ETAT_DUMP"
    echo "Rsync : $ETAT_RSYNC"
    echo "Incrementiel : _incrementiel/$HORODATAGE"
    echo
    cat "$JOURNAL"
} | mail -s "[RSYNC] O2switch_UBDF_2026 ultra-book.pro -> GIGABYTE NUC Martel : $BILAN" -r "$MAIL_DE" "$MAIL_A"

[ "$BILAN" = OK ]
