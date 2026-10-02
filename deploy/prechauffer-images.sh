#!/usr/bin/env bash
#
# Pre-genere en WebP les declinaisons des pages de book (ptf_medium,
# iph_medium) sur le serveur, en lots paralleles par premiere lettre du
# login. Detache : continue si la connexion SSH tombe. Relancable : ce
# qui est deja fait est saute.
#
#   ./deploy/prechauffer-images.sh            lance, 8 lots en parallele
#   ./deploy/prechauffer-images.sh 4          lance, 4 lots
#   ./deploy/prechauffer-images.sh etat       lots en cours, avancement, volume
#
set -euo pipefail
source "$(dirname "$0")/lib.sh"

case "${1:-8}" in
    etat)
        ssh_run "echo \"lots en cours : \$(pgrep -fc 'artisan [u]bdf:prechauffer-images' || true)\"; \
            for f in storage/logs/prechauffe-*.log; do [ -f \"\$f\" ] && printf '%s  %s\n' \"\$(basename \$f)\" \"\$(tr '\r' '\n' < \$f | grep -v '^\s*\$' | tail -n1)\"; done; \
            du -sh storage/app/public/cache_images 2>/dev/null"
        ;;
    *[!0-9]*)
        echo "Usage : $0 [nombre de lots | etat]"; exit 1
        ;;
    *)
        ssh_script "$1" <<'SCRIPT'
LOTS="$1"
if pgrep -f 'artisan [u]bdf:prechauffer-images' > /dev/null; then
    echo "Deja en cours : ./deploy/prechauffer-images.sh etat"; exit 1
fi
nohup sh -c "printf '%s\n' a b c d e f g h i j k l m n o p q r s t u v w x y z 0 1 2 3 4 5 6 7 8 9 _ \
    | xargs -P $LOTS -I{} sh -c '\"\$PHP\" artisan ubdf:prechauffer-images --lettre={} --declinaison=ptf_medium --declinaison=iph_medium > storage/logs/prechauffe-{}.log 2>&1'" \
    > /dev/null 2>&1 &
echo "lance : $LOTS lots en parallele"
SCRIPT
        ok "suivi : ./deploy/prechauffer-images.sh etat"
        ;;
esac
