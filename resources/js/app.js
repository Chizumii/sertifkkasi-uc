/**
 * Perilaku ringan untuk view Blade (vanilla JS, tanpa library).
 * Semua fitur dipasang lewat atribut data-* sehingga halaman tetap berfungsi tanpa JavaScript.
 * Kalkulasi biaya di sini hanya pratinjau; sistem menghitung ulang saat data disimpan.
 */

const rupiah = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

/**
 * Format angka menjadi teks Rupiah, misalnya "Rp 1.250.000".
 */
function formatRupiah(amount) {
    return `Rp ${rupiah.format(Math.round(amount))}`;
}

/**
 * Konfirmasi sebelum mengirim form yang memiliki data-confirm (misalnya hapus atau tolak).
 * Memakai dialog modal #confirm-dialog di layout, bukan window.confirm bawaan browser.
 * Atribut opsional: data-confirm-title (judul) dan data-confirm-button (label tombol setuju).
 */
const confirmDialog = document.getElementById('confirm-dialog');
let pendingConfirmForm = null;

if (confirmDialog) {
    confirmDialog.addEventListener('close', () => {
        const form = pendingConfirmForm;
        pendingConfirmForm = null;

        if (form && confirmDialog.returnValue === 'confirm') {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    });
}

document.addEventListener(
    'submit',
    (event) => {
        const form = event.target;

        if (!form.dataset.confirm) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        if (!confirmDialog || typeof confirmDialog.showModal !== 'function') {
            if (window.confirm(form.dataset.confirm)) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
            return;
        }

        pendingConfirmForm = form;
        confirmDialog.querySelector('#confirm-dialog-title').textContent = form.dataset.confirmTitle ?? 'Konfirmasi';
        confirmDialog.querySelector('#confirm-dialog-message').textContent = form.dataset.confirm;
        confirmDialog.querySelector('[data-confirm-accept]').textContent = form.dataset.confirmButton ?? 'Ya, lanjutkan';
        confirmDialog.returnValue = '';
        confirmDialog.showModal();
    },
    true,
);

/**
 * Tombol kirim ditandai sibuk setelah form dikirim, supaya tidak terkirim dua kali.
 */
document.addEventListener('submit', (event) => {
    if (event.defaultPrevented || event.target.method === 'dialog') {
        return;
    }

    event.target.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.setAttribute('aria-busy', 'true');
        button.disabled = true;
    });
});

/**
 * Saat kembali ke halaman lewat tombol Back, tombol yang tadi sibuk diaktifkan lagi.
 */
window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[aria-busy="true"]').forEach((button) => {
        button.removeAttribute('aria-busy');
        button.disabled = false;
    });
});

/**
 * Pratinjau gambar yang dipilih pada input file dengan data-image-preview="id-wadah".
 */
document.querySelectorAll('input[type="file"][data-image-preview]').forEach((input) => {
    const container = document.getElementById(input.dataset.imagePreview);
    if (!container) {
        return;
    }

    input.addEventListener('change', () => {
        const previous = container.querySelector('img');
        if (previous) {
            URL.revokeObjectURL(previous.src);
            previous.remove();
        }

        const file = input.files?.[0];
        if (!file || !file.type.startsWith('image/')) {
            container.hidden = true;
            return;
        }

        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = container.dataset.previewAlt ?? '';
        image.className = 'max-h-48 w-auto max-w-full rounded-input object-contain shadow-card';
        container.append(image);
        container.hidden = false;
    });
});

/**
 * Menampilkan atau menyembunyikan blok sesuai nilai field lain di form yang sama.
 * data-depends-on="nama-field", lalu data-show-values="a,b" (tampil bila nilainya a atau b)
 * atau data-hide-values="a,b" (sembunyi bila nilainya a atau b). Isian di blok yang tersembunyi dinonaktifkan.
 */
document.querySelectorAll('[data-depends-on]').forEach((block) => {
    const form = block.closest('form');
    const name = block.dataset.dependsOn;
    if (!form) {
        return;
    }

    const showValues = block.dataset.showValues?.split(',');
    const hideValues = block.dataset.hideValues?.split(',');

    const update = () => {
        const field = form.querySelector(`[name="${name}"]:checked`) ?? form.querySelector(`select[name="${name}"]`);
        const value = field?.value ?? '';
        const visible = showValues ? showValues.includes(value) : !(hideValues ?? []).includes(value);

        block.hidden = !visible;
        block.querySelectorAll('input, select, textarea').forEach((control) => {
            control.disabled = !visible;
        });
    };

    form.querySelectorAll(`[name="${name}"]`).forEach((field) => field.addEventListener('change', update));
    update();
});

/**
 * Tanggal selesai otomatis di form periode sewa.
 * Harian: tanggal mulai + jumlah hari. Bulanan: tanggal mulai + jumlah hari di bulan tanggal mulai.
 */
