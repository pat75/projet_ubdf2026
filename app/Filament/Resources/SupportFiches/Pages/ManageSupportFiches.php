<?php

namespace App\Filament\Resources\SupportFiches\Pages;

use App\Filament\Resources\SupportFiches\SupportFicheResource;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSupportFiches extends ManageRecords
{
    protected static string $resource = SupportFicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retour')->label('Support & contact')->icon('heroicon-o-arrow-left')->color('gray')
                ->url(SupportTicketResource::getUrl()),
            CreateAction::make(),
        ];
    }
}
