<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

/** Champs du formulaire de connexion du portail (`#login_form`). */
class ConnexionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:50'],
            'pass' => ['required', 'string', 'max:255'],
            'g-recaptcha-response' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'login' => __('identifiant'),
            'pass' => __('mot de passe'),
        ];
    }
}
