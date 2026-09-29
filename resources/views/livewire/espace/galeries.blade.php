<div x-data="{ visuel: null }">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Organiser mes projets') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Les portfolios') }}</h1>
        </div>

        <a href="{{ auth()->user()->bookUrl() }}" target="_blank" rel="noopener"
           class="bouton-espace bouton-espace-grand px-4.5">{{ __('Voir mon book ↗') }}</a>
    </div>

    <section class="mb-7 grid gap-5 md:grid-cols-2">
        {{-- Nouveau portfolio : cree tout de suite, a renommer sur place. --}}
        <button type="button" wire:click="creer" wire:loading.attr="disabled" wire:target="creer"
                class="carte-espace flex items-center gap-4 p-5 text-left transition hover:shadow-md disabled:opacity-60">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-ub-accent text-[24px] text-white">+</span>
            <span class="min-w-0 flex-1">
                <span class="flex justify-between gap-2">
                    <span class="text-[17px] font-bold text-ub-texte">{{ __('Nouveau portfolio') }}</span>
                    <span class="text-[13px] text-ub-texte3">{{ trans_choice(':n portfolio|:n portfolios', $portfolios->count(), ['n' => $portfolios->count()]) }}</span>
                </span>
                <span class="mt-1 block text-[13px] text-ub-texte3">{{ __('Il s’ajoute en bas de la liste, déplacez-le pour le mettre en premier.') }}</span>
            </span>
        </button>

        {{-- Depot d'images et ajout de video, vers le portfolio choisi. --}}
        @php
            $part = min(100, round($quota['valeur'] / max(1, $quota['plafond']) * 100));
        @endphp
        <div class="carte-espace flex flex-col gap-3 border-2 border-dashed border-ub-succes/60 bg-ub-succes/10 p-5"
             x-data="{ survol: false, video: false, lien: '', erreur: null, envoi: false }"
             :class="survol && 'border-ub-succes bg-ub-succes/20'"
             x-on:dragover.prevent="if ($event.dataTransfer.types.includes('Files')) survol = true"
             x-on:dragleave="survol = false"
             x-on:drop.prevent="survol = false; if ($event.dataTransfer.files.length) { $refs.envoi.files = $event.dataTransfer.files; $refs.envoi.dispatchEvent(new Event('change')) }">
            <label class="flex cursor-pointer items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-ub-succes text-white">
                    <span wire:loading.remove wire:target="fichiers"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15V4m0 0L8 8m4-4 4 4M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/></svg></span>
                    <svg wire:loading wire:target="fichiers" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex justify-between gap-2">
                        <span class="text-[18px] font-black text-ub-texte">
                            <span wire:loading.remove wire:target="fichiers">{{ __('Glisser-déposer vos images ici') }}</span>
                            <span wire:loading wire:target="fichiers">{{ __('Envoi en cours…') }}</span>
                        </span>
                        <span class="shrink-0 text-[13px] text-ub-texte3">{{ $quota['valeur'] }} / {{ $quota['plafond'] }}</span>
                    </span>
                    <span class="mt-2 block h-1.5 overflow-hidden rounded-full bg-white">
                        <span class="block h-full rounded-full bg-ub-succes" style="width: {{ $part }}%"></span>
                    </span>
                </span>
                <input type="file" multiple accept="image/jpeg,image/png,image/gif" class="sr-only" x-ref="envoi" id="envoi-visuels" wire:model="fichiers">
            </label>

            <form class="flex gap-2" x-show="video" x-cloak x-on:submit.prevent="
                    if (! lien.trim() || envoi) return;
                    envoi = true; erreur = null;
                    $wire.ajouterVideo(lien).then((r) => { envoi = false; if (r?.erreur) { erreur = r.erreur } else { lien = '' } })">
                <input type="text" x-model="lien" x-ref="lien" placeholder="{{ __('Coller un lien YouTube ou Vimeo') }}"
                       class="h-8.25 min-w-0 flex-1 border border-ub-bord bg-white px-3 text-[14px] text-ub-texte outline-none focus:border-ub-accent">
                <button type="submit" class="bouton-espace bouton-espace-petit px-4" :disabled="envoi">
                    <span x-show="! envoi">{{ __('Ajouter') }}</span>
                    <span x-show="envoi" x-cloak>{{ __('Recherche…') }}</span>
                </button>
            </form>
            <p x-show="erreur" x-cloak x-text="erreur" class="-mt-1 text-[13px] text-ub-danger"></p>
            @error('fichiers') <p class="-mt-1 text-[13px] text-ub-danger">{{ $message }}</p> @enderror
            @error('fichiers.*') <p class="-mt-1 text-[13px] text-ub-danger">{{ $message }}</p> @enderror
            @error('cible') <p class="-mt-1 text-[13px] text-ub-danger">{{ $message }}</p> @enderror

            <div class="flex items-center gap-2 text-[13px] text-ub-texte3">
                @if ($portfolios->isNotEmpty())
                    <span>{{ __('Dans le portfolio') }}</span>
                    <x-espace.select-discret :options="$portfolios->pluck('name', 'id')->all()" :valeur="$cible" wire:model.live="cible" />
                @endif
                <button type="button" title="{{ __('Ajouter une vidéo YouTube ou Vimeo') }}"
                        x-on:click="video = ! video; erreur = null; if (video) $nextTick(() => $refs.lien.focus())"
                        :class="video ? 'bg-ub-succes text-white' : 'bg-white text-ub-texte hover:text-ub-succes'"
                        class="ml-auto flex h-9 w-9 items-center justify-center rounded-md border border-ub-succes/40">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-2.28A1 1 0 0 1 21 8.62v6.76a1 1 0 0 1-1.45.9L15 14M5 18h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2z"/></svg>
                    <span class="sr-only">{{ __('Ajouter une vidéo YouTube ou Vimeo') }}</span>
                </button>
            </div>
        </div>
    </section>

    {{-- Les portfolios, deplacables par leur croix ; les visuels se trient
         dans un portfolio ou passent de l'un a l'autre (x-espace-visuels). --}}
    <section class="flex flex-col gap-4" x-espace-tri="ordonner" data-poignee="[data-poignee-portfolio]">
        @forelse ($portfolios as $portfolio)
            @php $liste = $visuels[$portfolio->id]; @endphp
            <div wire:key="portfolio-{{ $portfolio->id }}" id="portfolio-{{ $portfolio->id }}" data-id="{{ $portfolio->id }}"
                 class="carte-espace overflow-hidden" x-data="{ ouvert: true, survol: false, cadenas: false }"
                 :class="survol && 'ring-2 ring-ub-accent'"
                 x-on:dragover.prevent="if ($event.dataTransfer.types.includes('Files')) survol = true"
                 x-on:dragleave.self="survol = false"
                 x-on:drop="survol = false; if ($event.dataTransfer.files.length) { $event.preventDefault(); $wire.cible = {{ $portfolio->id }}; const envoi = document.getElementById('envoi-visuels'); envoi.files = $event.dataTransfer.files; envoi.dispatchEvent(new Event('change')) }">
                <div class="flex cursor-pointer items-center gap-3 px-5 py-3.5" :class="ouvert && 'border-b border-ub-filet'" x-on:click="ouvert = ! ouvert">
                    <span data-poignee-portfolio class="cursor-grab text-ub-texte4 hover:text-ub-texte" title="{{ __('Déplacer le portfolio') }}" x-on:click.stop>
                        <x-espace.picto nom="deplacer" class="h-4.5 w-4.5" />
                    </span>

                    <div class="min-w-0" x-on:click.stop>
                        <x-espace.champ-editable nom="portfolio-{{ $portfolio->id }}" :valeur="$portfolio->name"
                            :vide="__('Nom du portfolio')" typo="text-[18px] font-bold leading-snug" />
                    </div>

                    <div class="ml-auto flex shrink-0 items-center gap-3" x-on:click.stop>
                        <span class="text-[13px] text-ub-texte3">{{ trans_choice(':n projet|:n projets', $liste->count(), ['n' => $liste->count()]) }}</span>
                        <span class="hidden text-ub-texte4 sm:inline" aria-hidden="true">·</span>
                        <span class="hidden text-[13px] text-ub-texte3 sm:inline">{{ $portfolio->status === 'published' ? __('En ligne') : __('Masqué') }}</span>
                        <button type="button" x-on:click="cadenas = ! cadenas"
                                title="{{ $portfolio->estProtegee() ? __('Protégé par mot de passe') : __('Protéger par un mot de passe') }}"
                                @class(['p-1', 'text-ub-texte' => $portfolio->estProtegee(), 'text-ub-texte4 hover:text-ub-texte' => ! $portfolio->estProtegee()])>
                            @if ($portfolio->estProtegee()) <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg> @else <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.9-1"/></svg> @endif
                            <span class="sr-only">{{ __('Mot de passe du portfolio') }}</span>
                        </button>
                        <x-espace.interrupteur wire:click="basculerPublication({{ $portfolio->id }})" :actif="$portfolio->status === 'published'"
                            :libelle="__('Afficher le portfolio dans le book')" />
                        <button type="button" class="p-1 text-ub-texte4 hover:text-ub-danger" title="{{ __('Supprimer le portfolio') }}"
                                wire:click="supprimer({{ $portfolio->id }})"
                                wire:confirm="{{ __('Supprimer le portfolio « :nom » et ses :n visuels ?', ['nom' => $portfolio->name, 'n' => $liste->count()]) }}">
                            <x-espace.picto nom="poubelle" class="h-4 w-4" />
                        </button>
                    </div>

                    <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" x-show="! ouvert" />
                    <x-espace.picto nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" x-show="ouvert" x-cloak />
                </div>

                <div x-show="cadenas" x-cloak class="border-b border-ub-filet bg-ub-fond/50 px-5 py-4"
                     x-data="{ mdp: @js($portfolio->password), erreur: null, envoi: false,
                               enregistrer(valeur) {
                                   this.envoi = true; this.erreur = null;
                                   $wire.definirMotDePasse({{ $portfolio->id }}, valeur).then((r) => {
                                       this.envoi = false;
                                       if (r?.erreur) { this.erreur = r.erreur } else { this.mdp = valeur; cadenas = false }
                                   });
                               } }">
                    <p class="mb-2.5 text-[13px] text-ub-texte3">{{ __('Les visiteurs de votre book devront saisir ce mot de passe pour voir ce portfolio. Vous seul le voyez ici.') }}</p>
                    <form class="flex flex-wrap gap-2" x-on:submit.prevent="enregistrer($refs.mdp.value)">
                        <input type="text" x-ref="mdp" :value="mdp" autocomplete="off" spellcheck="false" placeholder="{{ __('Mot de passe') }}"
                               class="h-8.25 min-w-0 flex-1 basis-48 border border-ub-bord bg-white px-3 text-[14px] text-ub-texte outline-none focus:border-ub-accent">
                        <button type="submit" class="bouton-espace bouton-espace-petit px-4" :disabled="envoi">{{ __('Enregistrer') }}</button>
                        @if ($portfolio->estProtegee())
                            <button type="button" class="bouton-espace bouton-espace-petit px-4" :disabled="envoi" x-on:click="enregistrer('')">{{ __('Retirer') }}</button>
                        @endif
                    </form>
                    <p x-show="erreur" x-cloak x-text="erreur" class="mt-1.5 text-[13px] text-ub-danger"></p>
                </div>

                <div x-show="ouvert">
                    <ul class="min-h-12" x-espace-visuels data-galerie="{{ $portfolio->id }}">
                        @foreach ($liste as $item)
                            @php $video = $item->video(); @endphp
                            <li wire:key="visuel-{{ $item->id }}" data-id="{{ $item->id }}"
                                class="border-b border-ub-filet last:border-b-0"
                                :class="visuel === {{ $item->id }} && 'border border-ub-texte/50 last:border-b'">
                                <div class="flex cursor-pointer items-center gap-3.5 px-5 py-3 hover:bg-ub-accent-fond/40"
                                     x-on:click="visuel = visuel === {{ $item->id }} ? null : {{ $item->id }}">
                                    <span data-poignee class="cursor-grab text-ub-texte4 hover:text-ub-texte" title="{{ __('Déplacer') }}" x-on:click.stop>
                                        <x-espace.picto nom="deplacer" class="h-4 w-4" />
                                    </span>

                                    <span class="relative h-11 w-11 shrink-0 overflow-hidden rounded-sm bg-ub-fond">
                                        <img src="{{ $item->url('adm_medium') }}" alt="" loading="lazy"
                                             @class(['h-full w-full object-cover', 'opacity-40' => $item->status !== 'published'])>
                                    </span>

                                    <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <span class="truncate text-[15px] font-bold text-ub-texte">{{ $item->title ?: __('Sans titre') }}</span>
                                        <span class="text-[12px] text-ub-texte3">
                                            {{ $video ? $video->nomPlateforme() : number_format($item->size / 1024, 0, ',', ' ').' Ko' }}
                                            · {{ $item->created_at?->format('d/m/Y') }}
                                        </span>
                                    </span>

                                    @if ($video)
                                        <span class="hidden rounded-full bg-ub-accent-fond px-2 py-0.5 text-[11px] font-bold text-ub-accent-texte sm:inline">{{ __('Vidéo') }}</span>
                                    @endif
                                    @if ($item->status !== 'published')
                                        <span class="hidden rounded-full bg-ub-fond px-2 py-0.5 text-[11px] font-bold text-ub-texte3 sm:inline">{{ __('Masqué') }}</span>
                                    @endif

                                    <button type="button" class="p-1 text-ub-texte4 hover:text-ub-danger" title="{{ __('Supprimer') }}" x-on:click.stop
                                            wire:click="supprimerVisuel({{ $item->id }})" wire:confirm="{{ __('Supprimer ce visuel ?') }}">
                                        <x-espace.picto nom="poubelle" class="h-4 w-4" />
                                    </button>

                                    <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" x-show="visuel !== {{ $item->id }}" />
                                    <x-espace.picto nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" x-show="visuel === {{ $item->id }}" x-cloak />
                                </div>

                                <div x-show="visuel === {{ $item->id }}" x-cloak
                                     class="flex flex-wrap gap-6 border-t border-ub-filet px-5 pb-6 pt-4 md:pl-19.5">
                                    {{-- Apercu : une image glissee dessus (ou choisie au clic sur
                                         l'icone d'envoi) prend la place de l'actuelle. --}}
                                    <div class="flex w-full flex-col gap-3 sm:w-55"
                                         x-data="{ lecture: false, survol: false, envoi: false,
                                                   remplacer(fichier) {
                                                       if (! fichier || ! fichier.type.startsWith('image/')) return;
                                                       this.envoi = true;
                                                       $wire.set('remplace', {{ $item->id }}).then(() => $wire.upload('remplacement', fichier,
                                                           () => this.envoi = false, () => this.envoi = false));
                                                   } }">
                                        <div class="relative aspect-square overflow-hidden rounded-sm bg-ub-fond"
                                             :class="survol && 'ring-2 ring-ub-accent'"
                                             x-on:dragover.prevent.stop="if ($event.dataTransfer.types.includes('Files')) survol = true"
                                             x-on:dragleave="survol = false"
                                             x-on:drop.prevent.stop="survol = false; remplacer($event.dataTransfer.files[0])">
                                            <template x-if="lecture">
                                                <iframe src="{{ $video?->lecteur() }}" class="absolute inset-0 h-full w-full" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                                            </template>
                                            <img x-show="! lecture" src="{{ $item->url('ptf_medium') }}" alt="" loading="lazy" class="h-full w-full object-contain">
                                            @if ($video)
                                                <button type="button" x-show="! lecture" x-on:click="lecture = true" title="{{ __('Lancer la vidéo') }}"
                                                        class="absolute inset-0 cursor-pointer">
                                                    <span class="sr-only">{{ __('Lancer la vidéo') }}</span>
                                                </button>
                                            @endif
                                            <label x-show="! lecture" title="{{ __('Glisser une image ici, ou cliquer, pour la remplacer') }}"
                                                   class="absolute flex h-12 w-12 cursor-pointer items-center justify-center rounded-full bg-white/90 text-ub-texte shadow-md hover:text-ub-succes {{ $video ? 'right-2 top-2 h-10 w-10' : 'left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2' }}">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15V4m0 0L8 8m4-4 4 4M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/></svg>
                                                <span class="sr-only">{{ __('Remplacer l’image') }}</span>
                                                <input type="file" accept="image/jpeg,image/png,image/gif" class="sr-only" x-on:change="remplacer($event.target.files[0]); $event.target.value = ''">
                                            </label>
                                            <div x-show="survol || envoi" x-cloak
                                                 class="pointer-events-none absolute inset-0 flex items-center justify-center bg-white/85 px-4 text-center text-[13px] font-bold text-ub-accent-texte">
                                                <span x-text="envoi ? @js(__('Envoi en cours…')) : @js(__('Déposer pour remplacer l’image'))"></span>
                                            </div>
                                        </div>

                                        @if ($errors->has('remplacement') && $remplace === $item->id)
                                            <p class="-mt-1 text-[12px] text-ub-danger">{{ $errors->first('remplacement') }}</p>
                                        @endif
                                    </div>

                                    <div class="flex min-w-0 flex-1 basis-80 flex-col gap-5">
                                        <x-espace.champ-editable nom="titre-{{ $item->id }}" :valeur="$item->title"
                                            :libelle="__('Titre')" :vide="__('Ajouter un titre…')" />
                                        <x-espace.champ-editable nom="legende-{{ $item->id }}" :valeur="$item->description" multiligne
                                            :libelle="__('Légende')" :vide="__('Ajouter une légende…')" />
                                        <dl class="grid grid-cols-2 gap-x-4 gap-y-1.5 border-t border-ub-filet pt-4 text-[12px] text-ub-texte2">
                                            @if ($video)
                                                <div class="col-span-2 flex gap-1.5">
                                                    <dt class="text-ub-texte3">{{ __('Vidéo') }}</dt>
                                                    <dd><a href="{{ $item->video_url }}" target="_blank" rel="noopener" class="text-ub-accent-texte hover:underline">{{ $video->nomPlateforme() }} ↗</a></dd>
                                                </div>
                                            @else
                                                <div class="flex min-w-0 gap-1.5">
                                                    <dt class="shrink-0 text-ub-texte3">{{ __('Fichier') }}</dt>
                                                    <dd class="truncate" title="{{ $item->filename }}">{{ $item->filename }}</dd>
                                                </div>
                                                <div class="flex min-w-0 gap-1.5">
                                                    <dt class="shrink-0 text-ub-texte3">{{ __('Type') }}</dt>
                                                    <dd class="truncate">{{ $item->mime }}</dd>
                                                </div>
                                            @endif
                                            <div class="flex min-w-0 gap-1.5">
                                                <dt class="shrink-0 text-ub-texte3">{{ __('En ligne') }}</dt>
                                                <dd class="truncate">{{ $item->created_at?->format('d/m/Y') }}</dd>
                                            </div>
                                            <div class="flex min-w-0 gap-1.5">
                                                <dt class="shrink-0 text-ub-texte3">{{ __('Taille') }}</dt>
                                                <dd class="truncate">{{ number_format($item->size / 1024, 0, ',', ' ') }} Ko</dd>
                                            </div>
                                        </dl>

                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if ($liste->isEmpty())
                        <p class="px-5 pb-4 text-[14px] text-ub-texte3">{{ __('Portfolio vide : déposez-y des images, ou glissez-y celles d’un autre portfolio.') }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="carte-espace p-6 text-[14px] text-ub-texte3">{{ __('Aucun portfolio pour le moment.') }}</p>
        @endforelse
    </section>
</div>
