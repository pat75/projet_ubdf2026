# VPS OVH Ubuntu — installation et configuration

Plan pas à pas pour le proxy des domaines personnalisés décrit dans
`01_synthese_vps.md`. Les valeurs entre `<…>` sont à remplacer.

## 0. Commande du VPS chez OVH

- Gamme : **VPS** d'entrée de gamme (2 vCPU, 4 Go RAM, 80 Go) — un
  proxy consomme peu ; on montera en gamme si besoin.
- Datacenter : **Gravelines ou Strasbourg** (France, proche du
  mutualisé et du public).
- Système : **Ubuntu 24.04 LTS**.
- Clé SSH : ajouter sa clé publique dès la commande (`~/.ssh/id_ed25519.pub`).
- Option **sauvegarde automatique** : utile mais secondaire (le VPS ne
  stocke que de la configuration et des certificats).

Noter : IPv4 `<IP_VPS>`, IPv6 `<IP6_VPS>`, utilisateur initial `ubuntu`.

Dans l'espace client OVH, sur le VPS :
- **Reverse DNS** de l'IP : `proxy.ultra-book.com`.
- **Firewall réseau OVH** : laisser désactivé (UFW suffit) ou n'ouvrir
  que 22, 80, 443.

DNS de `ultra-book.com` (zone OVH) :
```
proxy.ultra-book.com.   A     <IP_VPS>
proxy.ultra-book.com.   AAAA  <IP6_VPS>
```

## 1. Premier accès et mise à jour

```bash
ssh ubuntu@<IP_VPS>
sudo apt update && sudo apt full-upgrade -y
sudo timedatectl set-timezone Europe/Paris
sudo hostnamectl set-hostname proxy-ub
sudo reboot
```

## 2. Utilisateur d'administration

```bash
sudo adduser pat
sudo usermod -aG sudo pat
sudo mkdir -p /home/pat/.ssh
sudo cp ~/.ssh/authorized_keys /home/pat/.ssh/
sudo chown -R pat:pat /home/pat/.ssh && sudo chmod 700 /home/pat/.ssh
```
Vérifier depuis le Mac, **dans un second terminal**, avant de continuer :
`ssh pat@<IP_VPS>`.

## 3. Durcissement SSH

`/etc/ssh/sshd_config.d/99-durci.conf` :
```
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
PubkeyAuthentication yes
AllowUsers pat
MaxAuthTries 3
```
```bash
sudo sshd -t && sudo systemctl restart ssh
```
Retester la connexion dans un nouveau terminal **avant** de fermer
l'ancien.

Côté Mac, `~/.ssh/config` :
```
Host proxy-ub
    HostName <IP_VPS>
    User pat
    IdentityFile ~/.ssh/id_ed25519
```

## 4. Pare-feu UFW

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 443/udp      # HTTP/3
sudo ufw enable
sudo ufw status verbose
```

## 5. fail2ban

```bash
sudo apt install -y fail2ban
```
`/etc/fail2ban/jail.local` :
```
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5

[sshd]
enabled = true
```
```bash
sudo systemctl enable --now fail2ban
sudo fail2ban-client status sshd
```

## 6. Mises à jour de sécurité automatiques

```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```
Dans `/etc/apt/apt.conf.d/50unattended-upgrades` :
```
Unattended-Upgrade::Automatic-Reboot "true";
Unattended-Upgrade::Automatic-Reboot-Time "04:30";
```

## 7. Installation de Caddy

Dépôt officiel :
```bash
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' \
  | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' \
  | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update && sudo apt install -y caddy
caddy version
```

## 8. Configuration Caddy

Prérequis côté Laravel (voir synthèse §3) : alias
`passerelle.ultra-book.com` actif sur le mutualisé et endpoint
`/api/v1/domaines/autorise` en ligne.

`/etc/caddy/Caddyfile` :
```
{
    email technique@ultra-book.com

    on_demand_tls {
        # Caddy ne demande un certificat que si Laravel valide le domaine.
        ask https://passerelle.ultra-book.com/api/v1/domaines/autorise
    }

    servers {
        protocols h1 h2 h3
    }
}

# Page de santé du proxy lui-même
proxy.ultra-book.com {
    respond /sante "ok" 200
    respond 404
}

# Tous les domaines personnalisés
https:// {
    tls {
        on_demand
    }

    encode zstd gzip

    reverse_proxy https://passerelle.ultra-book.com {
        header_up Host passerelle.ultra-book.com
        header_up X-Forwarded-Host {host}
        header_up X-Forwarded-Proto https
        header_up X-Proxy-Secret {env.PROXY_SECRET}

        transport http {
            tls_server_name passerelle.ultra-book.com
        }
    }

    log {
        output file /var/log/caddy/domaines.log {
            roll_size 50mb
            roll_keep 10
        }
        format json
    }
}

