#!/usr/bin/env bash
#
# Fonctions communes aux scripts de deploy/ (a sourcer, pas a executer).
#

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
CONFIG="$SCRIPT_DIR/deploy.config"
EXCLUDE="$SCRIPT_DIR/rsync-exclude.txt"

if [ -t 1 ]; then
    ROUGE=$'\e[31m'; VERT=$'\e[32m'; JAUNE=$'\e[33m'; GRAS=$'\e[1m'; RAZ=$'\e[0m'
else
    ROUGE=""; VERT=""; JAUNE=""; GRAS=""; RAZ=""
fi

etape()  { echo; echo "${GRAS}$*${RAZ}"; }
ok()     { echo "   ${VERT}$*${RAZ}"; }
alerte() { echo "   ${JAUNE}$*${RAZ}"; }
echec()  { echo "${ROUGE}ERREUR : $*${RAZ}" >&2; exit 1; }
ligne()  { echo "──────────────────────────────────────────────"; }

# confirm "Question" [defaut O|N]
confirm() {
    local defaut="${2:-N}" invite rep
    if [ "$defaut" = "O" ]; then invite="[O/n]"; else invite="[o/N]"; fi
    read -r -p "$1 $invite " rep
    rep="${rep:-$defaut}"
    [[ "$rep" =~ ^[OoYy]$ ]]
}

if [ ! -f "$CONFIG" ]; then
    echec "config manquante : $CONFIG
   -> cp deploy/deploy.config.example deploy/deploy.config, puis renseigner."
fi
# shellcheck disable=SC1090
source "$CONFIG"

: "${SSH_HOST:?manque SSH_HOST dans deploy.config}"
: "${REMOTE_PATH:?manque REMOTE_PATH dans deploy.config}"
SSH_PORT="${SSH_PORT:-22}"
REMOTE_PHP="${REMOTE_PHP:-/usr/local/bin/php}"
REMOTE_COMPOSER="${REMOTE_COMPOSER:-/usr/local/bin/composer}"
BACKUP_DIR="${BACKUP_DIR:-\$HOME/backups_deploy}"
KEEP="${KEEP:-5}"
LOCAL_PHP="${LOCAL_PHP:-/usr/local/opt/php@8.3/bin/php}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-}"
URL_CHECK="${URL_CHECK:-}"
NOINDEX_CHECK="${NOINDEX_CHECK:-0}"

# Avec un alias ~/.ssh/config, SSH_USER peut rester vide.
if [ -n "${SSH_USER:-}" ]; then REMOTE="${SSH_USER}@${SSH_HOST}"; else REMOTE="$SSH_HOST"; fi
SSH_CMD="ssh -p ${SSH_PORT}"

# Commande dans le dossier de l'application.
ssh_run() {
    $SSH_CMD "$REMOTE" "cd '$REMOTE_PATH' && $*"
}

# Script bash multi-lignes lu sur l'entree standard (heredoc), dans le dossier de l'app.
# Les variables utiles sont passees en environnement, pas interpolees.
ssh_script() {
    $SSH_CMD "$REMOTE" "cd '$REMOTE_PATH' && PHP='$REMOTE_PHP' COMPOSER='$REMOTE_COMPOSER' BACKUP_DIR=\"$BACKUP_DIR\" KEEP='$KEEP' bash -s -- $*"
}

artisan() {
    ssh_run "'$REMOTE_PHP' artisan $*"
}

# composer.phar de REMOTE_COMPOSER s'il existe, sinon le composer fourni par cPanel,
# toujours execute par PHP 8.3 (le composer du PATH tournerait avec le php par defaut).
composer_install() {
    ssh_run "C=\"$REMOTE_COMPOSER\"; \
        [ -f \"\$C\" ] || C=\"\$(command -v composer || true)\"; \
        [ -n \"\$C\" ] || { [ -f /opt/cpanel/composer/bin/composer ] && C=/opt/cpanel/composer/bin/composer; }; \
        [ -n \"\$C\" ] || { echo 'composer introuvable sur le serveur' >&2; exit 1; }; \
        '$REMOTE_PHP' \"\$C\" install --no-dev --optimize-autoloader --no-interaction --no-progress"
}

caches() {
    artisan optimize:clear >/dev/null
    artisan optimize
}
