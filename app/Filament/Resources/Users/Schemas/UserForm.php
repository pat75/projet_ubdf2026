<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use App\Models\User;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Compte')->columns(2)->schema([
                TextInput::make('login')->label('Identifiant')->required()->maxLength(50)
                    ->disabled()->dehydrated(false)
                    ->helperText('L’identifiant est l’adresse du book : il ne se change pas ici.'),
                TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255),
                Select::make('category_id')->label('Métier')->relationship('category', 'name')->searchable(),
                Select::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio'])->required(),
                TextInput::make('firstname')->label('Prénom')->maxLength(100),
                TextInput::make('lastname')->label('Nom')->maxLength(100),
                TextInput::make('company')->label('Société')->maxLength(150),
                TextInput::make('phone')->label('Téléphone')->maxLength(30),
            ]),

            Section::make('Formule')->columns(3)->schema([
                Select::make('plan')->label('Formule')->options([0 => 'Gratuite', 1 => 'Payante'])->required(),
                DatePicker::make('plan_started_at')->label('Début'),
                TextInput::make('plan_months')->label('Durée (mois)')->numeric()->minValue(0)->maxValue(240),
            ]),

            Section::make('Portail')->columns(3)->schema([
                Toggle::make('in_directory')->label('Dans l’annuaire'),
                Toggle::make('is_selected')->label('Ultra-sélection'),

                Toggle::make('in_home_selection')->label('En page d’accueil')
                    ->live()
                    // Cocher date la selection de l'instant ; decocher
                    // efface la date. La date reste modifiable a cote.
                    ->afterStateUpdated(function (bool $state, Set $set, Get $get) {
                        $set('home_selection_at', $state ? ($get('home_selection_at') ?: now()) : null);
                    })
                    ->helperText('Sélectionne le book immédiatement.'),
                DatePicker::make('home_selection_at')->label('Date de la sélection')
                    ->native(false)->displayFormat('d/m/Y')->closeOnDateSelection()
                    ->maxDate(now()->addYear())
                    ->visible(fn (Get $get) => (bool) $get('in_home_selection'))
                    ->helperText('Sert à ordonner la page d’accueil : les plus récentes en tête.')
                    ->columnSpan(2),
            ]),

            /*
             | Identite d'entreprise, relevee aupres de l'annuaire de l'Etat
             | depuis l'espace du createur. Elle est montree ici, mais pas
             | modifiable : elle ne se saisit pas a la main, sinon les
             | factures porteraient des mentions legales inventees.
             */
            Section::make('Facturation électronique')->columns(3)
                ->visible(fn (?User $record) => $record?->billingProfile()->exists())
                ->schema([
                    Placeholder::make('siret')->label('SIRET')
                        ->content(fn (User $record) => $record->billingProfile?->siretLisible()),
                    Placeholder::make('raison_sociale')->label('Raison sociale')
                        ->content(fn (User $record) => $record->billingProfile?->company_name),
                    Placeholder::make('tva')->label('TVA intracommunautaire')
                        ->content(fn (User $record) => $record->billingProfile?->vat_number),
                    Placeholder::make('forme')->label('Forme juridique')
                        ->content(fn (User $record) => $record->billingProfile?->formeJuridique()),
                    Placeholder::make('naf')->label('Code APE / NAF')
                        ->content(fn (User $record) => $record->billingProfile?->naf_code),
                    Placeholder::make('adresse_pro')->label('Adresse')
                        ->content(fn (User $record) => $record->billingProfile?->adresseComplete()),
                ]),

            Section::make('Note interne')->schema([
                Textarea::make('admin_note')->label('Note')->rows(3)
                    ->helperText('Visible des seuls administrateurs.'),
            ]),
        ]);
    }
}
