<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /** Les trois etats d'une newsletter : en cours d'ecriture, en attente, partie. */
    public function getTabs(): array
    {
        return [
            'brouillons' => Tab::make('Brouillons')->modifyQueryUsing(
                fn (Builder $query) => $query->whereNull('sent_at')->whereNull('scheduled_at'),
            ),
            'programmees' => Tab::make('Programmées')->modifyQueryUsing(
                fn (Builder $query) => $query->whereNull('sent_at')->whereNotNull('scheduled_at'),
            ),
            'envoyees' => Tab::make('Envoyées')->modifyQueryUsing(
                fn (Builder $query) => $query->whereNotNull('sent_at'),
            ),
        ];
    }
}
