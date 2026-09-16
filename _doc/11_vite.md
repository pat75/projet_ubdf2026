# Vite — configuration et rechargement automatique

## Portee

`@vite('resources/css/ubdf.css')` est declare dans `layouts/portail.blade.php`, donc actif sur **toutes** les pages du portail : accueil, categories, annuaire, fiche portfolio. Verifie page par page.

```bash
npm run dev     # serveur de developpement
npm run build   # build de production
```

## Trois obstacles resolus

Le rechargement ne se declenchait pas dans le navigateur. Trois causes cumulees, toutes liees au fait que le site est servi par Valet en HTTPS sur un nom de domaine.

### 1. Le port 5173 est occupe par Valet

Le pont de Valet ecoute deja sur 5173. Vite basculait donc silencieusement sur 5174, et `public/hot` pouvait pointer vers une instance arretee.

→ Port fixe **5273** avec `strictPort: true` : le demarrage echoue franchement au lieu de deriver.

### 2. Le certificat ne correspondait pas a l'adresse

Vite servait `https://127.0.0.1:5173` avec le certificat emis par Valet pour `ubdf2026.ultra-book.name`. Un navigateur qui recoit un certificat pour un autre nom rejette la connexion : ni la feuille de style, ni le websocket de rechargement n'arrivaient.

→ `server.origin` annonce les ressources sous `https://ubdf2026.ultra-book.name:5273`, nom pour lequel le certificat est valide.

### 3. Vite n'ecoutait que sur une seule pile IP

Se lier au domaine (`host: domaine`) ne reservait que l'adresse IPv6 (`::1`), la premiere vers laquelle il resout. Un navigateur tentant `127.0.0.1` n'obtenait rien. `host: '0.0.0.0'` produisait le defaut inverse.

→ `host: '::'` ecoute en **double pile**. Verifie : IPv4 et IPv6 repondent toutes deux 200.

## Perimetre du rechargement

`refresh: true` ne surveille pas tout ce qui produit du HTML ici. La liste est explicite :

```
resources/views/**      resources/css/**      resources/js/**
routes/**               config/**             public/js/**
app/View/Components/**  app/Http/Controllers/**
```

`app/View/Components` et `config` comptent particulierement : les libelles des metiers viennent de `config/categories.php`, et les cartes de books sont rendues par un composant.

## Verification

```bash
# Le serveur repond sur les deux piles
curl -sk -4 https://ubdf2026.ultra-book.name:5273/resources/css/ubdf.css -o /dev/null -w '%{http_code}\n'
curl -sk -6 https://ubdf2026.ultra-book.name:5273/resources/css/ubdf.css -o /dev/null -w '%{http_code}\n'

# Le certificat porte bien le nom du site
echo | openssl s_client -connect ubdf2026.ultra-book.name:5273 \
  -servername ubdf2026.ultra-book.name 2>/dev/null | openssl x509 -noout -subject
```

Le websocket a ete teste de bout en bout : la modification d'une vue Blade produit bien `connected` puis `full-reload`.

## Tailwind

`@tailwindcss/vite` est installe mais **aucune feuille ne l'importe**. Son preflight reinitialiserait Semantic UI, dont depend tout le rendu du portail. `resources/css/app.css` et `app.js` restent en place pour la refonte de la phase 9 ; seul `ubdf.css` est charge aujourd'hui.
