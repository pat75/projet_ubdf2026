{{--
    Mot de passe d'un portfolio protege, aux couleurs du book (Ultra-frais
    et Ultra-zen) : meme en-tete et meme menu que les autres pages, le
    formulaire a la place de la mosaique. Le controleur pose `sansIndex`,
    `$galerie` et, apres un essai, `$erreur`.
--}}
@extends('book.ultra2020.layout')

@section('contenu')
    <div class="mx-auto mt-10 w-full max-w-sm text-center {{ $vue->zen() ? 'md:mx-0 md:text-left' : '' }}">
        <x-book.cadenas class="size-9 text-book-texte" />
        <h2 class="mt-4 font-titre text-[24px] font-light text-book-texte">{{ $galerie->name }}</h2>

        <form method="post" class="mt-6">
            @csrf
            <label for="mot_de_passe" class="block text-[14px] text-book-texte3">{{ __('Ce portfolio est protégé. Saisissez son mot de passe.') }}</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required autofocus autocomplete="off"
                   class="mt-2 h-[43px] w-full border border-book-filet bg-transparent px-3 text-[16px] text-book-texte focus:border-book-texte focus:outline-none">
            @if ($erreur)
                <p class="mt-3 text-[14px] text-[#a12a2a]" role="alert">{{ $erreur }}</p>
            @endif
            <button type="submit" data-curseur class="mt-3 h-[43px] w-full bg-black text-[15px] font-bold text-white hover:bg-[#333]">{{ __('Ouvrir le portfolio') }}</button>
        </form>

        <a href="/portfolio" data-curseur class="mt-6 inline-block text-[13px] text-book-texte3 hover:text-book-texte">{{ __('← Retour aux projets') }}</a>
    </div>
@endsection
