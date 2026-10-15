#!/usr/bin/env bash
#
# Installation du VPS « domaines personnalises » (Ubuntu 24.04, OVH).
# Etapes de _doc/_doc_VPS/02_installation_vps.md + sauvegarde des
# certificats (04_sauvegardes.md). Relancable sans dommage : chaque etape
# verifie ce qui existe deja.
#
# Usage, en root sur le VPS, depuis ce dossier copie avec vps.config :
#     sudo bash installer.sh
#
set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_SRC="$DIR/vps.config"
CONFIG="/etc/vps-proxy/vps.config"

etape() { echo; echo "== $*"; }
echec() { echo "ERREUR : $*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || echec "lancer en root (sudo bash installer.sh)."
[ -f "$CONFIG_SRC" ] || echec "$CONFIG_SRC manquant : cp vps.config.example vps.config, puis renseigner."
# shellcheck disable=SC1090
source "$CONFIG_SRC"
: "${ADMIN_USER:?}" "${ADMIN_SSH_KEY:?}" "${ACME_EMAIL:?}" "${PROXY_HOST:?}" "${PASSERELLE_HOST:?}"

etape "1. Systeme a jour, fuseau, nom d'hote"
export DEBIAN_FRONTEND=noninteractive
apt-get update -q
apt-get full-upgrade -yq
apt-get install -yq curl rsync gnupg debian-keyring debian-archive-keyring apt-transport-https fail2ban unattended-upgrades
timedatectl set-timezone "${TIMEZONE:-Europe/Paris}"
hostnamectl set-hostname "${HOSTNAME_VPS:-proxy-ub}"

etape "2. Utilisateur d'administration $ADMIN_USER"
id "$ADMIN_USER" >/dev/null 2>&1 || adduser --disabled-password --gecos "" "$ADMIN_USER"
usermod -aG sudo "$ADMIN_USER"
install -d -m 700 -o "$ADMIN_USER" -g "$ADMIN_USER" "/home/$ADMIN_USER/.ssh"
cles="/home/$ADMIN_USER/.ssh/authorized_keys"
touch "$cles"
grep -qxF "$ADMIN_SSH_KEY" "$cles" || echo "$ADMIN_SSH_KEY" >> "$cles"
chown "$ADMIN_USER:$ADMIN_USER" "$cles" && chmod 600 "$cles"
# sudo sans mot de passe : le compte n'en a pas (SSH par cle seulement).
echo "$ADMIN_USER ALL=(ALL) NOPASSWD:ALL" > "/etc/sudoers.d/90-$ADMIN_USER"
chmod 440 "/etc/sudoers.d/90-$ADMIN_USER"

etape "3. SSH durci (cle uniquement, pas de root)"
cat > /etc/ssh/sshd_config.d/99-durci.conf <<EOF
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
PubkeyAuthentication yes
AllowUsers $ADMIN_USER
MaxAuthTries 3
EOF
sshd -t
systemctl reload ssh
echo "   Verifier MAINTENANT dans un autre terminal : ssh $ADMIN_USER@<IP_VPS>"

etape "4. Pare-feu UFW (22, 80, 443)"
ufw default deny incoming
ufw default allow outgoing
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 443/udp
ufw --force enable

etape "5. fail2ban"
cat > /etc/fail2ban/jail.local <<'EOF'
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5

[sshd]
enabled = true
EOF
systemctl enable --now fail2ban
systemctl restart fail2ban

etape "6. Mises a jour de securite automatiques"
cat > /etc/apt/apt.conf.d/20auto-upgrades <<'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
EOF
cat > /etc/apt/apt.conf.d/52reboot-auto <<'EOF'
Unattended-Upgrade::Automatic-Reboot "true";
Unattended-Upgrade::Automatic-Reboot-Time "04:30";
EOF

etape "7. Caddy (depot officiel)"
if ! command -v caddy >/dev/null; then
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' \
        | gpg --dearmor --yes -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' \
        > /etc/apt/sources.list.d/caddy-stable.list
    apt-get update -q
    apt-get install -yq caddy
fi
caddy version

etape "8. Configuration Caddy et secret partage"
install -d -m 700 /etc/vps-proxy
# Secret : celui de vps.config, sinon celui deja installe, sinon un nouveau.
if [ -z "${PROXY_SECRET:-}" ] && [ -f "$CONFIG" ]; then
    PROXY_SECRET="$(. "$CONFIG"; echo "${PROXY_SECRET:-}")"
fi
nouveau_secret=0
if [ -z "${PROXY_SECRET:-}" ]; then
    PROXY_SECRET="$(openssl rand -hex 32)"
    nouveau_secret=1
fi
install -m 600 "$CONFIG_SRC" "$CONFIG"
sed -i "s|^PROXY_SECRET=.*|PROXY_SECRET=\"$PROXY_SECRET\"|" "$CONFIG"

sed -e "s|__ACME_EMAIL__|$ACME_EMAIL|g" \
    -e "s|__PROXY_HOST__|$PROXY_HOST|g" \
    -e "s|__PASSERELLE_HOST__|$PASSERELLE_HOST|g" \
    "$DIR/Caddyfile" > /etc/caddy/Caddyfile

install -d /etc/systemd/system/caddy.service.d
cat > /etc/systemd/system/caddy.service.d/secret.conf <<EOF
[Service]
Environment=PROXY_SECRET=$PROXY_SECRET
EOF
chmod 600 /etc/systemd/system/caddy.service.d/secret.conf
systemctl daemon-reload
caddy validate --config /etc/caddy/Caddyfile --adapter caddyfile
systemctl enable caddy
systemctl restart caddy

etape "9. Journald limite a 500 Mo"
sed -i 's|^#\?SystemMaxUse=.*|SystemMaxUse=500M|' /etc/systemd/journald.conf
systemctl restart systemd-journald

etape "10. Sauvegarde des certificats (rsync)"
install -m 750 "$DIR/sauvegarder-certificats.sh" /usr/local/sbin/sauvegarder-certificats
install -m 750 "$DIR/restaurer-certificats.sh" /usr/local/sbin/restaurer-certificats
if [ -n "${SAUVEGARDE_DEST:-}" ]; then
    [ -f /root/.ssh/sauvegarde_ed25519 ] || ssh-keygen -q -t ed25519 -N "" \
        -f /root/.ssh/sauvegarde_ed25519 -C "sauvegarde-$(hostname)"

    cat > /etc/systemd/system/sauvegarde-certificats.service <<'EOF'
[Unit]
Description=Sauvegarde des certificats Caddy (rsync)
After=network-online.target
Wants=network-online.target

[Service]
Type=oneshot
ExecStart=/usr/local/sbin/sauvegarder-certificats
EOF
    cat > /etc/systemd/system/sauvegarde-certificats.timer <<EOF
[Unit]
Description=Sauvegarde quotidienne des certificats Caddy

[Timer]
OnCalendar=*-*-* ${SAUVEGARDE_HEURE:-03:15}:00
RandomizedDelaySec=10m
Persistent=true

[Install]
WantedBy=timers.target
EOF
    systemctl daemon-reload
    systemctl enable --now sauvegarde-certificats.timer
    echo "   Cle publique a autoriser sur $SAUVEGARDE_DEST :"
    cat /root/.ssh/sauvegarde_ed25519.pub
else
    echo "   SAUVEGARDE_DEST vide : sauvegarde non activee."
fi

etape "Termine"
systemctl --no-pager --quiet is-active caddy && echo "   Caddy actif."
if [ "$nouveau_secret" -eq 1 ]; then
    echo "   Nouveau PROXY_SECRET (a copier dans le .env de prod, puis dans le gestionnaire de mots de passe) :"
    echo "   $PROXY_SECRET"
fi
echo "   Suite : _doc/_doc_VPS/02_installation_vps.md, section 9 (test de bout en bout)."
