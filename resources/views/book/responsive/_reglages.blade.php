{{--
    Reglages Responsive 2014 du panneau d'edition (book/commun/_edition),
    dans l'ordre d'Ultra-frais : fond, diaporama, menu, reseaux,
    interrupteurs, textes libres de la colonne, pied de page, CSS expert.
    Enregistres par EditionBookController (ReglageBookRequest, liste Responsive).
--}}
@php
    $accueil = $vue->intitule('ub_menu_titre_accueil', '[accueil-noir]');
    $maison = in_array($accueil, ['[accueil-noir]', '[accueil-blanc]', ''], true) ? $accueil : 'texte';
@endphp

<section x-data="blocReglage('fond')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Fond') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <div class="grid grid-cols-3 gap-2">
            @foreach ([
            'theme_white' => __('Blanc'),
            'theme_gris' => __('Gris'),
            'theme_black' => __('Noir'),
            ] as $valeur => $nom)
                <button type="button" @click="regler('theme', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, (string) $vue->fond()) }}">{{ $nom }}</button>
            @endforeach
        </div>
        @unless ($vue->fond())
            <p class="mt-2 text-gray-500">{{ __('Votre book garde pour l’instant sa couleur d’origine.') }}</p>
        @endunless
    </div>
</section>

<section x-data="blocReglage('diaporama')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Diaporama du portfolio') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <p class="mb-1.5 text-gray-600">{{ __('Présentation') }}</p>
        <div class="grid grid-cols-3 gap-2">
            @foreach ([
            'slide' => __('Diaporama'),
            'full' => __('Grand'),
            'image' => __('À la suite'),
            ] as $valeur => $nom)
                <button type="button" @click="regler('ptf_type_presentation', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->presentation()) }}">{{ $nom }}</button>
            @endforeach
        </div>
        <p class="mb-1.5 mt-4 text-gray-600">{{ __('Navigation') }}</p>
        <div class="grid grid-cols-3 gap-2">
            @foreach ([
            'thumbs' => __('Vignettes'),
            'dots' => __('Points'),
            'none' => __('Aucune'),
            ] as $valeur => $nom)
                <button type="button" @click="regler('ptf_type_vign', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->navigation()) }}">{{ $nom }}</button>
            @endforeach
        </div>
        <p class="mb-1.5 mt-4 text-gray-600">{{ __('Position de la navigation') }}</p>
        <div class="grid grid-cols-2 gap-2">
            @foreach ([
            'top' => __('En haut'),
            'bottom' => __('En bas'),
            ] as $valeur => $nom)
                <button type="button" @click="regler('ptf_position_vign', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->navigationEnHaut() ? 'top' : 'bottom') }}">{{ $nom }}</button>
            @endforeach
        </div>
    </div>
</section>

<section x-data="blocReglage('menu')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Accueil dans le menu') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <p class="mb-1.5 text-gray-600">{{ __('Accueil') }}</p>
        <div class="grid grid-cols-4 gap-2">
            @foreach (['[accueil-noir]' => __('Maison'), '[accueil-blanc]' => __('Maison blanche'), 'texte' => __('Texte'), '' => __('Masqué')] as $valeur => $nom)
                <button type="button" @click="regler('ub_menu_titre_accueil', @js($valeur === 'texte' ? __('Accueil') : $valeur))"
                        class="border px-1 py-2 text-[12px] {{ $choix($valeur, $maison) }}">{{ $nom }}</button>
            @endforeach
        </div>
        @if ($maison === 'texte')
            <input type="text" value="{{ $accueil }}" maxlength="40" aria-label="{{ __('Accueil') }}"
                   @change="regler('ub_menu_titre_accueil', $el.value)" class="{{ $champ }} mt-2">
        @endif
        <p class="mt-2 text-gray-500">{{ __('Portfolio et Bio se modifient directement sur la page, au crayon.') }}</p>
    </div>
</section>

<section x-data="blocReglage('reseaux')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Réseaux sociaux') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <div class="flex flex-col gap-2">
            @foreach (\App\Services\Book\VueUltra2020::RESEAUX as $reseau)
                <label class="flex items-center gap-2">
                    <span class="w-20 shrink-0 capitalize text-gray-600">{{ $reseau }}</span>
                    <input type="text" value="{{ $pref->social_link->{'link_'.$reseau} ?? '' }}" placeholder="https://…"
                           @change="regler('social_link.link_{{ $reseau }}', $el.value)" class="{{ $champ }}">
                </label>
            @endforeach
        </div>
        <p class="mt-2 text-gray-500">{{ __('Liens vers vos profils, affichés en bas de page.') }}</p>
    </div>
</section>

@php
    $classique ??= false;
    $interrupteurs = ['ptf_titre_aff' => [__('Légendes des visuels'), __('Titre et description sous chaque image du diaporama.'), false]];
    if (! $classique) {
        $interrupteurs['ptf_vignette_aff'] = [__('Vignettes sur l’accueil'), __('Une tuile par rubrique du portfolio.'), true];
    }
    $textes = $classique
        ? ['cont_acceuil_bas' => __('Texte sous le visuel d’accueil'), 'cont_menu_gauche' => __('Texte en haut de la colonne'), 'cont_menu_gauche2' => __('Texte sous le menu')]
        : ['cont_menu_gauche' => __('Texte sous le visuel'), 'cont_menu_gauche2' => __('Texte sous le menu')];
@endphp
@foreach ($interrupteurs as $cle => [$nom, $aide, $defaut])
    @php $actif = $vue->actif($cle, $defaut); @endphp
    <section>
        <div class="flex items-center justify-between gap-4">
            <h3 class="font-semibold text-gray-900">{{ $nom }}</h3>
            <button type="button" role="switch" aria-checked="{{ $actif ? 'true' : 'false' }}" aria-label="{{ $nom }}"
                    @click="regler(@js($cle), @js($actif ? 'false' : 'true'))"
                    class="flex h-6 w-11 shrink-0 rounded-full p-0.5 transition-colors {{ $actif ? 'justify-end bg-black' : 'justify-start bg-gray-300' }}">
                <span class="size-5 rounded-full bg-white shadow"></span>
            </button>
        </div>
        <p class="mt-1 text-gray-500">{{ $aide }}</p>
    </section>
@endforeach

@foreach ($textes as $cle => $nom)
<section x-data="blocReglage('{{ $cle }}')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ $nom }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <textarea rows="4" @change="regler(@js('texte.'.$cle), $el.value)" class="{{ $champ }}">{{ $vue->texteLibre($cle) }}</textarea>
        <p class="mt-1 text-gray-500">{{ __('Mise en forme HTML simple acceptée (gras, liens, retours à la ligne).') }}</p>
    </div>
</section>
@endforeach

<section x-data="blocReglage('pied')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Pied de page') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <textarea rows="2" @change="regler('footer', $el.value)" class="{{ $champ }}">{{ $pref->footer ?? '' }}</textarea>
    </div>
</section>

<section x-data="blocReglage('css')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('CSS expert') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <textarea rows="5" @change="regler('expert_css', $el.value)" spellcheck="false" class="{{ $champ }} font-mono text-[12px]">{{ $pref->expert_css ?? '' }}</textarea>
    </div>
</section>

<p class="text-gray-500">{{ __('Les titres « Portfolio » et « Bio » de la colonne se modifient aussi directement sur la page : cliquez dessus.') }} {{ $classique ? __('Les visuels du bandeau et de l’accueil se changent depuis l’habillage de votre espace.') : __('Le visuel se change depuis l’habillage de votre espace.') }}</p>
