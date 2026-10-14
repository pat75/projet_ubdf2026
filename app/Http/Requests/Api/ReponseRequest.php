<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** Reponse du creatif a une demande. Memes bornes que l'espace (Livewire Messages). */
class ReponseRequest extends FormRequest
{
    public function rules(): array
    {
        return ['texte' => ['required', 'string', 'min:2', 'max:5000']];
    }
}
