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
use Illuminate\Database\Eloquent\Collection;

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
                IconColumn::make('is_spam')->label('Indésirable')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_spam')->label('Indésirable'),
            ])
            ->recordActions([
                Action::make('basculerSpam')->label(fn (Conversation $c) => $c->is_spam ? 'Rendre légitime' : 'Marquer indésirable')
                    ->icon('heroicon-o-shield-exclamation')->requiresConfirmation()
                    ->action(fn (Conversation $c) => $c->update(['is_spam' => ! $c->is_spam])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('spam')->label('Marquer indésirables')->icon('heroicon-o-shield-exclamation')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => Conversation::whereKey($records->pluck('id'))->update(['is_spam' => true])),
                ]),
            ]);
    }
}
