{{--
 | Pied de page de l'espace : le meme que celui du portail, transcrit en
 | Tailwind. Tant que le portail n'a pas bascule, les deux versions
 | coexistent — celle-ci sert l'espace, partials/footer.blade.php sert le
 | portail en Semantic UI. Les liens sont identiques des deux cotes.
--}}
<footer class="bg-ub-pied text-[14px] leading-6 text-[#cfcfcf]">
    <div class="mx-auto max-w-[1140px] px-5 pb-12 pt-14">

        {{-- Le logo en version claire : la version courante est noire sur
             transparent, illisible sur ce fond. --}}
        <img src="{{ $marque->logoClair }}" alt="{{ $marque->nom }}" class="mb-8 h-10 w-auto">

        <div class="grid gap-8 md:grid-cols-4">

            <p class="text-[#ddd]">
                {{ __('Depuis 2007 :marque vous permet de créer votre portfolio, d’y ajouter vos images, légendes, liens web, textes de présentation, et surtout de personnaliser votre espace book. Les books sont classés par domaine, une sélection est faite tous les trois mois par des professionnels.', ['marque' => $marque->nom]) }}
            </p>

            <div>
                <h2 class="mb-1.5 text-[13px] font-bold uppercase tracking-[1.2px] text-ub-pied-titre">{{ __('Plateforme portfolio') }}</h2>
                <ul class="space-y-1.5 text-[#ddd]">
                    <li><a class="hover:text-white" href="mailto:{{ $marque->email }}?subject={{ rawurlencode('Aide '.$marque->nom) }}">{{ __('Contact/aide') }}<br>{{ $marque->email }}</a></li>
                    <li><a class="hover:text-white" href="/doc/">{{ __('Documentation / Tuto') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/les-formules-ultra-book">{{ __('Tarifs') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/questions-frequentes-2">{{ __('Questions fréquentes') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/qui-sommes-nous">{{ __('Qui sommes nous ?') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/mentions-legales">{{ __('Mentions légales') }}</a></li>
                </ul>
            </div>

            <div>
                <h2 class="mb-1.5 text-[13px] font-bold uppercase tracking-[1.2px] text-ub-pied-titre">{{ __('Rubriques') }}</h2>
                <ul class="space-y-1.5 text-[#ddd]">
                    <li><a class="hover:text-white" href="{{ lien('home') }}#bloc_zoom">{{ __('Zoom') }}</a></li>
                    <li><a class="hover:text-white" href="{{ lien('home') }}#bloc_actu">{{ __('Tendances, Actualités') }}</a></li>
                    <li><a class="hover:text-white" href="{{ lien('home') }}#bloc_ultrabook_href">{{ __('Derniers :marque', ['marque' => $marque->nom]) }}</a></li>
                    <li><a class="hover:text-white" href="http://www.ultra-book.fr/ecoles/">→ {{ __('Annuaire des écoles') }}</a></li>
                    <li><a class="hover:text-white" href="https://www.les-illustrateurs.com">→ {{ __('Illustrateurs freelances') }}</a></li>
                    <li><a class="hover:text-white" href="/meilleurs-graphistes">→ {{ __('Graphistes freelances') }}</a></li>
                    <li><a class="hover:text-white" href="/webdesigner-freelance">→ {{ __('Webdesigners freelances') }}</a></li>
                    <li><a class="hover:text-white" href="/developpeur-freelance">→ {{ __('Développeurs freelances') }}</a></li>
                </ul>
            </div>

            <div>
                <h2 class="mb-1.5 text-[13px] font-bold uppercase tracking-[1.2px] text-ub-pied-titre">{{ __('Newsletter') }}</h2>
                <p class="text-[#ddd]">{{ __('Les dernières sélections du mois') }}</p>
                <p class="text-ub-pied-titre">{{ __('Confidentialité, sécurité et absence de spam') }}</p>

                <form action="/front/action_ajax_2.php" class="mt-3 flex overflow-hidden rounded-ub bg-white">
                    <input type="hidden" name="action" value="add">
                    <label for="pied_newsletter" class="sr-only">{{ __('Votre adresse e-mail') }}</label>
                    <input id="pied_newsletter" type="email" name="mail" placeholder="{{ __('Mail...') }}"
                           class="h-[42px] min-w-0 flex-1 border-none px-3 text-[15px] text-gray-800 outline-none placeholder:text-gray-400">
                    <button type="submit" class="bg-ub-accent px-4 font-semibold text-white hover:bg-ub-accent-fonce">
                        {{ __('OK') }}
                    </button>
                </form>

                {{-- Les pictogrammes des reseaux viennent de la fonte
                     d'icones du site (font_icon) : ce sont exactement
                     ceux du pied de page d'origine. --}}
                <div class="mt-4 flex gap-2">
                    @foreach ([
                        'Instagram' => ['fonticon-uniF05E', 'https://www.instagram.com/ultra.book/'],
                        'Facebook' => ['fonticon-uniF051', 'https://www.facebook.com/ultrabook.fr'],
                        'X / Twitter' => ['fonticon-uniF057', 'https://twitter.com/ultra_book'],
                        'Pinterest' => ['fonticon-pinterest', 'https://www.pinterest.com/ultrabook001/'],
                    ] as $reseau => [$icone, $url])
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="flex h-7 w-7 items-center justify-center rounded-full bg-white/85 text-[15px] text-ub-pied hover:bg-white">
                            <span class="{{ $icone }}" aria-hidden="true"></span>
                            <span class="sr-only">{{ $reseau }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4 space-y-1">
                    <div><a class="hover:text-white" href="https://www.dustfolio.com/accueil">→ Dustfolio</a></div>
                    <div><a class="hover:text-white" href="https://www.creer-un-book.com">→ {{ __('Créez un book') }}</a></div>
                </div>
            </div>
        </div>
    </div>
</footer>
