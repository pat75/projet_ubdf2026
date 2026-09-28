{{--
    Page contact Zoom 2016 (ex-ultrabook_contact) : carte du createur dans
    le tiers gauche (reglage « ptf_activer_gmap »), texte libre et
    formulaire (reglage « ptf_activer_contact ») dans les deux tiers.
--}}
@extends('book.zoom2016.layout')

@section('contenu')
    @php $carte = $vue->carte(); @endphp

    <div @class(['md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] md:gap-12' => $carte, 'mx-auto max-w-2xl' => ! $carte])>
        @if ($carte)
            <aside class="mb-10 motion-safe:animate-apparition md:mb-0">
                <iframe src="https://maps.google.com/maps?q={{ urlencode($carte) }}&z=12&output=embed"
                        title="{{ __('Localisation') }}{{ $b->us_ville ? ' : '.$b->us_ville : '' }}"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        class="aspect-square w-full border-0 grayscale-[.3]"></iframe>
            </aside>
        @endif

        <article class="text-left motion-safe:animate-apparition motion-safe:[animation-delay:120ms]">
            @if ($vue->texteContact() !== '')
                <div class="texte-libre mb-10 text-[16px] leading-relaxed text-book-texte2">{!! $vue->texteContact() !!}</div>
            @endif

            @if ($vue->actif('ptf_activer_contact'))
                @include('book.commun._formulaire-contact')
            @endif
        </article>
    </div>
@endsection
