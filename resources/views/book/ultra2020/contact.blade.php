{{--
    Page contact (ex-contact.tlp.php) : titre et pied saisis par le createur,
    formulaire envoye a BookController::envoyer (x-data="contactBook",
    resources/js/book/contact.js).
--}}
@extends('book.ultra2020.layout')

@section('contenu')
    <article @class(['mx-auto max-w-xl text-left', 'md:mx-0' => $vue->zen()])>
        @if ($vue->texte('contact_titre') !== '' || $vue->edition())
            <div class="mb-8"><x-book.texte-editable cle="contact_titre" tag="h1" :edition="$vue->edition()"
                class="font-titre text-[26px] font-semibold leading-tight text-book-texte2 md:text-[32px]">{!! $vue->texte('contact_titre') !!}</x-book.texte-editable></div>
        @endif

        @include('book.commun._formulaire-contact')

        @if ($vue->texte('contact_footer') !== '')
            <div class="contenu-page mt-12">{!! $vue->texte('contact_footer') !!}</div>
        @endif
    </article>
@endsection
