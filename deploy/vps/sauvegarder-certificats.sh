#!/usr/bin/env bash
#
# Sauvegarde des certificats Let's Encrypt de Caddy vers un autre serveur
# (rsync sur SSH). Installe par installer.sh dans /usr/local/sbin, lance
# chaque nuit par le timer systemd sauvegarde-certificats.timer.
#
# Copie le stockage de Caddy (certificats, cles, compte ACME). Les fichiers
# supprimes cote VPS (certificats expires) sont gardes 30 jours dans
# « supprimes/<date> » cote destination, jamais effaces tout de suite.
#
set -euo pipefail

CONFIG="/etc/vps-proxy/vps.config"
SOURCE="/var/lib/caddy/.local/share/caddy/"
CLE="/root/.ssh/sauvegarde_ed25519"

# shellcheck disable=SC1090
source "$CONFIG"
: "${SAUVEGARDE_DEST:?SAUVEGARDE_DEST vide dans $CONFIG}"

hote="${SAUVEGARDE_DEST%%:*}"
chemin="${SAUVEGARDE_DEST#*:}"
date_jour="$(date +%F)"
ssh_cmd="ssh -i $CLE -o BatchMode=yes -o StrictHostKeyChecking=accept-new"

[ -d "$SOURCE" ] || { echo "Rien a sauvegarder : $SOURCE absent." >&2; exit 1; }

$ssh_cmd "$hote" "mkdir -p '$chemin/actuel' '$chemin/supprimes'"

rsync -a --delete \
    --backup --backup-dir="../supprimes/$date_jour" \
    -e "$ssh_cmd" \
    "$SOURCE" "$hote:$chemin/actuel/"

# Menage des anciennes suppressions (plus de 30 jours)
$ssh_cmd "$hote" "find '$chemin/supprimes' -mindepth 1 -maxdepth 1 -type d -mtime +30 -exec rm -rf {} +"

nb=$(find "$SOURCE" -name '*.crt' | wc -l)
echo "Sauvegarde OK : $nb certificat(s) vers $SAUVEGARDE_DEST ($date_jour)."
