# Ultra-book 2026 — État des lieux du legacy (ubdf_2020)

Source analysée : `/Users/pat/Sites_2019/_projet_ubdf_2020` (PHP 7.1, sans framework, MAMP `ub2020ssl.localhost:4433`).
Cible : `/Users/pat/Sites_2026/projet_ubdf2026` (Laravel 12 / PHP 8.3, Valet `ubdf2026.ultra-book.name`).

## 1. Architecture actuelle

Trois applications distinctes cohabitent dans un seul document root :

| Bloc | Répertoire | Point d'entrée | Rôle |
|---|---|---|---|
| Front public (portail) | `front/`, `html_pages_v2018/` | `front/action.php` (98 Ko, dispatcher monolithique) | accueil, annuaire, recherche, pages SEO, contact, inscription/login, factures, formules |
| Book créatif (sous-domaine) | `2011_front/action_book.php` + `users_2/<a>/<b>/<login>/index.php` | `index.php` généré par book (contient `$book_id`) | rendu du portfolio public |
| Back-office admin | `admin_/` (~60 scripts), `cms_user/`, `2011_user_admin/` | `admin_/index.php` (33 Ko) | gestion utilisateurs, factures, relances, sélections, dispo, newsletters |

Couche métier : `inc/` — fichiers procéduraux massifs (`inc_user.php` 210 Ko, `inc_user_intermediate.php` 58 Ko, `inc_nl.php` 58 Ko, `inc_user_dispo.php` 47 Ko). Config : `conf/conf_site.php` (55 Ko), `conf_database.php`, `conf_domaine_2018.php`.

Empilement historique visible : `2010_*`, `2011_*`, `2012_*`, `html_pages_v2`, `v2014`, `v2018` coexistent. Seul `html_pages_v2018` est actif côté front.

## 2. Mécanisme des sous-domaines (élément critique)

Référence : `2011_front/conf_apache_book.txt`.

```
ServerAlias          *.ub2016.ddns.net
VirtualDocumentRoot  .../users_2/%1.1/%1.2/%1
```

- `mod_vhost_alias` : `pat10.ultra-book.com` → `users_2/p/a/pat10/`
- Sharding disque par 1re et 2e lettre du login (`users_2/p/a/pat10`).
- Chaque book possède un `index.php` **généré** contenant `$book_id`, `$book_top`, `$book_ver`, puis inclut `2011_front/action_book.php`.
- Rewrites internes au book : `/accueil`, `/contact`, `/portfolio`, `/<slug>-p<id>`, `/<slug>-r<rub>-c<page>`, `/actualites`, `/pdf-version-<n>`, version iPhone `-pi<id>`.

