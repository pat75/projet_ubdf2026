<?php

namespace App\Models;

use App\Support\VideoEnLigne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'legacy_id', 'user_id', 'gallery_id', 'filename', 'title', 'alt', 'link', 'video_url',
        'description', 'ai_title', 'ai_description', 'ai_status', 'ai_model', 'analysed_at', 'mime', 'size', 'width', 'height', 'status', 'position',
    ];

    /**
     * URL publique du visuel, dans la declinaison demandee.
     *
     * Le legacy pre-generait neuf declinaisons sur le disque a
     * l'enregistrement. Ici une seule copie est stockee et les declinaisons
     * naissent a la demande — les noms possibles sont ceux de
     * `config/images.php`.
     */
    public function url(?string $declinaison = null): string
    {
        $parametres = [
            'login' => $this->user->login,
            // Un nom vide est possible : la table source compte 23 % de
            // lignes sans fichier. La route rend alors l'image par defaut.
            'file' => $this->filename ?: 'introuvable',
        ];

        return $declinaison === null
            ? route('book.media', $parametres)
            : route('book.media.declinaison', $parametres + ['declinaison' => $declinaison]);
    }

    /** La video dont ce visuel est la vignette, s'il en est une. */
    public function video(): ?VideoEnLigne
    {
        return $this->video_url ? VideoEnLigne::depuis($this->video_url) : null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /** Hors des portfolios proteges par mot de passe : ce que le portail peut montrer. */
    /**
     * Visuels analyses montrables sur le portail : book diffuse de la marque,
     * accord du creatif pour l'analyse IA, hors portfolios proteges.
     */
    public function scopeVisiblesSurPortail(Builder $query, string $brand): Builder
    {
        return $query->published()->horsProteges()
            ->whereNotNull('media.analysed_at')
            ->where('media.filename', '!=', '')
            ->whereHas('user', fn (Builder $u) => $u->where('brand', $brand)
                ->whereHas('bookSetting', fn (Builder $b) => $b
                    ->where('diffuse_web', true)->where('diffuse_ub', true)->where('allow_ai_analysis', true)));
    }

    /**
     * Pages image ouvertes aux moteurs, regle unique de la page (robots) et
     * du sitemap : description IA d'au moins 120 caracteres, 5 mots-cles,
     * et un seul visuel par titre chez un meme createur (« Illustration
     * graphique » x 3 faisait trois pages au meme titre).
     */
    public function scopeIndexables(Builder $query): Builder
    {
        // 120 caracteres au moins (LIKE compte les caracteres, sur MySQL comme SQLite).
        return $query->where('media.ai_description', 'like', str_repeat('_', 120).'%')
            ->has('tags', '>=', 5)
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('media as homonyme')
                ->whereColumn('homonyme.user_id', 'media.user_id')
                ->whereColumn('homonyme.ai_title', 'media.ai_title')
                ->whereColumn('homonyme.id', '<', 'media.id')
                ->whereNull('homonyme.deleted_at'));
    }

    public function estIndexable(): bool
    {
        return static::whereKey($this->getKey())->indexables()->exists();
    }

    /** Page publique du visuel analyse (indexee sous condition : voir indexables()). */
    public function pageUrl(): string
    {
        return lien('image', ['id' => $this->id, 'slug' => \Illuminate\Support\Str::slug($this->ai_title ?: 'image')]);
    }

    public function scopeHorsProteges(Builder $query): Builder
    {
        return $query->whereDoesntHave('gallery', fn (Builder $g) => $g->whereNotNull('password'));
    }

    protected function casts(): array
    {
        return ['analysed_at' => 'datetime'];
    }

    /** Mots-cles de l'analyse IA. */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
