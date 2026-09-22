<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $creatif->fullName() }} | {{ $marque->nom }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 12px; color: #eee; background: #222; }
        a { color: inherit; text-decoration: none; }
        .mb { padding: 10px; }
        .mb_tete { display: flex; gap: 10px; align-items: center; }
        .mb_tete img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; background: #444; }
        .mb h1 { font-size: 15px; margin: 0 0 4px; }
        .mb_lien { color: #aaa; font-size: 11px; }
        .mb_tag { display: inline-block; margin-top: 4px; padding: 1px 6px; border-radius: 3px; background: #444; font-size: 10px; }
        .mb_grande { margin: 10px 0 6px; }
        .mb_grande img { width: 100%; height: auto; display: block; }
        .mb_vignettes { display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px; list-style: none; margin: 0; padding: 0; }
        .mb_vignettes img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; cursor: pointer; opacity: .7; }
        .mb_vignettes img:hover, .mb_vignettes img.on { opacity: 1; }
        .mb_pied { margin-top: 8px; font-size: 10px; color: #999; }
    </style>
</head>
<body>
<div class="mb">
    <div class="mb_tete">
        <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener">
            <img src="{{ $creatif->thumbnailUrl('carre_183') ?? asset('img_front/_ultra_book_62x62.gif') }}" alt="{{ $creatif->fullName() }}">
        </a>
        <div>
            <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"><h1>{{ $creatif->fullName() }}</h1></a>
            <a class="mb_lien" href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener">{{ preg_replace('#^https?://#', '', $creatif->bookUrl()) }}</a><br>
            @if ($creatif->category) <span class="mb_tag">{{ $creatif->category->name }}</span> @endif
            @if ($creatif->city) <span class="mb_tag">{{ $creatif->city }}</span> @endif
        </div>
    </div>

    @if ($visuels->isNotEmpty())
        <div class="mb_grande">
            <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"><img id="mb_grande" src="{{ $visuels[0]->url('iph_medium') }}" alt="{{ $visuels[0]->title }}"></a>
        </div>
        <ul class="mb_vignettes">
            @foreach ($visuels as $i => $v)
                <li><img src="{{ $v->url('iph_small') }}" data-grande="{{ $v->url('iph_medium') }}" alt="{{ $v->title }}" @class(['on' => $i === 0])></li>
            @endforeach
        </ul>
    @endif

    @if ($avecPied)
        <div class="mb_pied"><a href="{{ lien('home') }}" target="_blank" rel="noopener">{{ $marque->nom }}</a></div>
    @endif
</div>
<script>
    document.querySelectorAll('.mb_vignettes img').forEach((v) => v.addEventListener('click', () => {
        document.getElementById('mb_grande').src = v.dataset.grande;
        document.querySelectorAll('.mb_vignettes img').forEach((n) => n.classList.toggle('on', n === v));
    }));
</script>
</body>
</html>