# Redirection HTTP -> HTTPS (gérée par Caddy, rien à ajouter)
```

Secret partagé avec Laravel (en plus du filtrage par IP, car un
mutualisé voit parfois l'IP d'un proxy intermédiaire) :
```bash
sudo systemctl edit caddy
```
```
[Service]
Environment=PROXY_SECRET=<chaine_aleatoire_64_caracteres>
```
Générer la chaîne : `openssl rand -hex 32`. La même valeur va dans le
`.env` de Laravel (`PROXY_SECRET=`), vérifiée par le middleware.

Valider et lancer :
```bash
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl restart caddy
sudo systemctl status caddy
journalctl -u caddy -f
```

## 9. Test de bout en bout

1. Acheter à la main un domaine de test (ex. `test-ub-proxy.fr`), DNS
   `A` → `<IP_VPS>`.
2. L'insérer en base `domaines` avec `statut = actif` et un créatif de
   test.
3. Vérifier :
```bash
dig +short test-ub-proxy.fr                       # → IP_VPS
curl -I https://proxy.ultra-book.com/sante         # → 200
curl -I https://test-ub-proxy.fr                   # → 200, book du créatif
curl -I https://un-domaine-inconnu.fr --resolve un-domaine-inconnu.fr:443:<IP_VPS>
                                                   # → échec TLS (refusé par ask)
```
4. Dans le navigateur : cadenas valide, canonical = domaine perso,
   images chargées depuis le domaine Ultra-book.

## 10. Supervision

- Sonde externe (UptimeRobot ou Uptime Kuma hébergé ailleurs) sur
  `https://proxy.ultra-book.com/sante` et sur le domaine de test.
- Alerte disque :
  `df -h` ; logs Caddy limités par `roll_*`, journald par
  `/etc/systemd/journald.conf` → `SystemMaxUse=500M`.
- Expiration des certificats : gérée par Caddy ; la sonde HTTPS détecte
  un échec.

## 11. Sauvegarde et reconstruction

Le VPS est jetable. À conserver (dans le dépôt, dossier `_doc/_doc_VPS/`
ou `deploy/vps/`) :
- `Caddyfile` ;
- override systemd (sans le secret, stocké dans le gestionnaire de mots
  de passe) ;
- ce document.

Les certificats (`/var/lib/caddy`) se régénèrent seuls. Attention à la
limite Let's Encrypt (50 certificats / domaine enregistré / semaine) :
en cas de reconstruction avec beaucoup de domaines, sauvegarder aussi
`/var/lib/caddy/.local/share/caddy` pour éviter de tout redemander.

## 12. Côté Laravel (rappel, mutualisé)

- Alias `passerelle.ultra-book.com` sur l'hébergement.
- `.env` : `PROXY_SECRET=…` (même valeur que sur le VPS), `VPS_IP=<IP_VPS>`,
  `VPS_IP6=<IP6_VPS>`, et les clés `OVH_APPLICATION_KEY`,
  `OVH_APPLICATION_SECRET`, `OVH_CONSUMER_KEY`.
- Pas de `trustProxies` : la passerelle (`ServirDomainePerso`) se fie au
  secret, pas à l'IP. Sans le bon `X-Proxy-Secret`, `X-Forwarded-Host`
  est ignoré. (Déjà codé.)
- Endpoint `domaines/autorise` : `throttle`, réponse 200/404 sans corps.
  (Déjà codé.)
- Cron Laravel actif (`schedule:run`) : `ubdf:verifier-domaines` tourne
  toutes les heures.

## 13. Check-list finale

- [ ] VPS Ubuntu 24.04 commandé, reverse DNS posé
- [ ] DNS `proxy.ultra-book.com` → VPS
- [ ] Utilisateur `pat`, SSH par clé, root et mot de passe désactivés
- [ ] UFW actif (22, 80, 443)
- [ ] fail2ban actif
- [ ] Mises à jour automatiques
- [ ] Caddy installé, Caddyfile validé
- [ ] `PROXY_SECRET` identique VPS / Laravel
- [ ] Alias passerelle + endpoint `autorise` en ligne
- [ ] Domaine de test servi en HTTPS, domaine inconnu refusé
- [ ] Sonde de supervision en place
- [ ] Caddyfile versionné
