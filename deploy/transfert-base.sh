#!/usr/bin/env bash
#
# Transfert de la base MySQL de l'ancienne prod vers O2switch.
#
#   ./deploy/transfert-base.sh diagnostic    O2switch joint-il l'ancien serveur ?
#   ./deploy/transfert-base.sh recuperer     1) dump de l'ancien serveur -> data_sql/ (tire depuis O2switch, dans screen)
#        [--chemin=/home/adminpat/autre.sql.gz]
#   ./deploy/transfert-base.sh suivre        revenir sur la recuperation ou la conversion en cours
#   ./deploy/transfert-base.sh verifier      dump intact ? (gzip, « Dump completed », empreinte)
#   ./deploy/transfert-base.sh analyser      2) sauvegarde, import des tables legacy dans la base de prod, analyse
#   ./deploy/transfert-base.sh convertir     3) sauvegarde, confirmation, reprise vers les tables 2026 (dans screen)
#   ./deploy/transfert-base.sh reprendre --depuis=N   relance une reprise interrompue apres us_id N (sans rien vider)
#   ./deploy/transfert-base.sh rapport       rapatrie et affiche le dernier rapport de conversion
#   ./deploy/transfert-base.sh etat          dump, tables legacy, sauvegardes, espace
#   ./deploy/transfert-base.sh supprimer     fin de parcours : sauvegarde, puis retire le dump et les tables legacy
#
# Une seule base : sc4tapa2662_ultra-book-prod. Les tables legacy (inc_*,
# ub2_*, bn_*, df2_*, nl_*, wp_import) y sont importees a cote des tables
# 2026, sans collision de nom. Avant CHAQUE modification de cette base :
# sauvegarde horodatee en gzip dans ~/backups_deploy/.
#
# Le dump se fait a la main sur l'ancien serveur (voir Transfer-mysql.txt).
# Reglages : OLD_* , OLD_DUMP et DATA_SQL dans deploy.config.
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"
source "$(dirname "$0")/lib-ancien.sh"

: "${OLD_DUMP:?manque OLD_DUMP dans deploy.config (chemin du dump sur l ancien serveur)}"
DATA_SQL="${DATA_SQL:-data_sql}"

# Valeur d'une variable de .env.prod (local : il remplace celui du serveur a
# chaque deploiement, c'est donc lui qu'on modifie).
ENV_PROD="$PROJECT_DIR/.env.prod"
val() { grep -E "^$1=" "$ENV_PROD" | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }

DB_PROD="$(val DB_DATABASE)"
DB_USER="$(val DB_USERNAME)"
# Les tables legacy vivent dans la base de prod elle-meme.
DB_LEGACY="$DB_PROD"

# Seules tables qu'un dump legacy a le droit de creer ou d'ecraser. Le
# schema 2026 n'a aucune table a ces prefixes : un dump qui en contiendrait
# une autre (users, media...) est refuse avant l'import.
LEGACY_TABLES='^(inc_|ub2_|bn_|df2_|nl_|wp_import$)'

journaliser transfert-base "$@"

# --chemin= remplace OLD_DUMP pour recuperer un autre fichier.
for a in "$@"; do
    case "$a" in --chemin=*) OLD_DUMP="${a#--chemin=}" ;; esac
done
DUMP="$DATA_SQL/$(basename "$OLD_DUMP")"

# Ecrit (ou remplace) une variable dans .env.prod local.
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

# Controles du dump sur O2switch ; renvoie 1 si un controle echoue.
verifier_dump() {
    ssh_run "f='$DUMP'; \
        [ -f \"\$f\" ] || { echo \"   ABSENT : \$f (lancer : recuperer)\"; exit 1; }; \
        echo \"   fichier   : \$f (\$(du -h \"\$f\" | cut -f1))\"; \
        gzip -t \"\$f\" 2>/dev/null && echo '   gzip      : intact' || { echo '   gzip      : CORROMPU (transfert coupe ?)'; exit 1; }; \
        fin=\"\$(zcat \"\$f\" | tail -1)\"; \
        case \"\$fin\" in *'Dump completed'*) echo \"   fin       : \$fin\";; *) echo \"   fin       : PAS de « Dump completed » : export incomplet\"; exit 1;; esac; \
        echo \"   empreinte : \$(sha256sum \"\$f\" | cut -c1-16)\""
}

