#!/usr/bin/env bash
#
# Reduction a 2000 px des originaux des books, sur O2switch (ubdf:images:reduire).
#
#   ./_scripts/reduire-images.sh compter   essai a blanc : liste et compte, ne modifie rien
#   ./_scripts/reduire-images.sh lancer    reduit pour de bon (originaux -> storage/app/originaux)
#   ./_scripts/reduire-images.sh suivre    revenir dans le screen en cours (progression en direct)
#   ./_scripts/reduire-images.sh etat      ou en est la passe, sans s'attacher
#
# Ajouter --recommencer a compter/lancer pour repartir du premier book.
#
# Lance depuis le Mac, tourne dans screen sur O2switch : survit a une
# coupure SSH ou a la fermeture du Mac. Reprenable : apres un arret, relancer
# la meme commande repart du book suivant le dernier termine. Long (disque
# partage lent) : environ 1 h pour tout parcourir.
#
set -euo pipefail
source "$(dirname "$0")/../deploy/lib.sh"

SESSION="reduire-images"

sur_o2switch() { $SSH_CMD -t "$REMOTE" "cd '$REMOTE_PATH' && $*"; }

demarrer() {
    local options="$1" mode="$2"
    local journal="storage/logs/reduire-images-$(date +%Y%m%d-%H%M%S)-$mode.log"

    # Deux passes en parallele se marcheraient dessus (meme fichier de reprise).
    if ssh_run "screen -ls | grep -q $SESSION"; then
        echec "une passe tourne deja : $0 suivre"
    fi

    printf '%s\n' \
        "nice '$REMOTE_PHP' artisan ubdf:images:reduire $options 2>&1 | tee '$journal'" \
        "echo; echo 'Journal : $journal. Entree pour fermer.'; read _" \
        | ssh_run "cat > storage/$SESSION.run"
    alerte "Ctrl-A puis D : detacher. Revenir : $0 suivre"
    sur_o2switch "screen -S $SESSION bash storage/$SESSION.run"
}

case "${1:-}" in
    compter)
        etape "Essai a blanc sur O2switch (rien n'est modifie)"
        demarrer "--dry-run ${2:-}" essai
        ;;
    lancer)
        etape "Reduction des originaux > 2000 px sur O2switch"
        alerte "Originaux deplaces dans storage/app/originaux/ (a supprimer a la main une fois verifie)."
        confirm "Lancer la reduction pour de bon ?" || exit 0
        demarrer "${2:-}" reduction
        ;;
    suivre)
        sur_o2switch "screen -r $SESSION" || ok "aucune reduction en cours (terminee ?)"
        ;;
    etat)
        ssh_run "screen -ls | grep -q $SESSION && echo 'En cours' || echo 'Aucune passe en cours'; \
            f=\$(ls -t storage/logs/reduire-images-*.log 2>/dev/null | head -1); \
            [ -n \"\$f\" ] && { echo \"Journal : \$f\"; tail -c 2000 \"\$f\" | tr '\\r' '\\n' | grep -v '^\$' | tail -2; }; \
            for e in essai reduction; do [ -f storage/app/reduire-images-\$e.json ] && echo \"Reprise \$e : \$(cat storage/app/reduire-images-\$e.json)\"; done; \
            du -sh storage/app/originaux 2>/dev/null || true"
        ;;
    *)
        echo "Usage : $0 compter|lancer|suivre|etat"; exit 1
        ;;
esac
