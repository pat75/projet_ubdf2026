@props(['titre' => null, 'sousTitre' => null, 'action' => null])

{{-- La carte blanche : un en-tete facultatif, puis le contenu. C'est la
     brique de tous les ecrans de l'espace. --}}
<section {{ $attributes->merge(['class' => 'carte-espace']) }}>
    @if ($titre)
        {{-- `action` (slot nomme, facultatif) : un bouton aligne a droite du titre. --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-7 pb-1.5 pt-5.5">
            <div>
                <h2 class="text-[20px] font-semibold">{{ $titre }}</h2>
                @if ($sousTitre)
                    <p class="mt-1 text-[14px] text-ub-texte3">{!! $sousTitre !!}</p>
                @endif
            </div>
            @if ($action)
                {{ $action }}
            @endif
        </div>
    @endif

    <div class="{{ $titre ? 'px-7 pb-5.5 pt-2' : 'p-7' }}">{{ $slot }}</div>
</section>
