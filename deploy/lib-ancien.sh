#!/usr/bin/env bash
#
# Fonctions communes aux scripts qui tirent des donnees de l'ancien serveur
# vers O2switch (transfert-images.sh, transfert-base.sh). A sourcer apres
# lib.sh.
#
# Reglages OLD_* dans deploy.config. Connexion a l'ancien serveur par MOT DE
# PASSE, demande au lancement, jamais ecrit. O2switch bloque le port 22 en
# sortie : OLD_SSH_PORT doit etre un port autorise (2083), voir
# tutoriel_transfert_data_image.txt, section BLOCAGE SSH SORTANT.

: "${OLD_SSH_USER:?manque OLD_SSH_USER dans deploy.config}"
: "${OLD_SSH_HOST:?manque OLD_SSH_HOST dans deploy.config}"
OLD_SSH_PORT="${OLD_SSH_PORT:-22}"
ANCIEN="${OLD_SSH_USER}@${OLD_SSH_HOST}"
SSH_ANCIEN="ssh -p $OLD_SSH_PORT -o StrictHostKeyChecking=accept-new -o PubkeyAuthentication=no"

# Journal de chaque lancement, cote poste : deploy/logs/<nom>-<date>-<commande>.log
# journaliser <nom> "$@"
journaliser() {
    local nom="$1"; shift
    JOURNAL_DIR="$SCRIPT_DIR/logs"
    mkdir -p "$JOURNAL_DIR"
    JOURNAL="$JOURNAL_DIR/$nom-$(date +%Y%m%d-%H%M%S)-${1:-aide}.log"
    exec > >(tee -a "$JOURNAL") 2>&1
    echo "# $(date '+%F %T')  $0 $*  (journal : $JOURNAL)"
}

# Port joignable depuis O2switch ? « ouvert », « refuse » ou « muet » (expire).
port_depuis_o2switch() {
    ssh_run "timeout 6 bash -c '</dev/tcp/$1/$2' 2>/dev/null && echo ouvert || { [ \$? -eq 124 ] && echo muet || echo refuse; }"
}

# Execute sur O2switch, avec terminal (saisie du mot de passe de l'ancien serveur).
sur_o2switch() {
    $SSH_CMD -t "$REMOTE" "cd '$REMOTE_PATH' && $*"
}

# Lance un script dans screen sur O2switch : attache si on a un terminal,
# detache sinon (lancement automatise ; « suivre » permet d'y revenir).
# lancer_screen <session> <script>
lancer_screen() {
    if [ -t 0 ] && [ -t 1 ]; then
        sur_o2switch "screen -S $1 bash $2"
    else
        ssh_run "screen -dmS $1 bash $2" && ok "lance en arriere-plan (screen $1)"
    fi
}

# Diagnostic complet de la liaison O2switch -> ancien serveur.
diagnostic_ancien() {
    etape "1) Depuis ce poste -> ${OLD_SSH_HOST}:${OLD_SSH_PORT}"
    if nc -z -G 6 "$OLD_SSH_HOST" "$OLD_SSH_PORT" 2>/dev/null; then ok "port ouvert depuis le poste"; else alerte "port ferme depuis le poste aussi : sshd arrete, autre port, ou pare-feu de l'ancien serveur"; fi

    etape "2) IP de sortie d'O2switch (celle a autoriser sur l'ancien serveur)"
    echo "   $(ssh_run 'curl -s --max-time 6 https://ifconfig.me || echo inconnue')"

    etape "3) Depuis O2switch -> ${OLD_SSH_HOST}:${OLD_SSH_PORT}"
    local etat quiz
    etat="$(port_depuis_o2switch "$OLD_SSH_HOST" "$OLD_SSH_PORT")"
    echo "   ${OLD_SSH_HOST}:${OLD_SSH_PORT} : $etat"

    etape "4) Filtre sortant d'O2switch (portquiz.net ecoute sur tous les ports)"
    for p in "$OLD_SSH_PORT" 2083 443; do
        echo "   sortie vers le port $p : $(port_depuis_o2switch portquiz.net "$p")"
    done
    quiz="$(port_depuis_o2switch portquiz.net "$OLD_SSH_PORT")"

    etape "Conclusion"
    case "$etat" in
        ouvert) ok "reseau OK : la connexion par mot de passe peut etre tentee";;
        muet)   alerte "paquets perdus : pare-feu de l'ancien serveur (DROP). Y autoriser l'IP de l'etape 2.";;
        refuse) if [ "$quiz" != ouvert ]; then
                    alerte "filtre SORTANT d'O2switch : le port $OLD_SSH_PORT ne sort pas, vers aucun serveur."
                    alerte "L'ancien serveur n'y est pour rien. Faire ecouter son SSH sur 2083 (autorise par O2switch,"
                    alerte "ouvert dans csf.allow) et mettre OLD_SSH_PORT=\"2083\" dans deploy.config."
                else
                    alerte "O2switch laisse sortir le port $OLD_SSH_PORT : c'est l'ancien serveur qui refuse"
                    alerte "(CSF : csf -g <IP de l etape 2>, blocage LFD ?). Voir csf.allow / csf.ignore."
                fi
                alerte "Voir tutoriel_transfert_data_image.txt, section BLOCAGE SSH SORTANT.";;
    esac
}
