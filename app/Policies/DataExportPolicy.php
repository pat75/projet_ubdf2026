<?php

namespace App\Policies;

use App\Models\DataExport;
use App\Models\User;

class DataExportPolicy
{
    public function view(User $user, DataExport $export): bool
    {
        return $export->user_id === $user->id;
    }
}
