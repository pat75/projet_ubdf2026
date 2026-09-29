<?php

namespace App\Livewire\Memo;

use App\Models\User;
use App\Services\Memo\MemoBooks;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Page « Mon memo book » : les books mis de cote, une recherche par nom,
 * et le retrait d'un book. Servie au visiteur comme au creatif connecte.
 */
class Liste extends Component
{
    #[Url(as: 'q', except: '')]
    public string $recherche = '';

    public function retirer(string $login, MemoBooks $memo): void
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);

        if ($book = User::query()->where('login', $login)->first()) {
            $memo->retirer($proprietaire, $book);
        }

        // Le compteur du menu suit.
        $this->dispatch('memo-change', total: $memo->compter($proprietaire));
    }

    public function render(MemoBooks $memo): View
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);

        return view('livewire.memo.liste', [
            'books' => $memo->books($proprietaire, mb_substr($this->recherche, 0, 100)),
            'total' => $memo->compter($proprietaire),
        ]);
    }
}
