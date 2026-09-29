{{-- Actualites du tableau de bord : une carte par actualite, visuel a
     droite, texte replie tant qu'on n'a pas ouvert « Lire la suite ». --}}
@foreach ($actualites as $actualite)
    @php($visuel = $actualite->visuel())

    <section class="carte-espace mb-6 overflow-hidden" x-data="{ deplie: false }">
        <div class="flex flex-col md:flex-row">
            <div class="flex-1 p-7 {{ $visuel ? 'md:w-2/3' : '' }}">
                <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Actualité') }}</div>
                <h2 class="mt-1.5 font-titre text-[28px] font-light leading-tight tracking-tight text-ub-texte">{{ $actualite->titre }}</h2>

                @if ($actualite->sous_titre)
                    <p class="mt-2 text-[16px] text-ub-texte2">{{ $actualite->sous_titre }}</p>
                @endif

                <div class="mt-4 text-[15px] leading-relaxed text-ub-texte2 transition-all duration-300"
                     :class="deplie ? '' : 'max-h-40 overflow-hidden'">
                    {!! $actualite->contenu !!}
                </div>

                <button type="button" @click="deplie = ! deplie"
                        class="bouton-espace bouton-espace-petit mt-5 px-4">
                    <span x-text="deplie ? @js(__('Voir moins')) : @js(__('Lire la suite'))"></span>
                </button>
            </div>

            @if ($visuel)
                <div class="md:w-1/3 md:self-stretch">
                    <img src="{{ \App\View\Components\Espace\Actualites::urlVisuel($visuel) }}" alt=""
                         class="h-56 w-full object-cover object-center md:h-full">
                </div>
            @endif
        </div>
    </section>
@endforeach
