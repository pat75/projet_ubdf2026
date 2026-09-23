{{--
 | Pied de page de l'espace : le meme que celui du portail, transcrit en
 | Tailwind. Tant que le portail n'a pas bascule, les deux versions
 | coexistent — celle-ci sert l'espace, partials/footer.blade.php sert le
 | portail en Semantic UI. Les liens sont identiques des deux cotes.
--}}
<footer class="mt-6 bg-[#4a4a4a] text-[13px] leading-6 text-white">
    <div class="mx-auto max-w-[1127px] px-4 py-10">

        <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="mb-8 h-10 w-auto brightness-0 invert">

        <div class="grid gap-8 md:grid-cols-4">

            <p class="text-white/80">
                {{ __('Depuis 2007 :marque vous permet de créer votre portfolio, d’y ajouter vos images, légendes, liens web, textes de présentation, et surtout de personnaliser votre espace book. Les books sont classés par domaine, une sélection est faite tous les trois mois par des professionnels.', ['marque' => $marque->nom]) }}
            </p>

            <div>
                <h2 class="mb-3 font-titre text-[17px] font-light text-[#17b7bf]">{{ __('Plateforme portfolio') }}</h2>
                <ul class="space-y-1.5 text-white/80">
                    <li><a class="hover:text-white" href="mailto:{{ $marque->email }}?subject={{ rawurlencode('Aide '.$marque->nom) }}">{{ __('Contact/aide') }}<br>{{ $marque->email }}</a></li>
                    <li><a class="hover:text-white" href="/doc/">{{ __('Documentation / Tuto') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/les-formules-ultra-book">{{ __('Tarifs') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/questions-frequentes-2">{{ __('Questions fréquentes') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/qui-sommes-nous">{{ __('Qui sommes nous ?') }}</a></li>
                    <li><a class="hover:text-white" href="/doc/mentions-legales">{{ __('Mentions légales') }}</a></li>
                </ul>
            </div>

            <div>
                <h2 class="mb-3 font-titre text-[17px] font-light text-[#17b7bf]">{{ __('Rubriques') }}</h2>
                <ul class="space-y-1.5 text-white/80">
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
                <h2 class="mb-3 font-titre text-[17px] font-light text-[#17b7bf]">{{ __('Newsletter') }}</h2>
                <p class="text-white/80">{{ __('Les dernières sélections du mois') }}</p>
                <p class="text-[#17b7bf]">{{ __('Confidentialité, sécurité et absence de spam') }}</p>

                <form action="/front/action_ajax_2.php" class="mt-3 flex">
                    <input type="hidden" name="action" value="add">
                    <label for="pied_newsletter" class="sr-only">{{ __('Votre adresse e-mail') }}</label>
                    <input id="pied_newsletter" type="email" name="mail" placeholder="{{ __('Mail...') }}"
                           class="w-full rounded-l bg-white px-3 py-1.5 text-gray-800 placeholder-gray-400">
                    <button type="submit" class="rounded-r bg-[#5f5f5f] px-3 text-white hover:bg-[#6e6e6e]" aria-label="{{ __('S’inscrire') }}">
                        <x-espace.icone nom="lien" class="h-4 w-4" />
                    </button>
                </form>

                <div class="mt-4 flex gap-2">
                    @foreach ([
                        'Instagram' => 'https://www.instagram.com/ultra.book/',
                        'Facebook' => 'https://www.facebook.com/ultrabook.fr',
                        'Twitter' => 'https://twitter.com/ultra_book',
                        'Pinterest' => 'https://www.pinterest.com/ultrabook001/',
                    ] as $reseau => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="flex h-7 w-7 items-center justify-center rounded-full bg-white/80 text-[11px] font-bold text-[#4a4a4a] hover:bg-white">
                            {{ mb_substr($reseau, 0, 1) }}<span class="sr-only">{{ $reseau }}</span>
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
