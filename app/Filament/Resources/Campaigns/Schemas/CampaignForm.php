<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Models\Campaign;
use App\Services\Newsletter\Destinataires;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nom interne')->required()->maxLength(255),

            Select::make('brand')->label('Marque')->live()
                ->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio'])
                ->default('ub')->required(),

            CheckboxList::make('cibles')->label('Destinataires')->live()
                ->options(['creatifs' => 'Les créatifs', 'visiteurs' => 'Les visiteurs'])
                ->descriptions([
                    'creatifs' => 'Créatifs de la marque abonnés à la lettre d’information.',
                    'visiteurs' => 'Adresses inscrites depuis le portail, comptes visiteurs compris.',
                ])
                ->required()->minItems(1)
                ->helperText(fn (Get $get) => static::compter($get))
                ->columnSpanFull(),

            TextInput::make('subject')->label('Objet du message')->required()->maxLength(255),

            RichEditor::make('body')->label('Contenu')->columnSpanFull()
                ->helperText('{prenom} est remplacé par le prénom du destinataire, ou par rien s’il est inconnu.'),

            DateTimePicker::make('scheduled_at')->label('Envoi programmé')->seconds(false)
                ->helperText('Laisser vide pour envoyer à la main. Sinon le départ se fait à l’heure dite.'),
        ]);
    }

    /** Compteur vivant : le nombre change avec la marque et les cases cochees. */
    private static function compter(Get $get): string
    {
        $cibles = $get('cibles') ?: [];

        if ($cibles === []) {
            return 'Aucun destinataire tant qu’aucune case n’est cochée.';
        }

        $campagne = new Campaign(['brand' => $get('brand') ?: 'ub', 'cibles' => $cibles]);

        $nombre = app(Destinataires::class)->compter($campagne);

        return $nombre.' destinataire(s), doublons d’adresse retirés.';
    }
}
