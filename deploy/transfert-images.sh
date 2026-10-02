#!/usr/bin/env bash
#
# Transfert des images des books : ancien serveur -> O2switch.
#
#   ./deploy/transfert-images.sh diagnostic  ou ca bloque : reseau, port, filtre O2switch, mot de passe
#   ./deploy/transfert-images.sh test        O2switch joint-il l'ancien serveur ?
#   ./deploy/transfert-images.sh recuperer   1) rsync ancien users_2 -> data_source/ (tire depuis O2switch, dans screen)
#   ./deploy/transfert-images.sh suivre      revenir sur le transfert en cours
#   ./deploy/transfert-images.sh organiser   2) data_source/ -> storage/app/public/books/a/d/o/login
#        [--dry-run] [--lettres=a-f | --depuis=m --jusqua=f] [--copier | --deplacer]
#        Sans --copier/--deplacer, le mode est demande ; il est toujours
#        rappele et confirme juste avant l'execution.
#   ./deploy/transfert-images.sh suivre-organiser   revenir sur le rangement en cours
#   ./deploy/transfert-images.sh etat        volumes et espace disque
#
# Connexion a l'ancien serveur par MOT DE PASSE, demande au lancement.
# Reglages : OLD_* et DATA_SOURCE dans deploy.config.
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"

: "${OLD_SSH_USER:?manque OLD_SSH_USER dans deploy.config}"
: "${OLD_SSH_HOST:?manque OLD_SSH_HOST dans deploy.config}"
: "${OLD_USERS_PATH:?manque OLD_USERS_PATH dans deploy.config}"
OLD_SSH_PORT="${OLD_SSH_PORT:-22}"
DATA_SOURCE="${DATA_SOURCE:-data_source}"
CIBLE="$REMOTE_PATH/$DATA_SOURCE"
ANCIEN="${OLD_SSH_USER}@${OLD_SSH_HOST}"
SSH_ANCIEN="ssh -p $OLD_SSH_PORT -o StrictHostKeyChecking=accept-new -o PubkeyAuthentication=no"

# Seuls les dossiers d'originaux sont rapatries (voir LegacyFiles) : les
# declinaisons (img_ptf_small, img_iph_*…) se regenerent, inutile de les copier.
FILTRE="'--include=/*/' '--include=/*/*/' '--include=/*/*/*/' \
'--include=/*/*/*/img_/***' '--include=/*/*/*/img_adm_medium/***' '--include=/*/*/*/img_ptf_medium/***' \
'--include=/*/*/*/cms_pref/***' '--include=/*/*/*/img_cms/***' '--exclude=*'"

# Journal de chaque lancement, cote poste : deploy/logs/transfert-images-<date>.log
JOURNAL_DIR="$SCRIPT_DIR/logs"
mkdir -p "$JOURNAL_DIR"
JOURNAL="$JOURNAL_DIR/transfert-images-$(date +%Y%m%d-%H%M%S)-${1:-aide}.log"
exec > >(tee -a "$JOURNAL") 2>&1
echo "# $(date '+%F %T')  $0 $*  (journal : $JOURNAL)"

# Port joignable depuis O2switch ? « ouvert », « refuse » ou « muet » (expire).
port_depuis_o2switch() {
    ssh_run "timeout 6 bash -c '</dev/tcp/$1/$2' 2>/dev/null && echo ouvert || { [ \$? -eq 124 ] && echo muet || echo refuse; }"
}

# Execute sur O2switch, avec terminal (saisie du mot de passe de l'ancien serveur).
sur_o2switch() {
    $SSH_CMD -t "$REMOTE" "cd '$REMOTE_PATH' && $*"
}

