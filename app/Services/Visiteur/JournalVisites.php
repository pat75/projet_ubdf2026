<?php

namespace App\Services\Visiteur;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

/** Dernieres visites de books d'un visiteur connecte (table visitor_book_visits). */
class JournalVisites
{
    public function noter(Visitor $visiteur, User $book): void
    {
        DB::table('visitor_book_visits')->upsert(
            [['visitor_id' => $visiteur->id, 'book_id' => $book->id, 'visited_at' => now()]],
            ['visitor_id', 'book_id'],
            ['visited_at'],
        );
    }
}
