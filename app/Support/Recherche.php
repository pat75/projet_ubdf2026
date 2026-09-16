<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Criteres d'une recherche du portail.
 *
 * Le front 2018 les transporte a plat dans `tmp_data` (`q`, `anu_type`,
 * `recherche`, `flt_sel`, `flt_pro`, `suite`). Ces noms sont conserves a
 * l'entree — le JavaScript n'est pas reecrit avant la phase 9 — mais ne
 * ressortent pas au-dela de cette classe.
 */
final class Recherche
{
    public const MODES = ['mcles', 'pseudo'];

    public function __construct(
        public readonly string $q = '',
        public readonly string $mode = 'mcles',
        public readonly ?string $categorie = null,
        public readonly int $page = 0,
        public readonly string $brand = 'ub',
        public readonly bool $selection = false,
        public readonly bool $abonnes = false,
    ) {}

    /**
     * Les booleens arrivent du JavaScript en chaines « true » / « false ».
     */
    public static function depuisRequete(Request $request): self
    {
        $valide = fn (string $cle) => in_array($request->input($cle), [true, 'true', '1', 1], true);

        // « recherche » vient de l'appel JavaScript, « type_recherche » du
        // formulaire soumis sans JavaScript : le meme champ, deux noms.
        $mode = (string) ($request->input('recherche') ?: $request->input('type_recherche') ?: 'mcles');

        return new self(
            q: trim((string) $request->input('q', '')),
            mode: in_array($mode, self::MODES, true) ? $mode : 'mcles',
            categorie: $request->input('anu_type') ?: null,
            page: max(0, (int) $request->input('suite', 0)),
            brand: (string) $request->attributes->get('brand', 'ub'),
            selection: $valide('flt_sel'),
            abonnes: $valide('flt_pro'),
        );
    }

    public function page(int $page): self
    {
        return new self($this->q, $this->mode, $this->categorie, $page,
            $this->brand, $this->selection, $this->abonnes);
    }

    /**
     * @return list<string>
     */
    public function termes(): array
    {
        return MotsCles::decouper($this->q);
    }

    /**
     * Le legacy ignorait les requetes de moins de trois caracteres ; la
     * regle est conservee, elle evite de balayer la table pour « et ».
     */
    public function exploitable(): bool
    {
        return mb_strlen($this->q) >= 3 && $this->termes() !== [];
    }
}
