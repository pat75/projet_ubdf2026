<?php

namespace App\Filament\Resources\Selections;

use App\Filament\Resources\Selections\Pages\CreateSelection;
use App\Filament\Resources\Selections\Pages\EditSelection;
use App\Filament\Resources\Selections\Pages\ListSelections;
use App\Filament\Resources\Selections\Schemas\SelectionForm;
use App\Filament\Resources\Selections\Tables\SelectionsTable;
use App\Models\Selection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SelectionResource extends Resource
{
    protected static ?string $model = Selection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $modelLabel = 'sélection';

    protected static ?string $pluralModelLabel = 'sélections';

    protected static string|\UnitEnum|null $navigationGroup = 'Éditorial';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SelectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SelectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSelections::route('/'),
            'create' => CreateSelection::route('/create'),
            'edit' => EditSelection::route('/{record}/edit'),
        ];
    }
}
