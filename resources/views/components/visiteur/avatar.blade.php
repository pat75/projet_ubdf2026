@props(['visiteur', 'taille' => 44])

{{-- Medaillon d'initiales du visiteur (Visitor::initiales()). Styles en
     ligne : sert au menu du portail (Semantic, sans Tailwind) comme a
     l'espace visiteur. --}}
<span {{ $attributes }} aria-hidden="true"
      style="width:{{ $taille }}px;height:{{ $taille }}px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;background:{{ $visiteur->couleur() }};color:#fff;font-size:{{ round($taille * .34) }}px;font-weight:700;line-height:1">{{ $visiteur->initiales() }}</span>
