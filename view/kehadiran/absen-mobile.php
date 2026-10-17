<?php
// Default filter: awal bulan s/d hari ini
$awalBulan = date('Y-m-01');
$hariIni = date('Y-m-d');
?>
<style>
    .preview-thumbnail {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background: #fff;
    }

    .preview-thumbnail:hover {
        transform: scale(1.1);
        border-color: #1D5C96 !important;
        box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4) !important;
    }

    #modalPreviewSelfie .modal-content {
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Kehadiran /</span> Absensi Mobile
    </h4>

    <div class="alert alert-info py-2" role="alert">
        <i class="bx bx-mobile-alt me-1"></i>
        Rekap absensi masuk/keluar dari <strong>aplikasi mobile</strong> (validasi GPS + foto selfie).
        Kelola titik lokasi & radius di <a href="?page=data-site">Master Data &rarr; Data Site</a>.
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" id="filterAwal" class="form-control" value="<?= $awalBulan ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" id="filterAkhir" class="form-control" value="<?= $hariIni ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Site Absen</label>
                    <select id="filterSite" class="form-select">
                        <option value="">Semua Site</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="tepat">Tepat Waktu</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="selesai">Sudah Selesai (Absen Keluar)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Log Absensi Aplikasi</h5>
            <span class="badge bg-label-primary" id="jumlahData">0 data</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover border-top tabelAbsenMobile">
                <thead>
                    <tr class="align-middle text-nowrap">
                        <th class="text-center">No</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Lokasi Absen</th>
                        <th>Status</th>
                        <th class="text-center">Foto Selfie</th>
                    </tr>
                </thead>
                <tbody id="tbodyAbsenMobile" class="table-border-bottom-0">
                    <tr>
                        <td colspan="8" class="text-center py-4">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewSelfie" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                <img src="" id="imgSelfie" class="img-fluid rounded shadow-lg border border-3 border-white" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<script>
    const api_get_absen_mobile = 'app/controller/get-data/get-absen-mobile.php';

    function previewSelfie(src) {
        document.getElementById('imgSelfie').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewSelfie')).show();
    }

    // Load opsi site (reuse endpoint filter yang sudah ada)
    async function loadFilterSiteAbsen() {
        try {
            const res = await fetch('app/controller/get-data/get-site-departemen-jabatan.php');
            const data = await res.json();

            let opt = '<option value="">Semua Site</option>';
            data.site.forEach(s => opt += `<option value="${s.id_site}">${s.nama_site}</option>`);
            document.getElementById('filterSite').innerHTML = opt;
        } catch (e) {
            console.error('Gagal memuat opsi site:', e);
        }
    }

    async function loadTabelAbsenMobile() {
        const awal = document.getElementById('filterAwal').value;
        const akhir = document.getElementById('filterAkhir').value;
        const site = document.getElementById('filterSite').value;
        const status = document.getElementById('filterStatus').value;

        const params = new URLSearchParams({
            start_date: awal,
            end_date: akhir,
            site: site,
            status: status,
            _: new Date().getTime()
        });

        const res = await fetch(`${api_get_absen_mobile}?${params.toString()}`);
        const html = await res.text();

        if ($.fn.DataTable.isDataTable('.tabelAbsenMobile')) {
            $('.tabelAbsenMobile').DataTable().destroy();
        }

        document.getElementById('tbodyAbsenMobile').innerHTML = html;

        // Hitung jumlah baris data (bukan baris "belum ada data")
        const rows = document.querySelectorAll('#tbodyAbsenMobile tr').length;
        const kosong = document.getElementById('tbodyAbsenMobile').querySelector('.text-muted');
        document.getElementById('jumlahData').innerText = (rows === 1 && kosong) ? '0 data' : rows + ' data';

        $('.tabelAbsenMobile').DataTable({
            responsive: true,
            pageLength: 25,
            order: [] // pertahankan urutan dari server (tanggal terbaru dulu)
        });
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadFilterSiteAbsen();
        await loadTabelAbsenMobile();

        ['filterAwal', 'filterAkhir', 'filterSite', 'filterStatus'].forEach(id => {
            document.getElementById(id).addEventListener('change', loadTabelAbsenMobile);
        });
    });
</script>
