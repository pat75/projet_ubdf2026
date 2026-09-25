<?php

namespace App\Http\Requests\Front;

/**
 * Fin d'une creation de book par Google : Google a fourni le nom et
 * l'adresse mail, il reste l'adresse du book, le metier et les conditions.
 * Memes regles que le formulaire classique, sans mot de passe ni captcha.
 */
class GoogleInscriptionRequest extends InscriptionRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('google.inscription');
    }

    public function rules(): array
    {
        return array_intersect_key(parent::rules(), array_flip(['us_login', 'us_type', 'us_licence']));
    }

    /** Formulaire classique : retour a la page, erreurs en session. */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validateur): void
    {
        throw new \Illuminate\Validation\ValidationException($validateur);
    }
}
