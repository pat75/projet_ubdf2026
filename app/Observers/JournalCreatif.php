<?php

namespace App\Observers;

use App\Http\Controllers\Admin\PriseIdentiteController;
use App\Models\CreatifActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Journalise les ecritures d'un createur connecte a son espace : la matiere
 * du Coach crea. Ce que fait un administrateur en prise d'identite n'y
 * entre pas, il se retrouve deja dans le journal admin.
 */
class JournalCreatif
{
    /** Calculs automatiques, pas un geste du createur. */
    private const IGNORES = ['updated_at', 'last_login_at', 'remember_token', 'password', 'storage_used', 'media_count',
        'ai_title', 'ai_description', 'ai_status', 'ai_model', 'analysed_at', 'position', 'media_order', 'page_order'];

    public function created(Model $modele): void
    {
        $this->noter('created', $modele, null);
    }

    public function updated(Model $modele): void
    {
        $champs = array_diff(array_keys($modele->getChanges()), self::IGNORES);

        if ($champs !== []) {
            // Les noms des champs suffisent : pas de copie des valeurs (donnees personnelles).
            $this->noter('updated', $modele, array_fill_keys($champs, true));
        }
    }

    public function deleted(Model $modele): void
    {
        $this->noter('deleted', $modele, null);
    }

    public function restored(Model $modele): void
    {
        $this->noter('restored', $modele, null);
    }

    private function noter(string $action, Model $modele, ?array $changements): void
    {
        $creatif = Auth::guard('web')->user();

        if (! $creatif instanceof User || session()->has(PriseIdentiteController::SESSION)) {
            return;
        }

        CreatifActivity::create([
            'user_id' => $creatif->id,
            'action' => $action,
            'subject_type' => $modele::class,
            'subject_id' => (string) $modele->getKey(),
            'subject_label' => mb_substr((string) ($modele->title ?? $modele->name ?? $modele->login ?? ''), 0, 255) ?: null,
            'changes' => $changements,
            'ip' => request()->ip(),
        ]);
    }
}
