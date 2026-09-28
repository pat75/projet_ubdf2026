{{--
    Reglages Zoom 2016 du panneau d'edition (book/commun/_edition) :
    couleurs, intitules du menu, portfolio, contact, partage, textes libres.
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

<section x-data="blocReglage('zoom-couleurs')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Couleurs') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-3 flex flex-col gap-3">
        @foreach (['couleur_bandeau' => [__('Bandeau'), $vue->couleurBandeau()], 'couleur_fond' => [__('Fond de page'), $vue->couleurFond()]] as $cle => [$nom, $valeur])
            @php $hex = preg_match('/^#[0-9a-f]{6}$/i', $valeur) ? $valeur : '#ffffff'; @endphp
            <label class="flex items-center justify-between gap-3">
                <span class="text-gray-600">{{ $nom }}</span>
                <span class="flex items-center gap-2">
                    <span class="font-mono text-[12px] uppercase text-gray-500">{{ $hex }}</span>
                    <input type="color" value="{{ $hex }}" @change="regler(@js($cle), $el.value)"
                           class="h-8 w-12 cursor-pointer border border-gray-300 bg-white p-0.5">
                </span>
            </label>
        @endforeach
        <p class="text-gray-500">{{ __('La couleur du texte s’adapte d’elle-même au fond choisi.') }}</p>
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

<p class="text-gray-500">{{ __('Le visuel du bandeau se change depuis l’habillage de votre espace ; les images, depuis Mon portfolio.') }}</p>
