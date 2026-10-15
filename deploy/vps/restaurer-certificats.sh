#!/usr/bin/env bash
#
# Restaure les certificats de Caddy depuis la sauvegarde (VPS reconstruit).
# A lancer en root, apres installer.sh et avant d'ouvrir le trafic (DNS).
# Installe par installer.sh dans /usr/local/sbin.
#
set -euo pipefail

CONFIG="/etc/vps-proxy/vps.config"
CIBLE="/var/lib/caddy/.local/share/caddy/"
CLE="/root/.ssh/sauvegarde_ed25519"

# shellcheck disable=SC1090
source "$CONFIG"
: "${SAUVEGARDE_DEST:?SAUVEGARDE_DEST vide dans $CONFIG}"

ssh_cmd="ssh -i $CLE -o BatchMode=yes -o StrictHostKeyChecking=accept-new"

read -r -p "Remplacer les certificats locaux par ceux de $SAUVEGARDE_DEST ? [o/N] " rep
[[ "$rep" =~ ^[Oo]$ ]] || { echo "Annule."; exit 0; }

systemctl stop caddy
mkdir -p "$CIBLE"
rsync -a --delete -e "$ssh_cmd" "$SAUVEGARDE_DEST/actuel/" "$CIBLE"
chown -R caddy:caddy /var/lib/caddy/.local
systemctl start caddy

nb=$(find "$CIBLE" -name '*.crt' | wc -l)
echo "Restauration OK : $nb certificat(s). Caddy redemarre."
