<?php

namespace App\Filament\Resources\AdminActivities\Tables;

use App\Models\AdminActivity;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AdminActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Quand')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('admin_name')->label('Administrateur')->searchable(),
                TextColumn::make('action')->label('Action')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'created' => 'Création', 'updated' => 'Modification',
                        'deleted' => 'Suppression', default => 'Restauration',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'created' => 'success', 'deleted' => 'danger', default => 'gray',
                    }),
                TextColumn::make('subject_type')->label('Objet')
                    ->formatStateUsing(fn (AdminActivity $ligne) => $ligne->sujet())
                    ->description(fn (AdminActivity $ligne) => $ligne->subject_label),
                TextColumn::make('changes')->label('Champs modifiés')
                    ->formatStateUsing(fn (?array $state) => $state ? implode(', ', array_keys($state)) : '—')
                    ->wrap(),
                TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')->label('Action')->options([
                    'created' => 'Création', 'updated' => 'Modification',
                    'deleted' => 'Suppression', 'restored' => 'Restauration',
                ]),
                SelectFilter::make('subject_type')->label('Objet')->options(fn () => AdminActivity::query()
                    ->distinct()->pluck('subject_type', 'subject_type')
                    ->map(fn (string $type) => class_basename($type))->all()),
            ])
            ->recordActions([
                Action::make('detail')->label('Détail')->icon('heroicon-o-eye')
                    ->modalHeading('Modifications')
                    ->modalContent(fn (AdminActivity $ligne) => new HtmlString(
                        '<pre class="overflow-x-auto text-xs">'
                        .e(json_encode($ligne->changes ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        .'</pre>'
                    ))
                    ->modalSubmitAction(false)
                    ->visible(fn (AdminActivity $ligne) => filled($ligne->changes)),
            ]);
    }
}
