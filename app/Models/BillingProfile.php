<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Identite d'entreprise d'un createur, pour la facturation electronique.
 */
class BillingProfile extends Model
{
    protected $fillable = [
        'user_id', 'siret', 'siren', 'company_name', 'legal_form', 'naf_code',
        'vat_number', 'address', 'postcode', 'city', 'country',
        'admin_state', 'established_on', 'payload', 'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'established_on' => 'date',
            'checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** L'etablissement est-il encore en activite d'apres l'annuaire ? */
    public function actif(): bool
    {
        return $this->admin_state === 'A';
    }

    /** Forme juridique en clair, ou son code a defaut. */
    public function formeJuridique(): ?string
    {
        if ($this->legal_form === null) {
            return null;
        }

        return config('facturation.formes_juridiques.'.$this->legal_form, $this->legal_form);
    }

    /** SIRET par groupes, comme il s'ecrit : 552 081 317 66522. */
    public function siretLisible(): string
    {
        return trim(implode(' ', [
            substr($this->siret, 0, 3),
            substr($this->siret, 3, 3),
            substr($this->siret, 6, 3),
            substr($this->siret, 9),
        ]));
    }

    /** Adresse sur une ligne, pour les factures. */
    public function adresseComplete(): string
    {
        return trim(implode(', ', array_filter([
            $this->address,
            trim($this->postcode.' '.$this->city),
        ])));
    }
}
