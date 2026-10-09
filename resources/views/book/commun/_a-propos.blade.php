{{--
    Bloc discret en bas du book : qui est le createur, et ses specialites
    (mots-cles IA) en liens vers les pages /images/{mot} du portail. Du texte
    lisible par les moteurs et les IA sur des books souvent faits d'images
    seules. Styles en ligne : neutre dans tous les gabarits (couleur heritee).
--}}
@unless ($vue->edition())
    @php
        $motsCles = $vue->motsCles();
        // Lien vers la page metier du portail : sans lui, les books ne
        // renvoyaient aucun lien vers le portail. Adresse canonique, pas
        // l'hote du book.
        $slugMetier = $vue->b->book->category?->slug;
        $urlMetier = $slugMetier && $slugMetier !== 'autre'
            ? rtrim($vue->b->marque->canonique, '/').($vue->b->marque->multilingue() ? '/'.app()->getLocale() : '').'/'.\App\Support\Metier::slugUrl($slugMetier)
            : null;
    @endphp
    <section aria-label="{{ __('À propos') }}" style="max-width:640px;margin:0 auto;padding:16px 20px 24px;font-size:12px;line-height:1.6;text-align:center;opacity:.6">
        <p style="margin:0">{{ $vue->phraseCreateur() }}</p>
        @if ($motsCles->isNotEmpty())
            <p style="margin:4px 0 0">
                {{ __('Spécialités :') }}
                @foreach ($motsCles as $tag)
                    <a href="{{ $tag->url() }}" style="color:inherit">{{ $tag->label }}</a>@unless ($loop->last), @endunless
                @endforeach
            </p>
        @endif
        @if ($urlMetier)
            <p style="margin:4px 0 0">
                <a href="{{ $urlMetier }}" style="color:inherit">{{ __(':metiers freelance', ['metiers' => ucfirst(\App\Support\Metier::pluriel($slugMetier))]) }}</a>
                {{ __('sur :marque', ['marque' => $vue->b->marque->nom]) }}
            </p>
        @endif
    </section>
@endunless
