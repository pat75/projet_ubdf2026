@extends('layouts.espace')

@section('title', $page->title)

@section('content')
    <livewire:espace.editeur-page :page="$page" />
@endsection
