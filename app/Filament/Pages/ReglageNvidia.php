<?php

namespace App\Filament\Pages;

use App\Models\Media;
use App\Models\Reglage;
use App\Services\IA\Nvidia;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

/**
 * Cle, expiration et modele de l'API NVIDIA (App\Services\IA\Nvidia),
 * qui prend en charge l'analyse des visuels avant OpenRouter.
 */
class ReglageNvidia extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'IA NVIDIA';

    protected static ?string $title = 'IA NVIDIA';

    protected static string|\UnitEnum|null $navigationGroup = 'Réglages';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'expiration' => Reglage::texte(Reglage::NVIDIA_EXPIRATION),
            'modele' => Nvidia::modele(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Clé API')
                    ->description($this->etatCle())
                    ->schema([
                        TextInput::make('cle')
                            ->label('Nouvelle clé')
                            ->password()
                            ->revealable()
                            ->placeholder('nvapi-… (vide : la clé actuelle est gardée)')
                            ->rule('starts_with:nvapi-'),
                        DatePicker::make('expiration')
                            ->label('Expire le')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ]),
                Section::make('Modèle d’analyse des images')
                    ->description($this->etatModele())
                    ->schema([
                        Select::make('modele')
                            ->label('Modèle')
                            ->required()
                            ->options(fn ($state) => collect(Nvidia::MODELES_VISION)->mapWithKeys(fn ($m) => [$m => $m])->all()
                                + ($state ? [$state => $state] : []))
                            ->helperText('Modèles vision testés. Un modèle du catalogue NVIDIA peut être listé sans être déployé : seule la vérification fait foi.'),
                    ]),
            ])
            ->statePath('data');
    }

    private function etatCle(): string
    {
        $cle = Nvidia::cle();
        $texte = $cle ? 'Clé en place : '.substr($cle, 0, 9).'…'.substr($cle, -4) : 'Aucune clé : l’analyse passe par OpenRouter (payant).';

        if ($date = Reglage::texte(Reglage::NVIDIA_EXPIRATION)) {
            $jours = (int) now()->startOfDay()->diffInDays(Carbon::parse($date), false);
            $texte .= ' — '.($jours < 0 ? 'EXPIRÉE depuis '.abs($jours).' jours' : "expire dans {$jours} jours (".Carbon::parse($date)->format('d/m/Y').')');
        }

        return $texte;
    }

    private function etatModele(): string
    {
        $v = Nvidia::derniereVerification();

        return $v
            ? sprintf('Dernière vérification de %s le %s : %s — %s', $v['modele'], $v['date'], $v['valide'] ? 'valide' : 'INVALIDE', $v['motif'])
            : 'Pas encore vérifié.';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Enregistrer')->submit('save')->keyBindings(['mod+s']),
                    ])->key('form-actions'),
                ]),
            Section::make('Dernières analyses')
                ->description('IA qui a réellement répondu. En cas de panne NVIDIA, l’analyse bascule seule sur OpenRouter (payant) pendant '.Nvidia::PAUSE.' min, puis NVIDIA est retenté.')
                ->schema([
                    View::make('filament.nvidia.dernieres-analyses')->viewData(['analyses' => $this->dernieresAnalyses()]),
                ]),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, array{date: string, nvidia: bool, modele: string, login: string}> */
    private function dernieresAnalyses(): \Illuminate\Support\Collection
    {
        $nvidia = [...Nvidia::MODELES_VISION, Nvidia::modele()];

        return Media::with('user:id,login')->whereNotNull('analysed_at')->latest('analysed_at')->limit(10)
            ->get(['id', 'user_id', 'ai_model', 'analysed_at'])
            ->map(fn (Media $m) => [
                'date' => $m->analysed_at->timezone('Europe/Paris')->format('d/m/Y H:i:s'),
                'nvidia' => in_array($m->ai_model, $nvidia, true),
                'modele' => (string) $m->ai_model,
                'login' => (string) $m->user?->login,
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verifier')
                ->label('Vérifier le modèle')
                ->icon(Heroicon::ArrowPath)
                ->action(function () {
                    $nvidia = app(Nvidia::class);
                    $etat = $nvidia->verifier();

                    if ($etat['valide']) {
                        Notification::make()->title("{$etat['modele']} : valide, {$etat['motif']}")->success()->send();

                        return;
                    }

                    // Modele retire ou en panne : on propose le premier qui repond.
                    if ($remplacant = $nvidia->remplacant($etat['modele'])) {
                        $this->data['modele'] = $remplacant;
                        Notification::make()
                            ->title("{$etat['modele']} ne répond plus")
                            ->body("Remplacé par {$remplacant} — enregistrez pour confirmer.")
                            ->warning()->persistent()->send();

                        return;
                    }

                    Notification::make()->title('Aucun modèle NVIDIA ne répond')->body($etat['motif'])->danger()->persistent()->send();
                }),
        ];
    }

    public function save(): void
    {
        $etat = $this->form->getState();

        if (filled($etat['cle'] ?? null)) {
            Nvidia::definirCle($etat['cle']);
        }
        Reglage::definirTexte(Reglage::NVIDIA_EXPIRATION, $etat['expiration'] ?? null);
        Reglage::definirTexte(Reglage::NVIDIA_MODELE, $etat['modele']);

        $this->data['cle'] = null;
        Notification::make()->title('Enregistré')->success()->send();
    }
}
