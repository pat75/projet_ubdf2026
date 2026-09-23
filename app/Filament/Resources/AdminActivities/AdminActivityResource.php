<?php

namespace App\Filament\Resources\AdminActivities;

use App\Filament\Resources\AdminActivities\Pages\ListAdminActivities;
use App\Filament\Resources\AdminActivities\Tables\AdminActivitiesTable;
use App\Models\AdminActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** Journal en lecture seule : rien ne s'y cree ni ne s'y modifie. */
class AdminActivityResource extends Resource
{
    protected static ?string $model = AdminActivity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'action';

    protected static ?string $pluralModelLabel = 'journal des actions';

    protected static string|\UnitEnum|null $navigationGroup = 'Réglages';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return AdminActivitiesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminActivities::route('/'),
        ];
    }
}
