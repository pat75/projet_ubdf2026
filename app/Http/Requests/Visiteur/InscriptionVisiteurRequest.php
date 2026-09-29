<?php

namespace App\Http\Requests\Visiteur;

use App\Services\Memo\MemoBooks;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Creation d'un compte visiteur depuis la fenetre du memo book.
 *
 * Une adresse deja portee par un creatif est refusee : a la connexion,
 * elle designerait deux comptes, et le creatif a deja son memo.
 */
class InscriptionVisiteurRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255',
                Rule::unique('visitors', 'email'),
                Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', Password::min(8)],
            'logins' => ['nullable', 'array', 'max:'.MemoBooks::MAX],
            'logins.*' => ['string', 'max:50'],
            'g-recaptcha-response' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => __('Un compte existe déjà avec cette adresse : connectez-vous.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => __('adresse mail'),
            'password' => __('mot de passe'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }
}
