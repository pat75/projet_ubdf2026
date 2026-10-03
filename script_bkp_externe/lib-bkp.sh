#!/usr/bin/env bash
#
# Fonctions communes de script_bkp_externe/ (a sourcer, pas a executer).
# Le script appelant fixe CONFIG (bkp.config sur o2switch, pull.config sur
# giga) et REQUIS (variables obligatoires) avant de sourcer.
#

BKP_HOME="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_DIR="$BKP_HOME/logs"

[ -f "$CONFIG" ] || { echo "config manquante : $CONFIG (copier le .example)" >&2; exit 1; }
# shellcheck disable=SC1090
source "$CONFIG"

for v in $REQUIS; do
    [ -n "${!v:-}" ] || { echo "manque $v dans $(basename "$CONFIG")" >&2; exit 1; }
done
MAIL_FROM="${MAIL_FROM:-backup@$(hostname)}"
MAIL_ON="${MAIL_ON:-toujours}"
SENDMAIL="${SENDMAIL:-/usr/sbin/sendmail}"
LOG_DAYS="${LOG_DAYS:-30}"

ERREURS=0
RESUME=""
JOURNAL="(aucun)"

etape()  { echo; echo "== $*"; }
info()   { echo "   $*"; RESUME+="$*"$'\n'; }
erreur() { echo "   ERREUR : $*" >&2; RESUME+="ERREUR : $*"$'\n'; ERREURS=$((ERREURS + 1)); }

# Journal du lancement : logs/<commande>-<date>.log, purge au-dela de LOG_DAYS.
journaliser() {
    mkdir -p "$LOG_DIR"
    JOURNAL="$LOG_DIR/${1:-bkp}-$(date +%Y%m%d-%H%M%S).log"
    exec > >(tee -a "$JOURNAL") 2>&1
    echo "# $(date '+%F %T')  $0 $*"
    find "$LOG_DIR" -name '*.log' -mtime +"$LOG_DAYS" -delete 2>/dev/null || true
}

# Une seule execution a la fois.
verrouiller() {
    mkdir -p "$LOG_DIR"
    exec 9>"$LOG_DIR/.verrou"
    flock -n 9 || { echo "une sauvegarde tourne deja" >&2; exit 1; }
}

taille() { du -sh "$1" 2>/dev/null | cut -f1; }

# Mail de rapport. envoyer_rapport <commande> <duree s>
envoyer_rapport() {
    local statut="OK"
    [ "$ERREURS" -eq 0 ] || statut="ECHEC"
    [ "$MAIL_ON" = "erreur" ] && [ "$statut" = "OK" ] && return 0
    {
        echo "From: $MAIL_FROM"
        echo "To: $MAIL_TO"
        echo "Subject: [UB backup] $statut - $(hostname -s) $1 - $(date '+%F %H:%M')"
        echo "Content-Type: text/plain; charset=UTF-8"
        echo
        echo "Hote : $(hostname)"
        echo "Duree : ${2}s"
        echo "Disque : $(df -h "${DEST_DIR:-$HOME}" 2>/dev/null | tail -1 | awk '{print $4 " libres sur " $2}')"
        echo
        printf '%s' "$RESUME"
        echo
        echo "Journal : $JOURNAL"
    } | "$SENDMAIL" -t || echo "envoi du mail impossible ($SENDMAIL)" >&2
}
