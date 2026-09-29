<?php

namespace App\Filament\Resources\Actualites\Schemas;

use App\Models\Actualite;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ActualiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titre')->label('Titre')->required()->columnSpanFull(),
                TextInput::make('sous_titre')->label('Sous-titre')->columnSpanFull(),
                RichEditor::make('contenu')->label('Contenu')->required()->columnSpanFull(),
                FileUpload::make('images')
                    ->label('Visuels')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('actualites')
                    ->columnSpanFull(),
                Select::make('emplacement')
                    ->label('Affichage')
                    ->options(Actualite::EMPLACEMENTS)
                    ->default('deux')
                    ->required(),
                TextInput::make('ordre')->label('Ordre')->numeric()->default(0)->required(),
                Toggle::make('actif')->label('Active')->default(true),
            ]);
    }
}
