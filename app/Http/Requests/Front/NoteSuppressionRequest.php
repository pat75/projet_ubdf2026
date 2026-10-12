<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class NoteSuppressionRequest extends FormRequest
{
    /** Le lien signe tient lieu d'autorisation (middleware `signed`). */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['note' => ['required', 'integer', 'between:1,5']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['note.*' => __('Choisissez une note de 1 à 5 étoiles.')];
    }
}