# Sauvegarde horodatee de la base de prod, verifiee ; arrete le script si
# elle est invalide. sauvegarder_prod <etiquette>
sauvegarder_prod() {
    ssh_script "$(date +%Y%m%d-%H%M%S) $1" <<'DISTANT'
set -euo pipefail
STAMP="$1"; ETIQUETTE="$2"
mkdir -p "$BACKUP_DIR"
val() { grep -E "^$1=" .env.prod | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
CNF="$(mktemp)"; chmod 600 "$CNF"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
SAUVEGARDE="$BACKUP_DIR/base-$STAMP-$ETIQUETTE.sql.gz"
mysqldump --defaults-extra-file="$CNF" --single-transaction --quick --no-tablespaces "$(val DB_DATABASE)" | gzip > "$SAUVEGARDE"
gzip -t "$SAUVEGARDE" && [ "$(stat -c %s "$SAUVEGARDE")" -gt 1000 ] || { echo "   SAUVEGARDE INVALIDE ($SAUVEGARDE) : arret, la base n'est pas modifiee"; exit 1; }
echo "   $SAUVEGARDE ($(du -h "$SAUVEGARDE" | cut -f1))"
DISTANT
}

empreinte_dump() {
    ssh_run "sha256sum '$DUMP' | cut -c1-16"
}

case "${1:-}" in
    diagnostic)
        diagnostic_ancien
        ;;

    recuperer)
        ligne
        echo "  1) ${ANCIEN}:${OLD_DUMP}"
        echo "     -> O2switch ${REMOTE_PATH}/${DUMP}"
        echo "  Relancable : un fichier coupe reprend la ou il s'etait arrete."
        ligne
        confirm "Lancer la recuperation ?" || exit 0
        printf '%s\n' \
            "mkdir -p '$DATA_SQL'" \
            "echo \"Mot de passe de l'ANCIEN serveur ($ANCIEN) :\"" \
            "rsync -a --partial --info=progress2 -e '$SSH_ANCIEN' '$ANCIEN:$OLD_DUMP' '$DATA_SQL/'" \
            "code=\$?" \
            "echo; echo \"Code de sortie rsync : \$code (0 = complet).\"" \
            "[ \$code -eq 0 ] && { gzip -t '$DUMP' && echo 'gzip : intact' || echo 'gzip : CORROMPU'; zcat '$DUMP' | tail -1; }" \
            "echo 'Entree pour fermer.'; read _" \
            | ssh_run "cat > storage/transfert-base.run"
        alerte "Mot de passe de l'ANCIEN serveur demande ci-dessous. Ctrl-A puis D : detacher."
        lancer_screen transfert-base storage/transfert-base.run
        ok "Ensuite : ./deploy/transfert-base.sh analyser"
        ;;

    suivre)
        sur_o2switch "screen -r transfert-base" || ok "aucune operation en cours (terminee ?)"
        ;;

    verifier)
        etape "Dump sur O2switch"
        verifier_dump
        ;;

    analyser)
        etape "1) Dump sur O2switch"
        verifier_dump || echec "dump absent ou incomplet : ./deploy/transfert-base.sh recuperer"

        etape "2) Tables du dump (seules les tables legacy sont acceptees)"
        # Noms des tables creees par le dump (le « . » remplace l'accent grave).
        INTRUSES="$(ssh_run "zcat '$DUMP' | grep -oE '^CREATE TABLE .[a-z0-9_]+' | cut -c15- | grep -vE '$LEGACY_TABLES' || true")"
        [ -z "$INTRUSES" ] || echec "le dump contient des tables hors legacy, refuse pour ne pas ecraser la prod : $INTRUSES"
        ok "$(ssh_run "zcat '$DUMP' | grep -c '^CREATE TABLE'") tables, toutes legacy"

        etape "3) Sauvegarde de ${DB_PROD} avant import"
        sauvegarder_prod avant-import-legacy

        etape "4) Import des tables legacy dans ${DB_PROD} (latin1, celui de l'export)"
        STAMP="$(date +%Y%m%d-%H%M%S)"
        ssh_script "'$DUMP' '$DB_PROD'" <<'DISTANT'
