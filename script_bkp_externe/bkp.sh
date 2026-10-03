#!/usr/bin/env bash
#
# Cote O2SWITCH : dump quotidien de la base d'Ultra-book dans BKP_DIR.
# Giga vient ensuite tirer les dumps, le code et storage/ (pull.sh).
#
#   bkp.sh dump        dump gzip, verifie, empreinte, garde KEEP_LOCAL versions (ligne du cron)
#   bkp.sh verifier    controle gzip + empreinte du dernier dump
#   bkp.sh etat        dumps presents
#   bkp.sh test-mail   envoie un mail d'essai
#
set -uo pipefail
CONFIG="$(dirname "$0")/bkp.config"
REQUIS="DB_NAME BKP_DIR MAIL_TO"
source "$(dirname "$0")/lib-bkp.sh"

MYSQL_DEFAULTS_FILE="${MYSQL_DEFAULTS_FILE:-$HOME/.my.cnf}"
KEEP_LOCAL="${KEEP_LOCAL:-3}"
DEBUT=$(date +%s)

verifier_dump() {
    local f="$1"
    gzip -t "$f" 2>/dev/null || { erreur "archive gzip corrompue : $f"; return 1; }
    zcat "$f" | tail -1 | grep -q 'Dump completed' || { erreur "dump incomplet (pas de « Dump completed ») : $f"; return 1; }
    if [ -f "$f.sha256" ] && [ "$(sha256sum "$f" | awk '{print $1}')" != "$(cat "$f.sha256")" ]; then
        erreur "empreinte differente : $f"; return 1
    fi
}

faire_dump() {
    etape "Dump de $DB_NAME"
    mkdir -p "$BKP_DIR"
    local f="$BKP_DIR/${DB_NAME}_$(date +%Y-%m-%d_%H%M).sql.gz"
    if ! mysqldump --defaults-extra-file="$MYSQL_DEFAULTS_FILE" \
            --single-transaction --quick --routines --triggers --events \
            --default-character-set=utf8mb4 "$DB_NAME" | gzip -6 > "$f.tmp"; then
        rm -f "$f.tmp"; erreur "mysqldump a echoue"; return 1
    fi
    # .tmp jusqu'a la fin : giga ne tire jamais un dump en cours d'ecriture.
    mv "$f.tmp" "$f"
    verifier_dump "$f" || return 1
    sha256sum "$f" | awk '{print $1}' > "$f.sha256"
    info "dump : $(basename "$f") ($(taille "$f"))"

    ls -1t "$BKP_DIR"/*.sql.gz | tail -n +"$((KEEP_LOCAL + 1))" | while read -r vieux; do
        rm -f "$vieux" "$vieux.sha256"
        info "purge : $(basename "$vieux")"
    done
}

case "${1:-}" in
    dump)
        journaliser dump; verrouiller
        faire_dump
        envoyer_rapport dump $(( $(date +%s) - DEBUT ))
        ;;
    verifier)
        f="$(ls -1t "$BKP_DIR"/*.sql.gz 2>/dev/null | head -1)"
        [ -n "$f" ] || { echo "aucun dump dans $BKP_DIR"; exit 1; }
        verifier_dump "$f" && echo "OK : $(basename "$f")"
        ;;
    etat)
        ls -lht "$BKP_DIR"/*.sql.gz 2>/dev/null || echo "aucun dump dans $BKP_DIR"
        ;;
    test-mail)
        MAIL_ON=toujours
        info "mail d'essai de bkp.sh (o2switch)"
        envoyer_rapport test-mail 0
        echo "mail envoye a $MAIL_TO"
        ;;
    *)
        sed -n '3,9p' "$0"; exit 1
        ;;
esac

[ "$ERREURS" -eq 0 ]
