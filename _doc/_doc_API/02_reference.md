# API — référence pour les applications

Base : `https://<portail>/api/v1` (ex. `https://www.ultra-book.com/api/v1`).
Toujours envoyer `Accept: application/json`.

## Authentification

1. L'application demande un jeton avec l'identifiant (login ou adresse
   mail) et le mot de passe du créatif.
2. Elle stocke le jeton dans le trousseau du système (Keychain macOS/iOS,
   Keystore Android, Credential Manager Windows) — jamais en clair.
3. Chaque requête porte `Authorization: Bearer <jeton>`.

Un jeton n'expire pas ; il est révoqué par la déconnexion, par le
créatif (page « Appareils connectés », phase 4) ou par le blocage du
compte.

### `POST /jetons` — se connecter

Limites : 5 requêtes / minute / IP, et 10 / heure par identifiant (sans captcha, c'est la parade aux essais de mots de passe).

```json
{
  "identifiant": "jeanne",
  "mot_de_passe": "••••••",
  "appareil": "iPhone de Jeanne",
  "droits": ["images:lire", "images:ecrire"]
}
```
`droits` est facultatif : absent, le jeton reçoit tous les droits listés
ci-dessous.

| Réponse | Cas |
|---|---|
| `201 {"jeton": "1|abc…", "droits": [...]}` | connecté |
| `422` erreur sur `identifiant` | mauvais identifiants, compte visiteur, ou adresse partagée par plusieurs books (utiliser le login) |
| `422` erreur sur `droits.N` | droit inconnu |
| `403` | compte suspendu |
| `429` | trop de tentatives |

### `DELETE /jetons/courant` — se déconnecter

`204`, le jeton utilisé est supprimé.

### Connexion par code (application desktop, recommandée)

Le mot de passe ne passe jamais par l'application :

1. `POST /appareils/code` avec `{"appareil": "Mac de Jeanne", "droits": [...]}`
   → `201 {"code": "ABCD-2345", "code_appareil": "<64 car.>", "expire_dans": 600, "url": "https://…/espace/compte#appareils"}`.
2. L'application affiche `code` et ouvre `url` dans le navigateur ; le
   créatif, connecté sur le site, saisit le code dans **Mon compte >
   Appareils connectés**, voit le nom de l'appareil et les droits
   demandés, puis clique « Autoriser ». Le nom envoyé en 1 doit donc
   être reconnaissable (« Mac de Jeanne », pas « client-v2 »).
3. Pendant ce temps, l'application appelle `POST /appareils/jeton`
   avec `{"code_appareil": "…"}` toutes les 5 secondes :

| Réponse | Sens |
|---|---|
| `202 {"statut": "en_attente"}` | pas encore validé, recommencer |
| `201 {"jeton": "…", "droits": [...]}` | connecté ; le code ne resservira pas |
| `410 {"statut": "expire"}` | code expiré (10 min) ou déjà utilisé : en redemander un |

Limites : 5 demandes de code / minute / IP, 20 interrogations / minute / IP.

Le créatif voit et déconnecte chaque appareil dans Mon compte >
Appareils connectés.

## Droits

| Droit | Donne accès à |
|---|---|
| `compte:lire` / `compte:ecrire` | Mon compte |
| `images:lire` / `images:ecrire` | portfolios et visuels |
| `pages:lire` / `pages:ecrire` | pages (bio) |
| `messages:lire` / `messages:ecrire` | Mes messages |
| `memo:lire` / `memo:ecrire` | Mon mémoBook |
| `stats:lire` | statistiques |

Une route appelée sans le droit requis répond `403`.

## Points d'entrée

| Méthode | Route | Droit | Réponse |
|---|---|---|---|
| `GET` | `/moi` | `compte:lire` | profil du créatif |
| `GET` | `/portfolios` | `images:lire` | portfolios, dans l'ordre, avec `nb_visuels` |
| `GET` | `/portfolios/{id}` | `images:lire` | un portfolio et ses `visuels`, dans l'ordre du book |
| `GET` | `/messages?dossier=contacts\|poubelle` | `messages:lire` | demandes, 20 par page, plus `non_lus` global |
| `GET` | `/messages/{id}` | `messages:lire` | un fil complet ; **le marque lu** |
| `GET` | `/pages` | `pages:lire` | rubriques et leurs pages (bio), dans l'ordre du book |
| `GET` | `/memo?recherche=` | `memo:lire` | books mémorisés, du plus récent au plus ancien |
| `GET` | `/statistiques` | `stats:lire` | visites : 90 jours par surface, 12 mois, total |

### Écriture

| Méthode | Route | Droit | Corps | Réponse |
|---|---|---|---|---|
| `PATCH` | `/moi` | `compte:ecrire` | champs du profil (noms de `GET /moi`), `statut`, `categorie`, `accepte_sms` | profil |
| `POST` | `/portfolios` | `images:ecrire` | `nom?`, `publie?`, `mot_de_passe?` | `201` portfolio |
| `PATCH` | `/portfolios/{id}` | `images:ecrire` | `nom?`, `publie?`, `mot_de_passe?` (null retire) | portfolio |
| `DELETE` | `/portfolios/{id}` | `images:ecrire` | — | `204` (visuels supprimés aussi) |
| `PUT` | `/portfolios/ordre` | `images:ecrire` | `ids` | `204` |
| `PUT` | `/portfolios/{id}/ordre` | `images:ecrire` | `ids` (visuels) | `204` |
| `POST` | `/portfolios/{id}/visuels` | `images:ecrire` | multipart `fichier` **ou** `video` (URL) | `201` visuel, en tête du portfolio |
| `PATCH` | `/visuels/{id}` | `images:ecrire` | `titre?`, `legende?`, `portfolio_id?` | visuel |
| `POST` | `/visuels/{id}/image` | `images:ecrire` | multipart `fichier` | visuel (même place, mêmes textes) |
| `DELETE` | `/visuels/{id}` | `images:ecrire` | — | `204` |
| `POST` | `/messages/{id}/reponses` | `messages:ecrire` | `texte` (2 à 5000) | fil complet ; l'émetteur est prévenu par mail |
| `DELETE` | `/messages/{id}` | `messages:ecrire` | — | `204`, à la poubelle |
| `POST` | `/messages/{id}/restaurer` | `messages:ecrire` | — | `204` |
| `DELETE` | `/messages/poubelle` | `messages:ecrire` | — | `204`, suppression définitive |
| `POST` | `/rubriques` | `pages:ecrire` | `titre?` (≤ 100) | `201` rubrique |
| `PATCH` | `/rubriques/{id}` | `pages:ecrire` | `titre?`, `publiee?` | rubrique |
| `DELETE` | `/rubriques/{id}` | `pages:ecrire` | — | `204`, ou `409` si elle a des pages |
| `PUT` | `/rubriques/{id}/ordre` | `pages:ecrire` | `ids` (pages) | `204` |
| `POST` | `/rubriques/{id}/pages` | `pages:ecrire` | `titre?`, `contenu?`, `publiee?` | `201` rubrique |
| `PATCH` | `/pages/{id}` | `pages:ecrire` | `titre?`, `contenu?` (HTML), `publiee?` | rubrique |
| `DELETE` | `/pages/{id}` | `pages:ecrire` | — | `204` |
| `POST` | `/memo` | `memo:ecrire` | `login` | `204` |
| `DELETE` | `/memo/{login}` | `memo:ecrire` | — | `204` |

Règles à connaître :
- **Images** : jpg, png ou gif, 20 Mo maximum (`IMAGES_ENVOI_KO_MAX`),
  un fichier par requête, 60 dépôts par minute. L'image est réencodée
  (EXIF retiré) et bornée à 1980 × 3600. Quota de la formule dépassé,
  image illisible, vidéo introuvable : `422` sur `fichier`.
- **`ids`** : identifiants dans l'ordre voulu ; ceux d'un autre créatif
  sont ignorés.
- **Pages** : le contenu est du HTML, nettoyé comme dans l'espace
  (scripts et attributs dangereux retirés). L'écrire par l'API efface
  la version « blocs » de l'éditeur par blocs.
- **Compte** : `email` et le mot de passe ne se changent que sur le web ;
  tout champ hors liste est ignoré.
- **Répondre** à une demande à la poubelle : `409`, la restaurer d'abord.

Une ressource d'un autre créatif répond `404`.

`GET /moi` :
```json
{
  "data": {
    "id": 42,
    "login": "jeanne",
    "email": "jeanne@exemple.fr",
    "prenom": "Jeanne",
    "nom": "Dupont",
    "marque": "ub",
    "langue": "fr",
    "url_book": "https://jeanne.ultra-book.com",
    "societe": null, "civilite": "mme", "statut": "…",
    "adresse": "…", "code_postal": "69001", "ville": "Lyon", "pays": "FR",
    "telephone": "…", "mobile": "…", "site": "https://…",
    "facebook": null, "twitter": null, "instagram": "https://…",
    "disponible": true, "accepte_sms": false, "dans_annuaire": true,
    "nb_visuels": 84, "stockage_utilise": 152000000
  }
}
```

`GET /portfolios/{id}` :
```json
{
  "data": {
    "id": 7, "nom": "Mode", "publie": true, "protege": false, "position": 1,
    "visuels": [
      {
        "id": 120, "portfolio_id": 7, "titre": "Robe", "legende": null, "video": null,
        "url": "https://…/jeanne/robe.jpg", "miniature": "https://…/adm_medium/…",
        "largeur": 1980, "hauteur": 2970, "poids": 412000, "publie": true
      }
    ]
  }
}
```
Le mot de passe d'un portfolio protégé n'est jamais renvoyé.

`GET /messages` :
```json
{
  "data": [
    {
      "id": 31, "objet": "Prise de contact", "expediteur": "Client Dupont",
      "societe": null, "email": "client@exemple.fr", "telephone": null, "detail": null,
      "indesirable": false, "dernier_message_le": "2026-10-14T10:12:00+02:00",
      "non_lus": 1, "a_la_poubelle": false
    }
  ],
  "links": { "first": "…", "next": null },
  "meta": { "current_page": 1, "last_page": 1, "total": 1 },
  "non_lus": 1
}
```
`GET /messages/{id}` ajoute `messages` : `id`, `du_creatif`, `texte`,
`envoye_le`, `lu_le`. Les doublons consécutifs (double envoi) sont
filtrés.

`GET /pages` :
```json
{ "data": [ { "id": 3, "titre": "Bio", "publiee": true, "privee": false,
  "pages": [ { "id": 9, "titre": "Parcours", "publiee": true, "image": null, "contenu": "<p>…</p>" } ] } ] }
```
`contenu` est du HTML déjà nettoyé, tel qu'affiché dans le book.

`GET /memo` :
```json
{ "data": [ { "login": "paul", "nom": "Paul Martin", "societe": null,
  "url_book": "https://paul.ultra-book.com", "vignette": "https://…",
  "memorise_le": "2026-10-01T09:00:00+02:00" } ] }
```

`GET /statistiques` :
```json
{ "data": {
  "par_jour": { "2026-07-17": { "book": 4, "minibook": 1 }, "…": {} },
  "par_mois": { "2025-11": 120, "…": 0, "2026-10": 87 },
  "total": 15230 } }
```
`par_jour` : 90 entrées, de la plus ancienne à aujourd'hui.

## Point d'entrée technique (VPS)

`GET /domaines/autorise?domain=jeanne-dupont.fr` — appelé par Caddy
avant chaque demande de certificat. `200` si le domaine est servi
(`dns_ok` ou `actif`), `404` sinon. Sans corps, sans authentification,
20 requêtes / minute / IP.

## Codes communs

| Code | Sens |
|---|---|
| `401` | jeton absent, invalide, révoqué, ou compte bloqué |
| `403` | droit manquant |
| `404` | ressource introuvable, ou appartenant à un autre créatif |
| `409` | geste impossible dans l'état actuel (rubrique non vide, demande à la poubelle) |
| `422` | validation : `{"message": "...", "errors": {"champ": ["..."]}}` |
| `429` | limite de débit (120 requêtes / minute par créatif) |

## Exemple

```bash
JETON=$(curl -s -X POST https://www.ultra-book.com/api/v1/jetons \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"identifiant":"jeanne","mot_de_passe":"secret","appareil":"curl"}' | jq -r .jeton)

curl -s https://www.ultra-book.com/api/v1/moi \
  -H 'Accept: application/json' -H "Authorization: Bearer $JETON"
```
