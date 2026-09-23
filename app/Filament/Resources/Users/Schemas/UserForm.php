<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                Toggle::make('in_home_selection')->label('En page d’accueil'),
                Toggle::make('is_selected')->label('Ultra-sélection'),
            ]),

            Section::make('Note interne')->schema([
                Textarea::make('admin_note')->label('Note')->rows(3)
                    ->helperText('Visible des seuls administrateurs.'),
            ]),
        ]);
    }
}
