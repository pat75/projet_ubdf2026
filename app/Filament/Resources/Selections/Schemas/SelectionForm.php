<?php

namespace App\Filament\Resources\Selections\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SelectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio'])->required(),
            Select::make('category_slug')->label('Métier')
                ->options(fn () => \App\Models\Category::orderBy('position')->pluck('name', 'slug'))
                ->placeholder('Tous les métiers'),
            DatePicker::make('starts_at')->label('Du')->required(),
            DatePicker::make('ends_at')->label('Au')->required()->afterOrEqual('starts_at'),
            Select::make('users')->label('Créatifs sélectionnés')->relationship('users', 'login')
                ->multiple()->searchable()->preload(false),
        ]);
    }
}
