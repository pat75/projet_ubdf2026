<?php

namespace App\Filament\Resources\Visitors;

use App\Filament\Resources\Visitors\Pages\EditVisitor;
use App\Filament\Resources\Visitors\Pages\ListVisitors;
use App\Filament\Resources\Visitors\Schemas\VisitorForm;
use App\Filament\Resources\Visitors\Tables\VisitorsTable;
use App\Models\Visitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Comptes visiteurs : ceux qui gardent un memo book, pas les creatifs.
 *
 * Table et modele distincts des creatifs (App\Models\Visitor) : un
 * visiteur n'a ni identifiant de sous-domaine, ni book, ni formule. La
 * liste reprend les memes gestes que celle des creatifs — avatar,
 * blocage — et laisse tomber ceux qui n'ont pas de sens ici.
 */
class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'visiteur';

    protected static ?string $pluralModelLabel = 'visiteurs';

    protected static ?string $recordTitleAttribute = 'email';

    protected static string|\UnitEnum|null $navigationGroup = 'Visiteurs';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return VisitorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitorsTable::configure($table);
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
            'index' => ListVisitors::route('/'),
            'edit' => EditVisitor::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
