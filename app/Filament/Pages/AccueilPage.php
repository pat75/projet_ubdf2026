<?php

namespace App\Filament\Pages;

use App\Models\AccueilBloc;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
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

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(AccueilBloc::etats());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(
                collect(AccueilBloc::CLES)
                    ->map(fn (string $libelle, string $cle) => Toggle::make($cle)->label($libelle))
                    ->values()
                    ->all(),
            )
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
        foreach ($this->form->getState() as $cle => $actif) {
            AccueilBloc::definir($cle, (bool) $actif);
        }

        Notification::make()->title('Enregistré')->success()->send();
    }
}
