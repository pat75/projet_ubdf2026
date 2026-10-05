<?php

namespace App\Filament\Resources\Conversations\Tables;

use App\Models\Conversation;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Espace\AffichageProfil;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Reçue le')->dateTime('d/m/Y H:i')->sortable(),
                // Avatar collé au texte : identifiant, puis nom et prénom dessous.
                TextColumn::make('user.login')->label('Créatif')->searchable()->sortable()
                    ->formatStateUsing(fn (Conversation $c) => $c->user ? self::personne(
                        app(AffichageProfil::class)->photoUrl($c->user) ?? app(AffichageProfil::class)->medaillonCreatif($c->user),
                        $c->user->login,
                        trim($c->user->firstname.' '.$c->user->lastname),
                    ) : null)
                    ->html(),
                // Avatar de l'emetteur seulement s'il a un compte, adresse dessous.
                TextColumn::make('sender_name')->label('Émetteur')->searchable()
                    ->formatStateUsing(fn (Conversation $c) => self::personne(
                        self::avatarEmetteur($c->sender_email), $c->sender_name, (string) $c->sender_email,
                    ))
                    ->html(),
                TextColumn::make('subject')->label('Objet')->formatStateUsing(fn (Conversation $c) => $c->objet())->toggleable(),
                TextColumn::make('messages_count')->label('Messages')->counts('messages'),
                // Etat lu comme « legitime » : coche verte pour un message
                // normal, croix rouge pour un indesirable.
                IconColumn::make('legitime')->label('Indésirable')->boolean()
                    ->getStateUsing(fn (Conversation $c) => ! $c->is_spam),
                // Probabilite de spam donnee par l'IA (Jev via OpenRouter),
                // en rouge au-dela du seuil ; « … » tant qu'elle n'est pas
                // calculee (analyse en cours sur la page).
                TextColumn::make('spam_ia_probabilite')->label('IA')
                    ->formatStateUsing(fn ($state) => number_format((float) $state * 100, 0).' %')
                    ->placeholder('…')
                    ->badge()
                    ->color(fn (Conversation $c) => $c->spam_ia ? 'danger' : 'gray'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['messages', 'user.bookSetting']))
            ->defaultSort('created_at', 'desc')
            // Indesirable : texte de la ligne en rouge (voir styles.blade.php).
            ->recordClasses(fn (Conversation $record) => $record->is_spam ? 'ub-conversation-spam' : null)
            ->filters([
                TernaryFilter::make('is_spam')->label('Indésirable'),
            ])
            ->recordActions([
                Action::make('basculerSpam')->label(fn (Conversation $c) => $c->is_spam ? 'Rendre légitime' : 'Marquer indésirable')
                    ->icon('heroicon-o-shield-exclamation')
                    ->action(fn (Conversation $c) => $c->update(['is_spam' => ! $c->is_spam])),
                // Lecture du message : bulle d'aide au survol, rien d'autre.
                // Pour decider « legitime ou non », l'administrateur n'a
                // besoin que du texte, sans ouvrir la conversation.
                Action::make('lire')->label('Lire le message')
                    ->icon('heroicon-o-envelope-open')->iconButton()
                    ->tooltip(fn (Conversation $c) => self::extrait($c))
                    ->action(fn () => null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('spam')->label('Marquer indésirables')->icon('heroicon-o-shield-exclamation')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => Conversation::whereKey($records->pluck('id'))->update(['is_spam' => true])),
                ]),
            ]);
    }

    /** Premier message de la conversation, texte brut, borne pour la bulle. */
    private static function extrait(Conversation $c): string
    {
        $texte = trim(strip_tags((string) ($c->messages->first()?->body ?: $c->request_detail)));

        return $texte === '' ? 'Aucun message.' : Str::limit($texte, 600);
    }

    /**
     * Avatar du compte portant l'adresse de l'emetteur : un createur
     * (photo, sinon initiales), a defaut un visiteur (initiales). Null
     * sans compte. Memorise par adresse : un meme emetteur revient souvent.
     */
    private static function avatarEmetteur(?string $email): ?string
    {
        static $memo = [];

        if (blank($email)) {
            return null;
        }

        return $memo[$email] ??= (function () use ($email): ?string {
            $profil = app(AffichageProfil::class);

            if ($createur = User::where('email', $email)->first()) {
                return $profil->photoUrl($createur) ?? $profil->medaillonCreatif($createur);
            }

            $visiteur = Visitor::where('email', $email)->first();

            return $visiteur ? $profil->medaillon($visiteur->initiales(), $visiteur->couleur()) : null;
        })();
    }

    /** Avatar (facultatif) collé a un titre et a une ligne grise dessous. */
    private static function personne(?string $avatar, ?string $titre, string $dessous): HtmlString
    {
        return new HtmlString('<span class="ub-personne">'
            .($avatar ? '<img src="'.e($avatar).'" alt="" class="ub-personne-avatar">' : '')
            .'<span class="ub-personne-textes"><span class="ub-personne-titre">'.e((string) $titre).'</span>'
            .($dessous !== '' ? '<span class="ub-personne-dessous">'.e($dessous).'</span>' : '')
            .'</span></span>');
    }
}
