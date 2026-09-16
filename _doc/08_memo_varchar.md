# Memo — les varchar d'Ultra-book

Etat des lieux des 157 colonnes `varchar` / `char` de `ub2020`, decisions prises pour `2026_ubdf`, et regles a appliquer pour la suite du developpement.

---

## 1. Ce que contient le legacy

157 colonnes `varchar`, dont la longueur suit trois valeurs par habitude plus que par besoin :

| Longueur | Colonnes | Commentaire |
|---|---|---|
| 250 | 47 | valeur par defaut de fait |
| 200 | 38 | idem |
| 255 | 13 | la seule justifiee techniquement |
| 400 / 355 / 350 | 5 | limites arbitraires sur des contenus libres |
| 100 et moins | 54 | dont plusieurs trop courtes (voir §3) |

S'y ajoutent **22 colonnes `enum('true','false')`** : des booleens stockes en chaine. Toutes converties en `boolean` dans le nouveau schema.

---

## 2. Les varchar utilises comme cle etrangere

Le probleme le plus couteux de la reprise.

| Colonne | Type | Contenu reel |
|---|---|---|
| `ub2_contact_form.cf_us_id` | `varchar(255)` | un **login** dans 6 740 cas sur 6 743, un `us_id` numerique dans 3 |
| `ub2_intermediate_form.mf_us_id` | `varchar(255)` | un login, **ou** un `us_id` numerique, **ou** une chaine vide (22 lignes) |
| `inc_auto_selection_user.us_login` | `varchar(60)` | login |
| `ub2_parrainage.par_us_login`, `ub2_codepromo.pro_us_login` | `varchar` | login, **en doublon** d'une colonne `*_us_id` numerique |
| `inc_user_token.us_dir` | `varchar(355)` | login (la colonne `us_dir` vaut toujours le login) |

Aucune contrainte d'integrite n'est possible sur ce modele, et rien n'empeche une reference de pointer dans le vide : l'audit trouve **498 references orphelines**, vers des comptes qui n'existent nulle part.

**Traite par** `App\Services\Legacy\LegacyUserResolver`, qui essaie le login puis l'id numerique, et distingue les references *hors echantillon* des references reellement *orphelines*.

**Dans `2026_ubdf`** : toute reference utilisateur est un `foreignId` contraint. Le login reste unique et sert de cle de route (`User::getRouteKeyName()`), mais jamais de cle etrangere.

---

## 3. Les colonnes saturees

Une valeur atteignant exactement la longueur maximale signale une troncature. Mesure sur les 6 tables principales :

### Troncatures reelles — donnees perdues dans le legacy

| Colonne | Type | Valeurs saturees | Consequence |
|---|---|---|---|
| `inc_user_pref.us_pf_css` | `varchar(400)` | **506** | feuilles de style de book coupees en plein milieu |
| `inc_user.us_nav` | `varchar(150)` | 2 709 | user-agents tronques (colonne non reprise) |
| `inc_user.us_referer` | `varchar(120)` | 1 249 | URL d'inscription tronquees |
| `inc_user_pref.us_pf_descp_mobile` | `varchar(200)` | 116 | descriptions mobiles coupees |
| `ub2_gal_img.img_titre_alt` | `varchar(250)` | 70 | textes alternatifs coupes (SEO et accessibilite) |
| `inc_user.us_cp` | `varchar(10)` | 60 | codes postaux etrangers coupes |
| `ub2_gal_img.img_titre` | `varchar(250)` | 21 | titres de visuels coupes |
| `ub2_gal_rub.rub_nom` | `varchar(250)` | 13 | noms de rubriques coupes |
| `inc_user.us_login` | `varchar(50)` | 7 | logins coupes — donc **sous-domaines coupes** |

Ces donnees sont perdues a la source : la reprise ne peut pas les restaurer. Elle peut seulement cesser d'aggraver.

### Faux positifs — longueur fixe par nature

`us_key` (32, hash), `us_view` (2, `ub`/`df`), `rub_coul` (6, hexadecimal), `us_lat` / `us_lng` (17, precision fixe), `us_pf_fdcoul` (10). Saturation normale, aucune perte.

> **Effet de bord de la troncature.** Les 2 seules valeurs non UTF-8 valides du corpus (2 031 104 verifiees) sont des `varchar(250)` coupes **au milieu d'un caractere accentue**, laissant un octet orphelin. C'est le seul defaut d'encodage reel de la base, et `App\Support\LegacyText` le traite. Voir `_doc/06_charset.md`.

---

## 4. Corrections apportees a `2026_ubdf`

Migration `2026_01_01_000500_adjust_varchar_lengths` — trois colonnes cibles reconduisaient une limite du legacy que l'audit invalide :

| Colonne | Avant | Apres | Motif |
|---|---|---|---|
| `book_settings.custom_css` | `varchar(400)` | `text` | 506 books deja tronques |
| `users.signup_referer` | `varchar(255)` | `text` | une URL n'a pas de longueur bornee |
| `users.zipcode` | `varchar(10)` | `varchar(20)` | codes postaux hors France |

Autre ecart assume : `us_lat` / `us_lng`, stockes en texte sur 17 caracteres (jusqu'a 12 decimales), deviennent des `decimal(10,7)`. Sept decimales valent environ un centimetre au sol : la precision perdue n'a aucun sens geographique.

---

## 5. Regles pour la suite

**Choisir le type d'apres le contenu, pas par habitude.**

| Contenu | Type |
|---|---|
| Texte libre sans borne metier (CSS, description, URL de provenance) | `text` |
| Identifiant technique de longueur connue (hash, token, code couleur) | `char` ou `varchar` a la longueur exacte |
| Enumeration | `varchar` court + valeurs en constantes PHP, ou `boolean` |
| Reference a une autre table | `foreignId` contraint, **jamais** un login ou un libelle |

**Ne pas depasser 255 sans raison.** Au-dela, c'est du `text`. Entre les deux, il n'y a pas de gain.

**Une colonne indexee reste sous 191 caracteres quand c'est possible.** En utf8mb4, un `varchar(255)` indexe pese 1 020 octets. Les index actuels ne passent que grace a `innodb_large_prefix=1` + `ROW_FORMAT Dynamic`. Sur un serveur MySQL 5.7 sans cette configuration, les migrations echouent en **erreur 1071**. A verifier avant tout deploiement.

**Valider la longueur cote applicatif.** Une contrainte de base qui tronque en silence est un bug qui ne remonte jamais. Les Form Requests doivent porter un `max:` coherent avec le schema.

**Le login merite une attention particuliere** : il determine le sous-domaine du book. Un label DNS est limite a 63 caracteres, la colonne a 50 — et 7 comptes du legacy y sont deja a l'etroit. Toute evolution de cette colonne se repercute sur des URL publiques indexees.
