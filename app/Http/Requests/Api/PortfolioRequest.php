<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** Creation ou modification d'un portfolio. Memes bornes que l'espace (Livewire Galeries). */
class PortfolioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'min:1', 'max:100'],
            'publie' => ['sometimes', 'boolean'],
            // Vide ou null : retire la protection.
            'mot_de_passe' => ['sometimes', 'nullable', 'string', 'min:4', 'max:50'],
        ];
    }
}