set -euo pipefail
DUMP="$1"; BASE="$2"
val() { grep -E "^$1=" .env.prod | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
CNF="$(mktemp)"; chmod 600 "$CNF"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
debut=$(date +%s)
gunzip -c "$DUMP" | mysql --defaults-extra-file="$CNF" --default-character-set=latin1 "$BASE"
echo "   import termine en $(( $(date +%s) - debut )) s"
DISTANT

        etape "5) Connexion legacy dans .env.prod (meme base, jeu de caracteres latin1)"
        if [ "$(val DB_LEGACY_DATABASE)" != "$DB_LEGACY" ]; then
            echo "   A ajouter dans .env.prod (local, puis envoye) :"
            echo "     DB_LEGACY_HOST=$(val DB_HOST)  DB_LEGACY_PORT=$(val DB_PORT)  DB_LEGACY_DATABASE=$DB_LEGACY"
            echo "     DB_LEGACY_USERNAME=$DB_USER  DB_LEGACY_PASSWORD=(celui de la prod)"
            confirm "Modifier .env.prod ?" O || echec "connexion legacy absente : analyse impossible"
            definir_env DB_LEGACY_HOST "$(val DB_HOST)"
            definir_env DB_LEGACY_PORT "$(val DB_PORT)"
            definir_env DB_LEGACY_SOCKET ""
            definir_env DB_LEGACY_DATABASE "$DB_LEGACY"
            definir_env DB_LEGACY_USERNAME "$DB_USER"
            definir_env DB_LEGACY_PASSWORD "$(val DB_PASSWORD)"
            envoyer_env
        else
            ok "deja en place"
        fi

        etape "6) Analyse (lecture seule)"
        RAPPORT="storage/logs/analyse-legacy-$STAMP.txt"
        artisan "ubdf:prod:analyser-legacy --rapport='$RAPPORT'"
        # Temoin lu par « convertir » : cette analyse porte sur ce dump-la.
        ssh_run "printf '%s %s\n' \"\$(sha256sum '$DUMP' | cut -c1-16)\" '$RAPPORT' > storage/app/analyse-legacy.ok"
        rsync -a -e "$SSH_CMD" "$REMOTE:$REMOTE_PATH/$RAPPORT" "$JOURNAL_DIR/" && ok "rapport copie dans deploy/logs/$(basename "$RAPPORT")"
        echo
        echo "   Tables 2026 intactes (seules les tables legacy ont ete ajoutees). Lire le rapport, puis :"
        echo "   ./deploy/transfert-base.sh convertir"
        ;;

    convertir)
        etape "1) Analyse prealable"
        TEMOIN="$(ssh_run "cat storage/app/analyse-legacy.ok 2>/dev/null || true")"
        [ -n "$TEMOIN" ] || echec "aucune analyse : ./deploy/transfert-base.sh analyser"
        [ "${TEMOIN%% *}" = "$(empreinte_dump)" ] || echec "le dump a change depuis l'analyse : relancer analyser"
        ssh_run "grep -E '^(SYNTHESE|! )' '${TEMOIN#* }'" || true

        ligne
        echo "  3) Reprise dans ${GRAS}${DB_PROD}${RAZ} : tables legacy -> tables 2026"
        echo "     Avant : sauvegarde horodatee (~/backups_deploy/base-<date>-avant-reprise.sql.gz)."
        echo "     ${ROUGE}Videes puis rechargees${RAZ} : comptes, books, visuels, pages, messages, factures,"
        echo "       stats, visiteurs, memos, parrainages, codes promo, offres, abonnes newsletter."
        echo "     ${VERT}Gardees${RAZ} : administrateurs, reglages, pages CMS, actualites, campagnes,"
        echo "       categories, selections."
        echo "     Site en maintenance pendant la reprise (1 h 30 a 2 h : rechiffrement des"
        echo "     mots de passe). Les images ne sont pas touchees (--skip-files)."
        ligne
        # CONFIRMER=CONVERTIR : lancement automatise (ssh avale l'entree standard).
        rep="${CONFIRMER:-}"; [ -n "$rep" ] || read -r -p "  Taper CONVERTIR pour lancer : " rep
        [ "$rep" = "CONVERTIR" ] || { echo "Annule."; exit 0; }

        etape "2) Sauvegarde de ${DB_PROD} avant reprise"
        sauvegarder_prod avant-reprise
        STAMP="$(date +%Y%m%d-%H%M%S)"

        etape "3) Reprise (dans screen)"
        RAPPORT="storage/logs/reprise-legacy-$STAMP.txt"
        printf '%s\n' \
            "PHP='$REMOTE_PHP'" \
            "echo '== maintenance'; \$PHP artisan tinker --execute='App\\Models\\Reglage::definir(App\\Models\\Reglage::MAINTENANCE, true);'" \
            "echo '== migrations'; \$PHP artisan migrate --force || { echo 'ECHEC migrations : site laisse en maintenance'; read _; exit 1; }" \
            "debut=\$(date +%s)" \
            "\$PHP artisan ubdf:migrate-legacy --tous --fresh --skip-files --rapport='$RAPPORT' 2>&1 | tee 'storage/logs/reprise-legacy-$STAMP.log'" \
            "if [ \${PIPESTATUS[0]} -ne 0 ]; then echo; echo 'ECHEC de la reprise : site LAISSE EN MAINTENANCE.'; echo 'Relance : --depuis=<dernier us_id affiche>, ou restaurer la sauvegarde.'; read _; exit 1; fi" \
            "echo \"Duree : \$(( (\$(date +%s) - debut) / 60 )) min\" >> '$RAPPORT'" \
            "\$PHP artisan cache:clear > /dev/null" \
            "echo '== fin de maintenance'; \$PHP artisan tinker --execute='App\\Models\\Reglage::definir(App\\Models\\Reglage::MAINTENANCE, false);'" \
            "echo; cat '$RAPPORT'; echo; echo 'Rapport : $RAPPORT. Entree pour fermer.'; read _" \
            | ssh_run "cat > storage/transfert-base.run"
        alerte "Ctrl-A puis D : detacher. Revenir : ./deploy/transfert-base.sh suivre"
        lancer_screen transfert-base storage/transfert-base.run
        ok "Rapport : ./deploy/transfert-base.sh rapport"
        ;;

    reprendre)
        DEPUIS=""
        for a in "$@"; do case "$a" in --depuis=[0-9]*) DEPUIS="${a#--depuis=}" ;; esac; done
        [ -n "$DEPUIS" ] || echec "preciser --depuis=<us_id du dernier paquet complet> (ligne « relance : --depuis=... » du journal)"
        ligne
        echo "  Reprise relancee apres us_id $DEPUIS, SANS vider les tables (les paquets"
        echo "  deja passes restent ; un paquet rejoue ne cree pas de doublon)."
        echo "  Le site reste en maintenance jusqu'a la fin."
        ligne
        rep="${CONFIRMER:-}"; [ -n "$rep" ] || read -r -p "  Taper REPRENDRE pour lancer : " rep
        [ "$rep" = "REPRENDRE" ] || { echo "Annule."; exit 0; }
        ssh_run "screen -S transfert-base -X quit 2>/dev/null || true"
        STAMP="$(date +%Y%m%d-%H%M%S)"
        RAPPORT="storage/logs/reprise-legacy-$STAMP.txt"
        printf '%s\n' \
            "PHP='$REMOTE_PHP'" \
            "echo '== maintenance'; \$PHP artisan tinker --execute='App\\Models\\Reglage::definir(App\\Models\\Reglage::MAINTENANCE, true);'" \
            "debut=\$(date +%s)" \
            "\$PHP artisan ubdf:migrate-legacy --tous --skip-files --depuis=$DEPUIS --rapport='$RAPPORT' 2>&1 | tee 'storage/logs/reprise-legacy-$STAMP.log'" \
            "if [ \${PIPESTATUS[0]} -ne 0 ]; then echo; echo 'ECHEC de la reprise : site LAISSE EN MAINTENANCE.'; echo 'Relance : reprendre --depuis=<dernier us_id affiche>.'; read _; exit 1; fi" \
            "echo \"Duree : \$(( (\$(date +%s) - debut) / 60 )) min\" >> '$RAPPORT'" \
            "\$PHP artisan cache:clear > /dev/null" \
            "echo '== fin de maintenance'; \$PHP artisan tinker --execute='App\\Models\\Reglage::definir(App\\Models\\Reglage::MAINTENANCE, false);'" \
            "echo; cat '$RAPPORT'; echo; echo 'Rapport : $RAPPORT. Entree pour fermer.'; read _" \
            | ssh_run "cat > storage/transfert-base.run"
        lancer_screen transfert-base storage/transfert-base.run
        ;;

    rapport)
        DERNIER="$(ssh_run "ls -1t storage/logs/reprise-legacy-*.txt 2>/dev/null | head -1")"
        [ -n "$DERNIER" ] || echec "aucun rapport de reprise sur le serveur"
        rsync -a -e "$SSH_CMD" "$REMOTE:$REMOTE_PATH/$DERNIER" "$JOURNAL_DIR/"
        cat "$JOURNAL_DIR/$(basename "$DERNIER")"
        ok "copie : deploy/logs/$(basename "$DERNIER")"
        ;;

    etat)
        ssh_run "echo \"dump        : \$(ls -lh $DATA_SQL/*.gz 2>/dev/null | awk '{print \$5, \$9}' || echo aucun)\"; \
            echo \"analyse     : \$(cat storage/app/analyse-legacy.ok 2>/dev/null || echo aucune)\"; \
            echo \"sauvegardes : \$(ls -1t \$HOME/backups_deploy/base-*.sql.gz 2>/dev/null | head -3 | tr '\n' ' ')\"; \
            echo \"en cours    : \$(screen -ls 2>/dev/null | grep -c transfert-base || true) session(s) screen\"; \
            df -h ~ | tail -1"
        ssh_script "'$DB_PROD' '$LEGACY_TABLES'" <<'DISTANT'
