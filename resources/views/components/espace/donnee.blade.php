@props(['libelle'])

{{-- Une donnee relevee : son libelle au-dessus, en petit, et la valeur
     en dessous. Sert au bloc de facturation electronique. --}}
<div {{ $attributes }}>
    <dt class="text-[13px] text-ub-texte3">{{ $libelle }}</dt>
    <dd class="mt-0.5 font-semibold">{{ $slot }}</dd>
</div>
