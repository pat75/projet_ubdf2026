@php
    $types = ['accueil' => __('Accueil'), 'pages' => __('Pages'), 'news' => __('Actualités')];
@endphp
<div>
    {{-- Dans la racine : Livewire exige un seul element racine, sans rien avant. --}}
    @if ($editeurTexte === 'redactor_bloc')
        @vite('resources/js/espace-blocs.js')
    @endif
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Écrire, publier, raconter') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Les pages de contenu') }}</h1>
        </div>

        <a href="{{ auth()->user()->bookUrl() }}" target="_blank" rel="noopener"
           class="bouton-espace bouton-espace-grand px-4.5">{{ __('Voir mon book ↗') }}</a>
    </div>

    @if ($devEditeur)
        {{-- Developpement seulement : moteur d'edition en service, et bascule. --}}
        <div class="dev_only mb-5 flex flex-wrap items-center gap-3 border border-dashed border-ub-danger px-4 py-2.5 text-[13px] text-ub-texte">
            <span>{{ __('Éditeur :') }} <strong>{{ $this->libelleEditeur() }}</strong></span>
            <button type="button" wire:click="basculerEditeur" class="bouton-espace bouton-espace-petit px-3">
                {{ $editeurTexte === 'redactor' ? __('Passer à Redactor bloc') : __('Passer à Redactor classique') }}
            </button>
        </div>
    @endif

    {{-- Nouvelle rubrique : creee sans nom, a nommer sur place. --}}
    <section class="mb-7">
        <button type="button" wire:click="creerRubrique" wire:loading.attr="disabled"
                class="carte-espace flex items-center gap-2.5 px-5 py-3.5 text-[14px] font-semibold text-ub-accent-texte hover:bg-ub-accent-fond/40">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-ub-accent text-[18px] leading-none text-white">+</span>
            {{ __('Nouvelle rubrique') }}
        </button>
    </section>

    {{-- Les rubriques, deplacables par leur croix ; les pages se trient a
         l'interieur de chacune (window.espaceTri). --}}
    <section class="flex flex-col gap-4">
        @forelse ($rubriques as $rubrique)
            <div wire:key="rubrique-{{ $rubrique->id }}" class="carte-espace" x-data="{ ouvert: true }">
                <div class="flex cursor-pointer items-center gap-3 px-5 py-3.5" :class="ouvert && 'border-b border-ub-filet'" x-on:click="ouvert = ! ouvert">
                    <div class="min-w-0 flex-1" x-on:click.stop>
                        <x-espace.champ-editable nom="rubrique-{{ $rubrique->id }}" :valeur="$rubrique->title"
                            :vide="__('Nouvelle rubrique')" typo="text-[18px] font-bold leading-snug" />
                    </div>

                    <span class="hidden shrink-0 rounded-full bg-ub-accent-fond px-2 py-0.5 text-[11px] font-bold text-ub-accent-texte sm:inline">
                        {{ $types[$rubrique->kind] ?? $rubrique->kind }}
                    </span>
                    <span class="shrink-0 text-[13px] text-ub-texte3">{{ trans_choice(':n page|:n pages', $rubrique->articles->count(), ['n' => $rubrique->articles->count()]) }}</span>

                    <div class="ml-auto flex shrink-0 items-center gap-3" x-on:click.stop>
                        <span class="hidden text-[13px] text-ub-texte3 sm:inline">{{ $rubrique->is_published ? __('En ligne') : __('Masquée') }}</span>
                        <x-espace.interrupteur petit wire:click="basculerRubrique({{ $rubrique->id }})" :actif="$rubrique->is_published"
                            :libelle="__('Afficher la rubrique dans le book')" />
                        <x-espace.confirmer-suppression sens="bas" action="supprimerRubrique({{ $rubrique->id }})"
                            :question="$rubrique->title !== '' ? __('Supprimer la rubrique « :nom » ?', ['nom' => $rubrique->title]) : __('Supprimer cette rubrique ?')"
                            :bloque="$rubrique->articles->isEmpty() ? null : trans_choice(
                                'Cette rubrique contient encore :n page. Supprimez-la d’abord, puis vous pourrez supprimer la rubrique.|Cette rubrique contient encore :n pages. Supprimez-les d’abord, puis vous pourrez supprimer la rubrique.',
                                $rubrique->articles->count(), ['n' => $rubrique->articles->count()])">
                            <button type="button" class="p-1 text-ub-texte4 hover:text-ub-danger" title="{{ __('Supprimer la rubrique') }}">
                                <x-espace.picto nom="poubelle" class="h-4 w-4" />
                            </button>
                        </x-espace.confirmer-suppression>
                    </div>

                    <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" x-show="! ouvert" />
                    <x-espace.picto nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" x-show="ouvert" x-cloak />
                </div>

                <div x-show="ouvert">
                    <ul class="min-h-12" x-data x-init="window.espaceTri($el, ids => $wire.ordonnerPages({{ $rubrique->id }}, ids))">
                        @foreach ($rubrique->articles as $page)
                            <li wire:key="page-{{ $page->id }}" data-id="{{ $page->id }}"
                                class="border-b border-ub-filet last:border-b-0">
                                <div class="flex cursor-pointer items-center gap-3.5 px-5 py-3 hover:bg-ub-accent-fond/40"
                                     wire:click="ouvrirPage({{ $page->id }})">
                                    <span data-poignee class="cursor-grab text-ub-texte4 hover:text-ub-texte" title="{{ __('Déplacer') }}" x-on:click.stop>
                                        <x-espace.picto nom="deplacer" class="h-4 w-4" />
                                    </span>

                                    <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <span class="truncate text-[15px] font-bold text-ub-texte">{{ $page->title ?: __('Nouvelle page') }}</span>
                                        <span class="text-[12px] text-ub-texte3">{{ $page->updated_at?->format('d/m/Y') }}</span>
                                    </span>

                                    @if ($edition === $page->id)
                                        <x-espace.picto nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" />
                                    @else
                                        <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
                                    @endif
                                </div>

                                @if ($edition === $page->id)
                                    <div class="flex flex-col gap-4 border-t border-ub-filet px-5 pb-6 pt-4">
                                        {{-- Titre et editeur, encadres d'un filet gris. --}}
                                        <div class="flex flex-col gap-4 border border-ub-bord p-4">
                                        <div class="flex items-start gap-3">
                                            <div class="min-w-0 flex-1">
                                                <x-espace.champ-editable nom="page-{{ $page->id }}" :valeur="$page->title"
                                                    :vide="__('Nouvelle page')" typo="text-[20px] font-bold leading-snug" />
                                            </div>
                                            <x-espace.confirmer-suppression sens="bas" action="supprimerPage({{ $page->id }})" :question="__('Supprimer cette page ?')">
                                                <button type="button" class="p-1 text-ub-texte4 hover:text-ub-danger" title="{{ __('Supprimer la page') }}">
                                                    <x-espace.picto nom="poubelle" class="h-4 w-4" />
                                                </button>
                                            </x-espace.confirmer-suppression>
                                        </div>

                                        @php
                                            $urlsImages = [
                                                'upload' => route('espace.pages.upload-image'),
                                                'liste' => route('espace.pages.images.index'),
                                                'action' => route('espace.pages.images.update', ['image' => '__ID__']),
                                            ];
                                        @endphp

                                        @if ($editeurTexte === 'redactor_bloc')
                                            {{-- Editeur par blocs (Editor.js) : wire:ignore, le contenu ne
                                                 quitte le composant qu'a onChange (resources/js/espace-blocs.js). --}}
                                            <div wire:ignore x-data
                                                 x-init="window.espacePageEditorBlocs($refs.blocs, @js($blocs), (blocs) => $wire.blocs = blocs, @js($urlsImages))"
                                                 class="border border-ub-bord bg-white">
                                                <div x-ref="blocs"></div>
                                            </div>
                                            @error('blocs') <p class="text-[13px] text-ub-danger">{{ $message }}</p> @enderror
                                        @else
                                            {{-- Editeur Redactor : wire:ignore, le contenu ne quitte le
                                                 composant qu'au callback `changed` (resources/js/espace.js). --}}
                                            <div wire:ignore x-data
                                                 x-init="window.espacePageEditor($refs.corps, (html) => $wire.corps = html, @js($urlsImages))"
                                                 class="border border-ub-bord bg-white">
                                                <div x-ref="corps">{!! $corps !!}</div>
                                            </div>
                                            @error('corps') <p class="text-[13px] text-ub-danger">{{ $message }}</p> @enderror
                                        @endif

                                        </div>

                                        <div class="flex items-center gap-4">
                                            <button type="button"
                                                    wire:click="{{ $editeurTexte === 'redactor_bloc' ? 'enregistrerBlocs' : 'enregistrerPage' }}"
                                                    wire:loading.attr="disabled"
                                                    class="bouton-espace bouton-espace-petit px-4">{{ __('Enregistrer') }}</button>
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($rubrique->articles->isEmpty())
                        <p class="px-5 pb-4 text-[14px] text-ub-texte3">{{ __('Rubrique vide : ajoutez-y une page ci-dessous.') }}</p>
                    @endif

                    <button type="button" wire:click="creerPage({{ $rubrique->id }})" wire:loading.attr="disabled"
                            class="flex w-full items-center gap-2.5 border-t border-ub-filet px-5 py-3.5 text-[14px] font-semibold text-ub-accent-texte hover:bg-ub-accent-fond/40">
                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-ub-accent text-[18px] leading-none text-white">+</span>
                        {{ __('Nouvelle page') }}
                    </button>
                </div>
            </div>
        @empty
            <p class="carte-espace p-6 text-[14px] text-ub-texte3">{{ __('Aucune rubrique pour le moment.') }}</p>
        @endforelse
    </section>
</div>
