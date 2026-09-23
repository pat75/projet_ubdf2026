<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Invoice;
use App\Services\Admin\ExportCsv;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Numéro')->searchable()
                    ->formatStateUsing(fn (Invoice $f) => $f->numero()),
                TextColumn::make('user.login')->label('Créatif')->searchable()->sortable(),
                TextColumn::make('designation')->label('Désignation')->limit(40)->toggleable(),
                TextColumn::make('amount')->label('TTC')->money('EUR')->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->money('EUR')->label('Total')),
                TextColumn::make('gateway')->label('Paiement')->badge()->toggleable(),
                TextColumn::make('status')->label('État')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success', 'cancelled' => 'danger', default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid' => 'Payée', 'cancelled' => 'Annulée', default => 'En attente',
                    }),
                TextColumn::make('issued_at')->label('Date')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('issued_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('État')
                    ->options(['paid' => 'Payée', 'pending' => 'En attente', 'cancelled' => 'Annulée']),
                SelectFilter::make('gateway')->label('Paiement')
                    ->options(['payplug' => 'Payplug', 'paypal' => 'PayPal', 'cheque' => 'Chèque', 'virement' => 'Virement', 'autre' => 'Autre']),
                SelectFilter::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
                Filter::make('annee')->label('Année en cours')
                    ->query(fn (Builder $q) => $q->whereYear('issued_at', now()->year)),
            ])
            ->headerActions([
                Action::make('exporter')->label('Exporter en CSV')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($livewire) => app(ExportCsv::class)->reponse(
                        $livewire->getFilteredSortedTableQuery()->with('user'),
                        [
                            'Numéro' => fn (Invoice $f) => $f->numero(),
                            'Date' => fn (Invoice $f) => $f->issued_at?->format('Y-m-d'),
                            'Créatif' => fn (Invoice $f) => $f->user?->login,
                            'Société' => fn (Invoice $f) => $f->user?->company,
                            'Désignation' => fn (Invoice $f) => $f->designation,
                            'HT' => fn (Invoice $f) => number_format((float) $f->amount - (float) $f->vat, 2, ',', ''),
                            'TVA' => fn (Invoice $f) => number_format((float) $f->vat, 2, ',', ''),
                            'TTC' => fn (Invoice $f) => number_format((float) $f->amount, 2, ',', ''),
                            'Paiement' => fn (Invoice $f) => $f->gateway,
                            'État' => fn (Invoice $f) => $f->status,
                        ],
                        'factures',
                    )),
            ])
            ->recordActions([
                Action::make('voir')->label('Facture')->icon('heroicon-o-document-text')
                    ->url(fn (Invoice $f) => url('/admin/factures/'.$f->id), shouldOpenInNewTab: true),
                Action::make('pdf')->label('PDF')->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $f) => route('admin.facture.pdf', $f)),
            ]);
    }
}
