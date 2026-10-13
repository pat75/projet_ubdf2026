<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportFiches\SupportFicheResource;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use Filament\Actions\Action;
use App\Models\SupportTicket;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSupportTickets extends ListRecords
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('base')->label('Base de connaissances')->icon('heroicon-o-book-open')->color('gray')
                ->url(SupportFicheResource::getUrl()),
        ];
    }

    /** Discussions en cours (par defaut) ou cloturees, avec leur nombre. */
    public function getTabs(): array
    {
        $compte = fn (bool $closes) => SupportTicket::where('is_spam', false)
            ->where('statut', $closes ? '=' : '!=', 'clos')->count();

        return [
            'en_cours' => Tab::make('En cours')->badge($compte(false))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', '!=', 'clos')),
            'closes' => Tab::make('Clôturées')->badge($compte(true))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'clos')),
        ];
    }
}
