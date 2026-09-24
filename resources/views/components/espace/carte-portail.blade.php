@props(['creatif', 'visites' => 0])

{{--
 | La carte du createur, exactement celle que la page d'accueil affiche
 | de lui aux visiteurs (x-book-card, .ui.card du portail Semantic UI).
 |
 | Reproduite en Tailwind plutot que reutilisee telle quelle : l'espace
 | n'a pas Semantic UI, seulement sa feuille de police d'icones. Les
 | mesures viennent du rendu reel du portail (206 px de large — la
 | grille « five doubling cards » du portail, capee a 1151 px de
 | conteneur, rend toujours cette largeur quel que soit l'ecran —,
 | rayon 4 px, ombre 0 0 10px rgba(100,100,100,.1), vignette 60 px
 | chevauchant de 45 px, en-tete a 20,5 px/600).
--}}
@php
    $couverture = $creatif->media()->published()->whereNot('filename', '')->orderBy('position')->first();
@endphp

<a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"
   {{ $attributes->merge(['class' => 'block w-[206px] shrink-0 overflow-hidden rounded-[4px] bg-white shadow-[0_0_10px_rgba(100,100,100,.1)] transition hover:shadow-[0_0_10px_rgba(0,0,0,.4)]']) }}>

    <div class="h-27.5 bg-black/20">
        @if ($couverture)
            <img src="{{ $couverture->url('front_desk') }}" alt="{{ $couverture->title }} - {{ $creatif->fullName() }}" class="h-full w-full object-cover">
        @endif
    </div>

    <div class="flex flex-col items-center px-4 pb-4 pt-0 text-center">
        @if ($creatif->bookSetting?->thumbnail)
            <img src="{{ $creatif->thumbnailUrl() }}" alt="{{ $creatif->fullName() }}"
                 class="-mt-[45px] mb-5 h-15 w-15 rounded-full object-cover shadow-[1px_1px_6px_#aaa]">
        @endif

        <div class="text-[20.5px] font-semibold leading-[1.28] text-black/85">{{ $creatif->fullName() }}</div>
        <div class="mt-1 text-[12px] uppercase leading-[28px] tracking-wide text-black/40">{{ $creatif->category?->name }}</div>
    </div>

    <div class="flex items-center justify-between border-t border-black/5 px-4 py-3 text-[14px] text-black/40">
        <span class="inline-flex items-center gap-1.5">
            <span class="fonticon-eye3" aria-hidden="true"></span>{{ number_format($visites, 0, ',', ' ') }}
        </span>
        <span class="inline-flex items-center gap-1.5">
            <span class="fonticon-heart2" aria-hidden="true"></span>{{ $creatif->memoCount ?? 0 }}
        </span>
    </div>
</a>
