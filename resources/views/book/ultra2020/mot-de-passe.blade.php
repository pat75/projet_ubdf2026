{{--
    Mot de passe d'un portfolio protege, aux couleurs du book (Ultra-frais
    et Ultra-zen) : meme en-tete et meme menu que les autres pages, le
    formulaire a la place de la mosaique. Le controleur pose `sansIndex`,
    `$galerie` et, apres un essai, `$erreur`.
--}}
@extends('book.ultra2020.layout')

@section('contenu')
    @include('book.commun._mot-de-passe', ['aligneGauche' => $vue->zen()])
@endsection
