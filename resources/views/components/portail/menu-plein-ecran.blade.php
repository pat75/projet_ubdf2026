{{-- Menu plein ecran du portail, ouvert par le burger ($store.menu) :
     modale 100 % qui apparait en fondu progressif (maquette « Menu
     ultra-book »). Remplace l'ancien #overlay-menu Semantic UI. --}}
@php
    $categories = [
        ['/illustrateur-freelance', 'Illustration', '#8c8a88'],
        ['/meilleurs-illustrateurs-jeunesse', 'Illustration jeunesse', '#a39d8c'],
        ['/graphistes-freelance', 'Graphisme', '#7a809e'],
        ['/directeur-artistique', 'Direction artistique', '#9c9cc0'],
        ['/webdesigner-developpeur-freelance', 'Digital & dev', '#6f9bb8'],
        ['/plasticien', 'Art', '#b07a6c'],
        ['/photographe', 'Photographie', '#c28a7c'],
        ['/design', 'Design objet', '#b08c9c'],
        ['/architecte', 'Architecture', '#a79cc4'],
    ];
    $rouge = 'oklch(0.62 0.2 25)';
    $etapes = ['Sélectionnez un créatif', 'Envoyez votre demande', 'Validez et démarrez un projet'];
@endphp

<div x-data x-show="$store.menu.ouvert" x-cloak
     x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 scale-[.98]" x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition duration-300 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     x-effect="document.documentElement.style.overflow = $store.menu.ouvert ? 'hidden' : ''"
     @keydown.escape.window="$store.menu.ouvert && $store.menu.basculer()"
     role="dialog" aria-modal="true" aria-label="Menu"
     class="menu-plein-ecran fixed inset-0 z-[10000] flex flex-col overflow-y-auto bg-[#141414] text-[#f2f0ed] antialiased">
    <style>
        .menu-plein-ecran, .menu-plein-ecran * { box-sizing: border-box; font-family: 'Source Sans Pro', system-ui, sans-serif; }
        .menu-plein-ecran a { color: #f2f0ed; text-decoration: none; }
        .menu-plein-ecran a:hover { color: #fff; }
        .menu-plein-ecran h1, .menu-plein-ecran h2, .menu-plein-ecran p { font-family: inherit; margin: 0; }
        .menu-plein-ecran input::placeholder { color: #8a8784; }
        /* Les feuilles legacy (non layerees) l'emportent sur hidden/sm: :
           la mise en page responsive est posee ici. */
        .menu-plein-ecran .mpe-cat { transition: background-color .2s, border-color .2s, transform .2s; }
        .menu-plein-ecran .mpe-cat:hover { background: #33312f; border-color: #5a5856; transform: translateY(-2px); }
        .menu-plein-ecran .mpe-cat .mpe-fleche { transition: transform .2s, color .2s; }
        .menu-plein-ecran .mpe-cat:hover .mpe-fleche { transform: translateX(4px); color: #f2f0ed; }
        .menu-plein-ecran .mpe-lien { color: #bdbab6; text-decoration-color: #5a5856; transition: color .2s, text-decoration-color .2s; }
        .menu-plein-ecran .mpe-lien:hover { color: #fff; text-decoration-color: oklch(0.62 0.2 25); }
        .menu-plein-ecran section { display: flex; flex-direction: column; }
        .menu-plein-ecran .mpe-large { display: none; }
        .menu-plein-ecran .mpe-etapes { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .menu-plein-ecran .mpe-bas { margin-top: 56px; }
        @@media (min-width: 640px) {
            .menu-plein-ecran .mpe-large { display: flex; }
            .menu-plein-ecran .mpe-etapes { grid-template-columns: auto auto auto auto auto; justify-content: start; }
        }
    </style>

    <header class="grid grid-cols-[1fr_auto_1fr] items-center gap-4 border-b border-[#2a2928] px-[clamp(20px,4vw,56px)] py-5">
        <button type="button" @click="$store.menu.basculer()"
                class="flex cursor-pointer items-center gap-2.5 justify-self-start rounded-full border border-[#333130] bg-transparent py-2.5 pl-3.5 pr-[18px] text-[14px] font-semibold text-[#f2f0ed] hover:border-[#4a4846] hover:bg-[#222120]">
            <svg width="14" height="14" viewBox="0 0 14 14"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="mpe-large">Fermer</span>
        </button>

        <a href="{{ lien('accueil') }}" aria-label="{{ $marque->nom }}"><img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="block h-auto w-[130px] invert"></a>

        {{-- Colonne conservee meme vide : la grille 1fr/auto/1fr garde le
             logo centre. Un utilisateur connecte a deja son portfolio. --}}
        <div class="flex items-center gap-2.5 justify-self-end">
            @guest
            <a href="{{ lien('inscription.page') }}" class="mpe-large items-center gap-2 rounded-full px-5 py-3 text-[14px] font-semibold" style="background:#f2f0ed;color:#141414">
                <svg width="12" height="12" viewBox="0 0 12 12"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                Créer un portfolio
            </a>
            @endguest
        </div>
    </header>

    <main class="mx-auto w-full max-w-[1280px] flex-1 px-[clamp(20px,4vw,56px)] py-[clamp(32px,5vw,64px)]">
        <section class="flex flex-col" style="gap: 32px">
            <div class="flex flex-col" style="gap: 6px">
                <p class="m-0 text-[clamp(34px,4.4vw,56px)] font-light leading-[1.02] tracking-[-0.02em]">Une mine de créatifs</p>
                <p class="m-0 text-[19px] text-[#bdbab6]">Trouver et contacter les meilleurs créatifs freelances !</p>
            </div>
            <ol class="mpe-etapes m-0 list-none p-0">
                @foreach ($etapes as $i => $etape)
                    @if ($i)
                        <li aria-hidden="true" class="mpe-large items-center justify-center text-[#5a5856]">
                            <svg width="28" height="10" viewBox="0 0 28 10"><path d="M0 5h26M22 1l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </li>
                    @endif
                    <li class="flex items-center gap-3 whitespace-nowrap rounded-xl border border-[#2a2928] bg-[#1c1b1a] p-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-[14px] font-bold text-white" style="background: {{ $rouge }}">{{ $i + 1 }}</span>
                        <span class="text-[15px] font-semibold leading-tight">{{ $etape }}</span>
                    </li>
                @endforeach
            </ol>
        </section>

        <div class="mpe-bas flex flex-wrap gap-x-16 gap-y-12 border-t border-[#2a2928] pt-10">
            <section class="flex min-w-0 flex-[2_1_520px] flex-col gap-[18px]">
                <p class="m-0 text-[13px] font-semibold uppercase tracking-[0.08em] text-[#8a8784]">Rechercher par catégories</p>
                <div class="grid gap-2.5 [grid-template-columns:repeat(auto-fill,minmax(180px,1fr))]">
                    @foreach ($categories as [$url, $nom, $couleur])
                        <a href="{{ $url }}" class="mpe-cat flex items-center gap-3 rounded-[10px] border border-[#302f2d] bg-[#262524] p-4 hover:border-[#454341] hover:bg-[#2e2d2b]">
                            <span class="h-3.5 w-3.5 flex-none rounded-[3px]" style="background: {{ $couleur }}"></span>
                            <span class="min-w-0 flex-1 text-[16px] font-semibold">{{ $nom }}</span>
                            <span class="mpe-fleche text-[#6e6b68]">→</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <div class="flex min-w-0 flex-[1_1_280px] flex-col gap-10">
                <section class="flex flex-col gap-3.5" x-data="newsletter">
                    <p class="m-0 text-[13px] font-semibold uppercase tracking-[0.08em] text-[#8a8784]">Newsletter</p>
                    <p class="m-0 text-[17px] font-semibold">Les dernières sélections du mois</p>
                    <form action="{{ route('newsletter.inscription') }}" @submit.prevent="envoyer($el)"
                          class="m-0 flex items-center rounded-full bg-[#f2f0ed] py-1 pl-4 pr-1">
                        @csrf
                        <svg width="18" height="14" viewBox="0 0 18 14" class="flex-none text-[#6e6b68]"><rect x="1" y="1" width="16" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M1.5 2l7.5 6 7.5-6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                        <input type="email" name="mail" required placeholder="Votre e-mail"
                               class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-[15px] text-[#141414] outline-none">
                        <button type="submit" class="cursor-pointer self-stretch rounded-full border-0 bg-[#141414] px-[18px] text-[14px] font-semibold text-[#f2f0ed] hover:bg-[oklch(0.62_0.2_25)]">S'inscrire</button>
                    </form>
                    <p x-show="message" x-transition.opacity x-cloak x-text="message"
                       class="m-0 rounded-xl border border-[#2a2928] bg-[#1c1b1a] px-4 py-3 text-[15px]" :class="erreur ? 'text-red-400' : 'text-green-300'"></p>
                    <span class="text-[13px] text-[#8a8784]">Confidentialité, sécurité et absence de spam</span>
                </section>

                <section class="flex flex-col gap-3">
                    <p class="m-0 text-[13px] font-semibold uppercase tracking-[0.08em] text-[#8a8784]">Contact / Aide</p>
                    <a href="mailto:{{ $marque->email }}?subject={{ rawurlencode(__('Aide').' '.$marque->nom) }}" class="break-all text-[17px] font-semibold">{{ $marque->email }}</a>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-[15px]">
                        <a href="/doc/les-formules-ultra-book" class="mpe-lien underline underline-offset-[3px]">Formules et tarifs</a>
                        <a href="/doc/mentions-legales" class="mpe-lien underline underline-offset-[3px]">Mentions légales</a>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
