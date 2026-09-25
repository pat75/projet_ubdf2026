{{-- Menu des pages de contenu ($menu = VueUltra2020::menuPages()), ex-ultra2020__front_nav_2020. --}}
<ul class="flex flex-col gap-3 text-left">
    @foreach ($menu['rubriques'] as $rubrique)
        <li>
            @unless ($menu['seule'])
                <a href="{{ $rubrique['url'] }}" data-curseur @class([
                    'font-titre text-[14px] text-book-texte2 hover:text-book-texte',
                    'font-bold text-book-texte' => $rubrique['active'],
                    'font-semibold' => ! $rubrique['active'],
                ])>{{ $rubrique['nom'] }}</a>
            @endunless
            <ul @class(['flex flex-col gap-1.5', 'mt-1.5 pl-3' => ! $menu['seule']])>
                @foreach ($rubrique['pages'] as $page)
                    <li>
                        <a href="{{ $page['url'] }}" data-curseur @if ($page['active']) aria-current="page" @endif @class([
                            'text-[15px] font-light text-book-texte3 hover:text-book-texte',
                            'font-semibold text-book-texte' => $page['active'],
                        ])>{{ $page['titre'] }}</a>
                    </li>
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>
