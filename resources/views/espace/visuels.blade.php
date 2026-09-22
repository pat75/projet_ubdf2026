@extends('layouts.espace')

@section('title', $galerie->name)

@section('content')
    <livewire:espace.visuels :galerie="$galerie" />
@endsection
