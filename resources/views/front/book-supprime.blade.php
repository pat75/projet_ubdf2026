@extends('layouts.portail')

@section('title', __('Book supprimé').' | '.$marque->nom)
@section('robots', 'noindex, nofollow')

{{-- Apres la suppression d'un book (SuppressionBookController) : confirmation,
     puis une note de 1 a 5 etoiles. Les etoiles se remplissent au survol,
     et restent remplies jusqu'a la note choisie. --}}
@section('content')
    <style>
        .book_supprime { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 56px 20px 80px; }
        .book_supprime h1 { margin: 0 0 12px; font-family: 'Lato', sans-serif; font-weight: 300; font-size: 34px; line-height: 1.2; }
        .book_supprime p { margin: 0 auto; max-width: 520px; font-size: 17px; line-height: 1.5; color: #555; }
        .book_supprime_etoiles { display: flex; gap: 6px; margin: 28px 0 8px; }
        .book_supprime_etoiles button { padding: 2px; background: none; border: 0; cursor: pointer; color: #d4d4d4; transition: color .15s, transform .15s; }
        .book_supprime_etoiles button:hover { transform: scale(1.12); }
        .book_supprime_etoiles button.pleine { color: #f5b301; }
        .book_supprime_etoiles svg { display: block; width: 40px; height: 40px; }
        .book_supprime .valider { margin-top: 20px; border-radius: 0; }
        .book_supprime .erreur { margin-top: 8px; color: #c0392b; font-size: 14px; }
        @@media (max-width: 767px) { .book_supprime h1 { font-size: 28px; } .book_supprime p { font-size: 15px; } }
    </style>

    <div class="ui container">
        <div class="book_supprime">
            <h1>{{ __('Votre book est supprimé') }}</h1>
            <p>
                <span style="display: block">{{ __('Vos données sont conservées quelque temps : écrivez-nous si vous changez d’avis.') }}</span>
            </p>

            @if (session('note_enregistree') || $compte->deletion_rating)
                <p style="margin-top: 28px">{{ __('Merci pour votre note.') }}</p>
            @else
                <p style="margin-top: 28px">{{ __('Vous pouvez nous laisser une note sur votre expérience.') }}</p>

                <form method="post" action="{{ $action }}" x-data="{ note: 0, survol: 0 }">
                    @csrf
                    <input type="hidden" name="note" :value="note">

                    <div class="book_supprime_etoiles" @mouseleave="survol = 0" role="radiogroup" aria-label="{{ __('Note') }}">
                        @foreach (range(1, 5) as $n)
                            <button type="button" @click="note = {{ $n }}" @mouseenter="survol = {{ $n }}"
                                    :class="(survol || note) >= {{ $n }} && 'pleine'"
                                    role="radio" :aria-checked="note === {{ $n }}"
                                    aria-label="{{ trans_choice(':n étoile|:n étoiles', $n, ['n' => $n]) }}">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8z"/></svg>
                            </button>
                        @endforeach
                    </div>

                    @error('note') <div class="erreur">{{ $message }}</div> @enderror

                    <button type="submit" class="ui black button valider" :disabled="! note">{{ __('Valider ma note') }}</button>
                </form>
            @endif
        </div>
    </div>
@endsection
