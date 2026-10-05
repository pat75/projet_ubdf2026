<?php

namespace App\Filament\Resources\Conversations\Tables;

use App\Models\Conversation;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Reçue le')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('user.login')->label('Créatif')->searchable()->sortable(),
                TextColumn::make('sender_name')->label('Émetteur')->searchable()
                    ->description(fn (Conversation $c) => $c->sender_email),
                TextColumn::make('subject')->label('Objet')->formatStateUsing(fn (Conversation $c) => $c->objet())->toggleable(),
                TextColumn::make('messages_count')->label('Messages')->counts('messages'),
                // Etat lu comme « legitime » : coche verte pour un message
                // normal, croix rouge pour un indesirable.
                IconColumn::make('legitime')->label('Indésirable')->boolean()
                    ->getStateUsing(fn (Conversation $c) => ! $c->is_spam),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with('messages'))
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
}