case "${1:-}" in
    diagnostic)
        etape "1) Depuis ce poste -> ${OLD_SSH_HOST}:${OLD_SSH_PORT}"
        if nc -z -G 6 "$OLD_SSH_HOST" "$OLD_SSH_PORT" 2>/dev/null; then ok "port ouvert depuis le poste"; else alerte "port ferme depuis le poste aussi : sshd arrete, autre port, ou pare-feu de l'ancien serveur"; fi

        etape "2) IP de sortie d'O2switch (celle a autoriser sur l'ancien serveur)"
        echo "   $(ssh_run 'curl -s --max-time 6 https://ifconfig.me || echo inconnue')"

        etape "3) Depuis O2switch -> ${OLD_SSH_HOST}:${OLD_SSH_PORT}"
        ETAT="$(port_depuis_o2switch "$OLD_SSH_HOST" "$OLD_SSH_PORT")"
        echo "   ${OLD_SSH_HOST}:${OLD_SSH_PORT} : $ETAT"

        etape "4) Filtre sortant d'O2switch (portquiz.net ecoute sur tous les ports)"
        for p in "$OLD_SSH_PORT" 2083 443; do
            echo "   sortie vers le port $p : $(port_depuis_o2switch portquiz.net "$p")"
        done
        QUIZ="$(port_depuis_o2switch portquiz.net "$OLD_SSH_PORT")"

        etape "Conclusion"
        case "$ETAT" in
            ouvert) ok "reseau OK : lancer ./deploy/transfert-images.sh test (mot de passe)";;
            muet)   alerte "paquets perdus : pare-feu de l'ancien serveur (DROP). Y autoriser l'IP de l'etape 2.";;
            refuse) if [ "$QUIZ" != ouvert ]; then
                        alerte "filtre SORTANT d'O2switch : le port $OLD_SSH_PORT ne sort pas, vers aucun serveur."
                        alerte "L'ancien serveur n'y est pour rien. Faire ecouter son SSH sur 2083 (autorise par O2switch,"
                        alerte "ouvert dans csf.allow) et mettre OLD_SSH_PORT=\"2083\" dans deploy.config."
                    else
                        alerte "O2switch laisse sortir le port $OLD_SSH_PORT : c'est l'ancien serveur qui refuse"
                        alerte "(CSF : csf -g <IP de l etape 2>, blocage LFD ?). Voir csf.allow / csf.ignore."
                    fi
                    alerte "Voir tutoriel_transfert_data_image.txt, section BLOCAGE SSH SORTANT.";;
        esac
        ;;

    test)
        etape "Connexion O2switch -> ${ANCIEN}:${OLD_SSH_PORT}"
        alerte "Mot de passe de l'ANCIEN serveur demande ci-dessous."
        sur_o2switch "$SSH_ANCIEN -v -o ConnectTimeout=15 $ANCIEN 'ls -d $OLD_USERS_PATH/*/ | head -5; du -sh $OLD_USERS_PATH 2>/dev/null'"
        ;;

    recuperer)
        ligne
        echo "  1) ${ANCIEN}:${OLD_USERS_PATH}/"
        echo "     -> O2switch ${CIBLE}/"
        echo "  Originaux seulement (img_, img_adm_medium, img_ptf_medium, cms_pref, img_cms)."
        echo "  Relancable : ne recopie que ce qui manque ; reprend un fichier coupe."
        ligne
        confirm "Lancer le transfert ?" || exit 0
        # Le transfert tourne dans screen : il survit a une coupure SSH ou a la
        # fermeture du terminal. Ctrl-A puis D pour s'en detacher.
        ssh_run "cat > '$REMOTE_PATH/storage/transfert-images.run'" <<EOF
