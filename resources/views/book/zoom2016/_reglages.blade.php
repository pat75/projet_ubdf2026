{{--
    Reglages Zoom 2016 du panneau d'edition (book/commun/_edition), dans
    l'ordre d'Ultra-frais : fond, marge, visuel, intitules, reseaux,
    interrupteurs, textes libres, pied de page, CSS expert.
    Enregistres par EditionBookController (ReglageBookRequest, liste Zoom).
--}}
@php
    $interrupteurs = [
        'ptf_activer_iso_category' => [__('Portfolio groupé par rubrique'), __('Un intertitre avant chaque rubrique.'), false],
        'ptf_activer_contact' => [__('Formulaire de contact'), null, true],
        'ptf_activer_gmap' => [__('Carte sur la page contact'), __('Position indiquée dans Mon compte.'), false],
        'ptf_activer_sociaux' => [__('Boutons de partage'), null, true],
    ];
@endphp

<section x-data="blocReglage('fond')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Fond') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <div class="grid grid-cols-3 gap-2">
            @foreach (['theme_white' => __('Blanc'), 'theme_gris' => __('Gris'), 'theme_black' => __('Noir')] as $valeur => $nom)
                <button type="button" @click="regler('theme', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, (string) $vue->fond()) }}">{{ $nom }}</button>
            @endforeach
        </div>
        @unless ($vue->fond())
            <p class="mt-2 text-gray-500">{{ __('Votre book garde pour l’instant ses couleurs d’origine.') }}</p>
        @endunless
    </div>
</section>

<section x-data="blocReglage('marge')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Marge autour des visuels') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        <div class="grid grid-cols-3 gap-2">
            @foreach (['small' => __('Aucune'), 'normal' => __('Normale'), 'large' => __('Large')] as $valeur => $nom)
                <button type="button" @click="regler('visuel_size', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->tailleVisuels()) }}">{{ $nom }}</button>
            @endforeach
        </div>
    </div>
</section>

<section x-data="blocReglage('entete')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Visuel de profil') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
        @php $entete = (string) ($pref->header ?? 1); @endphp
        <div class="grid grid-cols-6 gap-1.5">
            <button type="button" @click="regler('header', 0)" title="{{ __('Aucun') }}"
                    class="flex aspect-square items-center justify-center border {{ $choix('0', $entete) }}">—</button>
            <button type="button" @click="regler('header', 1)" title="{{ __('Photo') }}"
                    class="flex aspect-square items-center justify-center overflow-hidden border p-1 {{ $choix('1', $entete) }}">
                <img src="{{ $vue->photo() }}" alt="" class="size-full object-contain">
            </button>
            @foreach (\App\Services\Book\VueUltra2020::ICONES as $i => $trace)
                <button type="button" @click="regler('header', {{ $i + 2 }})"
                        class="flex aspect-square items-center justify-center border p-1 {{ $choix((string) ($i + 2), $entete) }}">
                    <svg viewBox="0 0 24 24" class="size-full fill-current" aria-hidden="true">{!! $trace !!}</svg>
                </button>
            @endforeach
        </div>
        <div class="mt-2 grid grid-cols-3 gap-2">
            @foreach (['S', 'M', 'L'] as $valeur)
                <button type="button" @click="regler('header_size', @js($valeur))" class="border px-2 py-1.5 {{ $choix($valeur, $vue->tailleEntete()) }}">{{ $valeur }}</button>
            @endforeach
        </div>
        <p class="mt-2 text-gray-500">{{ __('La photo se change depuis l’habillage de votre espace.') }}</p>
    </div>
</section>

<section x-data="blocReglage('zoom-menu')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Intitulés du menu') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2 flex flex-col gap-2">
        @foreach (['link_accueil' => __('Portfolio'), 'link_bio' => __('Bio'), 'link_contact' => __('Contact')] as $cle => $defaut)
            <input type="text" value="{{ $vue->lien($cle, $defaut) }}" maxlength="40" aria-label="{{ $defaut }}"
                   @change="regler(@js($cle), $el.value)" class="{{ $champ }}">
        @endforeach
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
        @if ($aide)
            <p class="mt-1 text-gray-500">{{ $aide }}</p>
        @endif
    </section>
@endforeach

@foreach (['cont_menu_gauche' => [__('Présentation sous le visuel'), $vue->presentation()], 'cont_menu_gauche2' => [__('Texte de la page contact'), $vue->texteContact()]] as $cle => [$nom, $valeur])
    <section x-data="blocReglage('zoom-{{ $cle }}')">
        <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
            <span>{{ $nom }}</span>
            <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
            <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
        </button>
        <div x-show="ouvert" x-cloak class="mt-2">
            <textarea rows="4" @change="regler(@js('texte.'.$cle), $el.value)" class="{{ $champ }}">{{ $valeur }}</textarea>
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

<p class="text-gray-500">{{ __('Les intitulés du menu se modifient aussi directement sur la page : cliquez dessus. Les images se changent depuis Mon portfolio.') }}</p>
