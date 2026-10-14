<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisuelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'portfolio_id' => $this->gallery_id,
            'titre' => $this->title,
            'legende' => $this->description,
            'video' => $this->video_url,
            'url' => $this->url(),
            'miniature' => $this->url('adm_medium'),
            'largeur' => $this->width,
            'hauteur' => $this->height,
            'poids' => $this->size,
            'publie' => $this->status === 'published',
        ];
    }
}
