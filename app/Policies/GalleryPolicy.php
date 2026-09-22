<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    /** Un creatif ne gere que ses propres galeries. */
    public function update(User $user, Gallery $gallery): bool
    {
        return $gallery->user_id === $user->id;
    }
}
