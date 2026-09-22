{{-- Visible en local et en developpement uniquement (voir le composant). --}}
@if ($url)
    <div class="item dev_only dev_switch_marque cursor_effect">
        <a href="{{ $url }}" title="Basculer vers {{ $cible->nom }} (developpement)">
            <strong>{{ strtoupper($courante->code) }}</strong> / {{ strtoupper($cible->code) }}
        </a>
    </div>
@endif
