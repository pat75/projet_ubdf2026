<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Connexion d'une application (mobile, desktop) : delivre un jeton par appareil. */
class JetonRequest extends FormRequest
{
    /**
     * Droits qu'un jeton peut porter. Jamais « * » : une application
     * demande ce dont elle a besoin, et le creatif voit ce qu'il accorde.
     */
    public const DROITS = [
        'compte:lire', 'compte:ecrire',
        'images:lire', 'images:ecrire',
        'pages:lire', 'pages:ecrire',
        'messages:lire', 'messages:ecrire',
        'memo:lire', 'memo:ecrire',
        'stats:lire',
    ];

    public function rules(): array
    {
        return [
            'identifiant' => ['required', 'string', 'max:255'],
            'mot_de_passe' => ['required', 'string', 'max:255'],
            'appareil' => ['required', 'string', 'max:100'],
            'droits' => ['sometimes', 'array', 'min:1'],
            'droits.*' => ['string', Rule::in(self::DROITS)],
        ];
    }

    /** @return list<string> */
    public function droits(): array
    {
        return array_values(array_unique($this->validated('droits', self::DROITS)));
    }
}
