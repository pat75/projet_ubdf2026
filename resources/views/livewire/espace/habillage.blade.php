@php
    // Vignettes reprises telles quelles du site d'origine (user_admin_core.css,
    // regle #ub_mdl_2017 .mdl#<theme>) : les quatre premiers modeles utilisent
    // un motif tuile en repetition, les suivants une illustration pleine, non
    // repetee, centree en haut.
    $vignettes = [
        'mdl_classique' => ['motif1.gif', 'repeat'],
        'mdl_2012' => ['motif2.gif', 'repeat'],
        'mdl_2012_slide' => ['motif7.gif', 'repeat'],
        'mdl_2013_pinter' => ['motif4.gif', 'repeat'],
        'mdl_2014_responsive' => ['mdl_2014_responsive.gif', 'couvrir'],
        'mdl_2015_classique' => ['ub_mdl_classique_2015.gif', 'couvrir'],
        'mdl_2015_grid' => ['mdl_grid_2015.gif', 'couvrir'],
        'mdl_2016_zoom' => ['mdl_zoom_2017.gif', 'couvrir'],
        'mdl_2020_ultra_zen' => ['mdl_2020_ultra_zen.gif', 'couvrir'],
        'mdl_2020_ultra_frais' => ['mdl_2020_ultra_frais.gif', 'couvrir'],
    ];

    // Mise en avant : les deux modeles courants en tete, puis les deux
    // precedents ; le reste, plus ancien, se deplie a la demande.
    $recents = ['mdl_2020_ultra_frais', 'mdl_2020_ultra_zen'];
    $precedents = ['mdl_2015_grid', 'mdl_2016_zoom'];
    $anciens = collect($themes)->keys()->diff([...$recents, ...$precedents])->all();
