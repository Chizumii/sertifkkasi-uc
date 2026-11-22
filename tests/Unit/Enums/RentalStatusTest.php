<?php

use App\Enums\RentalStatus;
use Carbon\CarbonImmutable;

test('menentukan status sewa dari tanggal hari ini (tanggal selesai eksklusif)', function (string $today, RentalStatus $status) {
    $start = CarbonImmutable::parse('2027-03-10');
    $end = CarbonImmutable::parse('2027-03-13');

    expect(RentalStatus::forPeriod($start, $end, CarbonImmutable::parse($today)))->toBe($status);
})->with([
    'sehari sebelum mulai' => ['2027-03-09', RentalStatus::Scheduled],
    'tanggal mulai' => ['2027-03-10', RentalStatus::Active],
    'hari terakhir' => ['2027-03-12', RentalStatus::Active],
    'tanggal selesai' => ['2027-03-13', RentalStatus::Completed],
    'setelah selesai' => ['2027-03-20', RentalStatus::Completed],
]);

test('memberi label Indonesia untuk tiap status sewa', function () {
    expect(collect(RentalStatus::cases())->map->label()->all())->toBe(['Terjadwal', 'Berjalan', 'Selesai']);
});
