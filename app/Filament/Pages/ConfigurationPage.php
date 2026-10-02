<?php

namespace App\Filament\Pages;

use App\Models\Reglage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Configuration du portail : reglages globaux qui ne touchent ni la page
 * d'accueil ni la maintenance (celles-ci restent dans AccueilPage).
 * Chaque reglage est une cle de App\Models\Reglage.
 */
class ConfigurationPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuration';

    protected static ?string $title = 'Configuration';

    protected static ?string $slug = 'configuration';

    protected static string|\UnitEnum|null $navigationGroup = 'Réglages';

    protected static ?int $navigationSort = 0;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        // Le formulaire parle en positif (« proposer Google »), la table
        // en negatif (cle absente = Google propose).
        $this->form->fill(['google' => Reglage::googleActif()]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Connexion et création de compte')
                    ->schema([
                        Toggle::make('google')
                            ->label('Proposer la connexion avec Google')
                            ->helperText('Désactivé : le bouton « Continuer avec Google » disparaît des fenêtres de connexion et de création de book, et les adresses /auth/google refusent l’accès. Les comptes déjà liés à Google se connectent avec leur identifiant et leur mot de passe.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Enregistrer')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->key('form-actions'),
                ]),
        ]);
    }

    public function save(): void
    {
        $etat = $this->form->getState();

        Reglage::definir(Reglage::GOOGLE_MASQUE, ! ($etat['google'] ?? true));

        Notification::make()->title('Enregistré')->success()->send();
    }
}
