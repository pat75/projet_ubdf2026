<?php

namespace App\Http\Requests\Api;

use App\Livewire\Espace\Compte;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Champs du compte modifiables par l'API : la liste blanche et les regles
 * de l'espace (Compte::CHAMPS), sous leurs noms d'API. Adresse mail et mot
 * de passe restent reserves au web.
 */
class CompteRequest extends FormRequest
{
    /** Nom d'API => colonne. */
    public const COLONNES = [
        'prenom' => 'firstname',
        'nom' => 'lastname',
        'societe' => 'company',
        'adresse' => 'address',
        'code_postal' => 'zipcode',
        'ville' => 'city',
        'pays' => 'country',
        'telephone' => 'phone',
        'mobile' => 'mobile',
        'site' => 'website',
        'facebook' => 'facebook_url',
        'instagram' => 'instagram_url',
        'twitter' => 'twitter_url',
        'statut' => 'status',
        'categorie' => 'category_id',
        'accepte_sms' => 'accepts_sms',
    ];

    public function rules(): array
    {
        $regles = [];

        foreach (self::COLONNES as $api => $colonne) {
            if (isset(Compte::CHAMPS[$colonne])) {
                $regles[$api] = 'sometimes|'.Compte::CHAMPS[$colonne];
            }
        }

        return $regles + [
            'statut' => ['sometimes', 'nullable', Rule::in(config('ubdf.statuts'))],
            'categorie' => ['sometimes', 'nullable', Rule::exists('categories', 'id')],
            'accepte_sms' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, mixed> colonnes => valeurs, vides ramenees a null */
    public function colonnes(): array
    {
        $colonnes = [];

        foreach ($this->validated() as $api => $valeur) {
            $valeur = is_string($valeur) ? (trim($valeur) ?: null) : $valeur;
            $colonnes[self::COLONNES[$api]] = $valeur;
        }

        return $colonnes;
    }
}
