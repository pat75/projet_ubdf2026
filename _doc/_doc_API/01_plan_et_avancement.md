# API Ultra-book / Dustfolio — plan et avancement

API des applications **mobile** et **desktop** (Mac/PC) du créatif.
Le VPS des domaines personnalisés n'utilise qu'un point d'entrée dédié
(`domaines/autorise`), voir `_doc/_doc_VPS/`.

Périmètre : Mon compte, Mes messages, Mon mémoBook, Statistiques,
Images (portfolios), Pages (bio). **Ma formule : hors périmètre pour
l'instant.**

## Choix structurants

| Sujet | Choix | Pourquoi |
|---|---|---|
| Authentification | Laravel Sanctum, jeton par appareil | pas d'application tierce : OAuth (Passport) inutile |
| Droits | liste fermée par jeton (`JetonRequest::DROITS`), jamais `*` | un jeton d'upload desktop n'a pas à lire les messages |
| Version | préfixe `/api/v1` | une application installée ne se met pas à jour avec le site |
| Logique métier | services partagés `app/Services/Espace/*`, appelés par Livewire **et** par l'API | une seule règle (quotas, ordre, compteurs), pas deux copies |
| Autorisation | chez l'appelant (policy / requête limitée au créatif), pas dans le service | le service reste réutilisable et testable |
| Sortie | API Resources (`app/Http/Resources/Api`) | liste blanche : jamais le modèle brut |
| Compte bloqué | jetons invalidés à la requête suivante (`Sanctum::authenticateAccessTokensUsing`) | même règle que les sessions web |

## Phases

| Phase | Contenu | État |
|---|---|---|
| 0 — Socle | Sanctum, `routes/api.php` v1, jetons, droits, limites de débit, `GET /moi` | **fait** (11 tests) |
| 1 — Extraction | logique des composants Livewire vers des services partagés | **fait** : Galeries → `Portfolios`, Messages → `Boite` |
| 2 — Lecture | portfolios & visuels, pages (bio), messages, mémoBook, statistiques, compte | **fait** |
| 3 — Écriture | upload d'image (`DepotVisuel` + quotas), ordre, déplacement, réponse aux messages, champs du compte, pages, mémoBook | **fait** (48 tests API au total) |
| 4 — Appareils | page « Appareils connectés » (Mon compte), révocation, connexion desktop par code | **fait** (5 tests) |
| 5 — Domaines | `domaines/autorise` pour Caddy, passerelle, service OVH, achat par le créatif | **fait** ; achat en développement seulement (`_doc_VPS/03_achat_domaine.md`) |
| 6 — Durcissement | audit sécurité, documentation OpenAPI (Scramble), CORS fermé | **fait** (Scramble écarté, voir journal) |

## Journal

### 2026-10-14 — Phase 0
- `laravel/sanctum` 4.3 installé (à la main en PHP 8.3 : `install:api`
  passe par le PHP 8.1 du PATH et échoue sur Composer).
- Migration `personal_access_tokens` passée sur `2026_ubdf`.
- Alias `ability` / `abilities` déclarés dans `bootstrap/app.php`.
- Limites : `api-jetons` 5/min/IP, `api` 120/min par créatif.

### 2026-10-14 — Phase 1 (début)
- `App\Services\Espace\Portfolios` : liste, créer, publier/dépublier,
  supprimer (compteurs `media_count`/`storage_used`), ordonner,
  supprimer un visuel, ranger (`media_order`), déplacer un visuel,
  visuels dans l'ordre du book.
- `Livewire\Espace\Galeries` délègue à ce service (16 tests verts).
- Correctif annexe : `$marque` partagé par défaut aux vues
  (`AppServiceProvider`) — une 404 levée dans une requête Livewire
  plantait faute de `ResoudreMarque`.

### 2026-10-14 — Phase 1 (fin) et phase 2 (début)
- `App\Services\Messagerie\Boite` : dossiers, non lus, indésirables,
  marquer lu, corbeille, filtrage des doublons. `Livewire\Espace\Messages`
  délègue (24 tests verts). La réponse reste dans `Intermediation`.
- Lecture : `GET /portfolios`, `GET /portfolios/{id}`, `GET /messages`,
  `GET /messages/{id}` (9 tests). Les ressources d'un autre créatif
  répondent 404 (requête limitée au créatif, pas de policy à part).
- Ouvrir un fil par l'API le marque lu, comme dans l'espace.

### 2026-10-14 — Phase 2 (fin)
- `App\Services\Espace\PagesBook` (rubriques et pages dans l'ordre du
  book) : `Livewire\Espace\Pages` l'utilise.
- `App\Services\Stats\TableauVisites` (90 jours, 12 mois, total) :
  `StatistiquesController` ne fait plus que la mise en forme (libellés
  de mois traduits). L'API rend les mois en `Y-m`.
