<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CategoriesTable
{
    /**
     * Couleurs des metiers du portail (classes .coultxt_* de core.css).
     * Directeur artistique et modele n'en ont pas dans le portail : ils
     * prennent celle d'« autre ».
     */
    private const COULEURS = [
        'illustrateur' => '#8a8b8a',
        'illustrateur-jeunesse' => '#979689',
        'graphiste' => '#757c98',
        'digital' => '#9ca1bc',
        'plasticien' => '#a27365',
        'photographe' => '#bb8372',
        'design' => '#af8897',
        'architecte' => '#a79fbc',
        'scenographe' => '#b7b2be',
        'styliste' => '#b9a3a2',
    ];

    private const COULEUR_DEFAUT = '#b9a3a2';

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->label('Ordre')->sortable(),
                TextColumn::make('name')->label('Métier')
                    ->formatStateUsing(fn (Category $record, string $state): HtmlString => new HtmlString(
                        '<span style="display:inline-flex;align-items:center;gap:.5rem">'
                        .'<span style="width:30px;height:30px;border-radius:2px;flex-shrink:0;background:'
                        .(self::COULEURS[$record->slug] ?? self::COULEUR_DEFAUT).'"></span>'.e($state).'</span>'
                    ))
                    ->html(),
                TextColumn::make('slug')->label('Adresse'),
                TextColumn::make('users_count')->label('Créatifs')->counts('users'),
                IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->defaultSort('position')
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([20, 50, 100])
            ->recordActions([EditAction::make()]);
    }
}
