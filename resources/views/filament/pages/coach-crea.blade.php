@php
    // Meme vignette et meme ligne « login nom » que la liste des creatifs.
    $avatar = function ($u) {
        $profil = app(\App\Services\Espace\AffichageProfil::class);

        return '<img src="'.e($profil->photoUrl($u) ?: $profil->medaillonCreatif($u)).'" alt="" class="ub-coach-avatar">';
    };
    $nom = fn ($u) => '<span class="ub-creatif"><span class="ub-creatif-login">'.e($u->login).'</span>'
        .(($n = trim($u->firstname.' '.$u->lastname)) !== '' ? '<span class="ub-creatif-nom">'.e($n).'</span>' : '').$u->etoilesSuppressionHtml().'</span>';
@endphp

<x-filament-panels::page>
{{-- IA de redaction des messages : NVIDIA (gratuit) ou OpenRouter par niveau de cout. --}}
@php($dernier = \App\Models\CoachMessage::whereNotNull('modele')->latest()->first(['modele', 'created_at']))
<div class="ub-coach-ia">
    <label for="coach-ia" class="ub-coach-ia-libelle">IA de rédaction</label>
    <x-filament::input.wrapper class="ub-coach-ia-choix">
        <x-filament::input.select id="coach-ia" wire:model.live="coachIa">
            @foreach (\App\Services\Coach\Redacteur::CHOIX as $valeur => $libelle)
                <option value="{{ $valeur }}">{{ $libelle }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
    <span class="ub-ia-modele">
        @if ($dernier)
            Dernier message rédigé par <strong>{{ $dernier->modele }}</strong> le {{ $dernier->created_at->timezone('Europe/Paris')->format('d/m/Y à H:i') }}
        @else
            Aucun message rédigé pour l’instant.
        @endif
        · NVIDIA en panne : bascule automatique sur OpenRouter coût 1.
    </span>
    <x-filament::button size="sm" color="gray" icon="heroicon-o-light-bulb" x-on:click="$dispatch('open-modal', { id: 'coach-memoire' })">
        Mémoire du coach <x-filament::badge size="sm" color="gray">{{ $memoire->count() }}</x-filament::badge>
    </x-filament::button>
</div>
<div class="ub-coach-colonnes">
    <div id="operations" class="min-w-0">{{ $this->table }}</div>

    {{--
        Messages à envoyer, sur le modèle de la liste des opérations : une
        ligne par créatif (flèche, avatar, nom, dates, objet), dépliée sur
        le diagnostic et le brouillon à relire.
    --}}
    {{-- x-data sur un div englobant : le composant section ne le transmet
         pas de facon fiable, et l'en-tete (onglets) doit voir l'etat. --}}
    <div x-data="{ onglet: 'a-envoyer' }" class="min-w-0">
    <x-filament::section id="messages" class="ub-coach-section-messages">
        <x-slot name="heading">
            <span class="ub-coach-tete">Messages</span>
        </x-slot>

        {{-- Onglets intercalaires alignés à gauche : l'onglet actif et sa liste
             partagent le même filet (styles : .ub-coach-onglet / .ub-coach-panneau). --}}
        <div class="ub-coach-onglets" role="tablist">
            <button type="button" role="tab" class="ub-coach-onglet" :class="{ 'est-actif': onglet === 'a-envoyer' }"
                    :aria-selected="onglet === 'a-envoyer'" x-on:click="onglet = 'a-envoyer'">
                À envoyer <span class="ub-coach-nombre">{{ $messages->count() }}</span>
            </button>
            <button type="button" role="tab" class="ub-coach-onglet" :class="{ 'est-actif': onglet === 'envoyes' }"
                    :aria-selected="onglet === 'envoyes'" x-on:click="onglet = 'envoyes'">
                Déjà envoyés <span class="ub-coach-nombre">{{ $envoyes->count() }}</span>
            </button>
        </div>
        <div class="ub-coach-panneau">

        <div x-show="onglet === 'a-envoyer'">
        @if ($messages->isEmpty())
            <p class="text-sm text-gray-500">Aucun message en attente.</p>
        @else
            <div class="ub-coach-messages">
                @foreach ($messages as $m)
                    <div wire:key="msg-{{ $m->id }}" x-data="{ ouvert: false }" class="ub-coach-message">
                        <div class="ub-coach-message-ligne" x-on:click="ouvert = ! ouvert">
                            <button type="button" class="ub-coach-deplier" :aria-expanded="ouvert"
                                    :aria-label="ouvert ? 'Replier le message' : 'Déplier le message'">
                                <x-filament::icon icon="heroicon-s-chevron-right" x-show="! ouvert" class="ub-coach-fleche" />
                                <x-filament::icon icon="heroicon-s-chevron-down" x-show="ouvert" x-cloak class="ub-coach-fleche" />
                            </button>
                            <span class="ub-coach-qui">{!! $avatar($m->user) !!}{!! $nom($m->user) !!}</span>
                            <span class="ub-coach-message-meta">
                                Préparé le {{ $m->created_at->format('d/m/Y H:i') }}
                                · dernier message :
                                @if ($dernier = $derniersEnvois[$m->user_id] ?? null)
                                    il y a {{ trans_choice(':n jour|:n jours', $j = (int) \Illuminate\Support\Carbon::parse($dernier)->diffInDays(now()), ['n' => $j]) }}
                                @else
                                    aucun
                                @endif
                            </span>
                            <span class="ub-coach-message-objet">{{ $m->objet }}</span>
                            {{-- Même bouton que l'action « Se connecter en tant que » de la liste. --}}
                            <x-filament::icon-button tag="a" :href="route('admin.prise-identite.relais', ['creatif' => $m->user])" target="_blank"
                                icon="heroicon-o-arrow-right-on-rectangle" color="gray" x-on:click.stop
                                :label="'Se connecter en tant que '.$m->user->login" :tooltip="'Ouvrir l’espace de '.$m->user->login" />
                            <x-filament::icon-button icon="heroicon-o-paper-airplane" color="primary" x-on:click.stop
                                wire:click="envoyer({{ $m->id }})" wire:target="envoyer({{ $m->id }})" wire:confirm="Envoyer ce message maintenant ?"
                                :label="'Envoyer maintenant à '.$m->user->login" tooltip="Envoyer maintenant" />
                            <x-filament::icon-button icon="heroicon-o-trash" color="danger" x-on:click.stop
                                wire:click="supprimer({{ $m->id }})" wire:confirm="Supprimer ce message ?"
                                label="Supprimer le message" tooltip="Supprimer le message" />
                        </div>

                        <div x-show="ouvert" x-cloak class="ub-coach-detail ub-coach-message-detail">
                            @if ($m->diagnostic)
                                <ol class="ub-coach-operations !p-0" style="grid-template-columns: auto 1fr">
                                    @foreach ($m->diagnostic as $conseil)
                                        <li><x-filament::badge color="gray" size="sm">Conseil</x-filament::badge><span>{{ $conseil }}</span></li>
                                    @endforeach
                                </ol>
                            @endif

                            <input type="text" wire:model="brouillons.{{ $m->id }}.objet" aria-label="Objet" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <textarea wire:model="brouillons.{{ $m->id }}.corps" rows="9" aria-label="Message" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"></textarea>
                            @error("brouillons.{$m->id}.*")<div class="text-sm text-danger-600">{{ $message }}</div>@enderror

                            <div class="flex gap-2">
                                <x-filament::button size="sm" wire:click="ouvrir({{ $m->id }})" color="gray" icon="heroicon-o-pencil-square">Modifier / relire</x-filament::button>
                                <x-filament::button size="sm" wire:click="envoyer({{ $m->id }})" wire:target="envoyer({{ $m->id }})" icon="heroicon-o-paper-airplane">Envoyer</x-filament::button>
                                <x-filament::button size="sm" wire:click="ignorer({{ $m->id }})" wire:confirm="Écarter ce message ?" color="gray">Ignorer</x-filament::button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        </div>

        {{-- Messages envoyés : lecture seule, menus cités en liens comme dans l'espace. --}}
        <div x-show="onglet === 'envoyes'" x-cloak>
            @if ($envoyes->isEmpty())
                <p class="text-sm text-gray-500">Aucun message envoyé.</p>
            @else
                <div class="ub-coach-messages">
                    @foreach ($envoyes as $e)
                        <div wire:key="envoye-{{ $e->id }}" x-data="{ ouvert: false }" class="ub-coach-message">
                            <div class="ub-coach-message-ligne" x-on:click="ouvert = ! ouvert">
                                <button type="button" class="ub-coach-deplier" :aria-expanded="ouvert"
                                        :aria-label="ouvert ? 'Replier le message' : 'Relire le message'">
                                    <x-filament::icon icon="heroicon-s-chevron-right" x-show="! ouvert" class="ub-coach-fleche" />
                                    <x-filament::icon icon="heroicon-s-chevron-down" x-show="ouvert" x-cloak class="ub-coach-fleche" />
                                </button>
                                <span class="ub-coach-qui">{!! $avatar($e->user) !!}{!! $nom($e->user) !!}</span>
                                <span class="ub-coach-message-meta">
                                    Envoyé le {{ $e->envoye_le?->timezone('Europe/Paris')->format('d/m/Y H:i') }}@if ($e->traite_par) par {{ $e->traite_par }}@endif
                                </span>
                                <span class="ub-coach-message-objet">{{ $e->objet }}</span>
                            </div>
                            <div x-show="ouvert" x-cloak class="ub-coach-detail ub-coach-message-detail">
                                <p class="text-sm font-semibold">{{ $e->objet }}</p>
                                <div class="text-sm" style="white-space:pre-line">{{ $e->corpsAvecLiens() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        </div>{{-- .ub-coach-panneau --}}
    </x-filament::section>
    </div>

</div>

{{-- Fenetre volante du brouillon : ouverte a la generation (ou par « Modifier / relire »). --}}
<x-filament::modal id="coach-brouillon" width="3xl" :close-by-clicking-away="false">
    @if ($enEdition && isset($brouillons[$enEdition]))
        @php($courant = $messages->firstWhere('id', $enEdition))
        <x-slot name="heading">Message pour {{ $courant?->user->fullName() }}</x-slot>
        @if ($courant?->diagnostic)
            <x-slot name="description">Conseil : {{ $courant->diagnostic[0] }}</x-slot>
        @endif

        {{-- Largeurs en style : le theme Filament ne compile pas toutes les classes utilitaires. --}}
        <div class="space-y-3" style="width:100%" wire:key="edition-{{ $enEdition }}">
            <input type="text" wire:model="brouillons.{{ $enEdition }}.objet" aria-label="Objet" style="display:block;width:100%;box-sizing:border-box"
                   class="w-full rounded-lg border-gray-300 text-sm font-semibold dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
            <textarea wire:model="brouillons.{{ $enEdition }}.corps" rows="12" aria-label="Message" style="display:block;width:100%;box-sizing:border-box"
                      class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"></textarea>
            @error("brouillons.{$enEdition}.*")<div class="text-sm text-danger-600">{{ $message }}</div>@enderror
        </div>

        <x-slot name="footerActions">
            <x-filament::button wire:click="valider({{ $enEdition }})" wire:target="valider" icon="heroicon-o-check">Valider</x-filament::button>
            <x-filament::button wire:click="relire({{ $enEdition }})" wire:target="relire" color="gray" icon="heroicon-o-sparkles"
                                tooltip="Corrige l’orthographe, la syntaxe et les tournures, sans changer le fond">Relire (syntaxe)</x-filament::button>
            <x-filament::button x-on:click="close" color="gray" outlined>Fermer</x-filament::button>
        </x-slot>
    @endif
</x-filament::modal>
{{-- Memoire du coach : le prompt, le guide appris et la base des corrections. --}}
<x-filament::modal id="coach-memoire" slide-over width="5xl">
    <x-slot name="heading">Mémoire du coach</x-slot>
    <x-slot name="description">Ce que l’IA reçoit à chaque rédaction.</x-slot>

    @php($conseils = $memoire->map(fn ($m) => $m->diagnostic[0] ?? null)->filter()->unique()->sort()->values())
    <div x-data="{ onglet: 'prompt', conseil: '' }">
        <div class="ub-coach-onglets" role="tablist">
            @foreach (['prompt' => 'Prompt', 'regles' => 'Règles', 'base' => 'Base de connaissances'] as $cle => $libelle)
                <button type="button" role="tab" class="ub-coach-onglet" :class="{ 'est-actif': onglet === '{{ $cle }}' }"
                        :aria-selected="onglet === '{{ $cle }}'" x-on:click="onglet = '{{ $cle }}'">
                    {{ $libelle }}@if ($cle === 'base') <span class="ub-coach-nombre">{{ $memoire->count() }}</span>@endif
                </button>
            @endforeach
        </div>
        <div class="ub-coach-panneau" style="padding:1rem">

            <div x-show="onglet === 'prompt'" class="space-y-4">
                <p class="text-sm text-gray-500">Fichier <code>resources/prompts/coach.md</code>, versionné : il se modifie dans le code.</p>
                <pre class="text-xs" style="white-space:pre-wrap">{{ file_get_contents(resource_path('prompts/coach.md')) }}</pre>

                <p class="text-sm font-semibold">Aperçu du prompt complet</p>
                @if ($apercu)
                    <p class="text-sm text-gray-500">Tel qu’il partirait pour le dernier brouillon ({{ $messages->first()->user->login }}) : prompt, règles, corrections du même conseil, puis contexte.</p>
                    @foreach ($apercu as $bloc)
                        <x-filament::badge color="gray" size="sm">{{ $bloc['role'] }}</x-filament::badge>
                        <pre class="text-xs" style="white-space:pre-wrap">{{ $bloc['content'] }}</pre>
                    @endforeach
                @else
                    <p class="text-sm text-gray-500">Aucun brouillon en attente pour construire l’aperçu.</p>
                @endif
            </div>

            <div x-show="onglet === 'regles'" x-cloak class="space-y-3">
                <p class="text-sm text-gray-500">Déduites par l’IA des corrections envoyées, ajoutées à chaque rédaction. Une règle mal comprise se corrige ici.</p>
                <textarea wire:model="regles" rows="16" aria-label="Règles apprises" style="display:block;width:100%;box-sizing:border-box"
                          class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                          placeholder="Aucune règle apprise pour l’instant."></textarea>
                @error('regles')<div class="text-sm text-danger-600">{{ $message }}</div>@enderror
                <div class="flex gap-2">
                    <x-filament::button size="sm" wire:click="enregistrerRegles" wire:target="enregistrerRegles" icon="heroicon-o-check">Enregistrer les règles</x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="reconstruireRegles" wire:target="reconstruireRegles" icon="heroicon-o-arrow-path"
                        wire:confirm="Remplacer les règles par celles déduites de toutes les corrections ?">Reconstruire depuis toutes les corrections</x-filament::button>
                </div>
            </div>

            <div x-show="onglet === 'base'" x-cloak>
                @if ($memoire->isEmpty())
                    <p class="text-sm text-gray-500">Aucune correction pour l’instant : chaque message modifié puis envoyé s’ajoute ici.</p>
                @else
                    <x-filament::input.wrapper class="mb-3">
                        <x-filament::input.select x-model="conseil" aria-label="Filtrer par conseil">
                            <option value="">Tous les conseils</option>
                            @foreach ($conseils as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                    <div class="ub-coach-messages">
                        @foreach ($memoire as $e)
                            <div wire:key="memoire-{{ $e->id }}" x-data="{ ouvert: false }" class="ub-coach-message"
                                 x-show="! conseil || conseil === @js($e->diagnostic[0] ?? '')">
                                <div class="ub-coach-message-ligne" x-on:click="ouvert = ! ouvert">
                                    <button type="button" class="ub-coach-deplier" :aria-expanded="ouvert" :aria-label="ouvert ? 'Replier' : 'Comparer les versions'">
                                        <x-filament::icon icon="heroicon-s-chevron-right" x-show="! ouvert" class="ub-coach-fleche" />
                                        <x-filament::icon icon="heroicon-s-chevron-down" x-show="ouvert" x-cloak class="ub-coach-fleche" />
                                    </button>
                                    <span class="ub-coach-qui">{!! $nom($e->user) !!}</span>
                                    <span class="ub-coach-message-meta">
                                        {{ $e->envoye_le?->timezone('Europe/Paris')->format('d/m/Y') }}@if ($e->traite_par) · {{ $e->traite_par }}@endif
                                    </span>
                                    <span class="ub-coach-message-objet">{{ $e->diagnostic[0] ?? '' }}</span>
                                    <x-filament::icon-button icon="heroicon-o-trash" color="danger" x-on:click.stop
                                        wire:click="retirerExemple({{ $e->id }})" wire:confirm="Retirer cet exemple de la mémoire ? Le message reste dans l’historique."
                                        label="Retirer de la mémoire" tooltip="Retirer de la mémoire" />
                                </div>
                                <div x-show="ouvert" x-cloak class="ub-coach-detail" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500">Version IA</p>
                                        <p class="text-sm font-semibold">{{ $e->objet_ia }}</p>
                                        <div class="text-sm" style="white-space:pre-line">{{ $e->corps_ia }}</div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500">Version envoyée</p>
                                        <p class="text-sm font-semibold">{{ $e->objet }}</p>
                                        <div class="text-sm" style="white-space:pre-line">{{ $e->corps }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament::modal>
</x-filament-panels::page>
