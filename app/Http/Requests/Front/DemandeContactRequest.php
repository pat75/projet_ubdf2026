<?php

namespace App\Http\Requests\Front;

use App\Models\User;
use App\Models\Visitor;
use App\Rules\CaptchaValide;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Demande adressee a un creatif depuis le portail.
 *
 * Les noms de champs sont ceux postes par `js_core_cards.js` : ils viennent
 * des colonnes `us_*` du legacy et ne peuvent pas etre renommes avant la
 * reecriture du JavaScript (phase 9).
 */
class DemandeContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Compte deja connecte (creatif ou visiteur) : il signe la demande. */
    public function compteConnecte(): User|Visitor|null
    {
        return auth('web')->user() ?? auth('visitor')->user();
    }

    /** Case « Créer mon compte » cochee par un visiteur non connecte. */
    public function veutUnCompte(): bool
    {
        return ! $this->compteConnecte() && $this->boolean('compte');
    }

    /**
     * Connecte : l'adresse et le nom viennent du compte, pas du formulaire
     * (le nom seulement s'il est renseigne, sinon celui saisi).
     */
    protected function prepareForValidation(): void
    {
        $compte = $this->compteConnecte();

        if (! $compte) {
            return;
        }

        $this->merge(array_filter([
            'us_mail' => $compte->email,
            'us_nom_prenom' => $compte->fullName(),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $connecte = $this->compteConnecte() !== null;

        return [
            'action' => ['required', Rule::in(array_keys(config('messagerie.demandes')))],
            'us_dir' => ['required', 'string', 'exists:users,login'],
            'us_key' => ['required', 'string'],
            'us_message' => ['required', 'string', 'min:10', 'max:5000'],
            'us_nom_prenom' => ['required', 'string', 'max:255'],
            // Pas de verification DNS : elle ajoute une resolution reseau
            // au temps de reponse et echoue des que le resolveur est lent,
            // ce qui perdrait une demande legitime. La validite reelle de
            // l'adresse se constate a l'envoi du courriel.
            'us_mail' => ['required', 'email:rfc', 'max:255'],
            'us_societe' => ['nullable', 'string', 'max:255'],
            'us_tel' => ['nullable', 'string', 'max:40'],
            'us_book_visuel' => ['nullable', 'string', 'max:250'],
            'mf_request_detail' => ['nullable', 'string', 'max:250'],
            // Un compte connecte a deja passe un captcha a la connexion.
            'captcha_answer' => $connecte ? ['exclude'] : ['required', 'string', new CaptchaValide('contact')],
            'compte' => ['exclude'],
            'password' => $this->veutUnCompte() ? ['required', 'string', Password::min(8)] : ['exclude'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Le type de demande est manquant.',
            'action.in' => 'Ce type de demande n’existe pas.',
            'us_dir.required' => 'Le destinataire est manquant.',
            'us_dir.exists' => 'Ce book n’existe pas.',
            'us_message.required' => 'Indiquez votre message.',
            'us_message.min' => 'Votre message est trop court.',
            'us_message.max' => 'Votre message est trop long.',
            'us_nom_prenom.required' => 'Indiquez votre nom.',
            'us_mail.required' => 'Indiquez votre adresse électronique.',
            'us_mail.email' => 'Cette adresse électronique est invalide.',
            'captcha_answer.required' => 'Recopiez le code de l’image.',
            'password.required' => 'Indiquez un mot de passe.',
            'password.min' => 'Le mot de passe doit faire au moins 8 caractères.',
        ];
    }

    /**
     * Le front 2018 attend un 200 portant `error: true`, pas un 422.
     *
     * Son gestionnaire de succes est le seul branche : sur un code d'erreur
     * il reste sur son indicateur de chargement, sans rien afficher. La
     * reponse garde donc la forme qu'il sait lire.
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'error' => true,
            'error_list' => $validator->errors()->toArray(),
            'savedb_result' => false,
        ]));
    }

    /**
     * Le creatif destinataire.
     *
     * `us_key` accompagne `us_dir` depuis 2019 : il empeche de poster une
     * demande a un login devine, en exigeant la cle publique que seule la
     * page du book expose. La verification se fait en temps constant.
     */
    public function destinataire(): ?User
    {
        $user = User::where('login', $this->input('us_dir'))
            ->where('in_home_selection', true)
            ->first();

        if (! $user || ! hash_equals($user->publicKey(), (string) $this->input('us_key'))) {
            return null;
        }

        return $user;
    }
}
