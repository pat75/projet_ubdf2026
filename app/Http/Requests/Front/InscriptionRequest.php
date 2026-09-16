<?php

namespace App\Http\Requests\Front;

use App\Services\Auth\Inscription;
use App\Support\Marque;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Champs du formulaire d'inscription (`#inscription_classic`).
 *
 * Les noms restent ceux du legacy : le JavaScript de 2019 poste le
 * formulaire tel quel.
 */
class InscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'us_login' => [
                'required', 'string', 'min:3', 'max:50',
                'regex:'.Inscription::MOTIF_LOGIN,
                Rule::unique('users', 'login'),
                /*
                 | Le login devient un sous-domaine. Le legacy ne consultait
                 | que la table des comptes : rien n'empechait d'ouvrir un
                 | book « www » ou « df », qui aurait masque le portail.
                 */
                function (string $attribut, mixed $valeur, \Closure $refuser): void {
                    if (Marque::sousDomaineReserve((string) $valeur)) {
                        $refuser(__('Cet identifiant est réservé.'));
                    }
                },
            ],
            /*
             | `email:rfc` et non `email:rfc,dns` : la resolution DNS ajoute
             | une latence a chaque inscription et refuse des adresses
             | legitimes dont le domaine ne repond pas au moment du test.
             */
            'us_mail' => ['required', 'string', 'email:rfc', 'max:255'],
            'us_pass' => ['required', 'string', 'min:8', 'max:255'],
            'us_nom' => ['required', 'string', 'max:120'],
            'us_type' => ['nullable', 'string', Rule::exists('categories', 'slug')],
            'us_licence' => ['accepted'],
            'g-recaptcha-response' => ['nullable', 'string'],
        ];
    }

    /**
     * Le JavaScript de 2019 attend `{error: true, error_msg: [...]}` et
     * ignore l'enveloppe 422 de Laravel. La reponse d'echec prend donc la
     * forme du legacy, tout en restant une ValidationException.
     */
    protected function failedValidation(Validator $validateur): void
    {
        throw new ValidationException($validateur, response()->json([
            'error' => true,
            'error_msg' => $validateur->errors()->all(),
        ]));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'us_login' => mb_strtolower(trim((string) $this->input('us_login'))),
        ]);
    }

    public function attributes(): array
    {
        return [
            'us_login' => __('identifiant'),
            'us_mail' => __('adresse mail'),
            'us_pass' => __('mot de passe'),
            'us_nom' => __('nom'),
            'us_type' => __('métier'),
            'us_licence' => __('conditions d\'utilisation'),
        ];
    }
}
