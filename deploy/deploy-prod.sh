#!/usr/bin/env bash
#
# Deploiement PROD (O2switch) par rsync over SSH, cle ed25519 via l'alias ~/.ssh/config.
#
#   ./deploy/deploy-prod.sh                    complet : controles, build, sauvegardes,
#                                              dry-run, maintenance, envoi, composer,
#                                              migrations, caches, verification
#   ./deploy/deploy-prod.sh sync               re-push rapide : build, envoi, composer,
#                                              caches (sans sauvegarde, dry-run ni migration)
#   ./deploy/deploy-prod.sh rollback [STAMP]   liste / restaure une sauvegarde
#
# - .env.prod local remplace .env.prod distant a chaque deploiement (lu par bootstrap/app.php).
# - --delete seulement sur les dossiers de DELETE_DIRS (fichiers renommes/supprimes).
# - vendor/ est construit sur le serveur (composer install --no-dev).
#
set -euo pipefail

MODE="${1:-full}"
case "$MODE" in
    full|sync) ;;
    rollback) exec "$(dirname "$0")/rollback-prod.sh" "${2:-}" ;;
    *) echo "Mode inconnu : '$MODE' (attendu : <aucun>, sync, rollback [STAMP])"; exit 1 ;;
esac

source "$(dirname "$0")/lib.sh"

# Dossiers synchronises a l'identique (suppression des fichiers disparus en local).
DELETE_DIRS=(app config routes resources/views database public/build)

STAMP="$(date +%Y%m%d-%H%M%S)"
COMMIT="$(git -C "$PROJECT_DIR" rev-parse --short HEAD 2>/dev/null || echo '?')"
RSYNC_OPTS=(-az --no-perms --exclude-from="$EXCLUDE" -e "$SSH_CMD")
EN_MAINTENANCE=0

# En cas d'echec ou d'interruption, ne jamais laisser le site en maintenance.
sortie_maintenance() {
    if [ "$EN_MAINTENANCE" = "1" ]; then
        echo; alerte "Interruption : remise en ligne (artisan up)…"
        artisan up || true
    fi
}
trap sortie_maintenance EXIT

ligne
echo "  Deploiement $COMMIT -> ${REMOTE}:${REMOTE_PATH}"
[ "$MODE" = "sync" ] && echo "  Mode : RE-PUSH RAPIDE (sans sauvegarde, dry-run ni migration)"
ligne

# 1) Controles locaux.
etape "1) Controles locaux"
[ -f "$PROJECT_DIR/.env.prod" ] || echec ".env.prod absent du projet."
[ -e "$PROJECT_DIR/public/hot" ] && echec "public/hot present : arreter 'npm run dev' (Vite)."
BRANCHE="$(git -C "$PROJECT_DIR" branch --show-current)"
if [ -n "$DEPLOY_BRANCH" ] && [ "$BRANCHE" != "$DEPLOY_BRANCH" ]; then
    confirm "Branche '$BRANCHE' au lieu de '$DEPLOY_BRANCH'. Continuer ?" || exit 0
fi
if [ -n "$(git -C "$PROJECT_DIR" status --porcelain)" ]; then
    alerte "Arbre git non propre (modifications non commitees) :"
    git -C "$PROJECT_DIR" status --short | head -20
    confirm "Deployer quand meme ?" || exit 0
fi
ok "branche $BRANCHE, commit $COMMIT"

if confirm "Lancer 'npm run build' ?" O; then
    ( cd "$PROJECT_DIR" && npm run build )
fi
if [ "$MODE" = "full" ] && confirm "Lancer les tests (Pest) ?" N; then
    ( cd "$PROJECT_DIR" && "$LOCAL_PHP" vendor/bin/pest --parallel ) || echec "tests en echec."
fi

# 2) Connexion et PHP distant.
etape "2) Connexion SSH"
VERSION="$(ssh_run "'$REMOTE_PHP' -r 'echo PHP_VERSION;'")" || echec "connexion SSH ou PHP distant impossible."
[[ "$VERSION" == 8.3* ]] || echec "PHP distant $VERSION (8.3 attendu) : verifier REMOTE_PHP."
ok "PHP $VERSION"

if [ "$MODE" = "full" ]; then
    # 3) Sauvegardes distantes : code (avec .env.prod) + base.
    etape "3) Sauvegardes distantes ($STAMP)"
    ssh_script "$STAMP" <<'DISTANT'
set -euo pipefail
STAMP="$1"
mkdir -p "$BACKUP_DIR"
tar czf "$BACKUP_DIR/code-$STAMP.tgz" --exclude=./storage --exclude=./vendor --exclude=./node_modules .
echo "   code : $BACKUP_DIR/code-$STAMP.tgz"

