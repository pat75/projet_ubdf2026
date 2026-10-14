<?php

namespace App\Filament\Resources\SupportTickets;

use App\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use App\Filament\Resources\SupportTickets\Pages\ViewSupportTicket;
use App\Models\SupportTicket;
use App\Services\Espace\AffichageProfil;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/** Support & contact : demandes envoyees depuis /support. */
class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?string $navigationLabel = 'Support & contact';

    protected static ?string $modelLabel = 'demande de support';

    protected static ?string $pluralModelLabel = 'support & contact';

    protected static string|\UnitEnum|null $navigationGroup = 'Créatifs';

    protected static ?int $navigationSort = 3;

    /** Demandes en attente de reponse, hors spam. */
    public static function getNavigationBadge(): ?string
    {
        $n = SupportTicket::where('statut', 'nouveau')->where('is_spam', false)->whereNull('corbeille_at')->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /** Le compte payant : formule en cours, meme regle que User::peutEtreAnalyseParIA(). */
    public static function estPayant(SupportTicket $record): bool
    {
        return $record->user && $record->user->plan > 0 && (bool) $record->user->echeanceFormule()?->isFuture();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.bookSetting', 'messages'])->withCount([
                'messages as questions_count' => fn (Builder $query) => $query->where('auteur', 'client'),
                'messages as reponses_count' => fn (Builder $query) => $query->where('auteur', 'admin'),
            ]))
            ->defaultSort('last_message_at', 'desc')
            ->recordUrl(fn (SupportTicket $record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                ImageColumn::make('avatar')->label('')->extraCellAttributes(['class' => 'ub-cellule-avatar'])->circular()->imageSize(36)
                    ->getStateUsing(function (SupportTicket $record) {
                        $profil = app(AffichageProfil::class);

                        return $record->user
                            ? ($profil->photoUrl($record->user) ?: $profil->medaillonCreatif($record->user))
                            : $profil->medaillon(mb_strtoupper(mb_substr((string) $record->email, 0, 2)), '#9ca3af');
                    }),
                TextColumn::make('email')->label('De')
                    ->getStateUsing(fn (SupportTicket $record) => $record->user?->login ?? $record->email)
                    ->formatStateUsing(fn (SupportTicket $record): HtmlString => new HtmlString($record->user
                        ? '<span class="ub-creatif"><span class="ub-creatif-login">'.e($record->user->login).'</span>'
                            .'<span class="ub-creatif-nom">'.e(trim($record->user->firstname.' '.$record->user->lastname)).'</span></span>'
                        : e($record->email)))
                    ->html()
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                        ->where('email', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $query) => $query->where('login', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))),
                TextColumn::make('identite')->label('Identité')->badge()
                    ->getStateUsing(fn (SupportTicket $record) => $record->compte_requis ? 'Non vérifiée' : null)->color('danger')->placeholder('')
                    ->tooltip('Envoyée sans connexion avec l’adresse d’un compte'),
                TextColumn::make('ip')->label('IP')->searchable()->toggleable(),
                TextColumn::make('formule')->label('Formule')->badge()
                    ->getStateUsing(fn (SupportTicket $record) => static::estPayant($record) ? 'Payante' : ($record->user ? 'Gratuite' : 'Visiteur'))
                    ->color(fn (string $state) => match ($state) { 'Payante' => 'success', 'Gratuite' => 'gray', default => 'info' }),
                TextColumn::make('extrait')->label('Dernier message')->wrap()
                    ->getStateUsing(fn (SupportTicket $record) => Str::limit((string) $record->messages->last()?->corps, 120)),
                TextColumn::make('questions_count')->label('Questions')->alignCenter()->sortable(),
                TextColumn::make('reponses_count')->label('Réponses')->alignCenter()->sortable(),
                TextColumn::make('statut')->badge()
                    ->formatStateUsing(fn (string $state) => SupportTicket::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'nouveau' => 'danger', 'repondu' => 'success', default => 'gray' }),
                TextColumn::make('note')->label('Note')->alignCenter()->sortable()
                    ->formatStateUsing(fn (?int $state) => $state ? str_repeat('★', $state).str_repeat('☆', 5 - $state) : null)
                    ->color('warning')->placeholder(''),
                TextColumn::make('last_message_at')->label('Dernier échange')->since()->sortable()
                    ->tooltip(fn (SupportTicket $record) => $record->last_message_at?->timezone('Europe/Paris')->format('d/m/Y H:i')),
            ])
            ->filters([
                TernaryFilter::make('is_spam')->label('Spam')->default(false),
            ])
            // A gauche de l'avatar : gestes sur le compte du createur.
            ->recordActions([
                static::priseIdentite()->iconButton(),
                static::selection()->iconButton(),
                Action::make('ouvrir')->label('Voir et répondre')->icon('heroicon-o-chat-bubble-left-right')->iconButton()
                    ->url(fn (SupportTicket $record) => static::getUrl('view', ['record' => $record])),
                Action::make('corbeille')->label('Mettre à la corbeille')->tooltip('Mettre à la corbeille')
                    ->icon('heroicon-o-trash')->color('danger')->iconButton()
                    ->visible(fn (SupportTicket $record) => ! $record->corbeille_at)
                    ->action(fn (SupportTicket $record) => $record->update(['corbeille_at' => now()])),
                Action::make('restaurer')->label('Restaurer')->tooltip('Restaurer')
                    ->icon('heroicon-o-arrow-uturn-left')->color('gray')->iconButton()
                    ->visible(fn (SupportTicket $record) => (bool) $record->corbeille_at)
                    ->action(fn (SupportTicket $record) => $record->update(['corbeille_at' => null])),
            ], position: RecordActionsPosition::BeforeColumns);
    }

    /** Ouvre l'espace du createur du ticket (meme relais que ActionsCompte). */
    public static function priseIdentite(): Action
    {
        return Action::make('prise_identite')->label('Se connecter au compte')->tooltip('Se connecter au compte')
            ->icon('heroicon-o-arrow-right-on-rectangle')->color('gray')
            ->visible(fn (SupportTicket $record) => (bool) $record->user)
            ->url(fn (SupportTicket $record) => $record->user ? route('admin.prise-identite.relais', ['creatif' => $record->user]) : null);
    }

    /** Meme geste que ActionsCompte::selection(), sur le createur du ticket. */
    public static function selection(): Action
    {
        return Action::make('selection')
            ->label(fn (SupportTicket $record) => $record->user?->in_home_selection ? 'Resélectionner maintenant' : 'Sélectionner')
            ->tooltip(fn (SupportTicket $record) => $record->user?->in_home_selection ? 'Resélectionner maintenant' : 'Sélectionner')
            ->icon(fn (SupportTicket $record) => $record->user?->in_home_selection ? 'heroicon-s-star' : 'heroicon-o-star')
            ->color(fn (SupportTicket $record) => $record->user?->in_home_selection ? 'warning' : 'gray')
            ->visible(fn (SupportTicket $record) => (bool) $record->user)
            ->action(function (SupportTicket $record) {
                $record->user->update(['in_home_selection' => true, 'home_selection_at' => now()]);
                Notification::make()->title($record->user->login.' est en sélection depuis '.now()->format('d/m/Y H:i').'.')->success()->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupportTickets::route('/'),
            'view' => ViewSupportTicket::route('/{record}'),
        ];
    }
}
