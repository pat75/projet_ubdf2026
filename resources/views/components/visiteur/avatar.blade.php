@props(['visiteur', 'taille' => 44])

{{-- Medaillon du visiteur : photo Google si elle existe, posee sur ses
     initiales (Visitor::initiales()), qui reapparaissent si la photo ne
     charge pas. Styles en ligne : sert au menu du portail (Semantic, sans
     Tailwind) comme a l'espace visiteur. --}}
<span {{ $attributes }} aria-hidden="true"
      style="position:relative;overflow:hidden;width:{{ $taille }}px;height:{{ $taille }}px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;background:{{ $visiteur->couleur() }};color:#fff;font-size:{{ round($taille * .34) }}px;font-weight:700;line-height:1">{{ $visiteur->initiales() }}@if ($visiteur->avatar_url)<img src="{{ $visiteur->avatar_url }}" alt="" referrerpolicy="no-referrer" onerror="this.remove()"
      style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">@endif</span>
