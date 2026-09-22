# Phase 5 — Espace créatif

Choix (22/09/2026) : l'espace est construit directement en Livewire 4 +
Alpine + Tailwind 4, et non porté depuis le legacy (jQuery/Semantic UI).
Il n'est vu que par les créatifs connectés, n'a aucune URL indexée à
préserver, et un portage aurait été refait en phase 9.

La feuille `resources/css/espace.css` n'est chargée que par
`layouts/espace` : le preflight de Tailwind n'atteint pas le portail, qui
reste sur Semantic UI.

## Inventaire du legacy

| Legacy | Rôle | Lot |
|---|---|---|
| `ubaction__user_bookaccueil` | tableau de bord | 5a |
| `ajax_2011_usadmin.php` rub_* / img_* | galeries, visuels, envoi, ordre | 5b |
| `ajax_2011_usadmin.php` pag_add, `ajax_2014_usadmin_editxt.php` | pages de texte, textes du thème | 5c |
| `ubaction__user_pref_form`, conf_modif_* | habillage et réglages des 11 thèmes | 5d |
| `ubaction__user_diffusion` | diffusion (portail, web, newsletter, dispo) | 5d |
| `ubaction__user_modif_form` | informations du compte, mot de passe | 5e |
| `ubaction__user_message`, `intermediate_msg_` | messages reçus | 5f |
| `ubaction__user_pref_formule`, factures | formule et factures | 5f |
| `ubaction__user_pref_nano` | export du book (nanobook) | 5f |
| PDF du book (propriétaire seul) | export PDF | 5f |

Les rubriques non livrées n'apparaissent pas dans le menu
(`x-espace.nav-lien` teste `Route::has`).

## 5a — Socle

Livewire installé, `layouts/espace`, menu, tableau de bord (`/espace`).

## 5b — Galeries et visuels

- `/espace/galeries` : créer, renommer (édition en place), masquer,
  supprimer (la galerie et ses visuels), réordonner par glisser-déposer.
- `/espace/galeries/{id}` : envoi multiple par dépôt ou sélection
  (JPG/PNG/GIF, 20 Mo, 50 fichiers), titre et description, masquer,
  supprimer, réordonner.
- `DepotVisuel` borne la source à 1980x3600 (déclinaison `source`) et la
  réencode : EXIF et contenu greffé disparaissent. Nom de fichier aléatoire.
- L'ordre des visuels reste `galleries.media_order`, en identifiants legacy
  pour les visuels repris : c'est ce que lit `ContexteBook::ordonner()`.
- Accès : `GalleryPolicy` (le propriétaire seul), vérifiée par la route et
  par chaque action Livewire.

## 5c — Pages de texte

- `/espace/pages` : rubriques (accueil, pages, actualités du classique 2010)
  et leurs pages ; créer, renommer, masquer, supprimer, réordonner
  (`page_order`, en identifiants legacy comme `media_order`).
- `/espace/pages/{id}` : édition avec Trix. Pas de fichier joint dans
  l'éditeur : les images passent par les galeries.
- `NettoyeurHtml` (symfony/html-sanitizer) filtre le corps à
  l'enregistrement : les gabarits l'affichent sans échappement.
- Limite connue : Trix normalise le HTML qu'il charge. Une page reprise du
  legacy (CKEditor : tableaux, polices, styles en ligne) perd cette mise en
  forme au premier enregistrement depuis l'espace.

## 5d — Habillage et diffusion

- `/espace/habillage` : choix parmi les modèles de `config/book_themes.php`,
  titre, description, pied de page, réglages du modèle.
- Le formulaire des réglages est déduit de la configuration JSON du thème
  (`data`) : couleur, case, nombre ou texte selon la valeur. Le format
  enregistré reste celui du legacy, lu tel quel par `ContexteBook`.
  Les valeurs sont contrôlées par type (couleur `#hex`, nombre, texte sans
  balises) : elles finissent dans du CSS en ligne.
- Chaque modèle garde ses réglages : ceux des modèles inactifs restent dans
  `legacy_payload` sous la colonne legacy du thème (`ReglagesTheme`).
- Libellés : dérivés des clés techniques (« Link bio », « Couleur fond »…),
  à remplacer par des libellés rédigés si besoin.
