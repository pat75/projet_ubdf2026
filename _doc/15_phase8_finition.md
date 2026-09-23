# Phase 8 — Finition

## 8a — Anciennes URL

`ubdf:verifier-urls` lit le `.htaccess` de 2019, tire une URL d'exemple de
chaque règle de réécriture et demande au routeur s'il sait y répondre.
Résultat : **94 URL couvertes, 0 sans route**. À relancer après toute
modification des routes.

Redirections ajoutées (`routes/redirections.php`, chargé en dernier pour ne
prendre que ce qui reste) :

| Ancienne URL | Devient |
|---|---|
| `/formules`, `/formules_ultra-book`, `/formules_dustfolio` | `/espace/formule` |
| `/messages` | `/espace/messages` |
| `/create`, `/login` | `/inscription`, `/connexion` |
| `/facture_n__<id>`, `/invoice_n__<id>` | la facture reprise, sinon la liste |
| `/book_<login>`, `/minibook_<login>`, `/-<login>` | le book du créatif |
| `/<slug>__wpactu_<id>` | l'article repris dans `/actus` |
| `/dustfolio__<slug>`, `/page__<slug>` | `/doc/<slug>` |
| `/support`, `/contact` | le site vitrine de la marque |
| `/ubaction__*`, `/ubactiontype__*` restants | `/accueil` |
| `/memo`, `/memobook`, `/microbook_externe` | `/accueil` |
| `/newsletters` | `/actus` |
| `/ja` | `/accueil` (langue plus servie) |

Deux manques réels trouvés au passage :
- la **seconde forme** de l'URL de défilement (`/accueil__sel__all__2`), que
  le legacy déclarait aussi et que le portail ne servait pas ;
- le **microbook** n'acceptait que 0 et 1 comme paramètres, là où le legacy
  prenait n'importe quel chiffre.

Les anciens liens de conversation (`intermediate_msg_`) ne sont pas honorés :
leur jeton ne prouvait rien. Le visiteur est renvoyé à l'accueil avec une
explication.

## 8b — Sitemap et robots.txt

- `/sitemap.xml` : index, puis `/sitemap-pages.xml` et
  `/sitemap-books-<n>.xml` par paquets de 10 000. Le legacy maintenait deux
  fichiers XML à la main (31 et 13 URL) où **aucun book ne figurait**.
- Seuls les books en ligne et diffusés sur le portail y entrent.
- `/robots.txt` est rendu selon la marque et ferme `/espace` et `/admin`,
  que le legacy laissait ouverts.
- Rendu mis en cache 6 heures.

## 8c — Restes du legacy et secrets

- **Google Analytics** : les gabarits chargeaient encore `ga.js` et la
  propriété `UA-464814-11`. Google a arrêté ce service en 2024 : ces appels
  ne mesuraient plus rien. Retirés des 15 gabarits concernés. La mesure
  propre au créatif (`cont_analytic`) subsiste, servie en GA4 (`gtag`)
  quand il en a déclaré une.
- **Clé Google Maps** : elle était écrite en dur dans trois gabarits et une
  vue du portail. Elle passe par `config('services.google_maps.key')` et
  `GOOGLE_MAPS_KEY` dans `.env`. **La clé du legacy est à révoquer** : elle
  a circulé en clair dans le dépôt de 2019.
- Recherche de secrets dans le dépôt (`sk_live`, `sk_test`, clés AWS,
  clés privées, mots de passe en dur) : rien d'autre. `.env` est ignoré.

## 8d — Sauvegardes

- `spatie/laravel-backup` : base de données et code, chaque nuit à 3 h,
  nettoyage à 2 h 30, contrôle de santé à 8 h.
- **Exclus de l'archive** : les visuels des books
  (`storage/app/public/books`, plusieurs dizaines de Go) et le cache des
  déclinaisons, qui se refabrique. Les visuels demandent une synchronisation
  de fichiers à part, vers un stockage distant — à mettre en place au
  déploiement.
