{{-- Reglages Ultra-frais / Ultra-zen du panneau d'edition (book/commun/_edition). --}}
@php $entete = (int) ($pref->header ?? 0); @endphp
<section x-data="blocReglage('fond')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Fond') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
    <div class="grid grid-cols-3 gap-2">
        @foreach (['theme_white' => __('Blanc'), 'theme_gris' => __('Gris'), 'theme_black' => __('Noir')] as $valeur => $nom)
            <button type="button" @click="regler('theme', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->couleur()) }}">{{ $nom }}</button>
        @endforeach
    </div>
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
    <div class="grid grid-cols-6 gap-1.5">
        <button type="button" @click="regler('header', 0)" title="{{ __('Aucun') }}"
                class="flex aspect-square items-center justify-center border {{ $choix('0', (string) $entete) }}">—</button>
        <button type="button" @click="regler('header', 1)" title="{{ __('Photo') }}"
                class="flex aspect-square items-center justify-center overflow-hidden border p-1 {{ $choix('1', (string) $entete) }}">
            <img src="{{ $vue->photo() }}" alt="" class="size-full rounded-full object-cover">
        </button>
        @foreach (\App\Services\Book\VueUltra2020::ICONES as $i => $trace)
            <button type="button" @click="regler('header', {{ $i + 2 }})"
                    class="flex aspect-square items-center justify-center border p-1 {{ $choix((string) ($i + 2), (string) $entete) }}">
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

<section class="flex items-center justify-between">
    <h3 class="font-semibold text-gray-900">{{ __('Curseur animé') }}</h3>
    <button type="button" role="switch" aria-checked="{{ $vue->curseur() ? 'true' : 'false' }}"
            @click="regler('cursor', @js($vue->curseur() ? 'false' : 'true'))"
            class="flex h-6 w-11 rounded-full p-0.5 transition-colors {{ $vue->curseur() ? 'justify-end bg-black' : 'justify-start bg-gray-300' }}">
        <span class="size-5 rounded-full bg-white shadow"></span>
    </button>
</section>

<section x-data="blocReglage('menu')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Intitulés du menu') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
    <div class="flex flex-col gap-2">
        @foreach (['name_portfolio' => 'Portfolio', 'name_page' => 'Bio', 'name_contact' => 'Contact'] as $cle => $defaut)
            <input type="text" value="{{ $vue->lien($cle, $defaut) }}" maxlength="40"
                   @change="regler('nav_link.{{ $cle }}', $el.value)" class="{{ $champ }}">
        @endforeach
    </div>
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
    </div>
</section>

<section x-data="blocReglage('pied')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Pied de page') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
    <textarea rows="2" @change="regler('footer', $el.value)" class="{{ $champ }} font-mono">{{ $pref->footer ?? '' }}</textarea>
    </div>
</section>

<section x-data="blocReglage('contact')">
    <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
        <span>{{ __('Texte sous le formulaire de contact') }}</span>
        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
    </button>
    <div x-show="ouvert" x-cloak class="mt-2">
    <textarea rows="3" @change="regler('contact_footer', $el.value)" class="{{ $champ }}">{{ $pref->contact_footer ?? '' }}</textarea>
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

<p class="text-gray-500">{{ __('Titre, description, intitulés du menu, « les projets » et titre du contact se modifient directement sur la page : cliquez dessus.') }}</p>