Volumétrie : **6 585 books**, **30 Go** dans `users_2/` (12 variantes d'images par book : `img_adm_small/medium`, `img_ptf_*`, `img_iph_*`, `img_front_desk/mob`, `img_cms`, `cms_html`, `cms_pref`).

## 3. Cartographie des URL du portail (extrait du `.htaccess` racine, ~300 règles)

| Famille | URL | Cible legacy |
|---|---|---|
| Accueil / annuaire | `/portfolios`, `/accueil`, `/recherche`, `/annuaire`, `/annuaire_<alpha>` | `action.php?page=accueil` |
| Pagination AJAX | `/accueil__<n>__(sel\|ult\|lub)__<type>` | `page_type=accueil_ajax` |
| Sélections | `/les-ultra-books`, `/les-ultra-selections` | `type_selection=lub\|ult` |
| Catégories métier | `/illustrateur`, `/photographe`, `/graphiste`, `/design`, `/modele`, `/plasticien`, `/architecte`, `/styliste`, `/digital`, `/scenographe`, `/directeur-artistique`, `/illustrateur-jeunesse`, `/autre` | `page_type=home&cat=` |
| Landings SEO | `/graphistes-freelance`, `/meilleurs-graphistes`, `/illustrateur-freelance`, `/webdesigner-freelance`, … (≈15) | `page_type=home&cat=&seo=` |
| Book SEO | `/portfolio/<login>/<slug>-<metier>` | `page_type=book_single` |
| Mini/micro book | `/minibook_<login>`, `/microbook_<p>_<p>__<login>` | `page_type=minibook\|nanobooki` |
| Contact | `/contact_show__<login>`, `/contact_reponse`, `/contact_reponse_frombook__…`, `/captcha_img` | `ajax_2016_contact*.php` |
| Messagerie intermédiée | `/intermediate_send`, `/intermediate_get`, `/intermediate_msg_/(cust\|user)<6>/<token>/<selector>` | `ajax_2019_intermediate*.php` |
| Compte | `/create`, `/inscription`, `/login`, `/messages`, `/memo`, `/memobook` | `action=user_add\|user_open\|user_pref_message` |
| Facturation | `/formules`, `/facture_n__<id>`, `/invoice_n__<id>`, `/paypal_send_…` | `action=facture\|paypal_send` |
| CMS / actus | `/page__<slug>`, `/doc/<slug>`, `/ultra-book__<slug>`, `/dustfolio__<slug>`, `/<x>__wpactu_<id>` | `page_type=wp` (import WordPress) |
| Divers | `/newsletters`, `/sitemap`, `/robots.txt`, `/en` `/fr` `/ja`, `/dispo` | — |
| Générique | `/ubaction__<action>`, `/ubactiontype__<type>`, `/ubajax__<x>`, `/fm_ajax` | dispatcher |

Multi-marques géré dans le même code : **ultra-book.com**, **dustfolio.com**, extra-book.*, ultra-book.pro/fr. Multi-langue : `fr_FR`, `en_US`, `ja_JP`.

## 4. Base de données `ub2020` (30 tables)

| Table | Lignes | Rôle |
|---|---|---|
| `ub2_gal_img` | 1 078 752 (350 Mo) | images des books |
| `bn_ultrabook_art_portefolio` | 694 058 | contenus CMS book (legacy « bn_ ») |
| `bn_ultranews_art` / `_tab_default` / `bn_ultranews_rub` | 827 k | moteur CMS/news des books |
| `ub2_gal_rub` | 239 251 | rubriques/galeries |
| `inc_user` | 77 859 | comptes créatifs |
| `inc_user_pref` | 75 179 | préférences/design du book |
| `inc_stats` / `ub2_stats_mcles` | 86 k | statistiques de visite |
| `inc_marketing`, `nl_newsletter`, `df2_mail_relance` | 60 k | emailing / relances |
| `ub2_fac`, `df2_fac`, `ub2_codepromo`, `ub2_parrainage` | 8 k | facturation, promo, parrainage |
| `ub2_contact_form`, `ub2_intermediate_form` | 12 k | messagerie |
| `inc_auto_selection*`, `ub2_edit_txt`, `inc_user_token`, `wp_import`, `ub2_dispo`, `inc_comt` | — | sélections éditoriales, textes, tokens, import WP |

Nommage hétérogène (`inc_`, `ub2_`, `df2_`, `bn_ultranews_`), `enum('true','false')` au lieu de booléens, pas de FK, pas de timestamps Laravel. La base cible `2026_ubdf` existe déjà et est **vide**.

## 5. Points durs identifiés

1. **Sous-domaines sous Valet** : Valet (`tld = name`) ne résout pas nativement `*.ubdf2026.ultra-book.name`. À valider/configurer avant tout (driver custom ou `Route::domain('{login}.…')` + entrée dnsmasq).
2. **Le book n'est pas une simple page** : c'est un mini-CMS par utilisateur (rubriques, pages, actus, PDF, version mobile, 12 formats d'images, préférences de design stockées en base + fichiers `cms_pref/`).
3. **30 Go d'images** : impossible à copier intégralement. Il faut un échantillon (~100 users) + un `Storage` disk pointant éventuellement en lecture seule vers le legacy.
4. **Mots de passe** : `us_pass` varchar(255) + `us_pass_old` — format à identifier (MD5 ? bcrypt ?) pour la stratégie de migration Auth.
5. **Design à l'identique** : les templates actuels sont du Handlebars/PHP (`html_pages_v2018/tpl*`) + jQuery. La reprise « pixel identique » impose de porter ces templates en Blade en conservant CSS/JS tels quels.
6. **Paiement** : PayPal + PayPlug (2 SDK vendorisés, versions 2.2.1/2.4). Tesli 2026 utilise Stripe + Stancer — opportunité d'unification.
7. **Secrets en clair** dans `conf/conf_database.php` (identifiants de production versionnés).

## 6. Référence Tesli 2026 (à imiter, jamais à modifier)

`/Users/pat/Sites_2026/projet_tesli2026/tesli_www` — Laravel 12 / PHP 8.3, structure `app/{Actions,Services,Repository,Rules,Policies,Support,Http/Controllers}`, Breeze, Sanctum, Socialite, dompdf, Intervention Image, spatie/backup, Stripe/Stancer. C'est le gabarit d'arborescence retenu.
