<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Models\PromoCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Code')->required()->maxLength(40)
                ->default(fn () => Str::upper(Str::random(8)))
                ->helperText('Saisi par le créatif sur sa page formule.')
                ->unique(ignoreRecord: true),
            TextInput::make('discount')->label('Mois de formule offerts')->numeric()
                ->required()->minValue(1)->maxValue(24)->default(3),
            TextInput::make('max_uses')->label('Nombre d’utilisations')->numeric()->minValue(1)->default(1)
                ->helperText('Vide : sans limite.'),
            DateTimePicker::make('ends_at')->label('Expire le')->seconds(false),
            Toggle::make('is_active')->label('Actif')->default(true),
            Hidden::make('discount_type')->default(PromoCode::MOIS),
        ]);
    }
}
