<?php

namespace App\Services\Memo;

use App\Models\MemoBook;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Memo book : les books mis de cote d'un clic sur le coeur.
 *
 * Le proprietaire est un visiteur connecte (guard `visitor`) ou un
 * creatif connecte (guard `web`). Un anonyme n'a pas de memo en base : sa
 * selection reste dans le localStorage du navigateur jusqu'a la creation
 * de son compte, ou elle est versee ici (`fusionner`).
 */
class MemoBooks
{
    /** Plafond d'une selection : au-dela, ce n'est plus un memo. */
    public const MAX = 500;

    /**
     * Le proprietaire courant, quel que soit le guard.
     *
     * Lu sur les deux guards explicitement : une requete Livewire
     * (/livewire/update) ne repasse pas par le middleware `auth:web,visitor`
     * de la page, et le guard par defaut y redevient `web`.
     */
    public function proprietaire(): User|Visitor|null
    {
        return auth('web')->user() ?? auth('visitor')->user();
    }

    public function ajouter(User|Visitor $proprietaire, User $book): void
    {
        if ($this->compter($proprietaire) >= self::MAX) {
            return;
        }

        MemoBook::query()->firstOrCreate([$this->colonne($proprietaire) => $proprietaire->id, 'book_id' => $book->id]);
    }

    public function retirer(User|Visitor $proprietaire, User $book): void
    {
        $this->requete($proprietaire)->where('book_id', $book->id)->delete();
    }

    public function compter(User|Visitor $proprietaire): int
    {
        return $this->requete($proprietaire)->count();
    }

    /** @return list<string> logins des books memorises, du plus recent au plus ancien */
    public function logins(User|Visitor $proprietaire): array
    {
        return $this->requete($proprietaire)
            ->join('users', 'users.id', '=', 'memo_books.book_id')
            ->whereNull('users.deleted_at')
            ->orderByDesc('memo_books.created_at')
            ->orderByDesc('memo_books.id')
            ->pluck('users.login')
            ->all();
    }

    /**
     * Les books memorises, du plus recent au plus ancien, filtres sur le
     * nom, le prenom, la societe ou l'identifiant (chaque mot saisi).
     *
     * @return Collection<int, User>
     */
    public function books(User|Visitor $proprietaire, string $recherche = '', ?int $limite = null): Collection
    {
        $recherche = trim($recherche);

        return User::query()
            ->select('users.*', 'memo_books.created_at as memorise_le')
            ->join('memo_books', 'memo_books.book_id', '=', 'users.id')
            ->where('memo_books.'.$this->colonne($proprietaire), $proprietaire->id)
            ->when($recherche !== '', function (Builder $q) use ($recherche) {
                // Chaque mot doit se retrouver dans l'un des champs : « jean
                // dupont » trouve Jean Dupont, prenom et nom separes.
                foreach (preg_split('/\s+/', $recherche) as $mot) {
                    $motif = '%'.addcslashes($mot, '%_\\').'%';

                    $q->where(fn (Builder $q) => $q
                        ->where('users.firstname', 'like', $motif)
                        ->orWhere('users.lastname', 'like', $motif)
                        ->orWhere('users.company', 'like', $motif)
                        ->orWhere('users.login', 'like', $motif));
                }
            })
            ->with(['category', 'media' => fn ($q) => $q->published()->horsProteges()->whereNot('filename', '')->orderBy('position')->limit(1)])
            ->orderByDesc('memo_books.created_at')
            ->orderByDesc('memo_books.id')
            ->when($limite, fn (Builder $q) => $q->limit($limite))
            ->get();
    }

    /**
     * Verse une selection du localStorage dans le memo en base.
     *
     * Les logins inconnus sont ignores sans bruit : la selection du
     * navigateur a pu survivre a la suppression d'un book.
     *
     * @param  list<string>  $logins
     */
    public function fusionner(User|Visitor $proprietaire, array $logins): void
    {
        $logins = array_slice(array_unique(array_map(fn ($l) => mb_strtolower(trim((string) $l)), $logins)), 0, self::MAX);

        User::query()->whereIn('login', $logins)->get()
            ->each(fn (User $book) => $this->ajouter($proprietaire, $book));
    }

    private function requete(User|Visitor $proprietaire): Builder
    {
        return MemoBook::query()->where($this->colonne($proprietaire), $proprietaire->id);
    }

    private function colonne(User|Visitor $proprietaire): string
    {
        return $proprietaire instanceof Visitor ? 'visitor_id' : 'user_id';
    }
}
