#!/usr/bin/env bash
#
# Connexion SSH au serveur PROD, dans le dossier de l'application,
# avec PHP 8.3 en tete du PATH (php artisan … utilise donc la bonne version).
#
#   ./deploy/ssh-prod.sh                          shell interactif
#   ./deploy/ssh-prod.sh "php artisan about"      commande ponctuelle
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"

PREFIXE="cd '$REMOTE_PATH' && export PATH=\"$(dirname "$REMOTE_PHP"):\$PATH\""

if [ $# -gt 0 ]; then
    exec $SSH_CMD -t "$REMOTE" "$PREFIXE && $*"
else
    echo "-> SSH ${REMOTE} (${REMOTE_PATH})"
    exec $SSH_CMD -t "$REMOTE" "$PREFIXE && exec \$SHELL -l"
fi
