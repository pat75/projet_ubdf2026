@extends('layouts.portail')

@section('title', $page->title.' | Ultra-book')
@section('body_class', 'page_cms')

@section('content')
    <div class="ui container bloc_cms">
        <div class="ui stackable grid">

            @if ($navigation->count() > 1)
                <div class="four wide column">
                    <nav class="ui vertical fluid menu sommaire_cms">
                        @foreach ($navigation as $lien)
                            <a class="item @if ($lien->is($page)) active @endif"
                               href="{{ route('cms.doc', $lien->slug) }}">{{ $lien->title }}</a>
                        @endforeach
                    </nav>
                </div>
            @endif

            <div class="{{ $navigation->count() > 1 ? 'twelve' : 'sixteen' }} wide column">
                <h1>{{ $page->title }}</h1>

                {{-- Contenu redige en interne dans l'ancien WordPress, importe
                     une fois. Il n'est pas alimente par des visiteurs. --}}
                <div class="contenu_cms">{!! $page->body !!}</div>
            </div>
        </div>
    </div>
@endsection
