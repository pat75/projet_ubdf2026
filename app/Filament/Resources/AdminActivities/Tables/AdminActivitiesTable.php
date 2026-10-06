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
    private const ACTIONS = [
        'created' => 'Création', 'updated' => 'Modification', 'deleted' => 'Suppression',
        'restored' => 'Restauration', 'login' => 'Connexion', 'logout' => 'Déconnexion',
        'prise_identite' => 'Prise d’identité',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Quand')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('admin_name')->label('Administrateur')->searchable(['admin_name', 'ip'])
                    ->formatStateUsing(fn (AdminActivity $ligne) => new HtmlString('<strong>'.e($ligne->admin_name).'</strong> '.e($ligne->ip)))
                    ->wrap(false),
                TextColumn::make('action')->label('Action')->badge()
                    ->formatStateUsing(fn (string $state) => self::ACTIONS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'created', 'login' => 'success', 'deleted' => 'danger', 'prise_identite' => 'warning', default => 'gray',
                    }),
                TextColumn::make('subject_type')->label('Objet')
                    ->formatStateUsing(fn (AdminActivity $ligne) => new HtmlString('<strong>'.e($ligne->sujet()).'</strong> '.e($ligne->subject_label)))
                    ->wrap(false),
                TextColumn::make('changes')->label('Champs modifiés')
                    ->formatStateUsing(fn (?array $state) => $state ? implode(', ', array_keys($state)) : '—')
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')->label('Action')->options(self::ACTIONS),
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
