{{-- Captcha rendu en SVG : ni GD ni fichier de police, et net a toute
     definition. Chaque lettre recoit sa rotation, son decalage et sa
     couleur, et deux courbes traversent l'image — de quoi gener un OCR
     simple sans gener la lecture. --}}
<?php
$largeur = (int) config('messagerie.captcha.largeur');
$hauteur = (int) config('messagerie.captcha.hauteur');
$lettres = str_split($code);
$pas = ($largeur - 24) / max(count($lettres), 1);
?>
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $largeur }}" height="{{ $hauteur }}"
     viewBox="0 0 {{ $largeur }} {{ $hauteur }}" role="img"
     aria-label="Code de sécurité à recopier">
    <rect width="100%" height="100%" fill="#f5f5f5"/>

    @for ($i = 0; $i < 3; $i++)
        <path d="M0 {{ random_int(6, $hauteur - 6) }} Q {{ $largeur / 2 }} {{ random_int(0, $hauteur) }} {{ $largeur }} {{ random_int(6, $hauteur - 6) }}"
              stroke="#{{ dechex(random_int(10, 13)) }}{{ dechex(random_int(10, 13)) }}{{ dechex(random_int(10, 13)) }}"
              stroke-width="1" fill="none"/>
    @endfor

    @foreach ($lettres as $rang => $lettre)
        <text x="{{ 14 + $rang * $pas }}" y="{{ random_int($hauteur - 14, $hauteur - 9) }}"
              font-family="Georgia, 'Times New Roman', serif"
              font-size="{{ random_int(22, 28) }}" font-weight="bold"
              fill="rgb({{ random_int(20, 90) }},{{ random_int(20, 90) }},{{ random_int(20, 120) }})"
              transform="rotate({{ random_int(-22, 22) }} {{ 14 + $rang * $pas }} {{ $hauteur - 12 }})">{{ $lettre }}</text>
    @endforeach

    @for ($i = 0; $i < 60; $i++)
        <circle cx="{{ random_int(0, $largeur) }}" cy="{{ random_int(0, $hauteur) }}" r="1"
                fill="#c8c8c8"/>
    @endfor
</svg>
