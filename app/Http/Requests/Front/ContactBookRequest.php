<?php

namespace App\Http\Requests\Front;

use App\Rules\CaptchaValide;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Formulaire de contact d'un book (page /contact de son sous-domaine).
 *
 * Noms de champs du formulaire d'origine (PFBC). La reponse d'echec prend
 * la forme que lit son JavaScript : `{errors: [...]}`.
 */
class ContactBookRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fm_contact_nom_prenom' => ['nullable', 'string', 'max:255'],
            'fm_contact_mail' => ['required', 'string', 'email:rfc', 'max:255'],
            'fm_contact_message' => ['required', 'string', 'min:10', 'max:5000'],
            // Captcha local (App\Services\Captcha\Captcha), a la place du reCAPTCHA.
            'captcha' => ['required', 'string', new CaptchaValide('contact_book')],
        ];
    }

    public function attributes(): array
    {
        return [
            'fm_contact_nom_prenom' => __('nom'),
            'fm_contact_mail' => __('adresse mail'),
            'fm_contact_message' => __('message'),
            'captcha' => __('code de sécurité'),
        ];
    }

    protected function failedValidation(Validator $validateur): void
    {
        throw new HttpResponseException(response()->json(['errors' => $validateur->errors()->all()]));
    }
}
