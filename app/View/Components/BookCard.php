<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

class BookCard extends Component
{
    /**
     * @param  bool  $nouvelle  carte arrivant par defilement : elle est
     *                          masquee a l'insertion, puis revelee en fondu.
     */
    public function __construct(public User $book, public bool $nouvelle = false) {}

    /**
     * Charge utile lue par js_core_cards.js pour le panneau de zoom.
     * Les cles sont celles du front 2018 : ne pas renommer.
     *
     * Privee a dessein : Laravel expose les methodes publiques d'un composant
     * a sa vue, ou elles masquent une variable du meme nom. Une methode
     * publique « detail() » rendrait donc $detail inutilisable dans le
     * template, et les attributs data-* sortiraient vides.
     */
    private function detail(): array
    {
        return [
            'book_type' => $this->book->category?->name ?? '',
            'book_type_titre' => $this->book->status ?? '',
            'book_prenom_nom' => $this->book->fullName(),
            'book_ville' => $this->book->city ?? '',
            'book_pays' => $this->book->country ?? '',
            'book_lat' => (string) ($this->book->latitude ?? ''),
            'book_lng' => (string) ($this->book->longitude ?? ''),
            'book_statut' => $this->book->status ?? '',
            'book_bio' => $this->book->bookSetting?->bio_photo ?? '',
            'book_dispo' => $this->book->is_available ? 'true' : 'false',
            'book_key' => $this->book->publicKey(),
        ];
    }

    /** Visuels du diaporama de la carte. Privee, meme raison que detail(). */
    private function slider(): array
    {
        return [
            'book_img' => $this->book->media->map(fn ($media) => [
                'fichier' => $media->url(),
                'title' => $media->title ?? '',
            ])->values()->all(),
            'book_type' => '',
            'book_prenom_nom' => $this->book->fullName(),
        ];
    }

    public function render(): View
    {
        return view('components.book-card', [
            'detail' => $this->detail(),
            'slider' => $this->slider(),
            'nouvelle' => $this->nouvelle,
        ]);
    }
}
