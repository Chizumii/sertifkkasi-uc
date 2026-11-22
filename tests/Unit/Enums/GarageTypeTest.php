<?php

use App\Enums\GarageType;

test('memakai prefiks kode S, C, dan E sesuai tipe garasi', function (GarageType $type, string $prefix, string $label) {
    expect($type->codePrefix())->toBe($prefix)
        ->and($type->label())->toBe($label);
})->with([
    'sepeda' => [GarageType::Bicycle, 'S', 'Garasi Sepeda'],
    'mobil' => [GarageType::Car, 'C', 'Garasi Mobil'],
    'mobil listrik' => [GarageType::Electric, 'E', 'Garasi Mobil Listrik'],
]);
