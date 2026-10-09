{{-- Pied de page commun : portail, espace creatif et compte visiteur (maquette « Footer ultra-book »). --}}
@php
    $rubriques = [
        [__('Zoom'), null, 'btn_zoom'],
        [__('Tendances, Actualités'), null, 'btn_actu'],
        [__('Derniers :marque', ['marque' => $marque->nom]), lien('home').'#bloc_ultrabook_href', null],
    ];
    $annuaires = [
        [__('Annuaire des écoles'), 'http://www.ultra-book.fr/ecoles/'],
        [__('Illustrateurs freelances'), 'https://www.les-illustrateurs.com'],
        [__('Graphistes freelances'), '/meilleurs-graphistes'],
        [__('Webdesigners freelances'), '/webdesigner-freelance'],
        [__('Développeurs freelances'), '/developpeur-freelance'],
    ];
    $autresSites = [
        ['Tesli', 'https://www.tesli.fr', 'Créez votre site en un clic, par IA'],
        ['La Belle Illustration', 'https://www.la-belle-illustration.fr', 'La boutique d’illustrations à vendre'],
        ['UB-diffusion', 'https://www.les-illustrateurs.com', 'La plateforme créative pour vendre vos créations'],
    ];
    $reseaux = [
        ['Instagram', 'instagram', 'https://www.instagram.com/ultra.book/'],
        ['Facebook', 'facebook', 'https://www.facebook.com/ultrabook.fr'],
        ['Twitter', 'twitter', 'https://twitter.com/ultra_book'],
        ['Pinterest', 'pinterest', 'https://www.pinterest.com/ultrabook001/'],
    ];
@endphp

