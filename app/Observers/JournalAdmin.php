<?php

namespace App\Observers;

use App\Models\AdminActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Journalise les ecritures faites depuis le back-office.
 *
 * Seules les actions d'un administrateur connecte sont retenues : ce qui
 * vient du site (inscription, paiement, envoi de visuel) n'a rien a faire
 * dans ce journal.
 */
class JournalAdmin
{
    /** Champs jamais recopies dans le journal. */
    private const SECRETS = ['password', 'remember_token'];

    public function created(Model $modele): void
    {
        $this->noter('created', $modele, []);
    }

    public function updated(Model $modele): void
    {
        $changements = [];

        foreach ($modele->getChanges() as $champ => $apres) {
            if ($champ === 'updated_at') {
                continue;
            }

            $changements[$champ] = in_array($champ, self::SECRETS, true)
                ? ['avant' => '***', 'apres' => '***']
                : ['avant' => $modele->getOriginal($champ), 'apres' => $apres];
        }

        if ($changements !== []) {
            $this->noter('updated', $modele, $changements);
        }
    }

    public function deleted(Model $modele): void
    {
        $this->noter('deleted', $modele, []);
    }

    public function restored(Model $modele): void
    {
        $this->noter('restored', $modele, []);
    }

    private function noter(string $action, Model $modele, array $changements): void
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return;
        }

        AdminActivity::create([
            'admin_id' => $admin->getKey(),
            'admin_name' => $admin->name,
            'action' => $action,
            'subject_type' => $modele::class,
            'subject_id' => (string) $modele->getKey(),
            'subject_label' => $this->libelle($modele),
            'changes' => $changements ?: null,
            'ip' => request()->ip(),
        ]);
    }

    private function libelle(Model $modele): ?string
    {
        foreach (['login', 'code', 'name', 'number', 'title'] as $champ) {
            if (filled($modele->{$champ} ?? null)) {
                return (string) $modele->{$champ};
            }
        }

        return null;
    }
}
