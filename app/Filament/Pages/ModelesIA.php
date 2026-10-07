<?php

namespace App\Filament\Pages;

use App\Models\Reglage;
use App\Services\IA\CatalogueOpenRouter;
use App\Services\IA\OpenRouterModelSelector;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;

/**
 * Modeles OpenRouter employes par OpenRouterModelSelector, par capacite
 * (texte, vision) et niveau de cout. Dans chaque niveau, l'ordre compte :
 * le premier est tente d'abord, les suivants prennent le relais en cas
 * d'echec. Chaque modele est controle contre le catalogue OpenRouter.
 */
class ModelesIA extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'Modèles IA';

    protected static ?string $title = 'Modèles IA (OpenRouter)';

    protected static string|\UnitEnum|null $navigationGroup = 'Réglages';

    private const CAPACITES = ['text' => 'Texte', 'vision' => 'Vision (images)'];

    private const NIVEAUX = [1 => 'Coût 1 — économique', 2 => 'Coût 2', 3 => 'Coût 3', 4 => 'Coût 4 — haut de gamme'];

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->remplir(OpenRouterModelSelector::listes());
    }

    /** Listes [capacite][niveau] => ids, vers l'etat du formulaire. */
    private function remplir(array $listes): void
    {
        $this->form->fill($listes);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dernier modèle utilisé')
                    ->description($this->dernierModele()),
                Tabs::make('capacites')->tabs(
                    collect(self::CAPACITES)->map(fn (string $libelle, string $capacite) => Tab::make($libelle)->schema(
                        collect(self::NIVEAUX)->map(fn (string $titre, int $niveau) => $this->repeater($capacite, $niveau, $titre))->values()->all(),
                    ))->values()->all(),
                ),
            ])
            ->statePath('data');
    }

    private function repeater(string $capacite, int $niveau, string $titre): Repeater
    {
        $catalogue = app(CatalogueOpenRouter::class);

        return Repeater::make("{$capacite}.{$niveau}")
            ->label($titre)
            ->simple(
                Select::make('id')
                    ->searchable()
                    ->required()
                    // Un modele deja choisi mais retire du catalogue reste
                    // dans la liste, pour qu'on le voie en rouge.
                    ->options(fn ($state) => $this->options($capacite) + ($state ? [$state => $state] : []))
                    ->hint(function ($state) use ($catalogue, $capacite) {
                        if (! $state) {
                            return null;
                        }
                        $etat = $catalogue->etat($state, $capacite);

                        return $etat['motif'] ?? $etat['prix'];
                    })
                    ->hintColor(fn ($state) => $state ? match ($catalogue->etat($state, $capacite)['valide']) {
                        true => 'success', false => 'danger', null => 'gray',
                    } : null)
                    ->hintIcon(fn ($state) => $state ? match ($catalogue->etat($state, $capacite)['valide']) {
                        true => Heroicon::CheckCircle, false => Heroicon::XCircle, null => Heroicon::QuestionMarkCircle,
                    } : null)
                    ->hintAction(
                        Action::make('remplacer')
                            ->label(fn (Select $component) => 'Remplacer par '.$this->suggestion($component->getState(), $capacite, $niveau))
                            ->visible(fn (Select $component) => $this->suggestion($component->getState(), $capacite, $niveau) !== null)
                            ->action(fn (Select $component) => $component->state($this->suggestion($component->getState(), $capacite, $niveau))),
                    ),
            )
            ->reorderable()
            ->defaultItems(0)
            ->addActionLabel('Ajouter un modèle');
    }

    /** Ids du niveau dans l'etat du formulaire (repeater simple : ['id' => …] par ligne). */
    private function ids(string $capacite, int $niveau): array
    {
        return collect($this->data[$capacite][$niveau] ?? [])
            ->map(fn ($ligne) => is_array($ligne) ? ($ligne['id'] ?? null) : $ligne)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Remplacant propose pour un modele invalide, null s'il est valide ou
     * sans equivalent. Prix vise : mediane des modeles valides du niveau.
     */
    private function suggestion(?string $id, string $capacite, int $niveau): ?string
    {
        $catalogue = app(CatalogueOpenRouter::class);
        if (! $id || $catalogue->etat($id, $capacite)['valide'] !== false) {
            return null;
        }

        $modeles = $catalogue->modeles();
        $prix = collect($this->ids($capacite, $niveau))
            ->map(fn (string $i) => $modeles[$i]['entree'] ?? null)
            ->filter()
            ->median();

        return $catalogue->suggestion($id, $capacite, $this->ids($capacite, $niveau), $prix ?: null);
    }

    /** @return array<string, string> */
    private function options(string $capacite): array
    {
        return collect(app(CatalogueOpenRouter::class)->modeles())
            ->filter(fn (array $m) => $capacite !== 'vision' || $m['vision'])
            ->keys()
            ->sort()
            ->mapWithKeys(fn (string $id) => [$id => $id])
            ->all();
    }

    /** Derniere ligne du journal `modelselector` (config/logging.php). */
    private function dernierModele(): string
    {
        $fichier = collect(File::glob(storage_path('logs/modelselector*.log')))->sort()->last();
        $ligne = $fichier ? collect(file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))->last() : null;

        if (! $ligne || ! preg_match('/(\{.*\})/', $ligne, $m) || ! ($j = json_decode($m[1], true))) {
            return 'Aucun appel journalisé.';
        }

        return sprintf('%s — coût %s, le %s', $j['modele'] ?? '?', $j['cout'] ?? '?', $j['date'] ?? '?');
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
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verifier')
                ->label('Vérifier maintenant')
                ->icon(Heroicon::ArrowPath)
                ->action(function () {
                    app(CatalogueOpenRouter::class)->oublier();
                    $n = count(app(CatalogueOpenRouter::class)->modeles());
                    $n
                        ? Notification::make()->title("Catalogue rechargé : {$n} modèles")->success()->send()
                        : Notification::make()->title('Catalogue OpenRouter injoignable')->danger()->send();
                }),
            Action::make('remplacerTout')
                ->label('Remplacer les modèles retirés')
                ->icon(Heroicon::Sparkles)
                ->requiresConfirmation()
                ->modalDescription('Chaque modèle absent du catalogue est remplacé par le plus proche : même fournisseur, même famille, coût approchant. Rien n’est enregistré avant « Enregistrer ».')
                ->action(function () {
                    $n = 0;
                    foreach (array_keys(self::CAPACITES) as $capacite) {
                        foreach (array_keys(self::NIVEAUX) as $niveau) {
                            foreach ($this->data[$capacite][$niveau] ?? [] as $cle => $ligne) {
                                $id = is_array($ligne) ? ($ligne['id'] ?? null) : $ligne;
                                if ($nouveau = $this->suggestion($id, $capacite, $niveau)) {
                                    data_set($this->data, "{$capacite}.{$niveau}.{$cle}".(is_array($ligne) ? '.id' : ''), $nouveau);
                                    $n++;
                                }
                            }
                        }
                    }
                    Notification::make()
                        ->title($n ? "{$n} modèle(s) remplacé(s) — vérifiez puis enregistrez" : 'Aucun modèle à remplacer')
                        ->success()->send();
                }),
            Action::make('defauts')
                ->label('Rétablir les défauts')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    Reglage::definirJson(Reglage::MODELES_IA, null);
                    $this->remplir(OpenRouterModelSelector::DEFAUTS);
                    Notification::make()->title('Listes par défaut rétablies')->success()->send();
                }),
        ];
    }

    public function save(): void
    {
        $etat = $this->form->getState();

        $listes = [];
        foreach (array_keys(self::CAPACITES) as $capacite) {
            foreach (array_keys(self::NIVEAUX) as $niveau) {
                $listes[$capacite][$niveau] = array_values(array_unique(array_filter($etat[$capacite][$niveau] ?? [])));
            }
        }

        Reglage::definirJson(Reglage::MODELES_IA, $listes);
        Notification::make()->title('Enregistré')->success()->send();
    }
}
