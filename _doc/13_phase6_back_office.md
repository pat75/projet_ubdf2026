# Phase 6 — Back-office

Remplace les 74 scripts de `admin_/` du legacy, protégés par un unique
`.htpasswd` et des mots de passe écrits dans les scripts.

Construit avec **Filament 5** (décision du 23/09/2026) : listes, filtres,
fiches et exports sans les écrire à la main, dans un rendu proche de
l'espace créatif (Livewire + Tailwind).

## Accès

- Panneau sur `/admin`, cantonné à l'hôte du portail
  (`ubdf.portail_domain`) : sans cela il répondrait sur chaque
  sous-domaine de book.
- Table `admins` et garde `admin` séparées des comptes créatifs : un
  créatif ne peut pas devenir administrateur, et l'accès se coupe en
  désactivant le compte (`is_active`).
- Compte local créé à la main (voir README de développement).

## Écrans

| Groupe | Écran | Contenu |
|---|---|---|
| Créatifs | Créatifs | Liste, filtres (marque, métier, formule payante, formule échue, supprimés), fiche (coordonnées, formule, portail, note interne). Pas de création : elle passe par l'inscription. |
| Créatifs | Demandes de contact | Fils reçus, marquage indésirable à l'unité ou en masse. Lecture seule. |
| Facturation | Factures | Liste, filtres (état, moyen de paiement, marque, année), total, lien vers la facture imprimable. Lecture seule. |
| Facturation | Codes promo | Création et suivi des codes qui créditent des mois de formule. |
| Éditorial | Sélections | Périodes de sélection par marque et métier, créatifs retenus. |
| Éditorial | Campagnes | Newsletters et relances : contenu et programmation (l'envoi est phase 7). |
| Éditorial | Métiers | Libellés, ordre, activation ; nombre de créatifs par métier. |
| Réglages | Administrateurs | Comptes du back-office. |

Tableau de bord : créatifs et inscriptions du mois, formules payantes et
échues, encaissements du mois, demandes des 30 derniers jours.

## Reste à faire

- Exports CSV (le legacy avait `export_diffusion.php`, `csv/`).
- Relances d'abonnement (`abo_relance_liste*.php`) : phase 7, en file
  d'attente.
- Sélection automatique (`_admin_autoselection.php`), publication Twitter
  et Instagram : à décider, ces comptes existent-ils encore ?
- Journal d'audit des actions d'administration.
