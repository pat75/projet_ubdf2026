<?php

namespace App\Filament\Resources\Selections\Pages;

use App\Filament\Resources\Selections\SelectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSelections extends ListRecords
{
    protected static string $resource = SelectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