- `/espace/diffusion` : book en ligne, portail, newsletter, disponibilité.
- Pas repris : la barre de réglage visuelle intégrée au book
  (`ubdf_barrereglage`), et le choix des polices Google Fonts dans une liste
  (champ texte libre pour l'instant).

## 5e — Compte

- `/espace/compte` : métier, coordonnées, liens (http/https seulement).
- Adresse e-mail et mot de passe : le mot de passe actuel est exigé ;
  adresse unique ; session régénérée après changement de mot de passe.
- Non fait : déconnexion des autres appareils (demanderait le middleware
  `AuthenticateSession`), confirmation de la nouvelle adresse par e-mail.

## 5f — Messages, formule et factures

- `/espace/messages` : demandes reçues (hors indésirables), lecture qui
  marque lu, réponse depuis l'espace. L'émetteur est prévenu par le même
  e-mail que depuis le lien du fil (`Intermediation::notifierAutrePartie`,
  sorti de `FilController`).
- `/espace/formule` : formule, échéance (`plan_started_at` + `plan_months`),
  factures payées. `/espace/factures/{id}` : facture imprimable, reprise du
  gabarit legacy ; l'éditeur est dans `config('ubdf.editeur')`.
- Correction de l'import : `fac_stats` vaut 0 ou NULL sur toutes les lignes,
  le legacy n'écrivant une facture qu'après paiement. Les factures étaient
  toutes importées « pending » ; elles sont maintenant `paid` (sauf
  annulation notée dans la trace), avec le vrai moyen de paiement
  (payplug, paypal, chèque, virement) déduit de la trace.
- Non fait, décision à prendre : la souscription et le renouvellement en
  ligne (Payplug dans le legacy).

## 5f — Export (microbook)

- `/microbook_<admin>_<pied>__<login>` : URL du legacy conservée (collée
  dans des sites tiers). Réécrit sans jQuery 1.7 : vignette, lien, métier,
  ville, 10 visuels (1re galerie complétée par la 2e). 404 si le book est
  hors ligne. `frame-ancestors *` : intégrable partout.
- `/espace/exporter` : code d'intégration à copier et aperçu.

## 5f — PDF du book

- `/espace/exporter/pdf` (propriétaire seul, 10 par minute) : FPDF,
  pages carrées 210 mm, couverture puis un visuel centré par page, comme
  `ultrabook_pdf_mdl_carre.php`. Limites du legacy : 4 galeries / 10
  visuels / 4 pages en formule gratuite, 30 / 50 / 80 sinon.
- Visuels réencodés en JPEG avant insertion (FPDF ne lit ni transparence
  ni PNG 16 bits). Mesure : 80 visuels, 18 Mo, 3,7 s.
- Différences : police Helvetica au lieu de Titillium ; plus de courriel
  envoyé à l'administrateur à chaque PDF ; pas de vignette en couverture.

## 5g — Souscription et renouvellement (Payplug)

- Grille reprise de `$conf_formule[1]` : `config/formules.php`, numéros
  d'option du legacy. Le réabonnement (29,80 €) remplace le 12 mois dès
  qu'une facture a été payée.
- `POST /espace/formule/payer/{option}` → page de paiement hébergée
  Payplug ; retour sur `/espace/formule/retour`.
- `POST /payplug/notification` (sans CSRF) : le SDK relit le paiement
  auprès de l'API, le corps reçu n'est jamais cru. Le montant doit égaler
  le prix de l'option (le legacy acceptait une liste de montants en dur).
  Idempotent sur l'identifiant du paiement.
- Une formule active est prolongée depuis son échéance (le legacy repartait
  d'aujourd'hui et faisait perdre les mois restants).
- À faire avant la mise en ligne : `PAYPLUG_SECRET_KEY` dans `.env`
  (clé de test en local), essai de bout en bout en sandbox. Les clés du
  legacy sont en clair dans `inc/inc_user_formule.php` : à régénérer.

## 5h — Statistiques

- Les gabarits des books appelaient un pixel sur `www.extra-book.com/2012_stats`
  (serveur du legacy). Ils appellent maintenant `/ubstats.gif` sur le
  sous-domaine du book (`StatsBookController`) : une vue par visiteur
  (IP + navigateur) et par demi-heure, robots exclus, le propriétaire compté
  à part (`admin_views`). Agrégat par jour dans `visit_stats`, dont
  l'historique importé du legacy.
- `/espace/statistiques` : aujourd'hui, 30 jours (histogramme), 12 mois,
  total depuis l'ouverture.
- Restent dans les gabarits : l'ancien Google Analytics (`ga.js`, UA-…,
  service arrêté par Google) et l'adresse `stats.ultraportfolio.info` lue
  par le JS d'administration intégré au book. Sans effet, à retirer avec
  la reprise des scripts des thèmes.

## 5i — Parrainage et codes promo

- Code de parrainage au format du legacy (« AR-46969 » : deux lettres du
  login, identifiant legacy ; « N » devant l'identifiant pour les comptes
  créés depuis). Parrain et filleul doivent avoir une formule payante ;
  bonus de 1 mois (formule ≤ 6 mois) ou 3 mois, un seul parrainage par
  filleul.
- Codes promo du legacy (`ub2_codepromo`) : crédit de mois, usage unique.
  Importés dans `promo_codes` (`discount_type = months`).
- Saisie dans `/espace/formule` : 5 essais par heure.
- `User::prolongerFormule()` est commun au paiement, au parrainage et aux
  codes promo.
- Import : `migrateReferrals`, `migratePromoCodes` ajoutés à
  `ubdf:migrate-legacy`.

## Non repris

- WordPress `www.ultra-book.fr/annonces` (offres de projets, locaux et
  coworking) : abandonné (décision du 22/09/2026). Ni les offres
  (`ubaction__offres__<id>`) ni les liens du portail vers ces annonces ne
  sont repris.

## 5j — Promotions ponctuelles

Reprise de `inc/inc_user_marketing.php` (`App\Services\Paiement\Promotions`) :

- Black Friday (option 30, 12 mois à 22 € au lieu de 36,80 €) pour tous,
  pendant les périodes de `formules.black_friday`. Prime sur le
  réabonnement, comme dans le legacy. Périodes 2025 et 2026 déduites du
  rythme de 2024 (lundi de la semaine précédente, 12 jours) : à confirmer.
- Promo auto 6 mois (option 10, 11,90 € au lieu de 21,90 €) pour un
  créateur qui n'a jamais payé : offre valable 24 h, 2 mois après
  l'inscription puis tous les 3 mois, créée à l'ouverture de la page
  formule. Historique : table `marketing_offers` ; la dernière offre de
  chaque créateur est importée de `inc_marketing` pour garder le rythme.
- Non repris : soldes ponctuelles (solde30/40/50, promo30), inactives dans
  la configuration du legacy.
