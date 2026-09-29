{{-- llms.txt (App\Http\Controllers\Front\LlmsController) : Markdown brut,
     sans echappement HTML ({!! !!}) — ce n'est pas une page web. --}}
# {!! $marque->nom !!}

> {!! $marque->description() !!}

@if ($marque->estDefaut())
Ultra-book est une plateforme de portfolios en ligne pour les créatifs freelances, créée en 2007. Chaque créatif y publie son book (images, légendes, liens, textes de présentation) sur une adresse personnelle, et le personnalise. Les books sont classés par métier ; une sélection est faite tous les trois mois par des professionnels. Les entreprises, agences et éditeurs y cherchent et contactent directement des illustrateurs, graphistes, photographes, directeurs artistiques et autres indépendants.
@else
{!! $marque->nom !!} is an online portfolio platform for freelance creatives: each creative publishes a customisable book (images, captions, links, texts), sorted by trade, where clients can find and contact them directly.
@endif

- {!! number_format($total, 0, ',', ' ') !!} {!! $marque->estDefaut() ? 'portfolios diffusés' : 'published portfolios' !!}
- {!! $marque->estDefaut() ? 'Recherche par métier, mots-clés ou nom' : 'Search by trade, keywords or name' !!} : {!! $recherche !!}?q=illustration&type_recherche=mcles
- {!! $marque->estDefaut() ? 'Créer son portfolio' : 'Create a portfolio' !!} : {!! $inscription !!}

## {!! $marque->estDefaut() ? 'Portfolios par métier' : 'Portfolios by trade' !!}

@foreach ($metiers as $metier)
- [{!! $metier['titre'] !!}]({!! $metier['url'] !!}): {!! $metier['description'] !!} ({!! number_format($metier['total'], 0, ',', ' ') !!})
@endforeach

@if ($marque->estDefaut())
## Questions fréquentes

@foreach ($faq as $entree)
### {!! $entree['question'] !!}

{!! $entree['reponse'] !!}

@endforeach
## Pages thématiques

@foreach ($thematiques as $page)
- [{!! $page['titre'] !!}]({!! $page['url'] !!}): {!! $page['description'] !!}
@endforeach

## Pages utiles

- [Formules et tarifs]({!! $racine !!}/doc/les-formules-ultra-book)
- [Questions fréquentes]({!! $racine !!}/doc/questions-frequentes-2)
- [Documentation]({!! $racine !!}/doc/doc)
- [Mentions légales]({!! $racine !!}/doc/mentions-legales)
- [Illustrateurs disponibles](https://www.les-illustrateurs.com): les illustrateurs et illustratrices disponibles aujourd'hui (UB-diffusion)

@endif
## Optional

- [Sitemap]({!! $racine !!}/sitemap.xml): toutes les pages et tous les books diffusés
- Contact : {!! $marque->email !!}
