<?php

use App\Filament\Resources\PromoCodes\Pages\ListPromoCodes;
use App\Models\Admin;
use App\Models\PromoCode;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');
    $this->utilise = PromoCode::create(['code' => 'USED', 'discount' => 1, 'uses' => 3, 'is_active' => true]);
    $this->neuf = PromoCode::create(['code' => 'NEW', 'discount' => 1, 'uses' => 0, 'is_active' => true]);
});

it('filtre les codes utilises et non utilises', function () {
    Livewire::test(ListPromoCodes::class)
        ->filterTable('utilise', true)
        ->assertCanSeeTableRecords([$this->utilise])
        ->assertCanNotSeeTableRecords([$this->neuf])
        ->filterTable('utilise', false)
        ->assertCanSeeTableRecords([$this->neuf])
        ->assertCanNotSeeTableRecords([$this->utilise]);
});

it('supprime plusieurs codes en masse', function () {
    Livewire::test(ListPromoCodes::class)
        ->callTableBulkAction('delete', [$this->utilise, $this->neuf]);

    expect(PromoCode::count())->toBe(0);
});