val() { grep -E "^$1=" .env.prod | tail -1 | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/; s/^'"'"'(.*)'"'"'$/\1/'; }
CNF="$(mktemp)"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" "$(val DB_HOST)" > "$CNF"
mysqldump --defaults-extra-file="$CNF" --single-transaction --quick --no-tablespaces "$(val DB_DATABASE)" \
    | gzip > "$BACKUP_DIR/base-$STAMP.sql.gz"
echo "   base : $BACKUP_DIR/base-$STAMP.sql.gz"

for motif in code base; do
    ls -1t "$BACKUP_DIR"/$motif-*.gz "$BACKUP_DIR"/$motif-*.tgz 2>/dev/null | tail -n +$((KEEP + 1)) | xargs -r rm -f
done
DISTANT

    # 4) Apercu.
    etape "4) Apercu (dry-run)"
    APERCU="$(rsync "${RSYNC_OPTS[@]}" --itemize-changes --dry-run "$PROJECT_DIR/" "$REMOTE:$REMOTE_PATH/" | grep -vE '^\.d' || true)"
    for d in "${DELETE_DIRS[@]}"; do
        APERCU+=$'\n'"$(rsync "${RSYNC_OPTS[@]}" --delete --itemize-changes --dry-run \
            "$PROJECT_DIR/$d/" "$REMOTE:$REMOTE_PATH/$d/" | grep '^\*deleting' | sed "s#\*deleting *#*suppression $d/#" || true)"
    done
    APERCU="$(echo "$APERCU" | sed '/^$/d')"
    ENV_DIFF="$(rsync -a -e "$SSH_CMD" --checksum --itemize-changes --dry-run "$PROJECT_DIR/.env.prod" "$REMOTE:$REMOTE_PATH/.env.prod" || true)"

    echo "$APERCU" | head -100
    NB="$(echo "$APERCU" | sed '/^$/d' | wc -l | tr -d ' ')"
    [ "$NB" -gt 100 ] && alerte "… $NB lignes au total."
    [ -n "$ENV_DIFF" ] && alerte ".env.prod local differe de celui du serveur : il sera remplace."
    NOUVELLES="$(echo "$APERCU" | grep -E '^>f\+{9} database/migrations/' | awk '{print $2}' || true)"
    if [ -n "$NOUVELLES" ]; then
        alerte "Migrations nouvelles :"; echo "$NOUVELLES" | sed 's/^/     /'
    fi
    confirm "Envoyer ces modifications ?" || exit 0

    # 5) Maintenance.
    etape "5) Mode maintenance"
    SECRET="$(openssl rand -hex 8)"
    artisan down --secret="$SECRET" --retry=60 >/dev/null
    EN_MAINTENANCE=1
    ok "acces pendant la maintenance : https://<portail>/$SECRET"
fi

# 6) Envoi.
etape "6) Envoi"
rsync "${RSYNC_OPTS[@]}" "$PROJECT_DIR/" "$REMOTE:$REMOTE_PATH/"
for d in "${DELETE_DIRS[@]}"; do
    rsync "${RSYNC_OPTS[@]}" --delete "$PROJECT_DIR/$d/" "$REMOTE:$REMOTE_PATH/$d/"
done
# Pas de --chmod : le rsync de macOS (openrsync / 2.6.9) ne le connait pas.
rsync -a -e "$SSH_CMD" "$PROJECT_DIR/.env.prod" "$REMOTE:$REMOTE_PATH/.env.prod"
ssh_run "chmod 600 .env.prod"
ok "code et .env.prod envoyes"

# 7) Dependances, migrations, caches.
etape "7) Composer, migrations, caches"
composer_install
if [ "$MODE" = "full" ]; then
    if artisan migrate:status --pending 2>/dev/null | grep -q Pending; then
        artisan migrate:status --pending
        if confirm "Executer ces migrations (une sauvegarde de la base existe : $STAMP) ?"; then
            artisan migrate --force
        else
            alerte "migrations NON executees."
        fi
    else
        ok "aucune migration en attente"
    fi
fi
caches
artisan queue:restart >/dev/null
[ "$MODE" = "full" ] && { artisan up >/dev/null; EN_MAINTENANCE=0; ok "site en ligne"; }

# 8) Verification HTTP.
etape "8) Verification"
ERREURS=0
for url in $URL_CHECK; do
    code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$url" || echo 000)"
    if [ "$code" = "200" ]; then ok "$code  $url"; else alerte "$code  $url"; ERREURS=1; fi
done
if [ "$NOINDEX_CHECK" = "1" ] && [ -n "$URL_CHECK" ]; then
    premiere="${URL_CHECK%% *}"
    if curl -sI --max-time 20 "$premiere" | grep -qi '^x-robots-tag:.*noindex'; then
        ok "en-tete X-Robots-Tag noindex present"
    else
        alerte "en-tete X-Robots-Tag noindex ABSENT"; ERREURS=1
    fi
fi

ssh_run "mkdir -p \"$BACKUP_DIR\" && echo '$STAMP $MODE $COMMIT $BRANCHE $(whoami)' >> \"$BACKUP_DIR/deploy.log\""

ligne
if [ "$ERREURS" = "0" ]; then echo "${VERT}Deploiement termine ($COMMIT).${RAZ}"; else echo "${JAUNE}Deploiement termine avec alertes : verifier.${RAZ}"; fi
if [ "$MODE" = "full" ]; then
    echo "   Rollback : ./deploy/deploy-prod.sh rollback $STAMP"
else
    echo "   Mode sync : aucune sauvegarde creee."
fi
ligne
