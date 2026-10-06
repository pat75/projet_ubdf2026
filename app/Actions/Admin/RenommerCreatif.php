<?php

namespace App\Actions\Admin;

use App\Models\User;
use App\Services\Auth\Inscription;
use App\Support\DossierBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

/**
 * Change l'identifiant d'un createur (_admin_change_book_name.php du
 * legacy). Le login est l'adresse du book : le sous-domaine suit, et
 * l'ancien cesse de repondre. Les tables liees pointent sur user_id, il
 * ne reste qu'a deplacer le dossier disque.
 */
class RenommerCreatif
{
    public function __construct(private readonly Inscription $inscription)
    {
    }

    public function __invoke(User $creatif, string $nouveau): void
    {
        $nouveau = mb_strtolower(trim($nouveau));

        if ($nouveau === $creatif->login) {
            return;
        }

        if (! $this->inscription->loginDisponible($nouveau)) {
            throw ValidationException::withMessages([
                'login' => __('Identifiant indisponible : déjà pris, réservé, ou contenant autre chose que lettres, chiffres, - et _.'),
            ]);
        }

        $ancien = $creatif->login;
        $source = DossierBook::chemin($ancien);
        $cible = DossierBook::chemin($nouveau);

        DB::transaction(function () use ($creatif, $nouveau, $source, $cible) {
            $creatif->update(['login' => $nouveau]);

            // Dans la transaction : si le deplacement echoue, le login revient.
            if (File::isDirectory($source)) {
                File::ensureDirectoryExists(dirname($cible));

                if (! File::moveDirectory($source, $cible)) {
                    throw new \RuntimeException("Déplacement du dossier impossible : {$source}");
                }
            }
        });
    }
}
