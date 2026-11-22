<?php

use App\Models\Garage;
use App\Models\Rental;
use Illuminate\Support\Carbon;

/**
 * Sewa tanpa database dengan garasi berharga harian Rp 50.000.
 */
function rentalBetween(string $startDate, string $endDate): Rental
{
    $rental = new Rental(['start_date' => $startDate, 'end_date' => $endDate]);
    $rental->setRelation('garage', new Garage(['daily_rate' => 50000]));

    return $rental;
}

test('menganggap sewa aktif dari tanggal mulai sampai sehari sebelum tanggal selesai', function (string $today, bool $active) {
    $this->travelTo($today);

    expect(rentalBetween('2027-03-10', '2027-03-13')->isActiveToday())->toBe($active);
})->with([
    'sehari sebelum mulai' => ['2027-03-09', false],
    'tanggal mulai' => ['2027-03-10', true],
    'hari terakhir' => ['2027-03-12', true],
    'tanggal selesai' => ['2027-03-13', false],
]);

test('menganggap sewa selesai mulai tanggal selesai, sehingga sewa yang akan datang masih bisa diperpanjang', function (string $today, bool $finished) {
    $this->travelTo($today);

    expect(rentalBetween('2027-03-10', '2027-03-13')->isFinished())->toBe($finished);
})->with([
    'belum mulai' => ['2027-03-05', false],
    'sedang berjalan' => ['2027-03-11', false],
    'tanggal selesai' => ['2027-03-13', true],
]);

test('menghitung perpanjangan harian dari tanggal selesai lama', function () {
    $preview = rentalBetween('2027-01-01', '2027-01-20')->extensionPreview(Carbon::parse('2027-02-19'));

    expect($preview)->toBe([
        'old_end_date' => '2027-01-20',
        'new_end_date' => '2027-02-19',
        'extension_days' => 30,
        'rental_type_label' => 'Harian',
        'months' => null,
        'daily_rate' => 50000,
        'additional_cost' => 1500000,
    ]);
});

test('menghitung harian bila hari tambahan kurang dari jumlah hari bulan tanggal selesai lama', function () {
    $preview = rentalBetween('2027-01-01', '2027-01-29')->extensionPreview(Carbon::parse('2027-02-01'));

    expect($preview['rental_type_label'])->toBe('Harian')
        ->and($preview['extension_days'])->toBe(3)
        ->and($preview['additional_cost'])->toBe(150000);
});

test('menghitung perpanjangan bulanan per blok bulan mulai dari tanggal selesai lama', function (string $oldEnd, string $newEnd, int $months, int $cost) {
    $preview = rentalBetween('2027-01-01', $oldEnd)->extensionPreview(Carbon::parse($newEnd));

    expect($preview['rental_type_label'])->toBe('Bulanan')
        ->and($preview['months'])->toBe($months)
        ->and($preview['additional_cost'])->toBe($cost);
})->with([
    'tepat satu bulan Januari' => ['2027-01-01', '2027-02-01', 1, 1250000],
    '45 hari = 2 blok' => ['2027-01-01', '2027-02-15', 2, 2500000],
    'tepat dua blok' => ['2027-01-01', '2027-03-01', 2, 2500000],
    'sehari melewati dua blok = 3 blok' => ['2027-01-01', '2027-03-02', 3, 3750000],
    '31 hari dari 20 Januari' => ['2027-01-20', '2027-02-20', 1, 1250000],
]);
