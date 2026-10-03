#!/usr/bin/env bash
#
# Installe la partie O2SWITCH de script_bkp_externe/ (dans ~/script_bkp_externe,
# hors de l'app). La partie giga (pull.sh) s'installe a la main : LISEZMOI.txt.
#
#   ./deploy/bkp-install.sh installer               pousse bkp.sh + bkp.config, cree ~/.my.cnf
#   ./deploy/bkp-install.sh autoriser-cle <fichier.pub> <IP_GIGA>
#                                                   autorise la cle publique de giga (restreinte a son IP)
#   ./deploy/bkp-install.sh tester                  dump + mail d'essai sur o2switch
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"

SRC="$PROJECT_DIR/script_bkp_externe"
DEST='$HOME/script_bkp_externe'

ENV_PROD="$PROJECT_DIR/.env.prod"
val() { grep -E "^$1=" "$ENV_PROD" | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }

case "${1:-}" in
    installer)
        [ -f "$SRC/bkp.config" ] || echec "creer d'abord $SRC/bkp.config (depuis bkp.config.example)"

        etape "Envoi des scripts o2switch"
        $SSH_CMD "$REMOTE" "mkdir -p $DEST/logs"
        rsync -av -e "$SSH_CMD" "$SRC/bkp.sh" "$SRC/restaurer-test.sh" "$SRC/lib-bkp.sh" "$SRC/bkp.config" \
            "$REMOTE:script_bkp_externe/"
        $SSH_CMD "$REMOTE" "chmod 700 $DEST && chmod 600 $DEST/bkp.config && chmod +x $DEST/*.sh"

        etape "~/.my.cnf (identifiants MySQL de .env.prod)"
        if $SSH_CMD "$REMOTE" "test -f \$HOME/.my.cnf"; then
            ok "deja present, inchange"
        else
            [ -f "$ENV_PROD" ] || echec ".env.prod introuvable"
            printf '[client]\nuser=%s\npassword="%s"\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" \
                | $SSH_CMD "$REMOTE" "umask 077 && cat > \$HOME/.my.cnf"
            ok "cree (chmod 600)"
        fi

        etape "Cron cPanel a ajouter (Taches Cron)"
        echo "   0 2 * * *  \$HOME/script_bkp_externe/bkp.sh dump >/dev/null 2>&1"
        echo "   30 4 1 * * \$HOME/script_bkp_externe/restaurer-test.sh >/dev/null 2>&1"
        ;;
    autoriser-cle)
        [ -f "${2:-}" ] && [ -n "${3:-}" ] || echec "usage : $0 autoriser-cle <fichier.pub> <IP_GIGA>"
        ligne_cle="from=\"$3\",no-pty,no-agent-forwarding,no-port-forwarding,no-X11-forwarding $(cat "$2")"
        $SSH_CMD "$REMOTE" "mkdir -p \$HOME/.ssh && chmod 700 \$HOME/.ssh && touch \$HOME/.ssh/authorized_keys"
        if $SSH_CMD "$REMOTE" "grep -qF '$(awk '{print $2}' "$2")' \$HOME/.ssh/authorized_keys"; then
            ok "cle deja autorisee"
        else
            printf '%s\n' "$ligne_cle" | $SSH_CMD "$REMOTE" "cat >> \$HOME/.ssh/authorized_keys && chmod 600 \$HOME/.ssh/authorized_keys"
            ok "cle de giga autorisee depuis $3"
        fi
        alerte "cPanel > Acces SSH / Autorisation SSH : ajouter aussi l'IP $3 si o2switch filtre les IP"
        ;;
    tester)
        $SSH_CMD -t "$REMOTE" "$DEST/bkp.sh dump && $DEST/bkp.sh test-mail"
        ;;
    *)
        sed -n '3,10p' "$0"; exit 1
        ;;
esac
