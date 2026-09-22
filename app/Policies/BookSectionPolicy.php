<?php

namespace App\Policies;

use App\Models\BookSection;
use App\Models\User;

class BookSectionPolicy
{
    public function update(User $user, BookSection $section): bool
    {
        return $section->user_id === $user->id;
    }
}
