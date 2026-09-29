<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

/** Champs du formulaire de connexion du portail (`#login_form`) : identifiant ou adresse mail. */
class ConnexionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'pass' => ['required', 'string', 'max:255'],
            'g-recaptcha-response' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'login' => __('identifiant ou adresse mail'),
            'pass' => __('mot de passe'),
        ];
    }
}
