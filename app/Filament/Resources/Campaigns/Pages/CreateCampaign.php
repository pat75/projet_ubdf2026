<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    /** La rubrique ne cree que des newsletters : le type n'est pas a choisir. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'newsletter';

        return $data;
    }
}