BASE="$1"; MOTIF="$2"
val() { grep -E "^$1=" .env.prod | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
CNF="$(mktemp)"; chmod 600 "$CNF"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
mysql --defaults-extra-file="$CNF" -N -e "select table_name, data_length+index_length from information_schema.tables where table_schema='$BASE'" \
    | grep -E "$MOTIF" | awk '{n++; o+=$2} END {printf "tables legacy dans la base : %d (%.0f Mo)\n", n, o/1048576}'
DISTANT
        ;;

    supprimer)
        ligne
        echo "  Retire : le dump ${DUMP}, les tables legacy de ${DB_PROD}"
        echo "  (inc_*, ub2_*, bn_*, df2_*, nl_*, wp_import), et DB_LEGACY_* de .env.prod."
        echo "  Les tables 2026 ne sont pas touchees. Sauvegarde horodatee avant."
        echo "  ${ROUGE}A faire seulement quand la reprise est validee ET que « organiser »${RAZ}"
        echo "  ${ROUGE}a tourne sur toutes les lettres${RAZ} (il lit les tables legacy)."
        ligne
        rep="${CONFIRMER:-}"; [ -n "$rep" ] || read -r -p "  Taper SUPPRIMER pour confirmer : " rep
        [ "$rep" = "SUPPRIMER" ] || { echo "Annule."; exit 0; }

        etape "1) Sauvegarde de ${DB_PROD}"
        sauvegarder_prod avant-suppression-legacy

        etape "2) Tables legacy"
        ssh_script "'$DB_PROD' '$LEGACY_TABLES'" <<'DISTANT'