mkdir -p '$CIBLE'
echo "Mot de passe de l'ANCIEN serveur ($ANCIEN) :"
rsync -a --partial --info=progress2 --no-perms $FILTRE -e '$SSH_ANCIEN' '$ANCIEN:$OLD_USERS_PATH/' '$CIBLE/'
echo; echo "Code de sortie rsync : \$? (0 = complet). Entree pour fermer."; read _
EOF
        alerte "Mot de passe de l'ANCIEN serveur demande ci-dessous. Ctrl-A puis D : detacher."
        sur_o2switch "screen -S transfert-images bash storage/transfert-images.run"
        ok "Revenir sur le transfert : ./deploy/transfert-images.sh suivre"
        echo "   Une fois termine : ./deploy/transfert-images.sh organiser --dry-run"
        ;;

    suivre)
        sur_o2switch "screen -r transfert-images" || ok "aucun transfert en cours (termine ?)"
        ;;

    organiser)
        shift
        ESSAI=0; MODE=""; DEPUIS=""; JUSQUA=""
        for a in "$@"; do
            case "$a" in
                --dry-run)     ESSAI=1 ;;
                --copier)      MODE=copier ;;
                --deplacer)    MODE=deplacer ;;
                --lettres=?-?) DEPUIS="${a:10:1}"; JUSQUA="${a:12:1}" ;;
                --depuis=*)    DEPUIS="${a#--depuis=}" ;;
                --jusqua=*)    JUSQUA="${a#--jusqua=}" ;;
                *) echec "option inconnue : $a (--copier, --deplacer, --lettres=a-f, --depuis=m, --jusqua=f, --dry-run)" ;;
            esac
        done
        PLAGE="toutes les lettres"
        [ -n "$DEPUIS$JUSQUA" ] && PLAGE="lettres ${DEPUIS:-debut} a ${JUSQUA:-fin}"
        OPTIONS="--source='$CIBLE'"
        [ -n "$DEPUIS" ] && OPTIONS="$OPTIONS --depuis=$DEPUIS"
        [ -n "$JUSQUA" ] && OPTIONS="$OPTIONS --jusqua=$JUSQUA"

        if [ "$ESSAI" = "1" ]; then
            ligne
            echo "  2) SIMULATION, $PLAGE : rien n'est copie ni deplace."
            ligne
            artisan ubdf:prod:dossiers-books $OPTIONS --dry-run | tail -40
            exit 0
        fi

        # Mode choisi et valide JUSTE avant l'execution, jamais par defaut.
        if [ -z "$MODE" ]; then
            echo
            echo "  Copier ou deplacer les originaux ?"
            echo "    c) COPIER   : data_source reste intact, recuperer peut etre relance."
            echo "                  Prend autant de place en plus que ce qui est copie."
            echo "    d) DEPLACER : instantane, sans place en plus, mais data_source se vide :"
            echo "                  un recuperer relance retelechargerait tout ce qui est parti."
            read -r -p "  Choix [c/d] : " rep
            case "$rep" in c|C) MODE=copier ;; d|D) MODE=deplacer ;; *) echo "Annule."; exit 0 ;; esac
        fi
        [ "$MODE" = "deplacer" ] && OPTIONS="$OPTIONS --deplacer"

        ligne
        echo "  2) ${CIBLE}/a/d/login -> storage/app/public/books/a/d/o/login"
        echo "     Perimetre : ${GRAS}${PLAGE}${RAZ}"
        if [ "$MODE" = "copier" ]; then
            echo "     Mode      : ${GRAS}${VERT}COPIER${RAZ} (data_source n'est pas modifie ;"
            echo "                 une relance ne recopie que les fichiers changes)"
            ssh_run "echo \"     Espace    : data_source \$(du -sh '$CIBLE' 2>/dev/null | cut -f1), libre \$(df -h . | awk 'NR==2{print \$4}')\""
        else
            echo "     Mode      : ${GRAS}${ROUGE}DEPLACER${RAZ} (data_source se vide de ce qui est repris)"
        fi
        ligne
        confirm "Confirmer : $(echo "$MODE" | tr '[:lower:]' '[:upper:]'), $PLAGE ?" || exit 0

        # Dans screen, comme recuperer : progression sur une ligne, survit a une
        # coupure SSH (Ctrl-A puis D pour detacher). Journal complet, resume
        # final compris, dans storage/logs/.
        JOURNAL="storage/logs/dossiers-books-$(date +%Y%m%d-%H%M%S)-$MODE.log"
        printf '%s\n' \
            "'$REMOTE_PHP' artisan ubdf:prod:dossiers-books $OPTIONS 2>&1 | tee '$JOURNAL'" \
            "'$REMOTE_PHP' artisan storage:link > /dev/null 2>&1" \
            "echo; echo 'Journal : $JOURNAL. Entree pour fermer.'; read _" \
            | ssh_run "cat > storage/organiser-images.run"
        alerte "Ctrl-A puis D : detacher. Revenir : ./deploy/transfert-images.sh suivre-organiser"
        sur_o2switch "screen -S organiser-images bash storage/organiser-images.run"
        ;;

    suivre-organiser)
        sur_o2switch "screen -r organiser-images" || ok "aucun rangement en cours (termine ?)"
        ;;

    etat)
        ssh_run "echo 'data_source : '\$(du -sh '$CIBLE' 2>/dev/null | cut -f1); \
            echo 'books       : '\$(du -sh storage/app/public/books 2>/dev/null | cut -f1); \
            echo 'books (nb)  : '\$(find storage/app/public/books -mindepth 4 -maxdepth 4 -type d 2>/dev/null | wc -l); \
            echo; quota -s 2>/dev/null || df -h ~ | tail -1"
        ;;

    *)
        sed -n '3,18p' "$0" | sed 's/^# \{0,1\}//'
        exit 1
        ;;
esac
