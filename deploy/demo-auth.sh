#!/usr/bin/env bash
#
# PROVISOIRE — mot de passe HTTP du serveur de demo (ultra-book.pro).
# Protege tout le site sauf la notification Payplug et /.well-known/ (SSL).
#
#   ./deploy/demo-auth.sh status   protection active ou non
#   ./deploy/demo-auth.sh off      retire la protection (site public)
#   ./deploy/demo-auth.sh on       la remet (demo / ubdf2026)
#
# Le .htaccess est pose dans REMOTE_PATH (au-dessus de public/) : le
# deploiement ne le touche jamais. Il est partage avec admin-auth.sh :
# ce script n'y touche que le bloc « demo ». Effet sous ~1 minute
# (cache O2switch).
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"

DEMO_USER="demo"
DEMO_PASS="ubdf2026"
PASSWD_DIR="/home/sc4tapa2662/.htpasswds/ultra-book.pro"

# Ancien .htaccess de demo, pose avant les blocs balises : il occupait
# tout le fichier, on le retire avant de poser le bloc.
if ssh_run "grep -q 'Ultra-book - demo' .htaccess 2>/dev/null && ! grep -qx '# BEGIN demo' .htaccess"; then
    ssh_run "rm -f .htaccess"
fi

case "${1:-status}" in
    status)
        if htaccess_a_bloc demo; then
            echo "Protection ACTIVE ($DEMO_USER / $DEMO_PASS)"
        else
            echo "Protection inactive : site public"
        fi
        ;;
    off)
        htaccess_bloc demo --retirer
        ok "protection retiree (effet sous ~1 minute)"
        ;;
    on)
        # SHA : le serveur O2switch (PowerBoost) refuse les hachages bcrypt et APR1.
        HASH="$(htpasswd -nbs "$DEMO_USER" "$DEMO_PASS")"
        $SSH_CMD "$REMOTE" "mkdir -p '$PASSWD_DIR' && chmod 755 \"\$(dirname '$PASSWD_DIR')\" '$PASSWD_DIR' \
            && printf '%s\n' '$HASH' > '$PASSWD_DIR/passwd' && chmod 644 '$PASSWD_DIR/passwd'"
        htaccess_bloc demo <<EOT
# PROVISOIRE — mot de passe du serveur de demo ($DEMO_USER / $DEMO_PASS).
# Retrait : ./deploy/demo-auth.sh off
AuthType Basic
AuthName "Ultra-book - demo"
AuthUserFile $PASSWD_DIR/passwd

# Notification Payplug (serveur a serveur) et validation SSL : sans mot de passe.
# THE_REQUEST = requete d'origine, insensible a la reecriture vers index.php.
<RequireAny>
    Require expr "%{THE_REQUEST} =~ m#^\\S+ /(payplug/notification|\\.well-known/)#"
    Require valid-user
</RequireAny>
EOT
        ok "protection active : $DEMO_USER / $DEMO_PASS (effet sous ~1 minute)"
        ;;
    *) echo "Usage : $0 status|on|off"; exit 1 ;;
esac
