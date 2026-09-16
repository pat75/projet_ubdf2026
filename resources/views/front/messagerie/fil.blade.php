@extends('layouts.portail')

@section('title', $conversation->objet().' | Ultra-book')
@section('body_class', 'page_messagerie')

@section('content')
    <div class="ui container bloc_messagerie">

        <div class="bloc_titre">
            <h1>{{ $conversation->objet() }}</h1>
            <div class="sub_title">
                @if ($role === App\Services\Messagerie\Intermediation::PROPRIETAIRE)
                    {{ __('Demande de') }} <strong>{{ $conversation->sender_name }}</strong>
                    @if ($conversation->sender_company) — {{ $conversation->sender_company }} @endif
                @else
                    {{ __('Votre échange avec') }} <strong>{{ $conversation->user->fullName() }}</strong>
                @endif
            </div>

            @if ($conversation->request_detail)
                <div class="ui label">{{ $conversation->request_detail }}</div>
            @endif
        </div>

        @if (session('envoye'))
            <div class="ui positive message">{{ __('Votre réponse a été transmise.') }}</div>
        @endif

        @if ($conversation->is_spam && $role === App\Services\Messagerie\Intermediation::PROPRIETAIRE)
            <div class="ui warning message">
                {{ __('Cette demande présente les caractéristiques d’un message indésirable. Vérifiez-la avant d’y répondre.') }}
            </div>
        @endif

        <div class="ui comments fil_messages">
            @foreach ($conversation->messages as $message)
                <div class="comment {{ $message->from_owner ? 'du_creatif' : 'du_visiteur' }}">
                    <div class="content">
                        <span class="author">
                            {{ $message->from_owner ? $conversation->user->fullName() : $conversation->sender_name }}
                        </span>
                        <div class="metadata">
                            <span class="date">{{ $message->created_at->translatedFormat('j F Y, H:i') }}</span>
                        </div>
                        {{-- nl2br sur du texte deja echappe : le corps est
                             saisi par un inconnu, il ne peut jamais porter
                             de HTML. --}}
                        <div class="text">{!! nl2br(e($message->body)) !!}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="post"
              action="{{ route('messagerie.repondre', ['role' => $role, 'selector' => $conversation->selector, 'jeton' => $jeton]) }}"
              class="ui form reponse_fil">
            @csrf

            <div class="field @error('message') error @enderror">
                <textarea name="message" rows="5"
                          placeholder="{{ __('Votre réponse') }}">{{ old('message') }}</textarea>
                @error('message')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="ui teal button">{{ __('Envoyer') }}</button>
        </form>

        <p class="note_intermediation">
            {{ __('Les adresses électroniques ne sont jamais communiquées : les messages transitent par Ultra-book.') }}
        </p>
    </div>
@endsection
