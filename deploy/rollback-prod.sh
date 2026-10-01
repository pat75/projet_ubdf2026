#!/usr/bin/env bash
#
# Restaure une sauvegarde creee par deploy-prod.sh.
#
#   ./deploy/rollback-prod.sh            liste les sauvegardes
#   ./deploy/rollback-prod.sh <STAMP>    restaure le code (et, sur confirmation, la base)
#
# Le code est remis a l'identique (rsync --delete, hors storage/ et vendor/),
# puis vendor/ est reconstruit par composer et les caches regeneres.
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"

if [ -z "${1:-}" ]; then
    echo "Sauvegardes disponibles (code / base) :"
    ssh_run "ls -1t \"$BACKUP_DIR\"/code-*.tgz 2>/dev/null | sed 's#.*/code-##; s#\.tgz##' \
        | while read s; do test -f \"$BACKUP_DIR/base-\$s.sql.gz\" && echo \"  \$s  (code + base)\" || echo \"  \$s  (code)\"; done" \
        || echo "  (aucune)"
    echo; echo "Historique : ./deploy/ssh-prod.sh \"tail $BACKUP_DIR/deploy.log\""
    echo "Restaurer  : ./deploy/rollback-prod.sh <STAMP>"
    exit 0
fi

STAMP="$1"
ssh_run "test -f \"$BACKUP_DIR/code-$STAMP.tgz\"" || echec "sauvegarde introuvable : code-$STAMP.tgz"

ligne
echo "  Rollback $STAMP -> ${REMOTE}:${REMOTE_PATH}"
echo "  Le code (et .env) revient a l'etat exact de la sauvegarde ;"
echo "  les fichiers apparus depuis sont supprimes. storage/ n'est pas touche."
ligne
confirm "Restaurer le code ?" || { echo "Annule."; exit 0; }

RESTAURER_BASE=0
if ssh_run "test -f \"$BACKUP_DIR/base-$STAMP.sql.gz\""; then
    alerte "Une sauvegarde de la base existe pour $STAMP."
    alerte "La restaurer ECRASE toutes les donnees saisies depuis (inscriptions, messages, paiements…)."
    confirm "Restaurer aussi la base ?" && RESTAURER_BASE=1
fi

artisan down --retry=60 >/dev/null || true

ssh_script "$STAMP" "$RESTAURER_BASE" <<'DISTANT'
set -euo pipefail
STAMP="$1"; BASE="$2"
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
tar xzf "$BACKUP_DIR/code-$STAMP.tgz" -C "$TMP"
rsync -a --delete --exclude=storage/ --exclude=vendor/ --exclude=node_modules/ "$TMP/" ./
echo "   code restaure"

if [ "$BASE" = "1" ]; then
    val() { grep -E "^$1=" .env | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
    CNF="$TMP/my.cnf"
    printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
    gunzip -c "$BACKUP_DIR/base-$STAMP.sql.gz" | mysql --defaults-extra-file="$CNF" "$(val DB_DATABASE)"
    echo "   base restauree"
fi
DISTANT

composer_install
caches
artisan queue:restart >/dev/null
artisan up >/dev/null

ssh_run "echo '$(date +%Y%m%d-%H%M%S) rollback $STAMP base=$RESTAURER_BASE $(whoami)' >> \"$BACKUP_DIR/deploy.log\""
ligne
echo "${VERT}Rollback termine ($STAMP).${RAZ}"
ligne
