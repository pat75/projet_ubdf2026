{{-- Selecteur de langue du menu du haut : Dustfolio seulement (marque
     multilingue). Ultra-book, monolingue, n'en affiche aucun.

     Chaque langue mene a la meme page dans cette langue, via
     `langue.choisir` qui retient le choix (cookie ub_lang). Une page sans
     version dans une langue renvoie a l'accueil de cette langue. --}}
@if ($marque->multilingue())
    @php($chemins = App\Support\VersionsLangue::chemins($marque))
    <div {{ $attributes->merge(['class' => 'item selecteur_langue']) }} role="navigation" aria-label="{{ __('Langue') }}">
        @foreach ($marque->langues as $code)
            @if (! $loop->first)<span class="selecteur_langue_sep" aria-hidden="true">|</span>@endif
            @if ($code === app()->getLocale())
                <span class="selecteur_langue_actif" aria-current="true" lang="{{ $code }}" title="{{ App\Support\Langue::nom($code) }}">{{ strtoupper($code) }}</span>
            @else
                <a href="{{ route('langue.choisir', ['langue' => $code, 'retour' => $chemins[$code] ?? '/'.$code]) }}"
                   hreflang="{{ $code }}" lang="{{ $code }}" title="{{ App\Support\Langue::nom($code) }}">{{ strtoupper($code) }}</a>
            @endif
        @endforeach
    </div>
@endif
