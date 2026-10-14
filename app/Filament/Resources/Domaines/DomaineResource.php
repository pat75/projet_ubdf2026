<?php

namespace App\Filament\Resources\Domaines;

use App\Filament\Resources\Domaines\Pages\ListDomaines;
use App\Filament\Resources\Domaines\Tables\DomainesTable;
use App\Models\Domaine;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** Noms de domaine commandes par les createurs : lecture seule. */
class DomaineResource extends Resource
{
    protected static ?string $model = Domaine::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $modelLabel = 'nom de domaine';

    protected static ?string $pluralModelLabel = 'noms de domaine';

    protected static string|\UnitEnum|null $navigationGroup = 'Créatifs';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return DomainesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDomaines::route('/'),
        ];
    }
}
