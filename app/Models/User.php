<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'legacy_id', 'login', 'email', 'password', 'category_id', 'brand', 'locale',
        'firstname', 'lastname', 'company', 'civility', 'status',
        'address', 'zipcode', 'city', 'country', 'phone', 'mobile', 'latitude', 'longitude',
        'website', 'facebook_url', 'twitter_url', 'instagram_url', 'custom_domain',
        'is_published', 'in_directory', 'is_selected', 'is_available',
        'accepts_sms', 'shares_link',
        'plan', 'plan_started_at', 'plan_months',
        'storage_used', 'media_count',
        'signup_ip', 'signup_referer', 'admin_note',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'plan_started_at' => 'datetime',
            'is_published' => 'boolean',
            'in_directory' => 'boolean',
            'is_selected' => 'boolean',
            'is_available' => 'boolean',
            'accepts_sms' => 'boolean',
            'shares_link' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'login';
    }

    /** URL publique du book, sur son sous-domaine. */
    public function bookUrl(): string
    {
        return 'https://'.$this->login.'.'.config('ubdf.book_domain');
    }

    public function fullName(): string
    {
        return trim($this->firstname.' '.$this->lastname) ?: $this->login;
    }

    /** URL de la fiche du book sur le portail (format SEO du legacy). */
    public function portfolioUrl(): string
    {
        return route('portfolio.show', [
            'login' => $this->login,
            'slug' => Str::slug($this->fullName().'-'.($this->category?->slug ?? 'autre')),
        ]);
    }

    /** Vignette du creatif, affichee sur la carte du portail. */
    public function thumbnailUrl(): ?string
    {
        $thumbnail = $this->bookSetting?->thumbnail;

        return $thumbnail
            ? route('book.media', ['login' => $this->login, 'file' => basename($thumbnail)])
            : null;
    }

    /**
     * Identifiant public non devinable, utilise par le front pour les
     * appels de statistiques. Ne revele pas la cle primaire.
     */
    public function publicKey(): string
    {
        return sha1($this->login.'|'.config('app.key'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookSetting(): HasOne
    {
        return $this->hasOne(BookSetting::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(BookSection::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(BookArticle::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function visitStats(): HasMany
    {
        return $this->hasMany(VisitStat::class);
    }

    public function selections(): BelongsToMany
    {
        return $this->belongsToMany(Selection::class)
            ->withPivot(['sent_twitter', 'sent_instagram', 'sent_mail'])
            ->withTimestamps();
    }
}
