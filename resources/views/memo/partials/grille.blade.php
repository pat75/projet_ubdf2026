{{-- Grille du memoBook : groupes par annee puis par mois de memorisation
     (ordre de MemoBooks::books), cartes de la page d'accueil (couverture,
     vignette ronde a cheval, nom, metier). `interactif` : croix de retrait
     au survol (toujours visible sur ecran tactile) et label des messages,
     absents de la version publique (memo/public). Attend `$books` et, en
     interactif, `$compteurs`. --}}
@php($compteurs ??= [])
@foreach ($books->groupBy(fn ($b) => \Illuminate\Support\Carbon::parse($b->memorise_le)->format('Y')) as $annee => $livresAnnee)
<section wire:key="annee-{{ $annee }}" class="mt-8">
    <h2 class="border-b border-ub-bord pb-2 font-titre text-[26px] font-light text-ub-texte">{{ $annee }}</h2>

    @foreach ($livresAnnee->groupBy(fn ($b) => \Illuminate\Support\Carbon::parse($b->memorise_le)->format('m')) as $mois => $livresMois)
    <h3 wire:key="mois-{{ $annee }}-{{ $mois }}" class="mt-5 text-[13px] font-semibold uppercase tracking-[.08em] text-ub-texte3">
        {{ \Illuminate\Support\Carbon::create((int) $annee, (int) $mois, 1)->translatedFormat('F') }}
        <span class="font-normal normal-case tracking-normal text-ub-texte4">· {{ trans_choice(':n book|:n books', $livresMois->count(), ['n' => $livresMois->count()]) }}</span>
    </h3>
<ul class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    @foreach ($livresMois as $book)
        @php($couverture = $book->media->first())
        <li wire:key="memo-{{ $book->id }}" class="group relative">
            @php($echanges = $compteurs[$book->id] ?? null)
            <div class="overflow-hidden rounded-sm bg-white shadow-[0_0_10px_rgba(100,100,100,.1)] transition-shadow hover:shadow-[0_0_14px_rgba(0,0,0,.25)]">
                <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener"
                   class="block">
                    <span class="block h-24 bg-black/10">
                        @if ($couverture)
                            <img src="{{ $couverture->url('front_desk') }}" alt="{{ $couverture->title }} - {{ $book->fullName() }}" loading="lazy" class="h-full w-full object-cover">
                        @endif
                    </span>

                    <span class="flex flex-col items-center px-4 pb-4 text-center {{ $book->bookSetting?->thumbnail ? '' : 'pt-4' }}">
                        @if ($book->bookSetting?->thumbnail)
                            <img src="{{ $book->thumbnailUrl('carre_183') }}" alt="" loading="lazy"
                                 class="-mt-7.5 mb-3 h-15 w-15 rounded-full object-cover shadow-[1px_1px_6px_#aaa]">
                        @endif
                        <span class="text-[16px] font-semibold leading-tight text-black/85">{{ $book->fullName() }}</span>
                        <span class="mt-1 text-[12px] uppercase leading-6 tracking-wide text-black/40">{{ $book->category?->name }}</span>
                    </span>
                </a>

                @if ($interactif)
                    {{-- Pied de carte : les messages echanges (s'il y en a) et
                         « Ecrire », qui ouvre la fenetre d'envoi. --}}
                    <div class="flex border-t border-ub-filet text-[13px] text-ub-texte2">
                        @if ($echanges)
                            <button type="button" wire:click="ouvrirMessages(@js($book->login))"
                                    class="flex min-w-0 flex-1 items-center justify-center gap-1.5 px-2 py-2.5 hover:bg-ub-fond"
                                    title="{{ __('Voir les messages échangés') }}">
                                <x-espace.picto nom="courrier" class="h-4 w-4 shrink-0 text-ub-accent-texte" />
                                <span class="truncate">
                                    {{ trans_choice(':n reçu|:n reçus', $echanges['recus'], ['n' => $echanges['recus']]) }}
                                    · {{ trans_choice(':n envoyé|:n envoyés', $echanges['envoyes'], ['n' => $echanges['envoyes']]) }}
                                </span>
                            </button>
                        @endif
                        <button type="button" wire:click="ouvrirEcriture(@js($book->login))"
                                @class(['flex items-center justify-center gap-1.5 py-2.5 hover:bg-ub-fond',
                                    'w-10 shrink-0 border-l border-ub-filet' => $echanges, 'flex-1 px-2' => ! $echanges])
                                title="{{ __('Envoyer un message à :nom', ['nom' => $book->fullName()]) }}">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"/></svg>
                            <span @class(['sr-only' => $echanges])>{{ __('Écrire') }}</span>
                        </button>
                    </div>
                @endif
            </div>

            @if ($interactif)
            <button type="button" wire:click="retirer(@js($book->login))" wire:loading.attr="disabled"
                    title="{{ __('Retirer du mémoBook') }}" aria-label="{{ __('Retirer :nom du mémoBook', ['nom' => $book->fullName()]) }}"
                    class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-white opacity-0 transition-opacity hover:bg-black focus:opacity-100 group-hover:opacity-100 [@media(hover:none)]:opacity-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
            @endif
        </li>
    @endforeach
</ul>
    @endforeach
</section>
@endforeach
