<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Adresse inscrite a la newsletter depuis le portail. */
class NewsletterMail extends Model
{
    protected $fillable = ['email', 'ip'];
}
