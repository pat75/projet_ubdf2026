<div @if ($archive?->enPreparation()) wire:poll.5s @endif>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Emporter, archiver, imprimer') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Exporter') }}</h1>
        </div>
    </div>

    {{-- Book en PDF : genere a la demande (PdfBook). Le fichier est recupere
         en arriere-plan pour afficher un indicateur pendant la generation,
         puis telecharge. --}}
    <section class="flex flex-col gap-3.5">
        <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Book en PDF') }}</h2>

        <div class="carte-espace flex flex-wrap items-center gap-5 p-5"
             x-data="{
                enCours: false,
                erreur: false,
                titres: true,
                legendes: false,
                async generer() {
                    this.enCours = true;
                    this.erreur = false;
                    try {
                        const reponse = await fetch(@js(route(nom_route('espace.pdf'))) + '?titres=' + (this.titres ? 1 : 0) + '&legendes=' + (this.legendes ? 1 : 0), { credentials: 'same-origin' });
                        if (! reponse.ok) throw new Error(reponse.status);
                        const nom = (reponse.headers.get('Content-Disposition') ?? '').match(/filename=&quot;?([^&quot;;]+)/)?.[1] ?? 'book.pdf';
                        const lien = Object.assign(document.createElement('a'), { href: URL.createObjectURL(await reponse.blob()), download: nom });
                        lien.click();
                        setTimeout(() => URL.revokeObjectURL(lien.href), 10000);
                    } catch {
                        this.erreur = true;
                    } finally {
                        this.enCours = false;
                    }
                },
             }">
            {{-- Icone PDF --}}
            <span class="flex h-14 w-12 shrink-0 flex-col items-center justify-end rounded-sm border-2 border-[#c8312b] pb-1.5 text-[#c8312b]" aria-hidden="true">
                <svg class="mb-1 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14"/></svg>
                <span class="text-[11px] font-black tracking-wide">PDF</span>
            </span>

            <div class="flex min-w-0 flex-[1_1_260px] flex-col gap-1">
                <span class="text-[16px] font-bold text-ub-texte">{{ __('Exporter mon book en PDF') }}</span>
                <span class="text-[13px] text-ub-texte2 text-pretty">
                    {{ __('Votre visuel de profil en couverture, puis vos portfolios mis en page selon le format des visuels.') }}
                </span>
                {{-- Options du PDF, repliees sous « Options de presentation » :
                     dessin de <x-espace.interrupteur> reduit (28 x 16), l'etat tenu par Alpine. --}}
                <div x-data="{ ouvert: false }" class="mt-1.5">
                    <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
                            class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-ub-texte">
                        <span>{{ __('Options de présentation') }}</span>
                        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-4 w-4 shrink-0 text-ub-texte" />
                        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-4 w-4 shrink-0 text-ub-texte" />
                    </button>
                    <div x-show="ouvert" x-cloak class="mt-2.5 flex flex-wrap items-center gap-x-6 gap-y-2.5">
                        @foreach (['titres' => __('Afficher le nom des rubriques'), 'legendes' => __('Afficher les titres des visuels')] as $option => $libelle)
                            <label class="inline-flex cursor-pointer items-center gap-2.5 text-[15.5px] font-semibold text-ub-texte">
                                <button type="button" role="switch" :aria-pressed="{{ $option }}" @click="{{ $option }} = ! {{ $option }}"
                                        class="flex h-4 w-7 shrink-0 cursor-pointer rounded-lg p-0.5 transition-colors duration-200"
                                        :class="{{ $option }} ? 'justify-end bg-ub-accent' : 'justify-start bg-[#d6d6d3]'">
                                    <span class="h-3 w-3 rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,.2)]"></span>
                                </button>
                                <span>{{ $libelle }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <span x-show="erreur" x-cloak class="text-[13px] text-ub-danger">{{ __('La génération a échoué. Réessayez dans un instant.') }}</span>
            </div>

            <button type="button" @click="generer()" :disabled="enCours" class="bouton-espace bouton-espace-grand shrink-0 gap-2 px-6">
                <svg x-show="enCours" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="enCours ? @js(__('Génération en cours…')) : @js(__('Télécharger le PDF'))">{{ __('Télécharger le PDF') }}</span>
            </button>
        </div>
    </section>

    {{-- Archive « Mes donnees » --}}
    <section class="mt-7 flex flex-col gap-3.5">
        <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Mes données') }}</h2>

        <div class="carte-espace flex flex-col gap-4 p-5">
            <div class="flex flex-wrap items-center gap-5">
                <span class="flex h-14 w-12 shrink-0 flex-col items-center justify-end rounded-sm border-2 border-ub-texte pb-1.5 text-ub-texte" aria-hidden="true">
                    <svg class="mb-1 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14"/></svg>
                    <span class="text-[11px] font-black tracking-wide">ZIP</span>
                </span>

                <div class="flex min-w-0 flex-[1_1_260px] flex-col gap-1">
                    <span class="text-[16px] font-bold text-ub-texte">{{ __('Télécharger une copie de mes informations') }}</span>
                    <span class="text-[13px] text-ub-texte2 text-pretty">
                        {{ __('Une archive ZIP de votre compte, portfolios, messages et factures, avec vos images en haute définition, à parcourir dans un navigateur.') }}
                    </span>
                </div>

                @unless ($archive?->enPreparation())
                    <button type="button" wire:click="demanderArchive" wire:loading.attr="disabled" @disabled($prochaine)
                            class="bouton-espace bouton-espace-grand shrink-0 px-6">
                        {{ __('Préparer mon archive') }}
                    </button>
                @endunless
            </div>

            @if ($refus)
                <p class="text-[13px] text-ub-danger">{{ $refus }}</p>
            @endif

            @if ($archive)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-ub-filet pt-4 text-[14px]">
                    @switch (true)
                        @case ($archive->enPreparation())
                            <svg class="h-4 w-4 animate-spin text-ub-accent-texte" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>{{ __('Archive en cours de préparation. Vous recevrez un e-mail à :email dès qu’elle sera prête ; vous pouvez quitter cette page.', ['email' => auth()->user()->email]) }}</span>
                            @break

                        @case ($archive->telechargeable())
                            <span class="text-ub-succes">✓</span>
                            <span>{{ __('Archive prête (:taille), disponible jusqu’au :date.', [
                                'taille' => \Illuminate\Support\Number::fileSize((int) $archive->taille, 1),
                                'date' => $archive->expire_at->format('d/m/Y'),
                            ]) }}</span>
                            <a href="{{ route(nom_route('espace.export.telecharger'), $archive) }}" class="bouton-espace bouton-espace-petit ml-auto px-4">{{ __('Télécharger') }}</a>
                            @break

                        @case ($archive->status === \App\Models\DataExport::ECHEC)
                            <span class="text-ub-danger">{{ __('La préparation de l’archive a échoué. Vous pouvez la redemander.') }}</span>
                            @break

                        @default
                            <span class="text-ub-texte3">{{ __('Votre dernière archive a expiré.') }}</span>
                    @endswitch
                </div>
            @endif

            @if ($prochaine && ! $archive?->enPreparation())
                <p class="text-[13px] text-ub-texte3">{{ __('Prochaine demande possible le :date.', ['date' => $prochaine->format('d/m/Y à H:i')]) }}</p>
            @endif
        </div>
    </section>

    {{-- Limites --}}
    <section class="mt-7 flex flex-col gap-3.5">
        <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Limites selon la formule') }}</h2>

        <div class="carte-espace overflow-hidden text-[14px]">
            <div class="grid grid-cols-[1.4fr_1fr_1fr] border-b border-ub-filet bg-ub-fond px-5 py-3 text-[13px] font-bold text-ub-texte3">
                <span></span>
                <span @class(['text-ub-accent-texte' => ! $payant])>{{ __('Formule gratuite') }}</span>
                <span @class(['text-ub-accent-texte' => $payant])>{{ __('Formule :marque', ['marque' => $nomMarque]) }}</span>
            </div>
            <div class="grid grid-cols-[1.4fr_1fr_1fr] border-b border-ub-filet px-5 py-3">
                <span class="text-ub-texte2">{{ __('Book en PDF') }}</span>
                <span>{{ __(':n pages', ['n' => \App\Services\Espace\PdfBook::PAGES_GRATUITE]) }}</span>
                <span>{{ __(':n pages', ['n' => \App\Services\Espace\PdfBook::PAGES_PAYANTE]) }}</span>
            </div>
            <div class="grid grid-cols-[1.4fr_1fr_1fr] border-b border-ub-filet px-5 py-3">
                <span class="text-ub-texte2">{{ __('Archive de mes données') }}</span>
                <span>{{ __('1 par :n h', ['n' => \App\Models\DataExport::DELAI_HEURES]) }}</span>
                <span>{{ __('1 par :n h', ['n' => \App\Models\DataExport::DELAI_HEURES]) }}</span>
            </div>
            <div class="grid grid-cols-[1.4fr_1fr_1fr] px-5 py-3">
                <span class="text-ub-texte2">{{ __('Conservation de l’archive') }}</span>
                <span>{{ __(':n jours', ['n' => \App\Models\DataExport::CONSERVATION_JOURS]) }}</span>
                <span>{{ __(':n jours', ['n' => \App\Models\DataExport::CONSERVATION_JOURS]) }}</span>
            </div>
        </div>

        @if ($developpement)
            <p class="dev_only text-[13px] text-ub-texte3">{{ __('Environnement de développement : PDF limité à :n pages.', ['n' => \App\Services\Espace\PdfBook::PAGES_DEVELOPPEMENT]) }}</p>
        @endif

        @unless ($payant)
            <a href="{{ route(nom_route('espace.formule')) }}" class="self-start text-[14px] font-semibold text-ub-accent-texte hover:underline">{{ __('Comparer les formules') }} →</a>
        @endunless
    </section>
</div>
