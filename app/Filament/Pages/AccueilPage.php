<?php

namespace App\Filament\Pages;

use App\Models\AccueilBloc;
use App\Models\Reglage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * Page d'accueil du portail : afficher ou masquer chacun de ses blocs
 * d'accroche (partials/accueil-hero.blade.php), un par un. Les cles
 * possibles et leurs intitules sont dans App\Models\AccueilBloc::CLES.
 */
class AccueilPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Page d’accueil';

    protected static ?string $title = 'Page d’accueil';

    protected static string|\UnitEnum|null $navigationGroup = 'Éditorial';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.accueil-page';

    protected Width|string|null $maxContentWidth = Width::Full;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(AccueilBloc::etats() + [
            Reglage::MAINTENANCE => Reglage::enMaintenance(),
            Reglage::MESSAGE_MAINTENANCE => Reglage::texte(Reglage::MESSAGE_MAINTENANCE),
        ]);
    }

    /** Adresse de la page d'accueil du portail, affichee dans l'apercu. */
    public function urlApercu(): string
    {
        return lien('accueil');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                /*
                 | Ce reglage ne touche pas la page d'accueil mais tout le
                 | portail : il est dans sa propre section, en rouge, pour
                 | qu'on ne le coche pas en croyant masquer un bloc.
                 */
                Section::make('Maintenance du site')
                    ->description('Ferme le portail au public. Les books créatifs restent en ligne.')
                    ->schema([
                        Toggle::make(Reglage::MAINTENANCE)
                            ->label('Mettre le site en maintenance')
                            ->helperText('Le portail affiche « Site en maintenance » : plus de connexion, plus d’inscription, plus de création de book. Les comptes créatifs et visiteurs déjà connectés sont déconnectés.')
                            ->onColor('danger')
                            ->onIcon(Heroicon::ExclamationTriangle)
                            ->live(),
                        Textarea::make(Reglage::MESSAGE_MAINTENANCE)
                            ->label('Message aux visiteurs')
                            ->helperText('Facultatif. Affiché sur la page de maintenance, sous le texte standard (ex. « Retour prévu à 14 h »).')
                            ->rows(3)
                            ->maxLength(1000)
                            ->visible(fn ($get) => (bool) $get(Reglage::MAINTENANCE)),
                    ]),

                Section::make('Blocs de la page d’accueil')
                    ->schema(
                        collect(AccueilBloc::CLES)
                            ->map(fn (string $libelle, string $cle) => Toggle::make($cle)->label($libelle))
                            ->values()
                            ->all(),
                    ),
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
                    Actions::make([$this->getSaveFormAction()])
                        ->key('form-actions'),
                ]),
        ]);
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Enregistrer')
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public function save(): void
    {
        $etat = $this->form->getState();

        // Le reglage de maintenance vit dans sa propre table : il est
        // retire avant la boucle sur les blocs d'accueil.
        $maintenance = (bool) ($etat[Reglage::MAINTENANCE] ?? false);
        // Champ masque quand la maintenance est coupee : absent de l'etat,
        // le message deja enregistre est conserve pour la prochaine fois.
        $aMessage = array_key_exists(Reglage::MESSAGE_MAINTENANCE, $etat);
        $message = $etat[Reglage::MESSAGE_MAINTENANCE] ?? null;
        unset($etat[Reglage::MAINTENANCE], $etat[Reglage::MESSAGE_MAINTENANCE]);

        Reglage::definir(Reglage::MAINTENANCE, $maintenance);

        if ($aMessage) {
            Reglage::definirTexte(Reglage::MESSAGE_MAINTENANCE, $message);
        }

        foreach ($etat as $cle => $actif) {
            AccueilBloc::definir($cle, (bool) $actif);
        }

        if ($maintenance) {
            Notification::make()
                ->title('Site en maintenance')
                ->body('Le portail est fermé au public.')
                ->warning()
                ->persistent()
                ->send();
        } else {
            Notification::make()->title('Enregistré')->success()->send();
        }

        // Recharge l'iframe d'apercu (ecoute dans accueil-page.blade.php).
        $this->dispatch('accueil-enregistree');
    }
}