- MémoBook : `MemoBooks::books()` existant, réutilisé tel quel.
- `GET /moi` enrichi (coordonnées, réseaux, préférences, compteurs) ;
  jamais `admin_note`, `signup_ip`, ni la formule.
- Routes : `GET /pages`, `GET /memo`, `GET /statistiques` (8 tests).

### 2026-10-14 — Phase 3
- Images : créer/modifier/supprimer/ordonner les portfolios, déposer
  (image ou vidéo YouTube/Vimeo), remplacer, modifier, déplacer,
  supprimer un visuel. Refus métier de `DepotVisuel` (quota, image
  illisible, vidéo introuvable) rendus en 422. Limite `api-depots`
  60/min par créatif.
- Messages : répondre (mail à l'émetteur via `Intermediation`),
  poubelle, restauration, vidage. Répondre à une demande à la poubelle
  : 409.
- Compte : `PATCH /moi` sur la liste blanche de l'espace
  (`Compte::CHAMPS`, passée en `public`), plus statut, catégorie, SMS.
  Mail et mot de passe : web uniquement.
- Pages : `PagesBook` porte désormais création/nommage/suppression de
  rubrique, création/nommage/enregistrement/ordre de page ;
  `Livewire\Espace\Pages` y délègue (17 tests verts). Écrire le
  contenu par l'API efface `body_blocks` (le HTML fait foi).
- MémoBook : ajout/retrait d'un book (`MemoBooks::ajouter/retirer`).
- Suite complète : 974 verts, 7 échecs **tous antérieurs à l'API**
  (vérifiés sans les modifications) : BackOffice, CompteEspace (adresse
  mail), EnteteDustfolio, Compte, Marque, Newsletter, TraductionsPortail.

### 2026-10-14 — Phase 4
- `App\Services\Api\ConnexionParCode` : connexion par code (principe
  du « device flow »), tout en cache 10 min, code court sans 0/O/1/I,
  code appareil haché dans la clé de cache, usage unique.
- `Livewire\Espace\Appareils`, inséré dans Mon compte (ancre
  `#appareils`, avant la zone de suppression) : liste des jetons
  (dernier usage, date), déconnexion (poubelle + bulle d'aide),
  saisie du code. `npm run build` relancé.
- Routes publiques `POST /appareils/code` (5/min/IP) et
  `POST /appareils/jeton` (20/min/IP, interrogation toutes les 5 s).
- Non vérifié dans un navigateur : rendu testé par Pest seulement.

### 2026-10-14 — Phase 5 (socle)
- Table `domaines` (migration passée sur `2026_ubdf`), modèle `Domaine`
  (statuts `en_attente`, `dns_ok`, `actif`, `suspendu`, `expire`).
  `users.custom_domain` (legacy) écarté : 13 valeurs, toutes des
  domaines de marque, aucune exploitable.
- `ServirDomainePerso` (premier middleware global) : avec le bon
  `X-Proxy-Secret`, `X-Forwarded-Host` d'un domaine servi fait passer la
  requête pour `<login>.<domaine des books>` ; sans secret, en-têtes
  ignorés ; domaine non servi : 404.
- `GET /api/v1/domaines/autorise?domain=` pour le `ask` de Caddy.
- `App\Services\Domaines\Ovh` : signature OVH maison (client HTTP
  Laravel, pas de paquet), disponibilité/prix, commande au nom du
  créatif (contact owner), pointage A/AAAA + www vers le VPS.
  **Enchaînement de commande à valider sur un premier achat réel.**
- Job `CommanderDomaine` (un seul essai : pas de double paiement).
- `ubdf:verifier-domaines` (toutes les heures) : pointe les domaines
  OVH livrés, active ceux dont le DNS vise le VPS, désactive ceux qui
  ne le visent plus.
- Configuration : `PROXY_SECRET`, `OVH_*`, `VPS_IP`, `VPS_IP6`
  (`config/ubdf.php`, `config/services.php`).

Reste à faire en phase 5 : page « Mon nom de domaine » (recherche,
achat, domaine externe) — dépend de la facturation des domaines, donc
de Ma formule.

### 2026-10-14 — Phase 5 (SEO des domaines personnels)
- Servi par la passerelle, le book génère ses URL sur le domaine perso
  (`URL::formatHostUsing`) : liens, canonique, `og:url`, JSON-LD,
  `User::bookUrl()`. Les images (`/books/…`, routes `book.media*`)
  restent sur le sous-domaine, sans transiter par le VPS.
- `VueBook::urlCanonique()` et `donneesStructurees()` passent par
  `url()` au lieu de l'hôte brut de la requête.
- Sous-domaine d'un book dont le domaine est `actif` : GET → 301 vers le
  domaine perso (chemin et requête conservés), sauf `/books/*`.
  `dns_ok` ne redirige pas (certificat pas forcément prêt).
- Liens du portail et sitemap global : gardent le sous-domaine, qui
  redirige (évite une requête par book dans les listes).

### 2026-10-14 — Remise au vert de la suite de tests
Les 8 échecs antérieurs à l'API sont corrigés ; suite complète :
1004 verts.
- `ArchiveBook` : suffixe aléatoire au nom d'archive (deux suppressions
  dans la même seconde se heurtaient) ; `CompteEcranTest` efface les
  archives qu'il crée.
- Tests alignés sur des changements volontaires du code : adresse mail
  partagée entre books (`edf0f22`), book diffusé dès l'inscription
  (`2b0c303`), page `qui-sommes-nous` retirée (`cd556af`), ressource
  Sélections supprimée (`10ab8ca`), back-office sur `/admin_`, burger
  Dustfolio sous 900 px (`4800168`).
- `lang/en.json` : 45 traductions manquantes ajoutées (dont la carte
  Appareils connectés).

### 2026-10-14 — Phase 6 (audit et durcissement)
Corrigé :
- **Moyen** — `POST /jetons` n'avait pas le captcha du portail et
  n'était limité que par IP : essais de mots de passe répartis sur
  plusieurs IP. Ajout d'une limite par identifiant (10/heure), en plus
  de 5/min/IP.
- **Moyen** — connexion par code : un code transmis par un tiers
  (« tapez ce code pour recevoir votre cadeau ») donnait le compte à
  l'appareil du tiers. Désormais en deux temps : le créatif voit le nom
  de l'appareil et les droits demandés, puis confirme. Essais de codes
  limités à 10 par quart d'heure.
- **Faible** — CORS par défaut de Laravel ouvert à toutes les origines
  sur `api/*` : fermé (`config/cors.php`).
- **Faible** — Sanctum essayait aussi la garde `web` : vidée, l'API ne
  s'authentifie que par jeton (sans effet aujourd'hui, la session n'est
  pas démarrée sur `api/*`, mais plus de piège si elle l'est un jour).

Vérifié sans problème : requêtes limitées au créatif (404 sur la donnée
d'autrui), Resources en liste blanche, jetons hachés par Sanctum,
révocation au blocage, upload réencodé (`DepotVisuel`), HTML des pages
nettoyé, secret de passerelle comparé en temps constant, signature OVH.

Laissé tel quel (choix) :
- jetons sans expiration : une application installée doit rester
  connectée ; révocation par le créatif ou le blocage du compte ;
- clés OVH : à créer avec des droits restreints (`/order/*`,
  `/me/contact`, `/domain/zone/*`), voir `_doc_VPS/02`.
- Scramble (OpenAPI) écarté : `02_reference.md` couvre déjà toutes les
  routes ; une dépendance de plus pour une doc générée moins précise.

## Fichiers

| Fichier | Rôle |
|---|---|
| `routes/api.php` | routes `api.v1.*` |
| `app/Http/Controllers/Api/JetonController.php` | création / révocation de jeton |
| `app/Http/Requests/Api/JetonRequest.php` | validation + liste des droits |
| `app/Http/Resources/Api/CreatifResource.php` | profil exposé |
| `app/Services/Espace/Portfolios.php` | règles portfolios/visuels partagées |
| `app/Services/Messagerie/Boite.php` | règles de la boîte de réception partagées |
| `app/Http/Controllers/Api/PortfolioController.php` | lecture portfolios |
| `app/Http/Controllers/Api/MessageController.php` | lecture messages |
| `app/Http/Resources/Api/{Portfolio,Visuel,Conversation}Resource.php` | formats de sortie |
| `app/Http/Controllers/Api/LectureController.php` | lecture pages, mémoBook, statistiques |
| `app/Services/Espace/PagesBook.php` | rubriques/pages partagées |
| `app/Services/Stats/TableauVisites.php` | statistiques partagées |
| `app/Http/Controllers/Api/VisuelController.php` | dépôt et édition des visuels |
| `app/Http/Controllers/Api/PageController.php` | écriture rubriques et pages |
| `app/Http/Requests/Api/*` | `Portfolio`, `Visuel`, `Depot`, `Ordre`, `Reponse`, `Compte`, `Page` |
| `app/Services/Api/ConnexionParCode.php` | connexion desktop par code |
| `app/Http/Controllers/Api/AppareilController.php` | routes de la connexion par code |
| `app/Livewire/Espace/Appareils.php` + vue | Mon compte > Appareils connectés |
| `app/Models/Domaine.php` + migration `create_domaines_table` | domaines personnels |
| `app/Http/Middleware/ServirDomainePerso.php` | passerelle VPS → book |
| `app/Services/Domaines/Ovh.php`, `Dns.php` | API OVH, lecture DNS |
| `app/Jobs/CommanderDomaine.php` | achat en file d'attente |
| `app/Console/Commands/VerifierDomainesCommand.php` | `ubdf:verifier-domaines` |
| `config/sanctum.php` | configuration Sanctum |
| `tests/Feature/Api/` | tests de l'API |
