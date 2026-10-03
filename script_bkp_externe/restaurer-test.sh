#!/usr/bin/env bash
#
# Test de restauration : importe le dernier dump dans TEST_DB (base vide
# dediee, jamais la prod), compare le nombre de tables, puis vide TEST_DB.
# Cron mensuel sur o2switch. Rapport par mail a chaque test.
#
set -uo pipefail
CONFIG="$(dirname "$0")/bkp.config"
REQUIS="DB_NAME BKP_DIR MAIL_TO TEST_DB"
source "$(dirname "$0")/lib-bkp.sh"

MYSQL_DEFAULTS_FILE="${MYSQL_DEFAULTS_FILE:-$HOME/.my.cnf}"
MAIL_ON=toujours
DEBUT=$(date +%s)
journaliser restauration; verrouiller

: "${TEST_DB:?manque TEST_DB dans bkp.config}"
[ "$TEST_DB" != "$DB_NAME" ] || { erreur "TEST_DB ne doit pas etre la base de prod"; envoyer_rapport restauration 0; exit 1; }

MY=(mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" -N -B)

vider_test() {
    local t
    for t in $("${MY[@]}" -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$TEST_DB'"); do
        echo "DROP TABLE IF EXISTS \`$t\`;"
    done | { echo "SET FOREIGN_KEY_CHECKS=0;"; cat; } | "${MY[@]}" "$TEST_DB"
}

f="$(ls -1t "$BKP_DIR"/*.sql.gz 2>/dev/null | head -1)"
if [ -z "$f" ]; then
    erreur "aucun dump dans $BKP_DIR"
else
    etape "Restauration de $(basename "$f") dans $TEST_DB"
    vider_test
    if zcat "$f" | "${MY[@]}" "$TEST_DB"; then
        attendu="$("${MY[@]}" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME'")"
        obtenu="$("${MY[@]}" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$TEST_DB'")"
        users="$("${MY[@]}" -e "SELECT COUNT(*) FROM \`$TEST_DB\`.users" 2>/dev/null || echo '?')"
        if [ "$obtenu" -gt 0 ] && [ "$obtenu" -ge "$attendu" ]; then
            info "restauration OK : $obtenu tables (prod : $attendu), $users users"
        else
            erreur "restauration incomplete : $obtenu tables sur $attendu"
        fi
    else
        erreur "import du dump a echoue"
    fi
    vider_test
fi

envoyer_rapport restauration $(( $(date +%s) - DEBUT ))
[ "$ERREURS" -eq 0 ]
