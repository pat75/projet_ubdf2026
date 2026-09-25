@extends('layouts.portail')

@section('body_class', 'page_accueil')

@push('scripts')
    {{-- Apparitions au defilement (x-apparition) : propres a l'accueil. --}}
    @vite(['resources/css/portail.css', 'resources/js/portail.js'])
@endpush

@section('content')
    <div x-data>
        @include('partials.accueil-hero')

        <div class="bloc_portfolios_">
            @foreach ($blocs as $bloc)
                <x-bloc-metier :slug="$bloc['slug']" :books="$bloc['books']" :total="$bloc['total']" />
            @endforeach
        </div>
    </div>
@endsection
