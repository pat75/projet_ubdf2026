<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Visitors\VisitorResource;
use App\Filament\Support\ActionsCompte;
use App\Models\Visitor;
use App\Services\Espace\AffichageProfil;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Les dix derniers comptes visiteurs ouverts.
 *
 * Pas de selection ici : elle met un *book* en page d'accueil, un
 * visiteur n'en a pas. Reste la prise d'identite, qui sert a voir son
 * memo book tel qu'il le voit.
 */
class DernieresInscriptionsVisiteurs extends TableWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Derniers visiteurs inscrits'))
            ->headerActions([
                Action::make('tous')
                    ->label(__('Voir tous les visiteurs'))
                    ->icon('heroicon-o-arrow-right')
                    ->link()
                    ->url(VisitorResource::getUrl()),
            ])
            ->query(Visitor::query()->latest('created_at')->limit(10))
            ->paginated(false)
            ->columns([
                // Un visiteur ne depose pas de photo : c'est toujours le
                // medaillon d'initiales, aux couleurs des creatifs.
                ImageColumn::make('avatar')->label('')->extraCellAttributes(['class' => 'ub-cellule-avatar'])->circular()->imageSize(32)
                    ->getStateUsing(fn (Visitor $v) => app(AffichageProfil::class)
                        ->medaillon($v->initiales(), $v->couleur())),

                TextColumn::make('email')->label(__('Adresse mail'))
                    ->description(fn (Visitor $v) => $v->fullName())
                    ->wrap(),

                TextColumn::make('created_at')->label(__('Inscrit'))
                    ->since()->tooltip(fn (Visitor $v) => $v->created_at?->format('d/m/Y H:i'))
                    ->alignEnd(),
            ])
            ->recordActions([
                ActionsCompte::priseIdentiteVisiteur(),
            ]);
    }
}
