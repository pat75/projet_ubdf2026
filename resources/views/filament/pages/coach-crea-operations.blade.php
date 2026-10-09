{{--
    Colonne « Opérations » du Coach créatif, en tête de ligne : flèche,
    nombre d'opérations dans un rond noir, bouton de génération. La flèche insère
    sous la ligne une ligne pleine largeur : une opération par ligne,
    date et heure, type en label coloré, objet et champs touchés.
--}}
@php
    $operations = $getRecord()->creatifActivities;
    $couleurs = ['created' => 'success', 'updated' => 'info', 'deleted' => 'danger', 'restored' => 'warning'];
@endphp
<div x-data="{
        ouvert: false,
        basculer() {
            this.ouvert = ! this.ouvert;
            const ligne = this.$el.closest('tr');
            if (this.ouvert) {
                const tr = document.createElement('tr');
                tr.dataset.coachDetail = '';
                tr.innerHTML = '<td colspan=99 class=&quot;ub-coach-detail&quot;>' + this.$refs.detail.innerHTML + '</td>';
                ligne.after(tr);
            } else if (ligne.nextElementSibling?.hasAttribute('data-coach-detail')) {
                ligne.nextElementSibling.remove();
            }
        },
    }" class="ub-coach-tete">
    <button type="button" x-on:click.stop="basculer()" class="ub-coach-deplier"
            :aria-expanded="ouvert" :aria-label="ouvert ? 'Replier les opérations' : 'Déplier les opérations'">
        <x-filament::icon icon="heroicon-s-chevron-right" x-show="! ouvert" class="ub-coach-fleche" />
        <x-filament::icon icon="heroicon-s-chevron-down" x-show="ouvert" x-cloak class="ub-coach-fleche" />
        <span class="ub-coach-nombre" aria-label="{{ trans_choice(':n opération|:n opérations', $operations->count(), ['n' => $operations->count()]) }}">{{ $operations->count() }}</span>
    </button>
    {{-- Loader dans le bouton tant que l'IA rédige. --}}
    <button type="button" class="ub-coach-generer" x-data="{ enCours: false }" :disabled="enCours" :aria-busy="enCours"
            x-on:click.stop="enCours = true; $wire.generer({{ $getRecord()->id }}).finally(() => enCours = false)">
        <x-filament::loading-indicator x-show="enCours" x-cloak class="ub-coach-picto" />
        <x-filament::icon icon="heroicon-o-sparkles" x-show="! enCours" class="ub-coach-picto" />
        <span x-text="enCours ? 'Rédaction…' : 'Générer un message'">Générer un message</span>
    </button>

    <template x-ref="detail">
        <ol class="ub-coach-operations">
            @foreach ($operations as $o)
                <li>
                    <time datetime="{{ $o->created_at->toIso8601String() }}">
                        <span>{{ $o->created_at->format('d/m/Y') }}</span>
                        <span class="ub-coach-heure">{{ $o->created_at->format('H:i') }}</span>
                    </time>
                    <x-filament::badge :color="$couleurs[$o->action] ?? 'gray'" size="sm">{{ $o->verbe() }}</x-filament::badge>
                    <span class="ub-coach-objet">
                        {{ $o->objet() }}
                        @if ($o->changes)<span class="ub-coach-champs">{{ implode(', ', array_keys($o->changes)) }}</span>@endif
                    </span>
                </li>
            @endforeach
        </ol>
    </template>
</div>
