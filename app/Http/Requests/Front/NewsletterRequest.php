<?php

namespace App\Http\Requests\Front;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/** Inscription a la newsletter (formulaires du pied de page et du menu). */
class NewsletterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mail' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['mail' => mb_strtolower(trim((string) $this->input('mail')))]);
    }

    // Meme format de reponse que le legacy : { error, message }.
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => true,
            'message' => __('Adresse mail invalide.'),
        ], 422));
    }
}
