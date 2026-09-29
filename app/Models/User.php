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
        'legacy_id', 'login', 'email', 'password', 'google_id', 'category_id', 'brand', 'locale',
        'firstname', 'lastname', 'company', 'civility', 'status',
        'address', 'zipcode', 'city', 'country', 'phone', 'mobile', 'latitude', 'longitude',
        'website', 'facebook_url', 'twitter_url', 'instagram_url', 'custom_domain',
        'in_home_selection', 'home_selection_at', 'in_directory', 'is_selected', 'is_available',
        'accepts_sms', 'shares_link',
        'plan', 'plan_started_at', 'plan_months', 'plan_expires_at',
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
            'plan_expires_at' => 'datetime',
            'in_home_selection' => 'boolean',
            'home_selection_at' => 'datetime',
            'in_directory' => 'boolean',
            'is_selected' => 'boolean',
            'is_available' => 'boolean',
            'accepts_sms' => 'boolean',
            'shares_link' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        /*
         | L'echeance suit toujours la formule : un changement de duree ou de
         | date de depart, d'ou qu'il vienne (back-office compris), la
         | recalcule. `prolongerFormule()` la pose deja lui-meme.
         */
        static::saving(function (self $compte) {
            if (! $compte->isDirty(['plan', 'plan_started_at', 'plan_months'])) {
                return;
            }

            $compte->plan_expires_at = $compte->plan && $compte->plan_started_at && $compte->plan_months
                ? $compte->plan_started_at->copy()->addMonths($compte->plan_months)
                : null;
        });

        /*
         | La date de selection suit l'interrupteur : le cocher date la
         | selection de l'instant, le decocher efface la date. Une date
         | saisie a la main (selection anterieure, ou programmee) est
         | respectee : on ne l'ecrase jamais.
         */
        static::saving(function (self $compte) {
            if (! $compte->isDirty('in_home_selection')) {
                return;
            }

            if ($compte->in_home_selection) {
                $compte->home_selection_at ??= now();
            } else {
                $compte->home_selection_at = null;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'login';
    }

    /** URL publique du book, sur son sous-domaine. */
    public function bookUrl(): string
    {
        // Domaine des books de la marque du compte : un book Dustfolio n'a
        // qu'une adresse, sur le domaine Dustfolio.
        return 'https://'.$this->login.'.'.\App\Support\Marque::depuisCode((string) $this->brand)->domaineBooks;
    }

    public function fullName(): string
    {
        return trim($this->firstname.' '.$this->lastname) ?: $this->login;
    }

    /** URL de la fiche du book sur le portail (format SEO du legacy). */
    public function portfolioUrl(): string
    {
        return lien('portfolio.show', [
            'login' => $this->login,
            'slug' => Str::slug($this->fullName().'-'.($this->category?->slug ?? 'autre')),
        ]);
    }

    /**
     * Vignette du creatif, affichee sur la carte du portail.
     *
     * `front_desk` est la declinaison que le legacy pre-generait pour cet
     * usage precis — 250x136, rognee.
     */
    public function thumbnailUrl(string $declinaison = 'front_desk'): ?string
    {
        $thumbnail = $this->bookSetting?->thumbnail;

        return $thumbnail
            ? route('book.media.declinaison', [
                'login' => $this->login,
                'declinaison' => $declinaison,
                'file' => basename($thumbnail),
            ])
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

    /** Echeance de la formule payante, null en formule gratuite. */
    public function echeanceFormule(): ?\Illuminate\Support\Carbon
    {
        if (! $this->plan) {
            return null;
        }

        return $this->plan_expires_at
            ?? ($this->plan_started_at && $this->plan_months
                ? $this->plan_started_at->copy()->addMonths($this->plan_months)
                : null);
    }

    /**
     * Ajoute des mois de formule. Une formule encore active est prolongee a
     * partir de son echeance ; sinon elle repart d'aujourd'hui. Le legacy
     * repartait toujours d'aujourd'hui : un renouvellement anticipe perdait
     * les mois restants.
     */
    public function prolongerFormule(int $mois): void
    {
        if ($this->echeanceFormule()?->isFuture()) {
            // L'echeance se recalcule toute seule a l'enregistrement.
            $this->update(['plan_months' => $this->plan_months + $mois]);

            return;
        }

        $this->update(['plan' => 1, 'plan_started_at' => now(), 'plan_months' => $mois]);
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

    /** Images deposees dans le corps des pages (App\Livewire\Espace\Pages), hors quota de portfolio. */
    public function pageImages(): HasMany
    {
        return $this->hasMany(PageImage::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Demandes qui portent au moins un message non lu, ecrit par le
     * client — celui du createur ne compte pas. Pastille du menu de l'espace.
     */
    /** Les books que ce creatif a mis dans son memo book. */
    public function memoBooks(): HasMany
    {
        return $this->hasMany(MemoBook::class, 'user_id');
    }

    public function conversationsNonLues(): int
    {
        return $this->conversations()
            ->where('is_spam', false)
            ->whereHas('messages', fn ($q) => $q->where('from_owner', false)->whereNull('read_at'))
            ->count();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** Identite d'entreprise, si le createur facture en professionnel. */
    public function billingProfile(): HasOne
    {
        return $this->hasOne(BillingProfile::class);
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
