<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\Admin\ExportCsv;
use App\Services\Admin\RevueBooks;
use App\Filament\Support\FiltrePeriode;
use App\Filament\Support\MenuTri;
use App\Services\Espace\AffichageProfil;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\TextSize as TextColumnSize;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    /**
     * Onglets ouverts au plus par « Ouvrir les books ».
     *
     * Vingt-cinq : au-dela, le navigateur rame et l'oeil ne suit plus.
     * C'est aussi la plus petite taille de page du tableau, donc un
     * reglage qui permet d'ouvrir exactement ce qu'on voit.
     */
    private const ONGLETS_MAX = 25;

    /** Durees proposees par l'action « Ajouter des mois de formule ». */
    private const DUREES = [3 => '3 mois', 6 => '6 mois', 12 => '12 mois', 24 => '24 mois'];

    public static function configure(Table $table): Table
    {
        return $table
            /*
             | Colonnes calibrees pour tenir sur un ecran de 1024 px : six
             | colonnes visibles, le reste en option dans le menu
             | « Colonnes ». L'e-mail reste cherchable meme masque.
             */
            ->columns([
                /*
                 | Vignette du creatif, celle qu'affiche le portail. A defaut
                 | de photo, le medaillon d'initiales du site plutot qu'une
                 | case vide : l'oeil retrouve une ligne bien plus vite sur
                 | une pastille de couleur que sur un identifiant.
                 */
                ImageColumn::make('avatar')->label('')->circular()->imageSize(36)
                    ->getStateUsing(fn (User $u) => app(AffichageProfil::class)->photoUrl($u))
                    ->defaultImageUrl(fn (User $u) => app(AffichageProfil::class)->medaillonCreatif($u)),

                TextColumn::make('login')->label('Identifiant')->searchable()->sortable()
                    ->description(fn (User $u) => trim($u->firstname.' '.$u->lastname) ?: null)
                    ->url(fn (User $u) => $u->bookUrl(), shouldOpenInNewTab: true)
                    ->wrap(),

                /*
                 | Un compte bloque se voit d'un coup d'oeil, sans ouvrir sa
                 | fiche : un vrai label rouge, pas l'identifiant repeint —
                 | la couleur doit nommer l'etat, pas deguiser une donnee.
                 | Vide pour un compte actif : le tableau n'est pas un damier
                 | de pastilles vertes.
                 */
                TextColumn::make('blocked_at')->label('État')->badge()->alignCenter()
                    ->formatStateUsing(fn () => __('Bloqué'))
                    ->color('danger')
                    ->placeholder('')
                    ->tooltip(fn (User $u) => $u->estBloque()
                        ? trim(__('Bloqué le :date', ['date' => $u->blocked_at?->format('d/m/Y')]).' — '.($u->blocked_reason ?: '—'))
                        : null),
                TextColumn::make('category.name')->label('Métier')->sortable()->toggleable()
                    ->size(TextColumnSize::Small)
                    // Les intitules de metier sont longs ; on les tronque
                    // plutot que de laisser le tableau deborder.
                    ->limit(18)->tooltip(fn (User $u) => $u->category?->name),
                TextColumn::make('brand')->label('Marque')->badge()->alignCenter()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book')
                    ->color(fn (?string $state) => $state === 'df' ? 'warning' : 'info'),
                TextColumn::make('plan')->label('Formule')->badge()->alignCenter()
                    ->formatStateUsing(fn (?int $state) => $state ? 'Payante' : 'Gratuite')
                    ->color(fn (?int $state) => $state ? 'success' : 'gray')
                    ->description(fn (User $u) => $u->echeanceFormule()?->format('d/m/Y'), position: 'below'),
                /*
                 | Les trois dates par lesquelles on lit cette liste au
                 | quotidien : « qui vient d'entrer en selection », « qui
                 | vient de payer », « qui vient de s'inscrire ». Elles
                 | portent le menu « Trier par », qui appelle le tri natif
                 | de la table — lequel ignore une colonne masquee. Elles
                 | ne sont donc pas masquables.
                 */
                TextColumn::make('home_selection_at')->label('Sélectionné le')
                    ->date('d/m/Y')->sortable()->placeholder('—')
                    ->size(TextColumnSize::Small)
                    ->color(fn (User $u) => $u->in_home_selection ? 'warning' : 'gray'),
                TextColumn::make('plan_started_at')->label('Formule depuis')
                    ->date('d/m/Y')->sortable()->placeholder('—')
                    ->size(TextColumnSize::Small)
                    ->description(fn (User $u) => $u->plan_months ? __(':n mois', ['n' => $u->plan_months]) : null),
                TextColumn::make('created_at')->label('Inscription')->date('d/m/Y')->sortable()
                    ->size(TextColumnSize::Small),
                IconColumn::make('bookSetting.diffuse_web')->label('En ligne')->boolean()
                    ->alignCenter()->toggleable(),
                IconColumn::make('billingProfile.siret')->label('Pro')->boolean()
                    ->tooltip(fn (User $u) => $u->billingProfile?->company_name)
                    ->alignCenter()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('media_count')->label('Visuels')->numeric()->sortable()
                    ->alignCenter()->size(TextColumnSize::Small)->toggleable(),

                // Masquees par defaut : a rappeler via le menu « Colonnes ».
                TextColumn::make('email')->label('E-mail')->searchable()
                    ->size(TextColumnSize::Small)->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')->label('Ville')->searchable()
                    ->size(TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginationPageOptions([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->filters([
                SelectFilter::make('brand')->label('Marque')
                    ->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
                SelectFilter::make('category_id')->label('Métier')->relationship('category', 'name')->searchable(),
                TernaryFilter::make('plan')->label('Formule payante')
                    ->queries(
                        true: fn (Builder $q) => $q->where('plan', '>', 0),
                        false: fn (Builder $q) => $q->where('plan', 0),
                    ),
                TernaryFilter::make('in_home_selection')->label('En sélection'),
                FiltrePeriode::make('selection_recente', 'Sélectionné', 'home_selection_at', jamais: true),
                FiltrePeriode::make('formule_recente', 'Formule prise', 'plan_started_at', jamais: true),
                FiltrePeriode::make('inscription_recente', 'Inscrit', 'created_at'),
                TernaryFilter::make('bloque')->label('Compte bloqué')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('blocked_at'),
                        false: fn (Builder $q) => $q->whereNull('blocked_at'),
                    ),
                TernaryFilter::make('facturation')->label('Facturation électronique')
                    ->queries(
                        true: fn (Builder $q) => $q->whereHas('billingProfile'),
                        false: fn (Builder $q) => $q->whereDoesntHave('billingProfile'),
                    ),
                Filter::make('echue')->label('Formule échue')
                    ->query(fn (Builder $q) => $q->where('plan', '>', 0)->where('plan_expires_at', '<', now())),
                TrashedFilter::make()->label('Comptes supprimés'),
            ])
            /*
             | Filtres derriere un bouton, sur la meme ligne que la
             | recherche, l'export et l'organisation des colonnes : les
             | quatre commandes de la liste se tiennent ensemble, et le
             | tableau commence plus haut. Le panneau est elargi a 4xl sur
             | trois colonnes — c'est son etroitesse, pas sa forme, qui le
             | rendait illisible.
             */
            ->filtersLayout(FiltersLayout::Dropdown)
            ->filtersFormWidth(Width::FourExtraLarge)
            ->filtersFormColumns(['default' => 1, 'md' => 2, 'xl' => 3])
            /*
             | Quatre gestes quotidiens en icones nues, directement sur la
             | ligne — selectionner, dater la selection, prolonger la
             | formule, bloquer —, le reste replie derriere le menu. Les
             | sortir du menu economise deux clics sur les seules actions
             | qu'on repete des dizaines de fois par jour ; quatre icones de
             | 24 px tiennent la ou deux boutons libelles ne tenaient pas.
             */
            ->recordActions([
                self::selection(),
                self::dateSelection(),
                self::formule(),
                self::blocage(),
                self::priseIdentite(),
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('book')->label('Voir le book')->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (User $u) => $u->bookUrl(), shouldOpenInNewTab: true),
                ]),
            ])
            /*
             | Export, tri et actions groupees dans la barre du tableau
             | plutot qu'au-dessus : ils rejoignent la recherche, les
             | filtres et l'organisation des colonnes sur une seule ligne,
             | qui rassemble tout ce qui pilote la liste.
             */
            ->toolbarActions([
                self::ouvrirLesBooks(),
                MenuTri::make([
                    'Dernières sélections' => ['home_selection_at', 'desc'],
                    'Dernières formules payantes' => ['plan_started_at', 'desc'],
                    'Derniers inscrits' => ['created_at', 'desc'],
                    'Plus de visuels' => ['media_count', 'desc'],
                ]),
                Action::make('exporter')->label('Exporter en CSV')->icon('heroicon-o-arrow-down-tray')
                    // La requete du tableau : l'export suit la recherche et les filtres affiches.
                    ->action(fn ($livewire) => app(ExportCsv::class)->reponse(
                        $livewire->getFilteredSortedTableQuery()->with(['category', 'bookSetting', 'billingProfile']),
                        [
                            'Identifiant' => fn (User $u) => $u->login,
                            'Nom' => fn (User $u) => $u->fullName(),
                            'E-mail' => fn (User $u) => $u->email,
                            'Métier' => fn (User $u) => $u->category?->name,
                            'Marque' => fn (User $u) => $u->brand === 'df' ? 'Dustfolio' : 'Ultra-book',
                            'Ville' => fn (User $u) => $u->city,
                            'Pays' => fn (User $u) => $u->country,
                            'Formule' => fn (User $u) => $u->plan ? 'Payante' : 'Gratuite',
                            'Échéance' => fn (User $u) => $u->echeanceFormule()?->format('Y-m-d'),
                            'Book en ligne' => fn (User $u) => $u->bookSetting?->diffuse_web ? 'oui' : 'non',
                            'Newsletter' => fn (User $u) => $u->bookSetting?->diffuse_newsletter ? 'oui' : 'non',
                            'Visuels' => fn (User $u) => $u->media_count,
                            'SIRET' => fn (User $u) => $u->billingProfile?->siret,
                            'Raison sociale' => fn (User $u) => $u->billingProfile?->company_name,
                            'TVA' => fn (User $u) => $u->billingProfile?->vat_number,
                            'Bloqué' => fn (User $u) => $u->estBloque() ? 'oui' : 'non',
                            'Motif du blocage' => fn (User $u) => $u->blocked_reason,
                            'Inscription' => fn (User $u) => $u->created_at?->format('Y-m-d'),
                        ],
                        'creatifs',
                    )),
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Met le book en page d'accueil, ou l'en retire. Sans fenetre de
     * confirmation : le geste se defait du meme clic.
     *
     * La date suit toute seule — le modele la pose en entrant en selection
     * et l'efface en sortant (App\Models\User::booted).
     */
    private static function selection(): Action
    {
        return Action::make('selection')
            ->label(fn (User $u) => $u->in_home_selection ? __('Retirer de la sélection') : __('Sélectionner'))
            ->tooltip(fn (User $u) => $u->in_home_selection ? __('Retirer de la sélection') : __('Sélectionner'))
            ->icon(fn (User $u) => $u->in_home_selection ? 'heroicon-s-star' : 'heroicon-o-star')
            ->color(fn (User $u) => $u->in_home_selection ? 'warning' : 'gray')
            ->iconButton()
            ->action(function (User $u) {
                $u->update(['in_home_selection' => ! $u->in_home_selection]);

                Notification::make()
                    ->title($u->in_home_selection
                        ? __(':login est en sélection.', ['login' => $u->login])
                        : __(':login n’est plus en sélection.', ['login' => $u->login]))
                    ->success()->send();
            });
    }

    /**
     * Change la date de selection sans passer par la fiche.
     *
     * Elle n'a de sens que pour un book en selection : c'est elle qui
     * ordonne la page d'accueil (les plus recentes en tete), et elle sert
     * aussi a dater apres coup une selection faite plus tot.
     */
    private static function dateSelection(): Action
    {
        return Action::make('date_selection')
            ->label(__('Date de la sélection'))
            ->tooltip(fn (User $u) => __('Date de la sélection : :date', [
                'date' => $u->home_selection_at?->format('d/m/Y') ?: '—',
            ]))
            ->icon('heroicon-o-calendar-days')
            ->color('gray')
            ->iconButton()
            ->visible(fn (User $u) => (bool) $u->in_home_selection)
            ->modalHeading(fn (User $u) => __('Sélection de :login', ['login' => $u->login]))
            ->modalSubmitActionLabel(__('Enregistrer'))
            ->fillForm(fn (User $u) => ['home_selection_at' => $u->home_selection_at])
            ->schema([
                DatePicker::make('home_selection_at')->label(__('Date de la sélection'))
                    ->native(false)->displayFormat('d/m/Y')->closeOnDateSelection()
                    ->maxDate(now()->addYear())->required()
                    ->helperText(__('Ordonne la page d’accueil : les plus récentes en tête.')),
            ])
            ->action(function (User $u, array $data) {
                $u->update(['home_selection_at' => $data['home_selection_at']]);

                Notification::make()->title(__('Date de sélection enregistrée.'))->success()->send();
            });
    }

    /**
     * Ajoute des mois de formule payante.
     *
     * Passe par User::prolongerFormule(), qui repart de l'echeance quand
     * elle est encore a venir : un renouvellement anticipe ne fait plus
     * perdre les mois restants, comme c'etait le cas dans le legacy.
     */
    private static function formule(): Action
    {
        return Action::make('formule')
            ->label(__('Ajouter des mois de formule'))
            ->tooltip(fn (User $u) => $u->echeanceFormule()
                ? __('Formule jusqu’au :date', ['date' => $u->echeanceFormule()->format('d/m/Y')])
                : __('Formule gratuite'))
            ->icon('heroicon-o-clock')
            ->color(fn (User $u) => $u->echeanceFormule()?->isPast() ? 'danger' : 'gray')
            ->iconButton()
            ->modalHeading(fn (User $u) => __('Formule de :login', ['login' => $u->login]))
            ->modalDescription(fn (User $u) => $u->echeanceFormule()
                ? __('Échéance actuelle : :date.', ['date' => $u->echeanceFormule()->format('d/m/Y')])
                : __('Ce compte est en formule gratuite.'))
            ->modalSubmitActionLabel(__('Ajouter'))
            ->fillForm(['mois' => 12])
            ->schema([
                Select::make('mois')->label(__('Durée à ajouter'))
                    ->options(self::DUREES)->default(12)->required()->native(false),
            ])
            ->action(function (User $u, array $data) {
                $u->prolongerFormule((int) $data['mois']);

                Notification::make()
                    ->title(__('Formule prolongée jusqu’au :date.', [
                        'date' => $u->refresh()->echeanceFormule()?->format('d/m/Y') ?: '—',
                    ]))
                    ->success()->send();
            });
    }

    /**
     * Ferme ou rouvre le compte, sans rien effacer.
     *
     * Le blocage n'est pas une suppression : book, visuels et factures
     * restent en place, seule la connexion est refusee
     * (App\Http\Middleware\RefuserComptesBloques). Le motif est note pour
     * l'equipe, jamais montre au creatif.
     */
    private static function blocage(): Action
    {
        return Action::make('blocage')
            ->label(fn (User $u) => $u->estBloque() ? __('Débloquer le compte') : __('Bloquer le compte'))
            ->tooltip(fn (User $u) => $u->estBloque()
                ? __('Compte bloqué le :date', ['date' => $u->blocked_at?->format('d/m/Y')])
                : __('Bloquer le compte'))
            ->icon(fn (User $u) => $u->estBloque() ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
            ->color(fn (User $u) => $u->estBloque() ? 'danger' : 'gray')
            ->iconButton()
            ->modalHeading(fn (User $u) => $u->estBloque()
                ? __('Débloquer :login', ['login' => $u->login])
                : __('Bloquer :login', ['login' => $u->login]))
            ->modalDescription(fn (User $u) => $u->estBloque()
                ? __('Le compte pourra de nouveau se connecter. Rien n’a été effacé pendant le blocage.')
                : __('Le compte ne pourra plus se connecter. Son book, ses visuels et ses factures sont conservés.'))
            ->modalSubmitActionLabel(fn (User $u) => $u->estBloque() ? __('Débloquer') : __('Bloquer'))
            ->schema(fn (User $u) => $u->estBloque() ? [] : [
                Textarea::make('motif')->label(__('Motif'))->rows(2)->maxLength(255)
                    ->helperText(__('Note interne : le créatif ne la voit pas.')),
            ])
            ->action(function (User $u, array $data) {
                if ($u->estBloque()) {
                    $u->debloquer();

                    Notification::make()->title(__('Compte débloqué.'))->success()->send();

                    return;
                }

                $u->bloquer($data['motif'] ?? null);

                Notification::make()->title(__('Compte bloqué.'))->warning()->send();
            });
    }
    /**
     * Ouvre l'espace du creatif sous son identite, sans quitter le
     * back-office.
     *
     * Les deux gardes cohabitent : la session `admin` reste ouverte et
     * c'est elle qui autorise le retour (App\Http\Controllers\Admin\
     * PriseIdentiteController). Le meme geste existe sur la fiche du
     * creatif ; il est remonte ici parce qu'on le declenche presque
     * toujours depuis la liste, apres avoir cherche quelqu'un — ouvrir la
     * fiche pour en ressortir aussitot ne servait a rien.
     *
     * Sans fenetre de confirmation : le geste est immediat, et il ne
     * detruit rien — le bandeau rouge de l'espace dit sous quelle identite
     * on se trouve, et rend la main d'un clic. Un compte bloque s'ouvre
     * aussi (RefuserComptesBloques exempte la prise d'identite).
     *
     * Un GET serait rejouable depuis l'historique du navigateur : on passe
     * par une page de relais qui poste le formulaire d'elle-meme.
     */
    private static function priseIdentite(): Action
    {
        return Action::make('prise_identite')
            ->label(__('Se connecter en tant que'))
            ->tooltip(fn (User $u) => __('Ouvrir l’espace de :login', ['login' => $u->login]))
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('gray')
            ->iconButton()
            ->action(fn (User $u) => redirect()->route('admin.prise-identite.relais', ['creatif' => $u]));
    }
    /**
     * Ouvre en une fois, chacun dans son onglet, tous les books de la page
     * courante — pour les juger a la suite et selectionner au passage.
     *
     * Chaque adresse porte un jeton signe (App\Services\Admin\RevueBooks)
     * qui fait apparaitre, en haut du book, un bandeau de selection. Sans
     * lui le bandeau n'existe pas : la session du back-office ne couvre pas
     * les sous-domaines des books.
     *
     * Plafonnee : au-dela, le navigateur peine autant que celui qui
     * regarde. Pour en ouvrir davantage, on change de page.
     */
    private static function ouvrirLesBooks(): Action
    {
        return Action::make('ouvrir_books')
            ->label(__('Ouvrir les books'))
            ->icon('heroicon-o-window')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('Ouvrir les books de cette page'))
            ->modalDescription(fn ($livewire) => trans_choice(
                'Un onglet sera ouvert.|:n onglets seront ouverts, un par book de la page. Chacun portera en tête un bandeau pour le mettre en sélection ou l’en retirer. Pensez à autoriser les fenêtres surgissantes pour ce site, sinon le navigateur n’en ouvrira qu’un.',
                $n = self::nombreOuvrable($livewire),
                ['n' => $n],
            ))
            ->modalSubmitActionLabel(__('Ouvrir'))
            ->action(function ($livewire) {
                $administrateur = Auth::guard('admin')->id();
                $revue = app(RevueBooks::class);

                $urls = self::booksDeLaPage($livewire)
                    ->map(fn (User $u) => $revue->url($u, $administrateur))
                    ->values()
                    ->all();

                if ($urls === []) {
                    Notification::make()->title(__('Aucun book à ouvrir sur cette page.'))->warning()->send();

                    return;
                }

                $livewire->dispatch('ouvrir-books', urls: $urls);

                Notification::make()
                    ->title(trans_choice('Un onglet ouvert.|:n onglets ouverts.', count($urls), ['n' => count($urls)]))
                    ->body(__('Refermez-les quand vous avez fini : la sélection est enregistrée à chaque clic.'))
                    ->success()->send();
            });
    }

    /** Les books affiches sur la page courante, dans l'ordre du tableau. */
    private static function booksDeLaPage($livewire): Collection
    {
        return collect($livewire->getTableRecords()?->all() ?? [])
            ->filter(fn (User $u) => ! $u->trashed())
            ->take(self::ONGLETS_MAX);
    }

    private static function nombreOuvrable($livewire): int
    {
        return self::booksDeLaPage($livewire)->count();
    }
}