@endphp
<div>
    {{-- En-tete de page, gabarit de Claude_design.md (reference : Mes messages). --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Sélectionner mon modèle de portfolio') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Habillage') }}</h1>
        </div>
    </div>

    <section class="mb-8">
        <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Modèle du book') }}</h2>
        <p class="mt-1 text-[13px] text-ub-texte3">{{ __('Chaque modèle garde ses propres réglages : revenir à un ancien modèle les retrouve.') }}</p>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($recents as $cle)
                @include('livewire.espace.partials.carte-theme', ['nom' => $themes[$cle]])
            @endforeach
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($precedents as $cle)
                @include('livewire.espace.partials.carte-theme', ['nom' => $themes[$cle]])
            @endforeach
        </div>

        @if ($anciens)
            <div x-data="{ ouvert: false }" class="carte-espace mt-4 overflow-hidden">
                <button type="button" @click="ouvert = ! ouvert"
                        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left">
                    <span class="text-[15px] font-bold text-ub-texte">{{ __('Anciens modèles') }}</span>
                    <x-espace.picto x-show="! ouvert" nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
                    <x-espace.picto x-show="ouvert" nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" />
                </button>

                <div x-show="ouvert" class="grid grid-cols-1 gap-4 border-t border-ub-filet p-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($anciens as $cle)
                        @include('livewire.espace.partials.carte-theme', ['nom' => $themes[$cle]])
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <div class="space-y-3">
        {{-- Presentation et reglages du modele : deux blocs depliables,
             empiles, titre + fleche a droite (voir Claude_design.md,
             « Listes depliables »). La presentation s'enregistre champ par
             champ ; seuls les reglages du modele passent par le bouton. --}}
        <div x-data="{ ouvert: true }" class="carte-espace overflow-hidden">
            <button type="button" @click="ouvert = ! ouvert"
                    class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left">
                <span class="text-[15px] font-bold text-ub-texte">{{ __('Présentation') }}</span>
                <x-espace.picto x-show="! ouvert" nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
                <x-espace.picto x-show="ouvert" nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" />
            </button>

            <div x-show="ouvert" class="space-y-5 border-t border-ub-filet px-5 pb-5 pt-4">
                {{-- Photo de profil : glisser-deposer ou clic sur le medaillon,
                     recadrage rond dans une modale (voir recadrageAvatar,
                     resources/js/espace.js). --}}
                <div x-data="recadrageAvatar()" class="flex items-center gap-5">
                    <div class="group relative h-24 w-24 shrink-0 cursor-pointer"
                         @click="$refs.entree.click()"
                         @dragover.prevent="survole = true" @dragleave.prevent="survole = false"
                         @drop.prevent="survole = false; fichierDepose($event.dataTransfer.files[0])"
                         :class="survole && 'ring-4 ring-ub-accent ring-offset-2'">
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ __('Photo de profil') }}" class="h-24 w-24 rounded-full object-cover">
                        @else
                            <span class="flex h-24 w-24 items-center justify-center rounded-full text-2xl font-bold text-white" style="background-color: {{ $couleurAvatar }}">{{ $initiales }}</span>
                        @endif

                        <span class="absolute inset-0 flex items-center justify-center rounded-full bg-black/0 text-white opacity-0 transition group-hover:bg-black/40 group-hover:opacity-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16V8a2 2 0 0 1 2-2h2l1.5-2h5L16 6h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/>
                                <circle cx="12" cy="12" r="3.2"/>
                            </svg>
                        </span>

                        <input type="file" accept="image/*" class="hidden" x-ref="entree" @change="fichierDepose($event.target.files[0]); $event.target.value = ''">
                    </div>

                    <div class="text-[13px] text-ub-texte3">
                        <p>{{ __('Glissez une image ici, ou cliquez sur la photo pour la changer.') }}</p>
                        @if ($photoUrl)
                            <button type="button" wire:click="retirerAvatar"
                                    class="mt-1 font-semibold text-ub-danger hover:underline">{{ __('Retirer la photo') }}</button>
                        @endif
                    </div>

                    {{-- Modale de recadrage. --}}
                    <div x-show="ouvert" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                        <div class="carte-espace w-full max-w-sm p-5">
                            <h3 class="mb-4 text-[15px] font-bold text-ub-texte">{{ __('Recadrer la photo') }}</h3>

                            <div class="relative mx-auto h-64 w-64 touch-none overflow-hidden rounded-full bg-ub-fond"
                                 @pointerdown="debuterGlisser($event)" @pointermove="glisser($event)"
                                 @pointerup="terminerGlisser" @pointerleave="terminerGlisser"
                                 @wheel.prevent="zoomerMolette($event)">
                                <canvas x-ref="canvas" width="256" height="256" class="h-full w-full"></canvas>
                            </div>

                            <input type="range" min="0.7" max="3" step="0.01" x-model.number="zoom" @input="redessiner"
                                   class="mt-4 w-full">

                            <div class="mt-5 flex justify-end gap-2">
                                <button type="button" @click="fermer"
                                        class="bouton-espace-petit inline-flex items-center justify-center border border-ub-bord bg-white px-3.5 text-[13px] font-semibold text-ub-texte hover:border-ub-accent hover:text-ub-accent-texte">
                                    {{ __('Annuler') }}
                                </button>
                                <button type="button" @click="valider" class="bouton-espace bouton-espace-petit px-3.5">
                                    {{ __('Valider') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <x-espace.champ-editable nom="titre" :valeur="$titre"
                    :libelle="__('Titre du book')" :vide="__('Ajouter un titre…')" />
                <x-espace.champ-editable nom="description" :valeur="$description" multiligne
                    :libelle="__('Description')" :vide="__('Ajouter une description…')" />
                <x-espace.champ-editable nom="piedDePage" :valeur="$piedDePage" multiligne
                    :libelle="__('Pied de page')" :vide="__('Ajouter un pied de page…')" />
            </div>
        </div>

        @if ($champs)
            <form wire:submit="enregistrer" class="space-y-3">
                <div x-data="{ ouvert: false }" class="carte-espace overflow-hidden">
                    <button type="button" @click="ouvert = ! ouvert"
                            class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left">
                        <span class="text-[15px] font-bold text-ub-texte">{{ __('Réglages du modèle') }}</span>
                        <x-espace.picto x-show="! ouvert" nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
                        <x-espace.picto x-show="ouvert" nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" />
                    </button>

                    <div x-show="ouvert" class="grid gap-4 border-t border-ub-filet px-5 pb-5 pt-4 sm:grid-cols-2">
                        @foreach ($champs as $i => $c)
                            <label wire:key="champ-{{ $theme }}-{{ $i }}" class="flex items-center justify-between gap-3 rounded-md bg-ub-fond px-3 py-2.5 text-[14px]">
                                <span class="text-ub-texte2">{{ $c['libelle'] }}</span>
                                @switch($c['type'])
                                    @case('booleen')
                                        <input type="checkbox" wire:model="valeurs.{{ $i }}" class="h-4 w-4 rounded border-ub-bord">
                                        @break
                                    @case('couleur')
                                        <input type="color" wire:model="valeurs.{{ $i }}" class="h-8 w-12 rounded border border-ub-bord">
                                        @break
                                    @case('nombre')
                                        <input type="number" wire:model="valeurs.{{ $i }}" class="w-24 rounded-md border border-ub-bord px-2 py-1 text-ub-texte">
                                        @break
                                    @default
                                        <input type="text" wire:model="valeurs.{{ $i }}" class="w-1/2 rounded-md border border-ub-bord px-2 py-1 text-ub-texte">
                                @endswitch
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3">
                    <x-espace.bouton wire:loading.attr="disabled" wire:target="enregistrer">{{ __('Enregistrer les réglages') }}</x-espace.bouton>
                </div>
            </form>
        @endif
    </div>
</div>
