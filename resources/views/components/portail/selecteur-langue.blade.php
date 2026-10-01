{{-- Selecteur de langue du menu du haut : Dustfolio seulement (marque
     multilingue). Ultra-book, monolingue, n'en affiche aucun.

     Au repos, seule la langue courante est affichee (« EN ») suivie d'une
     fleche vers le bas ; le survol deplie les autres langues, comme la bulle des
     metiers (x-infobulle.lent : 400 ms de grace pour traverser l'ecart). Le
     clic reste pour le tactile et le clavier. Chacune est un
     vrai lien vers la meme page dans cette langue, via `langue.choisir` qui
     retient le choix (cookie ub_lang). Une page sans version dans une langue
     renvoie a l'accueil de cette langue. --}}
@if ($marque->multilingue())
    @php($chemins = App\Support\VersionsLangue::chemins($marque))
    @php($courante = app()->getLocale())
    <div {{ $attributes->merge(['class' => 'item selecteur_langue']) }} x-data="{ ouvert: false, minuterie: null }"
         @mouseenter="clearTimeout(minuterie); ouvert = true"
         @mouseleave="minuterie = setTimeout(() => ouvert = false, 400)"
         @click.outside="ouvert = false" @keydown.escape.window="ouvert = false">
        <button type="button" class="selecteur_langue_actif" lang="{{ $courante }}"
                aria-haspopup="true" :aria-expanded="ouvert" aria-label="{{ __('Langue') }} : {{ App\Support\Langue::nom($courante) }}"
                @click="ouvert = ! ouvert">
            {{ strtoupper($courante) }}
            <x-espace.picto nom="angle-bas" style="width:11px;height:11px" />
        </button>

        <ul class="selecteur_langue_liste" x-show="ouvert" x-cloak x-transition.opacity.duration.150ms>
            @foreach ($marque->langues as $code)
                @continue($code === $courante)
                <li>
                    <a href="{{ route('langue.choisir', ['langue' => $code, 'retour' => $chemins[$code] ?? '/'.$code]) }}"
                       hreflang="{{ $code }}" lang="{{ $code }}">{{ strtoupper($code) }}<span>{{ App\Support\Langue::nom($code) }}</span></a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
