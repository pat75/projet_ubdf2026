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

## 7b — Relances d'abonnement

- `ubdf:relancer-formules`, planifiée chaque jour à 11 h (Europe/Paris) :
  J-5 puis le jour de l'échéance, comme le legacy.
- Le legacy se lançait à la main depuis une URL d'administration, par
  tranches de 500 comptes, sans trace des envois : relancer deux fois
  écrivait deux fois. Ici chaque relance est notée dans
  `subscription_reminders`, avec une clé unique (compte, échéance, jalon).
- Les messages partent en file d'attente (`ShouldQueue`), donc un worker
  est nécessaire : `php artisan queue:work`.
- Nouvelle colonne `users.plan_expires_at`, tenue à jour à chaque
  enregistrement du compte. L'échéance se recalculait auparavant en SQL
  (`DATE_ADD`), ce qui interdisait tout index et ne marchait que sous
  MySQL.

## 7c — Campagnes

- Envoi depuis le back-office : « Envoi d'essai » vers une adresse, puis
  « Envoyer » à tous les créatifs de la marque abonnés à la newsletter.
- Une ligne par destinataire dans `campaign_sends` avant la mise en file :
  reprendre un envoi interrompu ne renvoie rien à ceux qui ont déjà reçu.
- Chaque message porte un lien de désabonnement signé (pas de jeton à
  stocker) ; il coupe `diffuse_newsletter`. Le refus reste modifiable
  depuis `/espace/diffusion`.
- Le contenu rédigé dans l'éditeur riche est filtré à l'enregistrement
  (`NettoyeurHtml`).
- **À reprendre à la migration réelle** : la table `ub2_nl_unsubscribe` de
  production (absente de l'instantané ub2020 de 2022) porte les
  désabonnements déjà exprimés. Sans elle, des personnes désabonnées
  seraient réabonnées de fait.
