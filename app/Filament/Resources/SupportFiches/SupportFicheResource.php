<?php

namespace App\Filament\Resources\SupportFiches;

use App\Filament\Resources\SupportFiches\Pages\ManageSupportFiches;
use App\Models\SupportFiche;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Base de connaissances du support : les fiches proposees par l'IA apres
 * chaque reponse arrivent inactives, a relire puis activer. Seules les
 * fiches actives nourrissent les suggestions (BaseConnaissance).
 * Accessible par le bouton de la liste Support & contact.
 */
class SupportFicheResource extends Resource
{
    protected static ?string $model = SupportFiche::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $modelLabel = 'fiche';

    protected static ?string $pluralModelLabel = 'base de connaissances';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('question')->required()->rows(3)->columnSpanFull(),
            Textarea::make('reponse')->label('Réponse')->required()->rows(8)->columnSpanFull(),
            Toggle::make('actif')->label('Active (utilisée par les suggestions)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('question')->searchable()->wrap()->limit(120),
                TextColumn::make('reponse')->label('Réponse')->searchable()->wrap()->limit(160),
                ToggleColumn::make('actif')->label('Active'),
                TextColumn::make('created_at')->label('Créée')->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('actif')->label('Active'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSupportFiches::route('/')];
    }
}
