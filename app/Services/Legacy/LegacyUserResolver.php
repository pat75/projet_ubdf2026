<?php

namespace App\Services\Legacy;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Resolution des references utilisateur du legacy.
 *
 * Plusieurs tables designent le creatif par une colonne varchar qui contient
 * selon les lignes un login (« aalex »), un us_id numerique (« 1419 ») ou
 * rien du tout : ub2_contact_form.cf_us_id et ub2_intermediate_form.mf_us_id.
 * Cette classe absorbe cette heterogeneite et tient le compte des orphelins.
 */
final class LegacyUserResolver
{
    /** @var Collection<string, int> login → id local */
    private Collection $byLogin;

    /** @var Collection<int, int> us_id legacy → id local */
    private Collection $byLegacyId;

    /** Reference valide dans ub2020 mais hors de l'echantillon repris. */
    private int $outOfSample = 0;

    /** Reference ne correspondant a aucun compte, meme dans ub2020. */
    private int $orphans = 0;

    /** @var Collection<string, int> tous les logins de ub2020 */
    private Collection $legacyLogins;

    public function __construct()
    {
        $users = User::query()->select('id', 'login', 'legacy_id')->get();

        $this->byLogin = $users->pluck('id', 'login');
        $this->byLegacyId = $users->whereNotNull('legacy_id')->pluck('id', 'legacy_id');

        // Sert uniquement a distinguer « hors echantillon » de « orpheline ».
        $this->legacyLogins = DB::connection('legacy')->table('inc_user')
            ->pluck('us_id', 'us_login');
    }

    public function resolve(?string $reference): ?int
    {
        $reference = trim((string) $reference);

        if ($reference === '' || $reference === '0') {
            return null;
        }

        $id = $this->byLogin->get(mb_strtolower($reference))
            ?? (ctype_digit($reference) ? $this->byLegacyId->get((int) $reference) : null);

        if ($id === null) {
            $known = $this->legacyLogins->has(mb_strtolower($reference))
                || (ctype_digit($reference) && $this->legacyLogins->contains((int) $reference));

            $known ? $this->outOfSample++ : $this->orphans++;
        }

        return $id;
    }

    public function fromLegacyId(?int $legacyId): ?int
    {
        return $legacyId === null ? null : $this->byLegacyId->get($legacyId);
    }

    /** @return array<int, int> les us_id legacy repris */
    public function legacyIds(): array
    {
        return $this->byLegacyId->keys()->all();
    }

    /** References pointant vers un compte reel, non repris en developpement. */
    public function outOfSampleCount(): int
    {
        return $this->outOfSample;
    }

    /** References ne correspondant a aucun compte, meme dans ub2020. */
    public function orphanCount(): int
    {
        return $this->orphans;
    }
}
