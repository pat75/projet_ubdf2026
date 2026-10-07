@props(['creatif'])

{{-- Legende d'une vignette d'image : mini avatar (initiales sinon) et nom du createur. --}}
<div class="extra content" style="display: flex; align-items: center; gap: 6px; min-width: 0;">
    @if ($avatar = $creatif->thumbnailUrl('carre_183'))
        <img src="{{ $avatar }}" alt="" width="22" height="22" loading="lazy" style="width: 22px; height: 22px; flex: none; border-radius: 50%; object-fit: cover;">
    @else
        <span style="width: 22px; height: 22px; flex: none; border-radius: 50%; background: #4a4d50; color: #fff; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center;">{{ mb_strtoupper(mb_substr($creatif->firstname ?: $creatif->login, 0, 1).mb_substr($creatif->lastname ?? '', 0, 1)) }}</span>
    @endif
    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .95em; color: #3a3633;">{{ $creatif->fullName() }}</span>
</div>
