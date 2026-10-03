#!/usr/bin/env bash
#
# Cote GIGA : tire d'o2switch le code, storage/ et les dumps MySQL, en
# versions datees incrementales. Giga ouvre la connexion : sa cle privee ne
# le quitte jamais, et un o2switch compromis ne peut pas toucher aux sauvegardes.
#
#   pull.sh tout       tirage si INTERVAL_DAYS est ecoule, verifications, rotation, mail (ligne du cron)
#   pull.sh tirer      tirage immediat (ignore INTERVAL_DAYS)
#   pull.sh etat       versions, dernier tirage, espace disque
#   pull.sh test-ssh   connexion a o2switch
#   pull.sh test-mail  envoie un mail d'essai
#
# DEST_DIR/AAAA-MM-JJ_HHMM/{app,bkp_mysql}, lien « latest » vers la derniere
# version. --link-dest : chaque version est complete, mais les fichiers
# inchanges n'y sont stockes qu'une fois (liens physiques).
#
set -uo pipefail
CONFIG="$(dirname "$0")/pull.config"
REQUIS="SRC_USER SRC_HOST SSH_KEY SRC_APP SRC_DUMPS DEST_DIR MAIL_TO"
source "$(dirname "$0")/lib-bkp.sh"

SRC_PORT="${SRC_PORT:-22}"
KEEP_VERSIONS="${KEEP_VERSIONS:-8}"
INTERVAL_DAYS="${INTERVAL_DAYS:-4}"
ALERTE_DAYS="${ALERTE_DAYS:-5}"
DUMP_MAX_HOURS="${DUMP_MAX_HOURS:-36}"
SOURCE="${SRC_USER}@${SRC_HOST}"
SSH_OPTS="-p $SRC_PORT -i $SSH_KEY -o IdentitiesOnly=yes -o BatchMode=yes -o ConnectTimeout=15"
ETAT_PULL="$LOG_DIR/.dernier_tirage"          # epoch du dernier tirage reussi
DEBUT=$(date +%s)

jours_depuis_tirage() {
    [ -f "$ETAT_PULL" ] || { echo 9999; return; }
    echo $(( ($(date +%s) - $(cat "$ETAT_PULL")) / 86400 ))
}

tirer() {
    etape "Tirage depuis $SOURCE"
    ssh $SSH_OPTS "$SOURCE" true || { erreur "o2switch injoignable ($SRC_HOST:$SRC_PORT)"; return 1; }

    local partiel="$DEST_DIR/.partial" ex=() m
    mkdir -p "$DEST_DIR"
    rm -rf "$partiel"
    for m in $EXCLUDES; do ex+=(--exclude="$m"); done
    local base=(-aH --delete --stats -e "ssh $SSH_OPTS")

    rsync "${base[@]}" "${ex[@]}" --link-dest="$DEST_DIR/latest/app/" \
        "$SOURCE:$SRC_APP/" "$partiel/app/" \
        || { erreur "rsync du code/storage a echoue (code $?)"; return 1; }
    rsync "${base[@]}" --exclude='*.tmp' --link-dest="$DEST_DIR/latest/bkp_mysql/" \
        "$SOURCE:$SRC_DUMPS/" "$partiel/bkp_mysql/" \
        || { erreur "rsync des dumps a echoue (code $?)"; return 1; }

    verifier_dumps "$partiel/bkp_mysql" || return 1

    # Version validee seulement une fois complete et verifiee, puis rotation.
    local version
    version="$(date +%Y-%m-%d_%H%M)"
    mv "$partiel" "$DEST_DIR/$version"
    ln -sfn "$version" "$DEST_DIR/latest"
    (cd "$DEST_DIR" && ls -1d 20??-??-??_???? | sort | head -n -"$KEEP_VERSIONS" | while read -r vieux; do
        rm -rf "$vieux"; info "purge : $vieux"
    done)

    date +%s > "$ETAT_PULL"
    info "version $version : app $(taille "$DEST_DIR/$version/app"), dumps $(taille "$DEST_DIR/$version/bkp_mysql")"
    info "versions conservees : $(cd "$DEST_DIR" && ls -1d 20??-??-??_???? | wc -l), total $(taille "$DEST_DIR")"
}

# Dumps tires : empreinte, gzip, et fraicheur du plus recent.
verifier_dumps() {
    local dossier="$1" f dernier age
    for f in "$dossier"/*.sql.gz; do
        [ -f "$f" ] || { erreur "aucun dump tire d'o2switch"; return 1; }
        if [ -f "$f.sha256" ] && [ "$(sha256sum "$f" | awk '{print $1}')" != "$(cat "$f.sha256")" ]; then
            erreur "empreinte differente apres transfert : $(basename "$f")"; return 1
        fi
        gzip -t "$f" 2>/dev/null || { erreur "gzip corrompu : $(basename "$f")"; return 1; }
    done
    dernier="$(ls -1t "$dossier"/*.sql.gz | head -1)"
    age=$(( ($(date +%s) - $(stat -c %Y "$dernier")) / 3600 ))
    if [ "$age" -gt "$DUMP_MAX_HOURS" ]; then
        erreur "dernier dump vieux de ${age}h : le cron de bkp.sh tourne-t-il sur o2switch ?"
    else
        info "dernier dump : $(basename "$dernier") (${age}h, $(taille "$dernier"))"
    fi
}

case "${1:-}" in
    tout)
        journaliser tout; verrouiller
        jours="$(jours_depuis_tirage)"
        if [ "$jours" -ge "$INTERVAL_DAYS" ]; then
            tirer
        else
            # Pas de tirage aujourd'hui : rien a signaler, sauf erreur.
            MAIL_ON=erreur
            info "dernier tirage il y a $jours jour(s), prochain a $INTERVAL_DAYS"
        fi
        jours="$(jours_depuis_tirage)"
        [ "$jours" -lt "$ALERTE_DAYS" ] || erreur "aucun tirage reussi depuis $jours jour(s) (seuil $ALERTE_DAYS)"
        envoyer_rapport tout $(( $(date +%s) - DEBUT ))
        ;;
    tirer)
        journaliser tirer; verrouiller
        tirer
        envoyer_rapport tirer $(( $(date +%s) - DEBUT ))
        ;;
    etat)
        echo "Dernier tirage reussi : il y a $(jours_depuis_tirage) jour(s)"
        (cd "$DEST_DIR" 2>/dev/null && ls -1d 20??-??-??_???? && du -sh . && df -h .) || echo "aucune version dans $DEST_DIR"
        ;;
    test-ssh)
        ssh $SSH_OPTS "$SOURCE" "ls -lh '$SRC_DUMPS'" && echo "OK : o2switch joignable"
        ;;
    test-mail)
        MAIL_ON=toujours
        info "mail d'essai de pull.sh (giga)"
        envoyer_rapport test-mail 0
        echo "mail envoye a $MAIL_TO"
        ;;
    *)
        sed -n '3,12p' "$0"; exit 1
        ;;
esac

[ "$ERREURS" -eq 0 ]
