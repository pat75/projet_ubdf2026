# Politique d'encodage — chaine complete

Objectif : une seule regle, **UTF-8 normalise NFC de bout en bout**, et un seul endroit ou chaque exception est traitee. Complete `_doc/06_charset.md` (etat des lieux) et `_doc/08_memo_varchar.md` (typage).

---

## La chaine, maillon par maillon

| # | Maillon | Garantie | Ou |
|---|---|---|---|
| 1 | Schema `2026_ubdf` | `utf8mb4` / `utf8mb4_unicode_ci` par defaut | `ALTER DATABASE`, fait |
| 2 | Tables et colonnes | `utf8mb4_unicode_ci`, `ROW_FORMAT Dynamic` | migrations |
| 3 | Connexion applicative | `DB_CHARSET` / `DB_COLLATION` explicites | `.env`, `config/database.php` |
| 4 | Connexion legacy | `latin1` **volontairement** | `config/database.php` |
| 5 | Reprise des donnees | `App\Support\LegacyText` sur chaque chaine | `LegacyMigrator` |
| 6 | Entree utilisateur | `NormalizeUnicodeInput` (UTF-8 + NFC) | middleware global |
| 7 | Sortie HTTP | `charset=utf-8` | Laravel, par defaut |
| 8 | Vues et fichiers source | UTF-8 sans BOM | `.editorconfig` |
| 9 | Emails | UTF-8 | Laravel Mail, par defaut |

Les maillons 1 a 6 sont ceux qui demandaient une intervention. Les trois derniers sont acquis par le framework — ils n'etaient pas acquis dans le site de 2020, qui melangeait `header()` manuels, PHPMailer configure a la main et fichiers en latin1.

---

## Maillon 5 — `App\Support\LegacyText`

Toute chaine venant de `ub2020` passe par lui, sans exception. Il traite :

1. la **troncature** — 2 valeurs du corpus finissent par un octet orphelin, un `varchar(250)` coupe au milieu d'un caractere accentue ;
2. le **double encodage** — filet preventif, inactif sur le corpus actuel (verifie au niveau des octets) ;
3. le **repli Windows-1252** si une valeur restait invalide.

Mesure : 2 031 104 valeurs verifiees, 2 anomalies. Le groupe de tests `slow` rejoue cet audit.

## Maillon 6 — `NormalizeUnicodeInput`

Middleware global. La base et les reponses sont en UTF-8, mais rien n'oblige un client a en envoyer. Deux cas concrets sur une plateforme ouverte a l'international (le legacy sert `fr_FR`, `en_US`, `ja_JP`) :

- **entree non UTF-8** — un vieux client postant en Windows-1252 ferait echouer l'insertion ou corromprait la valeur ;
- **forme decomposee (NFD)** — un nom copie depuis macOS ecrit « é » comme « e » + accent combinant. Visuellement identique, octets differents : la recherche et les comparaisons echouent **en silence**, ce qui est pire qu'une erreur.

Les deux sont ramenes a de l'UTF-8 en forme composee (NFC). Le corpus legacy est deja integralement en NFC (verifie sur 20 000 noms) : c'est une garantie pour l'avenir, pas un rattrapage.

---

## Ce qui reste a surveiller

**Noms de fichiers.** 55 sur 1 078 752 sortent de `[a-zA-Z0-9._-]`, et seulement a cause de parentheses — aucun accent. Le pipeline d'upload de la phase 5 devra normaliser systematiquement (`Str::slug` sur le nom, extension validee separement) : un accent dans un nom de fichier casse selon le systeme de fichiers, le serveur web et le navigateur.

**Sous-domaines.** Le login determine l'URL du book. Il doit rester en ASCII minuscule : un login accentue produirait un sous-domaine invalide ou un IDN, qui ne se comporte pas de la meme facon selon les navigateurs et les certificats. La contrainte du sampler (`^[a-z0-9][a-z0-9-]{1,48}$`) doit devenir une regle de validation a l'inscription.

**Import / export.** CSV, flux RSS et sitemap devront declarer explicitement UTF-8. Le legacy exporte aujourd'hui sans declaration, ce qui produit des fichiers illisibles dans Excel.

---

## Regles

1. **Jamais de conversion d'encodage ailleurs que dans `LegacyText` ou `NormalizeUnicodeInput`.** Un `utf8_decode()` disperse dans un controleur est la facon dont le legacy en est arrive la.
2. **Ne jamais « corriger » la connexion `legacy` en utf8mb4.** Elle doit rester en `latin1` — le commentaire dans `config/database.php` explique pourquoi.
3. **Comparer des chaines normalisees.** Toute comparaison ou recherche sur un texte saisi suppose le passage par le middleware.
4. **Supprimer `LegacyPassword` et `LegacyText` apres la migration de production.** Ce sont des outils de reprise, pas du code applicatif.
