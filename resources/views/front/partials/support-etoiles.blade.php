{{-- Note de satisfaction (1 a 5 etoiles), rien si le client n'a pas note. --}}
@if ($note)
<span class="leading-none text-[#f5a623]" style="font-size:{{ $taille ?? 24 }}px" aria-label="{{ $note }} / 5">{{ str_repeat('★', $note) }}<span class="text-[#ccc]">{{ str_repeat('★', 5 - $note) }}</span></span>
@endif
