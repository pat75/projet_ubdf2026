<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Portfolio du creatif. Le mot de passe n'en sort jamais : seul « protege » le signale. */
class PortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->name,
            'publie' => $this->status === 'published',
            'protege' => $this->estProtegee(),
            'position' => $this->position,
            'nb_visuels' => $this->whenCounted('media'),
            // Pose par le controleur, dans l'ordre du book (Portfolios::dansLOrdre).
            'visuels' => VisuelResource::collection($this->whenLoaded('visuelsOrdonnes')),
        ];
    }
}
