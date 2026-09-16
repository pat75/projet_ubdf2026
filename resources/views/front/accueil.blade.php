@extends('layouts.portail')

@section('body_class', 'page_accueil')

@section('content')
    <div class="bloc_portfolios_">
        @foreach ($blocs as $bloc)
            <x-bloc-metier :slug="$bloc['slug']" :books="$bloc['books']" :total="$bloc['total']" />
        @endforeach
    </div>
@endsection