<footer class="pied-footer" style="color:#f2f0ed;border-top:1px solid #2a2928">
    <style>
        /* Fond #141414 inchange, degrade en diagonale : 10 % plus fonce
           (#121212) en haut a gauche vers 10 % plus clair (#2c2c2c) en bas
           a droite, la couleur de base au milieu. */
        .pied-footer { background: linear-gradient(135deg, #121212 0%, #141414 50%, #2c2c2c 100%); }
        .pied-footer, .pied-footer * { box-sizing: border-box; font-family: 'Source Sans Pro', sans-serif; }
        .pied-footer a { color: #f2f0ed; text-decoration: none; }
        .pied-footer a:hover { color: #fff; }
        .pied-footer input::placeholder { color: #8a8784; }
        .pied-footer h2, .pied-footer p { margin: 0; }

        .pied-footer .pf-conteneur {
            max-width: 1280px; margin: 0 auto; box-sizing: border-box;
            padding: clamp(40px,5vw,72px) clamp(20px,4vw,56px) 32px;
            display: flex; flex-direction: column; gap: 48px;
        }
        .pied-footer .pf-haut {
            display: flex; flex-wrap: wrap; gap: 40px 64px; align-items: flex-start;
            justify-content: space-between; padding-bottom: 48px; border-bottom: 1px solid #2a2928;
        }
        .pied-footer .pf-marque { flex: 1 1 360px; max-width: 520px; display: flex; flex-direction: column; align-items: flex-start; gap: 16px; }
        .pied-footer .pf-logo { display: block; height: 32px; width: auto; margin: 0; }
        .pied-footer .pf-intro { font-size: 15px; line-height: 1.6; color: #bdbab6; }

        .pied-footer .pf-newsletter { flex: 1 1 320px; max-width: 420px; display: flex; flex-direction: column; gap: 14px; }
        .pied-footer .pf-titre-section { font-size: 13px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #8a8784; }
        .pied-footer .pf-newsletter-accroche { font-size: 17px; font-weight: 600; }
        .pied-footer .pf-form-newsletter {
            display: flex; align-items: center; background: #f2f0ed; border-radius: 999px; padding: 4px 4px 4px 16px;
        }
        .pied-footer .pf-form-newsletter input {
            flex: 1; min-width: 0; border: none; background: transparent; padding: 10px 12px;
            font: 400 15px 'Source Sans Pro', sans-serif; color: #141414; outline: none;
        }
        .pied-footer .pf-form-newsletter button {
            align-self: stretch; border: none; border-radius: 999px; background: #141414; color: #f2f0ed;
            padding: 0 18px; font: 600 14px 'Source Sans Pro', sans-serif; cursor: pointer; transition: background .2s;
        }
        .pied-footer .pf-form-newsletter button:hover { background: oklch(0.62 0.2 25); }
        .pied-footer .pf-retour {
            margin: 0; padding: 12px 16px; border-radius: 12px; background: #1c1b1a; border: 1px solid #2a2928; font-size: 15px;
        }
        .pied-footer .pf-nospam { font-size: 13px; color: #8a8784; text-decoration: underline; text-underline-offset: 3px; }
        .pied-footer .pf-nospam:hover { color: #f2f0ed; }

        .pied-footer .pf-grille {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 40px 48px;
        }
        .pied-footer .pf-nav { display: flex; flex-direction: column; gap: 16px; }
        .pied-footer .pf-liens { display: flex; flex-direction: column; gap: 10px; font-size: 15px; }
        .pied-footer .pf-liens > a { color: #d6d3cf; }
        .pied-footer .pf-lien-contact { display: flex; flex-direction: column; gap: 2px; color: #d6d3cf; }
        .pied-footer .pf-lien-contact span { color: #8a8784; font-size: 14px; }
        .pied-footer .pf-lien-fleche { display: flex; align-items: center; gap: 8px; color: #d6d3cf; transition: gap .2s; }
        .pied-footer .pf-lien-fleche:hover { gap: 12px; }
        .pied-footer .pf-lien-fleche .pf-puce { color: oklch(0.62 0.2 25); }
        .pied-footer .pf-lien-site { display: flex; flex-direction: column; gap: 2px; font-weight: 600; color: #d6d3cf; }
        .pied-footer .pf-lien-site span { font-weight: 400; font-size: 14px; color: #8a8784; line-height: 1.35; }
        .pied-footer .pf-liens.pf-liens-sites { gap: 14px; }

        .pied-footer .pf-bas {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px;
            padding-top: 24px; border-top: 1px solid #2a2928;
        }
        .pied-footer .pf-reseaux { display: flex; gap: 10px; }
        .pied-footer .pf-reseau {
            width: 40px; height: 40px; border-radius: 999px; border: 1px solid #333130;
            display: flex; align-items: center; justify-content: center;
            color: #bdbab6; transition: background .2s, border-color .2s, color .2s;
        }
        .pied-footer .pf-reseau svg { width: 18px; height: 18px; fill: currentColor; }
        .pied-footer .pf-reseau:hover { background: #222120; border-color: #4a4846; color: #fff; }
        .pied-footer .pf-boutons { display: flex; flex-wrap: wrap; gap: 10px; }
        .pied-footer .pf-btn-contour {
            display: flex; align-items: center; gap: 8px; border: 1px solid #333130; border-radius: 999px;
            padding: 10px 18px; font-weight: 600; font-size: 14px; transition: background .2s, border-color .2s;
        }
        .pied-footer .pf-btn-contour:hover { background: #222120; border-color: #4a4846; }
        .pied-footer .pf-btn-plein {
            display: flex; align-items: center; gap: 8px; background: #f2f0ed; color: #141414 !important;
            border-radius: 999px; padding: 10px 20px; font-weight: 600; font-size: 14px; transition: background .2s;
        }
        .pied-footer .pf-btn-plein:hover { background: #fff; }
    </style>

    <div class="pf-conteneur">

        <div class="pf-haut">
            <div class="pf-marque">
                <img class="pf-logo" src="{{ $marque->logoClair }}" alt="{{ $marque->nom }}" @unless ($marque->estDefaut()) style="height:25.6px" @endunless>
                <p class="pf-intro">
                    {{ __('Depuis 2007 :marque vous permet de créer votre portfolio, d’y ajouter vos images, légendes, liens web, textes de présentation, et surtout de personnaliser votre espace book.', ['marque' => $marque->nom]) }}
                    {{ __('Les books sont classés par domaine, une sélection est faite tous les trois mois par des professionnels.') }}
                </p>
            </div>

            <div class="pf-newsletter" x-data="newsletter">
                <h2 class="pf-titre-section">Newsletter</h2>
                <p class="pf-newsletter-accroche">{{ __('Les dernières sélections du mois') }}</p>

                <form class="pf-form-newsletter" action="{{ route('newsletter.inscription') }}" @submit.prevent="envoyer($el)" x-show="! message" x-cloak>
                    @csrf
                    <svg width="18" height="14" viewBox="0 0 18 14" style="flex:none;color:#6e6b68">
                        <rect x="1" y="1" width="16" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.4"/>
                        <path d="M1.5 2l7.5 6 7.5-6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                    </svg>
                    <input type="email" name="mail" required placeholder="{{ __('Votre e-mail') }}">
                    <button type="submit">{{ __('S\'inscrire') }}</button>
                </form>
                <p class="pf-retour" x-show="message" x-transition.opacity.duration.300ms x-cloak>
                    <span :style="{ color: erreur ? '#ff6b6b' : '#8ce99a' }" x-text="message"></span>
                </p>
                <a href="/doc/mentions-legales" class="pf-nospam">{{ __('Confidentialité, sécurité et absence de spam') }}</a>
            </div>
        </div>

        <div class="pf-grille">
            <nav class="pf-nav">
                <h2 class="pf-titre-section">{{ __('Plateforme portfolio') }}</h2>
                <div class="pf-liens">
                    <a class="pf-lien-contact" href="mailto:{{ $marque->email }}?subject={{ rawurlencode(__('Aide').' '.$marque->nom) }}&body={{ rawurlencode(__('Indiquez l’adresse de votre portfolio, merci.')) }}">
                        {{ __('Contact/aide') }}<span>{{ $marque->email }}</span>
                    </a>
                    <a href="/doc/doc">{{ __('Documentation') }}</a>
                    <a href="/doc/les-formules-ultra-book">{{ __('Formules et tarifs') }}</a>
                    <a href="/doc/questions-frequentes-2">{{ __('Questions fréquentes') }}</a>
                    <a href="/doc/mentions-legales">{{ __('Mentions légales') }}</a>
                </div>
            </nav>

            <nav class="pf-nav">
                <h2 class="pf-titre-section">{{ __('Rubriques') }}</h2>
                <div class="pf-liens">
                    @foreach ($rubriques as [$libelle, $url, $classe])
                        <a @if ($url) href="{{ $url }}" @endif @if ($classe) class="{{ $classe }}" @endif>{{ $libelle }}</a>
                    @endforeach
                </div>
            </nav>

            <nav class="pf-nav">
                <h2 class="pf-titre-section">{{ __('Annuaires') }}</h2>
                <div class="pf-liens">
                    @foreach ($annuaires as [$libelle, $url])
                        <a href="{{ $url }}" class="pf-lien-fleche"><span class="pf-puce">→</span>{{ $libelle }}</a>
                    @endforeach
                </div>
            </nav>

            @if ($marque->estDefaut())
                <nav class="pf-nav">
                    <h2 class="pf-titre-section">Les sites</h2>
                    <div class="pf-liens pf-liens-sites">
                        @foreach ($autresSites as [$nom, $url, $description])
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="pf-lien-site">{{ $nom }}<span>{{ $description }}</span></a>
                        @endforeach
                    </div>
                </nav>
            @endif
        </div>

        <div class="pf-bas">
            <div class="pf-reseaux">
                @foreach ($reseaux as [$label, $icone, $url])
                    <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}" class="pf-reseau">
                        <x-portail.icone-reseau :nom="$icone" />
                    </a>
                @endforeach
            </div>
            <div class="pf-boutons">
                <a href="https://www.dustfolio.com/accueil" class="pf-btn-contour">Dustfolio <span>→</span></a>
                <a href="https://www.creer-un-book.com" title="{{ __('Comment créer un book') }}" class="pf-btn-plein">{{ __('Créez un book') }} <span>→</span></a>
            </div>
        </div>
    </div>
</footer>
