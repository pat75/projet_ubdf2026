#!/usr/bin/env bash
#
# Mot de passe HTTP devant le back-office (/admin_ et /admin_/*), en plus de
# la connexion Filament. Apache seulement (O2switch) : Valet (nginx)
# ignore les .htaccess, le back-office local reste sans cette barriere.
#
#   ./deploy/admin-auth.sh status   protection active ou non
#   ./deploy/admin-auth.sh on       la pose (ubdf / ubdf+2026-pat)
#   ./deploy/admin-auth.sh off      la retire
#
# Bloc « admin » du .htaccess de REMOTE_PATH (au-dessus de public/,
# jamais touche par le deploiement), partage avec demo-auth.sh. Le
# <If> s'applique apres le reste du fichier : sur /admin, son Require
# remplace celui de la demo, seul ubdf ouvre le back-office.
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"

ADMIN_USER="ubdf"
ADMIN_PASS="ubdf+2026-pat"
PASSWD_DIR="/home/sc4tapa2662/.htpasswds/ultra-book.pro"

case "${1:-status}" in
    status)
        if htaccess_a_bloc admin; then
            echo "Protection du back-office ACTIVE ($ADMIN_USER)"
        else
            echo "Protection du back-office inactive"
        fi
        ;;
    off)
        htaccess_bloc admin --retirer
        ssh_run "rm -f '$PASSWD_DIR/admin'"
        ok "protection du back-office retiree (effet sous ~1 minute)"
        ;;
    on)
        # SHA : le serveur O2switch (PowerBoost) refuse les hachages bcrypt et APR1.
        HASH="$(htpasswd -nbs "$ADMIN_USER" "$ADMIN_PASS")"
        $SSH_CMD "$REMOTE" "mkdir -p '$PASSWD_DIR' && chmod 755 \"\$(dirname '$PASSWD_DIR')\" '$PASSWD_DIR' \
            && printf '%s\n' '$HASH' > '$PASSWD_DIR/admin' && chmod 644 '$PASSWD_DIR/admin'"
        htaccess_bloc admin <<EOT
# Back-office : mot de passe HTTP. Retrait : ./deploy/admin-auth.sh off
# THE_REQUEST = requete d'origine, insensible a la reecriture vers index.php.
<If "%{THE_REQUEST} =~ m#^\\S+ /admin_(/|\\?|\\s)#">
    AuthType Basic
    AuthName "Ultra-book - administration"
    AuthUserFile $PASSWD_DIR/admin
    Require user $ADMIN_USER
</If>
EOT
        ok "protection du back-office active : $ADMIN_USER (effet sous ~1 minute)"
        ;;
    *) echo "Usage : $0 status|on|off"; exit 1 ;;
esac
