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
    $photo = $creatif->bookSetting?->thumbnail ? $creatif->thumbnailUrl() : null;

    $mots = preg_split('/\s+/', trim($creatif->fullName()), -1, PREG_SPLIT_NO_EMPTY) ?: [$creatif->login];
    $initiales = mb_strtoupper(count($mots) > 1
        ? mb_substr($mots[0], 0, 1).mb_substr(end($mots), 0, 1)
        : mb_substr($mots[0], 0, 2));

    // Meme palette et meme tirage que x-espace.vignette.
    $palette = ['#4285f4', '#ea4335', '#fbbc05', '#34a853', '#5e35b1', '#00897b',
        '#43a047', '#e53935', '#1e88e5', '#f4511e', '#3949ab', '#039be5'];
    $couleur = $palette[crc32($creatif->login) % count($palette)];

    $rond = 'width:44px;height:44px;border-radius:50%;flex-shrink:0;';
@endphp

<a href="{{ route(nom_route('espace')) }}" class="barre_createur"
   style="display:flex;align-items:center;gap:12px;color:#1b1b1b;text-decoration:none;font-family:'Source Sans 3','Source Sans Pro',sans-serif">
    @if ($photo)
        <img src="{{ $photo }}" alt="" style="{{ $rond }}object-fit:cover">
    @else
        <span aria-hidden="true"
              style="{{ $rond }}display:flex;align-items:center;justify-content:center;background:{{ $couleur }};color:#fff;font-size:14px;font-weight:700">{{ $initiales }}</span>
    @endif

    <span class="barre_createur_texte" style="line-height:1.2">
        <span style="display:block;font-size:17px;font-weight:600;white-space:nowrap">{{ $creatif->fullName() }}</span>
        <span style="display:block;font-size:13px;color:#777;white-space:nowrap">{{ $creatif->category?->name }}</span>
    </span>
</a>
