<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Profil du creatif expose aux applications : liste blanche, jamais le
 * modele brut (ni note d'administration, ni IP d'inscription, ni formule).
 */
class CreatifResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'prenom' => $this->firstname,
            'nom' => $this->lastname,
            'marque' => $this->brand,
            'langue' => $this->locale,
            'url_book' => $this->bookUrl(),
            'societe' => $this->company,
            'civilite' => $this->civility,
            'statut' => $this->status,
            'adresse' => $this->address,
            'code_postal' => $this->zipcode,
            'ville' => $this->city,
            'pays' => $this->country,
            'telephone' => $this->phone,
            'mobile' => $this->mobile,
            'site' => $this->website,
            'facebook' => $this->facebook_url,
            'twitter' => $this->twitter_url,
            'instagram' => $this->instagram_url,
            'disponible' => $this->is_available,
            'accepte_sms' => $this->accepts_sms,
            'dans_annuaire' => $this->in_directory,
            'nb_visuels' => $this->media_count,
            'stockage_utilise' => $this->storage_used,
        ];
    }
}
