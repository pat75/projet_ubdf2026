<?php

namespace App\Filament\Resources\Selections\Pages;

use App\Filament\Resources\Selections\SelectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSelection extends EditRecord
{
    protected static string $resource = SelectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