document.querySelectorAll('[data-end-date-form]').forEach((form) => {
    const startInput = form.querySelector('[data-start-date]');
    const daysInput = form.querySelector('[data-days]');
    const endInput = form.querySelector('[data-end-date]');
    if (!startInput || !endInput) {
        return;
    }

    const monthlyDays = form.querySelector('[data-monthly-days]');

    const update = () => {
        const type = form.querySelector('[name="rental_type"]:checked')?.value ?? 'daily';
        const [year, month, day] = (startInput.value || '').split('-').map(Number);
        if (!year || !month || !day) {
            endInput.value = '';
            return;
        }

        const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();
        if (monthlyDays) {
            monthlyDays.textContent = `${daysInMonth} hari (1 bulan penuh)`;
        }
        if (daysInput) {
            daysInput.max = String(daysInMonth - 1);
        }

        const days = type === 'monthly' ? daysInMonth : Number(daysInput?.value);
        if (!days || days < 1) {
            endInput.value = '';
            return;
        }

        const end = new Date(Date.UTC(year, month - 1, day + days));
        endInput.value = end.toISOString().slice(0, 10);
    };

    form.querySelectorAll('[name="rental_type"]').forEach((radio) => radio.addEventListener('change', update));
    startInput.addEventListener('input', update);
    daysInput?.addEventListener('input', update);
    update();
});

/**
 * Pratinjau biaya di form sewa tahap B: biaya sewa garasi terpilih,
 * ditambah biaya cas bila mobil listrik memilih garasi mobil listrik.
 */
document.querySelectorAll('[data-rental-preview]').forEach((form) => {
    const capacity = Number(form.dataset.batteryCapacity) || 0;
    const isElectricVehicle = form.dataset.electricVehicle === '1';
    const batteryInput = form.querySelector('[data-current-battery]');
    const output = {
        rental: form.querySelector('[data-preview-rental]'),
        chargingRow: form.querySelector('[data-preview-charging-row]'),
        kwh: form.querySelector('[data-preview-kwh]'),
        charging: form.querySelector('[data-preview-charging]'),
        total: form.querySelector('[data-preview-total]'),
    };

    const update = () => {
        const garage = form.querySelector('input[name="garage_id"]:checked');
        if (!garage) {
            return;
        }

        const rentalCost = Number(garage.dataset.rentalCost);
        let chargingKwh = 0;
        let chargingCost = 0;
        const charges = isElectricVehicle && garage.dataset.electricGarage === '1';

        if (charges && batteryInput && batteryInput.value !== '') {
            const remaining = Math.min(Math.max(Number(batteryInput.value), 0), capacity);
            chargingKwh = Math.round((capacity - remaining) * 100) / 100;
            chargingCost = Math.round(chargingKwh * Number(garage.dataset.kwhPrice));
        }

        output.rental.textContent = formatRupiah(rentalCost);
        output.chargingRow.hidden = !charges;
        output.kwh.textContent = `${chargingKwh.toLocaleString('id-ID')} kWh`;
        output.charging.textContent = formatRupiah(chargingCost);
        output.total.textContent = formatRupiah(rentalCost + chargingCost);
    };

    form.querySelectorAll('input[name="garage_id"]').forEach((radio) => radio.addEventListener('change', update));
    batteryInput?.addEventListener('input', update);
    update();
});

/**
 * Pratinjau rentang kode di form buat garasi, misalnya "Kode yang dibuat: E006 sampai E010".
 */
document.querySelectorAll('[data-garage-batch]').forEach((form) => {
    const countInput = form.querySelector('[data-garage-count]');
    const range = form.querySelector('[data-garage-range]');
    const warning = form.querySelector('[data-garage-quota-warning]');

    const update = () => {
        const type = form.querySelector('input[name="type"]:checked');
        const count = Number(countInput?.value);

        if (!type || !count || count < 1) {
            range.textContent = 'Pilih tipe dan isi jumlah untuk melihat kode yang akan dibuat.';
            warning.hidden = true;
            return;
        }

        const nextCode = type.dataset.nextCode;
        const remaining = Number(type.dataset.remaining);

        if (count > remaining) {
            warning.textContent = `Sisa kuota hanya ${remaining} garasi.`;
            warning.hidden = false;
        } else {
            warning.hidden = true;
        }

        const prefix = nextCode.charAt(0);
        const first = Number(nextCode.slice(1));
        const last = first + Math.min(count, remaining) - 1;
        const lastCode = prefix + String(last).padStart(3, '0');

        range.textContent = count === 1 || first === last
            ? `Kode yang dibuat: ${nextCode}`
            : `Kode yang dibuat: ${nextCode} sampai ${lastCode}`;
    };

    form.querySelectorAll('input[name="type"]').forEach((radio) => radio.addEventListener('change', update));
    countInput?.addEventListener('input', update);
    update();
});
