{{--
 | Le selecteur clair / sombre / systeme vit d'ordinaire au fond du menu
 | utilisateur, ou personne ne le trouve. On le remonte dans la barre du
 | haut, a droite, ou il est visible d'un coup d'oeil.
 |
 | Masque sous 640 px : la barre y est deja chargee (logo, recherche,
 | compte) et le menu utilisateur conserve le meme reglage.
--}}
@if (filament()->hasDarkMode() && ! filament()->hasDarkModeForced())
    <div class="hidden sm:flex fi-topbar-theme-switcher items-center">
        <x-filament-panels::theme-switcher />
    </div>
@endif
