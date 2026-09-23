# Phase 7 — Paiement et e-mails

La souscription Payplug elle-même a été faite en phase 5 (voir
`_doc/12_phase5_espace_creatif.md`, lots 5g et 5j).

## 7a — Factures en PDF

- `barryvdh/laravel-dompdf`. Le PDF reprend le même gabarit que la facture
  affichée (`espace/facture.blade.php`) : une seule source à maintenir.
  La variable `$pdf` retire le bouton d'impression.
- Pas de flexbox dans ce gabarit : dompdf ne la connaît pas.
- `/espace/factures/{id}/pdf` pour le créatif propriétaire,
  `/admin/factures/{id}/pdf` pour un administrateur (qui n'est pas le
  propriétaire et a donc sa propre porte d'entrée).
- Le legacy n'avait pas de PDF : la facture se sortait par l'impression du
  navigateur.
