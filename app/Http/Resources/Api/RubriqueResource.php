<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Rubrique de pages (bio) et ses pages, dans l'ordre du book. */
class RubriqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->title,
            'publiee' => $this->is_published,
            'privee' => $this->is_private,
            'pages' => $this->articles->map(fn ($page) => [
                'id' => $page->id,
                'titre' => $page->title,
                'publiee' => $page->status === 'published',
                'image' => $page->image,
                'contenu' => $page->body,
            ]),
        ];
    }
}
