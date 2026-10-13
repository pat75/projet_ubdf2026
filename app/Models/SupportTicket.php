<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Support\DossierBook;

/** Demande adressee au support via /support, et son fil. */
class SupportTicket extends Model
{
    public const STATUTS = ['nouveau' => 'Nouveau', 'repondu' => 'Répondu', 'clos' => 'Clos'];

    protected $fillable = [
        'user_id', 'email', 'compte_requis', 'statut', 'note', 'clos_at', 'page_origine', 'navigateur', 'ip',
        'is_spam', 'lu_admin_at', 'lu_client_at', 'last_message_at',
    ];

    protected function casts(): array
    {
        return ['is_spam' => 'boolean', 'compte_requis' => 'boolean', 'lu_admin_at' => 'datetime', 'lu_client_at' => 'datetime', 'clos_at' => 'datetime', 'last_message_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('created_at')->orderBy('id');
    }

    /** Adresse a laquelle repondre : celle du compte, sinon celle saisie. */
    public function adresse(): ?string
    {
        return $this->user?->email ?? $this->email;
    }

    /**
     * Lien permanent vers le fil : URL signee, rien a stocker. Qui la
     * detient lit et poursuit la discussion, connecte ou non.
     */
    public function lien(): string
    {
        // nom_route() : la signature doit viser la route prefixee de la
        // langue, sinon ResoudreLangue redirige et la signature casse.
        return URL::signedRoute(nom_route('support.fil'), ['ticket' => $this->id]);
    }

    /**
     * Le lien signe suffit-il a lire la discussion ? Pas pour une demande
     * deposee sans session avec l'adresse d'un compte : il faut alors etre
     * connecte a ce compte (par son id, ou par une adresse prouvee).
     */
    public function lisiblePar(User|Visitor|null $compte): bool
    {
        if (! $this->compte_requis) {
            return true;
        }

        if ($compte instanceof User && $this->user_id === $compte->id) {
            return true;
        }

        return $compte && $compte->email_verified_at
            && strcasecmp((string) $compte->email, (string) $this->email) === 0;
    }

    /** Adresse signee du bouton « Résolu ». */
    public function lienResolu(): string
    {
        return URL::signedRoute(nom_route('support.resoudre'), ['ticket' => $this->id]);
    }

    /**
     * Derniere discussion de ce compte avec le support : par le compte
     * createur, ou par l'adresse (demande deposee sans session, ou par un
     * visiteur). Nourrit le menu « Support » de l'espace.
     */
    public static function dernierePour(User|Visitor $compte): ?self
    {
        // Par l'adresse seulement si elle est prouvee : sinon, un compte
        // ouvert avec l'adresse d'autrui lirait les reponses qui lui sont
        // destinees.
        $adresseProuvee = (bool) $compte->email_verified_at;

        return static::where(fn ($query) => $query
                ->when($compte instanceof User, fn ($query) => $query->orWhere('user_id', $compte->id))
                ->when($adresseProuvee, fn ($query) => $query->orWhere('email', $compte->email)))
            ->when(! $compte instanceof User && ! $adresseProuvee, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('last_message_at')->first();
    }

    /** Le support a repondu et le client n'a pas encore ouvert la discussion. */
    public function reponseNonLue(): bool
    {
        return $this->statut === 'repondu' && ! $this->lu_client_at;
    }

    /** Enregistre un message et tient a jour statut et date du fil. */
    public function ajouter(string $auteur, string $corps, ?int $adminId = null, ?UploadedFile $piece = null): SupportMessage
    {
        $message = $this->messages()->create(['auteur' => $auteur, 'corps' => $corps, 'admin_id' => $adminId] + $this->deposer($piece));

        $this->update([
            'last_message_at' => now(),
            'statut' => $auteur === 'admin' ? 'repondu' : 'nouveau',
            'lu_admin_at' => $auteur === 'admin' ? now() : null,
            'lu_client_at' => $auteur === 'admin' ? null : now(),
        ]);

        return $message;
    }

    /**
     * Range la piece jointe dans le dossier du book (support/), sous un nom
     * aleatoire : le dossier des books est expose par le lien `storage`,
     * le nom ne doit pas se deviner. Elle n'est servie que par lienPiece().
     *
     * @return array{piece?: string, piece_nom?: string}
     */
    private function deposer(?UploadedFile $piece): array
    {
        if (! $piece || ! $this->user) {
            return [];
        }

        $chemin = $piece->storeAs(
            DossierBook::relatif($this->user->login, 'support'),
            Str::random(40).'.'.$piece->guessExtension(),
            'public',
        );

        // Nom affiche et renvoye au telechargement : sans / \\ % ni guillemet,
        // que l'en-tete Content-Disposition refuse.
        $nom = preg_replace('/[\\\\\/%"]+/', '-', $piece->getClientOriginalName());

        return ['piece' => $chemin, 'piece_nom' => Str::limit($nom, 200, '')];
    }
}
