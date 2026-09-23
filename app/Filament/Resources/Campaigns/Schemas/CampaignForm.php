<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nom interne')->required()->maxLength(255),
            Select::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio'])->required(),
            Select::make('type')->label('Type')
                ->options(['newsletter' => 'Newsletter', 'relance' => 'Relance d’abonnement', 'selection' => 'Sélection'])
                ->required(),
            TextInput::make('subject')->label('Objet du message')->required()->maxLength(255),
            RichEditor::make('body')->label('Contenu')->columnSpanFull(),
            DateTimePicker::make('scheduled_at')->label('Envoi programmé')->seconds(false)
                ->helperText('L’envoi lui-même est le sujet de la phase 7.'),
        ]);
    }
}