set -euo pipefail
BASE="$1"; MOTIF="$2"
val() { grep -E "^$1=" .env.prod | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
CNF="$(mktemp)"; chmod 600 "$CNF"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
TABLES="$(mysql --defaults-extra-file="$CNF" -N -e "select table_name from information_schema.tables where table_schema='$BASE'" | grep -E "$MOTIF" || true)"
[ -n "$TABLES" ] || { echo "   aucune table legacy"; exit 0; }
for t in $TABLES; do mysql --defaults-extra-file="$CNF" "$BASE" -e "DROP TABLE \`$t\`"; done
echo "   $(echo "$TABLES" | wc -l) tables legacy supprimees"
DISTANT

        etape "3) Dump et connexion legacy"
        ssh_run "rm -f '$DUMP' storage/app/analyse-legacy.ok; rmdir '$DATA_SQL' 2>/dev/null || true"
        for v in DB_LEGACY_HOST DB_LEGACY_PORT DB_LEGACY_SOCKET DB_LEGACY_DATABASE DB_LEGACY_USERNAME DB_LEGACY_PASSWORD; do
            sed -i '' -E "/^$v=/d" "$ENV_PROD"
        done
        envoyer_env
        ;;

    *)
        sed -n '3,17p' "$0" | sed 's/^# \{0,1\}//'
        exit 1
        ;;
esac
