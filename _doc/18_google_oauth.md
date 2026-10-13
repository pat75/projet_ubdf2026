# Google OAuth — configuration de la console Google

Connexion et création de book par Google. Le code est déjà en place : `App\Http\Controllers\Front\GoogleController`, routes `/auth/google*` (`routes/web.php`), `config/services.php` (clé `google`), Laravel Socialite. Il ne manque que le client OAuth côté Google et deux variables dans `.env`.

L'adresse de retour n'est pas fixée dans le code : elle suit l'hôte de la requête (Ultra-book ou Dustfolio). **Chaque hôte doit donc être déclaré dans la console Google**, sinon erreur `redirect_uri_mismatch`.

## 1. Projet Google Cloud

1. https://console.cloud.google.com/ → sélecteur de projet → **Nouveau projet** (ex. `ultra-book`).
2. Le projet créé, le sélectionner.

## 2. Écran de consentement

Menu **API et services → Écran de consentement OAuth** (ou **Google Auth Platform → Branding**).

1. Type d'utilisateur : **Externe**.
2. Nom de l'application : `Ultra-book`. Logo : PNG ou JPG, 120 px minimum (le SVG du site n'est pas accepté, en exporter un PNG).
3. E-mail d'assistance et e-mail du développeur : une adresse Ultra-book.
4. Domaines autorisés : `ultra-book.com` (ajouter `dustfolio.com` pour Dustfolio) ; pages d'accueil, confidentialité et conditions : les URL publiques correspondantes.
5. Champs d'application (scopes) : `openid`, `email`, `profile`. Ce sont des scopes non sensibles, **aucune validation Google n'est nécessaire**.
6. État de publication : tant que l'application est en **Test**, seuls les comptes listés dans **Utilisateurs test** peuvent se connecter. Pour ouvrir à tous : **Publier l'application** (passage en Production, sans revue pour ces scopes).

## 3. Identifiants OAuth

Menu **Identifiants → Créer des identifiants → ID client OAuth**.

- Type : **Application Web**.
- Nom : `Ultra-book web`.
- **Origines JavaScript autorisées** : laisser vide (flux par redirection serveur).
- **URI de redirection autorisés** : une ligne par hôte, schéma `https`, chemin exact `/auth/google/callback`, sans slash final.

| Environnement | URI de redirection |
|---|---|
| Dev Ultra-book (Valet) | `https://ubdf2026.ultra-book.name/auth/google/callback` |
| Dev Dustfolio | `https://ubdf-dust-2026.ultra-book.name/auth/google/callback` |
| Prod Ultra-book | `https://www.ultra-book.com/auth/google/callback` |
| Prod Ultra-book (sans www) | `https://ultra-book.com/auth/google/callback` |
| Prod Dustfolio | `https://www.dustfolio.com/auth/google/callback` |
| Prod Dustfolio (sans www) | `https://dustfolio.com/auth/google/callback` |

Remarques :

- Google exige **HTTPS** hors `localhost`. Valet sert déjà `.name` en HTTPS.
- Les sous-domaines de books (`<login>.ubdf2026…`) ne sont **pas** concernés : la connexion se fait sur le portail.
- Aucun joker n'est accepté : lister chaque hôte réellement utilisé. Pour la recette (`ultra-book.pro`), ajouter aussi `https://www.ultra-book.pro/auth/google/callback`.
- Le changement peut mettre quelques minutes à être pris en compte.

À la création, Google affiche l'**ID client** et le **code secret**. Les copier (le secret se retrouve ensuite dans la fiche de l'identifiant).

## 4. Variables d'environnement

Dans `.env.dev` (et `.env.prod` sur le serveur) :

```
GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxx
```

Un seul client OAuth sert Ultra-book et Dustfolio. Puis :

```bash
/usr/local/opt/php@8.3/bin/php artisan config:clear
```

En production, le secret ne va que dans le `.env.prod` du serveur, jamais dans le dépôt ni dans les fichiers `*.example`.

## 5. Vérification

1. Back-office, page **Accueil** : l'interrupteur « proposer Google » doit être activé (réglage `google_masque`, clé absente = Google proposé).
2. Sur le portail, « Se connecter » → bouton Google. Redirection vers `/auth/google`, puis Google.
3. Cas à tester :
   - **Adresse inconnue** : retour sur la page d'inscription, choix de l'adresse du book, compte créé avec e-mail déjà vérifié.
   - **Adresse d'un compte existant, vérifiée des deux côtés** : le compte est rattaché (`users.google_id`) et connecté.
   - **Adresse d'un compte non vérifié, ou partagée par plusieurs books** : refus, message invitant à utiliser identifiant et mot de passe.

## Erreurs courantes

| Message | Cause |
|---|---|
| `redirect_uri_mismatch` | l'hôte courant n'est pas dans les URI de redirection, ou `www` / sans `www`, `http` / `https` ne correspondent pas |
| `access_denied` / « application non validée » | application encore en Test et compte absent des utilisateurs test |
| `invalid_client` | ID client ou secret erroné, ou `config:clear` oublié |
| « La connexion par Google n’est pas disponible » | `GOOGLE_CLIENT_ID` vide, ou Google masqué dans le back-office |
