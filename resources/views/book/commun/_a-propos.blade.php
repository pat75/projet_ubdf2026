{{--
    Bloc discret en bas du book : qui est le createur, et ses specialites
    (mots-cles IA) en liens vers les pages /images/{mot} du portail. Du texte
    lisible par les moteurs et les IA sur des books souvent faits d'images
    seules. Styles en ligne : neutre dans tous les gabarits (couleur heritee).
--}}
@unless ($vue->edition())
    @php $motsCles = $vue->motsCles(); @endphp
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
    </section>
@endunless
