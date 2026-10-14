<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** Rubrique ou page (bio). Memes bornes que l'espace (Livewire Pages). */
class PageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // 100 pour une rubrique, 255 pour une page : le controleur borne la rubrique.
            'titre' => ['sometimes', 'string', 'min:1', 'max:255'],
            'publiee' => ['sometimes', 'boolean'],
            'contenu' => ['sometimes', 'nullable', 'string', 'max:200000'],
        ];
    }
}
