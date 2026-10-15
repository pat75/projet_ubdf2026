# Ultra-book 2026 — instructions projet

Refonte de la plateforme Ultra-book en Laravel 12 / PHP 8.3.
Documentation : `_doc/01_etat_des_lieux.md`, `_doc/02_plan_de_codage.md`, `_doc/03_valet_sous_domaines.md`.

## Regles absolues

- Tout le developpement se fait dans `/Users/pat/Sites_2026/projet_ubdf2026`. Rien ailleurs.
- `/Users/pat/Sites_2019/_projet_ubdf_2020` et la base `ub2020` sont des **sources en lecture seule**. Aucune modification, jamais.
- `/Users/pat/Sites_2026/projet_tesli2026/tesli_www` sert de reference d'architecture. Ne jamais le modifier.
- Base de travail : `2026_ubdf` uniquement.

## Piege : binaire PHP

Le `php` du PATH est **MAMP 8.1.13**, pas 8.3. Toujours utiliser :

```bash
/usr/local/opt/php@8.3/bin/php artisan ...
/usr/local/opt/php@8.3/bin/php /usr/local/bin/composer.phar ...
```

## Piege : Filament nomme les arguments de closure

Filament passe ses valeurs aux closures **par nom de parametre**
(`$query`, `$data`, `$state`, `$record`, `$livewire`, `$table`). Un nom
different ne recoit rien : le conteneur fabrique un objet vide a la place.

```php
// FAUX : $q ne recoit rien, Filament construit un Builder sans modele.
->modifyQueryUsing(fn (Builder $q) => $q->addSelect([...]))

// JUSTE
->modifyQueryUsing(fn (Builder $query) => $query->addSelect([...]))
```

Une seule exception, et c'est elle qui trompe : **l'enregistrement se
resout aussi par son type** (`Column::resolveDefaultClosureDependency
ForEvaluationByType`). `fn (User $u) => ...` marche donc dans les
colonnes et les actions, ce qui laisse croire que le type suffit
toujours. Il ne suffit que pour le modele.

L'echec est parfois bruyant (`Call to a member function
newQueryWithoutRelationships() on null`), parfois **silencieux** : un
filtre dont la closure prend `$requete` au lieu de `$query` ne filtre
rien et rend toute la table, sans erreur. Apres avoir ecrit un filtre,
verifier que le nombre de lignes bouge vraiment.

## Environnement

| | |
|---|---|
| Portail | https://ubdf2026.ultra-book.name |
| Book creatif | https://\<login\>.ubdf2026.ultra-book.name |
| Serveur | Valet (nginx, tld `.name`), server block patche — voir `_doc/03_valet_sous_domaines.md` |
| MySQL | MAMP, **socket uniquement** (`/Applications/MAMP/tmp/mysql/mysql.sock`), pas de TCP sur 8889 |
| Connexion `legacy` | base `ub2020`, protegee en ecriture par `App\Providers\LegacyDatabaseServiceProvider` |

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- Ne PAS lancer `graphify update .` a la main apres chaque modification : un
  passage complet prend ~3 minutes, meme a cache chaud. Le hook git
  `post-commit` (installe par `graphify hook install`) rebatit le graphe en
  tache de fond, de facon incrementale, apres chaque commit. Le graphe est
  donc a jour du dernier commit, pas du dernier fichier edite.
- `graphify-out/` n'est pas versionne : c'est un artefact derive (5 Mo). Sur
  un depot fraichement clone, lancer une fois `graphify update .`.

## Charte graphique

Toute modification graphique d'une page de l'espace suit la charte :

@Claude_design.md

## Traduction Dustfolio

Toute traduction d'une version Dustfolio (Ultra-book reste en français) suit :

@Claude-Dustfolio-traduction.md

## Fenetre de paiement Payplug (ne pas modifier sans demande explicite)

Validee par Pat le 2026-10-14. Code : fin de `resources/views/espace/formule.blade.php`
(bloc `<style>` + script). Sert aux formules **et** au nom de domaine.

- **Ouverture** : script officiel `https://api.payplug.com/js/1/form.latest.js`,
  `Payplug.showPayment(url)`. Formules : formulaire `form[data-payplug]` poste en
  JSON (`PaiementController::payer` renvoie `{url}` si `expectsJson`). Domaine :
  `NomDeDomaine::payer` emet l'evenement Livewire `payplug-ouvrir`. Sans le script
  ou en cas d'echec : redirection classique vers la page Payplug.
- **Piege** : le script etend son iframe `#iframe-payplug` a **toute la page**,
  transparente. Ne jamais lui donner un fond blanc sans la reduire : la page
  entiere devient blanche.
- **Taille** : iframe reduite a la carte Payplug, centree (`position: fixed`,
  `translate(-50%, -50%)`), 400px de large, hauteur `--pp-h` =
  `min(634px, 100vh - 162px)` (480px portes a 576px puis 634px, le 2026-10-15) (variable sur `:root`).
- **Cadre** : bordure blanche de 30px, 100px en bas (place de la mention),
  arrondi `1.5rem` (celui des formules), styles en `!important` (Payplug les pose
  en ligne).
- **Marge haute Payplug** : la page dans l'iframe (domaine Payplug, non stylable)
  commence par `padding-top:100px`. On en rogne 90px par le haut
  (`clip-path: inset(var(--pp-rogne) …)`, `--pp-rogne: 90px`) : il reste 10px.
  La fenetre, la croix et la mention remontent de `--pp-rogne / 2` pour rester
  centrees. Validee par Pat le 2026-10-15.
- **Croix** `#wrapper-close-3ds` : dans le coin haut droit, a l'interieur du cadre.
- **Mention legale** `#mention-payplug`, ajoutee par le script dans
  `#wrapper-payplug-iframe` (disparait avec la fenetre), dans la bande blanche
  du bas : « SAS POLYGUN — paiements sécurisés par PayPlug Enterprise SAS,
  établissement de paiement agréé par l’ACPR (CIB : 16778), 110 avenue de France,
  75013 Paris. » — 12.65px, `#474747`, centree. Texte passe par `Js::from(__())`,
  jamais `@json` (les parentheses du texte cassent la directive Blade).
- **Retour** : la formule est appliquee au retour en relisant le paiement chez
  Payplug (`Souscription::confirmer`, id en session `payplug.formule`), sans
  attendre la notification (qui n'atteint pas un poste local). Domaine :
  `PaiementDomaine::confirmer` au montage de `NomDeDomaine` (`?domaine=retour`).
- **Remerciement** (layout `layouts/espace.blade.php`, flash `paiement`,
  `paiement_ok`, `paiement_detail`, `paiement_lien`) : visuel
  `img_front/thank-you.png` 200px centre, message vert 22px semi-gras avec 30px
  au-dessus et en dessous, feu d'artifice de pixels couleur `--color-ub-accent`
  parti du visuel (~3 s, coupe si `prefers-reduced-motion`). Domaine : bouton
  « Gérer mon nom de domaine » vers `/espace/domaine`.
- Les identifiants `#iframe-payplug`, `#wrapper-payplug-iframe`,
  `#wrapper-close-3ds` viennent du script Payplug : a reverifier si Payplug change
  de version.
