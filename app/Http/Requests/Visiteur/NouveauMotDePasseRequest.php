<?php

namespace App\Http\Requests\Visiteur;

use Illuminate\Foundation\Http\FormRequest;

/** Nouveau mot de passe d'un compte visiteur, depuis le lien du mail. */
class NouveauMotDePasseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }
}
