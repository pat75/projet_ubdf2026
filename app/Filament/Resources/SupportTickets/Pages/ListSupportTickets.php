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

    /** Discussions en cours (par defaut), cloturees ou a la corbeille, avec leur nombre. */
    public function getTabs(): array
    {
        $compte = fn (bool $closes) => SupportTicket::where('is_spam', false)->whereNull('corbeille_at')
            ->where('statut', $closes ? '=' : '!=', 'clos')->count();

        return [
            'en_cours' => Tab::make('En cours')->badge($compte(false))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('corbeille_at')->where('statut', '!=', 'clos')),
            'closes' => Tab::make('Clôturées')->badge($compte(true))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('corbeille_at')->where('statut', 'clos')),
            'corbeille' => Tab::make('Corbeille')->icon('heroicon-o-trash')
                ->badge(SupportTicket::where('is_spam', false)->whereNotNull('corbeille_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('corbeille_at')),
        ];
    }
}
