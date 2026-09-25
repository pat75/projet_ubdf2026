@props(['creatif'])

{{--
 | Le createur connecte, tout a droite de la barre de tete : vignette
 | ronde, nom et metier, lien vers son espace.
 |
 | Le meme bloc sert au portail (Semantic UI, sans Tailwind) et a l'espace
 | (Tailwind, sans Semantic) : ses styles sont donc portes en ligne, pour
 | rendre a l'identique dans les deux, sans dependre de l'une ou l'autre
 | feuille.
--}}
@php
    $profil = app(\App\Services\Espace\AffichageProfil::class);
    $photo = $profil->photoUrl($creatif);
    $initiales = $profil->initiales($creatif);
    $couleur = $profil->couleur($creatif);

    $rond = 'width:44px;height:44px;border-radius:50%;flex-shrink:0;';
@endphp

<a href="{{ route(nom_route('espace')) }}" class="barre_createur"
   style="display:flex;align-items:center;gap:12px;color:#1b1b1b;text-decoration:none;font-family:'Source Sans 3','Source Sans Pro',sans-serif">
    @if ($photo)
        <img data-avatar-profil src="{{ $photo }}" alt="" style="{{ $rond }}object-fit:cover">
    @else
        <span data-avatar-profil aria-hidden="true"
              style="{{ $rond }}display:flex;align-items:center;justify-content:center;background:{{ $couleur }};color:#fff;font-size:14px;font-weight:700">{{ $initiales }}</span>
    @endif

    <span class="barre_createur_texte" style="line-height:1.2">
        <span style="display:block;font-size:17px;font-weight:600;white-space:nowrap">{{ $creatif->fullName() }}</span>
        <span style="display:block;font-size:13px;color:#777;white-space:nowrap">{{ $creatif->category?->name }}</span>
    </span>
</a>
