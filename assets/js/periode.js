// assets/js/periode.js
// Helper periode bulan terpusat (DRY).
// Dipakai view dengan filter <input type="month"> (tgl 1-5 → bulan lalu).
//
// Catatan: fungsi memakai jQuery ($) HANYA saat dipanggil (event time),
// jadi file ini aman dimuat setelah jQuery di index.php.
//   getPeriodeBulan('#filterBulan') -> {awal:'YYYY-MM-01', akhir:'YYYY-MM-DD'}
//   setDefaultBulan('#filterBulan', 5) -> isi input month (tgl<=cutoff = bulan lalu)

(function() {
    'use strict';
    if (typeof window.__IPM_PERIODE_V1__ !== 'undefined') return;
    window.__IPM_PERIODE_V1__ = true;

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    // Hitung tgl 1 s/d tgl akhir bulan dari input <input type="month">.
    window.getPeriodeBulan = function(inputSelector) {
        var sel = inputSelector || '#filterBulan';
        var val = (typeof $ !== 'undefined' ? $(sel).val() : null)
            || (document.querySelector(sel) || {}).value;
        if (!val) return { awal: '', akhir: '' };
        var parts = String(val).split('-');
        var year = parts[0], month = parts[1];
        var lastDay = new Date(year, month, 0).getDate();
        return {
            awal: year + '-' + month + '-01',
            akhir: year + '-' + month + '-' + lastDay
        };
    };

    // Isi default input month: tgl 1-cutoff tampilkan bulan lalu.
    window.setDefaultBulan = function(inputSelector, cutoff) {
        var sel = inputSelector || '#filterBulan';
        var cut = (typeof cutoff === 'number') ? cutoff : 5;
        var today = new Date();
        var targetDate = new Date();
        targetDate.setDate(1); // hindari bug rollover tgl 31
        if (today.getDate() <= cut) {
            targetDate.setMonth(targetDate.getMonth() - 1); // mundur ke bulan lalu
        }
        var v = targetDate.getFullYear() + '-' + pad2(targetDate.getMonth() + 1);
        if (typeof $ !== 'undefined' && $(sel).length) {
            $(sel).val(v);
        } else if (document.querySelector(sel)) {
            document.querySelector(sel).value = v;
        }
        return v;
    };
})();
