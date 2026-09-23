<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable implements FilamentUser
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean'];
    }

    /** Un compte desactive ne peut plus entrer, sans etre supprime. */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active;
    }
}
