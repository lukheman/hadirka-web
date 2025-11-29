<?php
// Bulan sekarang
$bulan_ini = date('m');
$tahun_ini = date('Y');

// Ambil bulan sebelumnya
$bulan_lalu = date('m', strtotime('-1 month'));
$tahun_lalu = date('Y', strtotime('-1 month'));

// Set periode tetap: 21 bulan lalu — 20 bulan ini
$tglAwal = date("$tahun_lalu-$bulan_lalu-21");
$tglAkhir = date("$tahun_ini-$bulan_ini-20");
?>

<div class="container-xxl mt-3 fade-in">

    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Data Karyawan /</span> Absensi
    </h4>
    <button class="btn btn-success mb-3" id="btnTambahAbsensi">
        <i class="fa fa-plus"></i> Tambah Absensi
    </button>


    <!-- Import CSV -->
    <div class="mb-4 p-3 bg-light rounded-3 shadow-sm">
        <label for="file_csv" class="form-label fw-bold text-primary">
            <i class="fa fa-upload fa-lg"></i> Import CSV Absensi
        </label>
        <form id="formImportCSV" enctype="multipart/form-data">
            <div class="input-group mb-2">
                <input type="file" name="file_csv" id="file_csv" class="form-control" required data-bs-toggle="tooltip" title="Pilih file CSV untuk import data absensi">
                <button type="submit" class="btn btn-success" id="btnImportCSV" data-bs-toggle="tooltip" title="Klik untuk mengimpor data dari file CSV">
                    <i class="fa fa-upload"></i> Import CSV
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Tanggal -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <label for="filterSite" class="form-label fw-bold text-primary">
                <i class="fa fa-location fa-lg"></i> Site
            </label>
            <select id="filterSite" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site">
                <option value="">-- Pilih Site --</option>
                <?php foreach (data_site() as $d) {
                    echo "<option value='{$d['id_site']}'>{$d['nama_site']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterJabatan" class="form-label fw-bold text-primary">
                <i class="fa fa-user fa-lg"></i> Jabatan
            </label>
            <select id="filterJabatan" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan jabatan">
                <option value="">-- Semua Jabatan --</option>
                <?php foreach (data_jabatan() as $d) {
                    echo "<option value='{$d['id_jabatan']}'>{$d['nama_jabatan']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="tglAwal" class="form-label fw-bold text-primary">
                <i class="fa fa-calendar fa-lg"></i> Dari Tanggal
            </label>
            <input type="date" id="tglAwal" class="form-control form-control-sm" value="<?= $tglAwal ?>" data-bs-toggle="tooltip" title="Pilih tanggal mulai periode absensi">
        </div>
        <div class="col-md-3">
            <label for="tglAkhir" class="form-label fw-bold text-primary">
                <i class="fa fa-calendar fa-lg"></i> Sampai Tanggal
            </label>
            <input type="date" id="tglAkhir" class="form-control form-control-sm" value="<?= $tglAkhir ?>" data-bs-toggle="tooltip" title="Pilih tanggal akhir periode absensi">
        </div>
    </div>

    <!-- Tabel Absensi -->
    <div class="card">
        <h5 class="card-header">
            <i class="fa fa-table fa-lg"></i> Data Absensi
        </h5>
        <div class="card-body">
            <!-- Spinner Loading -->
            <div class="text-center" id="loadingSpinner" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat data...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data absensi...</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped MyTableAbsensi" id="tabelAbsensi">
                    <thead class="table-info">
                        <tr>
                            <th class="text-center"><i class="fa fa-hashtag"></i> No</th>
                            <th class="text-center"><i class="fa fa-user"></i> Nama Lengkap</th>
                            <th class="text-center"><i class="fa fa-building"></i> Departemen</th>
                            <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                            <th class="text-center"><i class="fa fa-map-marker"></i> Site</th>
                            <th class="text-center"><i class="fa fa-check-circle text-success"></i> Hadir</th>
                            <th class="text-center"><i class="fa fa-clock"></i>Overtime 1</th>
                            <th class="text-center"><i class="fa fa-clock"></i>Overtime 2</th>
                            <th class="text-center"><i class="fa fa-sun"></i> SPL</th>
                            <th class="text-center"><i class="fa fa-clock"></i>Overtime 1 SPL</th>
                            <th class="text-center"><i class="fa fa-clock"></i>Overtime 2 SPL</th>
                            <th class="text-center"><i class="fa fa-times-circle text-danger"></i> Mangkir</th>
                            <th class="text-center"><i class="fa fa-exclamation-triangle text-warning"></i> Izin</th>
                            <th class="text-center"><i class="fa fa-medkit text-info"></i> Sakit</th>
                            <th class="text-center"><i class="fa fa-plane text-purple"></i> Cuti</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetailAbsensi" tabindex="-1" aria-labelledby="modalDetailAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetailAbsensiLabel"><i class="fa fa-info-circle"></i> Detail Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailAbsensiBody">
                <!-- Akan di-load via fetch -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat detail...</span>
                    </div>
                    <p>Memuat detail absensi...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tabelBody = document.querySelector("#tabelAbsensi tbody");
        const modalEl = document.getElementById("modalDetailAbsensi");
        const modal = new bootstrap.Modal(modalEl);
        const modalBody = document.getElementById("detailAbsensiBody");
        const tglAwalInput = document.getElementById("tglAwal");
        const tglAkhirInput = document.getElementById("tglAkhir");
        const filterSite = document.getElementById("filterSite");
        const filterJabatan = document.getElementById("filterJabatan");

        // Event delegation untuk klik row
        tabelBody.addEventListener("click", function(e) {
            const tr = e.target.closest("tr");
            if (!tr || !tr.dataset.id) return;

            const idKaryawan = tr.dataset.id;

            modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <p>Memuat detail absensi...</p>
            </div>
        `;
            modal.show();

            fetch(`app/controller/get-data/get-absensi-detail.php?id_karyawan=${idKaryawan}&tgl_a=${tglAwalInput.value}&tgl_b=${tglAkhirInput.value}`)
                .then(res => res.text())
                .then(htmlDetail => {
                    modalBody.innerHTML = htmlDetail;
                });
        });

        window.loadAbsensi = function() {
            const site = filterSite.value;
            const jabatan = filterJabatan.value;
            const tgl_a = tglAwalInput.value;
            const tgl_b = tglAkhirInput.value;

            if (!site) return showToast("Pilih Site terlebih dahulu!", "info");
            if (!tgl_a || !tgl_b) return showToast("Tanggal tidak boleh kosong!", "info");
            if (new Date(tgl_a) > new Date(tgl_b)) return showToast("Tanggal awal tidak boleh lebih besar dari tanggal akhir!", "info");

            const spinner = document.getElementById("loadingSpinner");
            spinner.style.display = "block";

            fetch(`app/controller/get-data/get-absensi.php?tgl_a=${tgl_a}&tgl_b=${tgl_b}&site=${site}&jabatan=${jabatan}`)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableAbsensi")) {
                        $(".MyTableAbsensi").DataTable().destroy();
                    }
                    tabelBody.innerHTML = html;
                    $(".MyTableAbsensi").DataTable({
                        responsive: true,
                        pageLength: 10,
                        destroy: true
                    });
                })
                .finally(() => {
                    spinner.style.display = "none";
                });
        };

        // Auto load saat halaman terbuka jika filter lengkap
        if (filterSite.value && tglAwalInput.value && tglAkhirInput.value) {
            loadAbsensi();
        }

        // Event CSV Import
        document.getElementById("formImportCSV").addEventListener("submit", function(e) {
            e.preventDefault();

            const fileInput = document.getElementById("file_csv");
            const btnImport = document.getElementById("btnImportCSV");
            const container = document.querySelector(".container-xxl");

            if (!fileInput.files.length)
                return showToast("Pilih file CSV terlebih dahulu!", "info");

            const formData = new FormData();
            formData.append("file_csv", fileInput.files[0]);

            // 🔵 TOMBOL BERUBAH LOADING
            btnImport.disabled = true;
            btnImport.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status"></span> Mengimpor...
    `;

            // 🔵 HALAMAN JADI BURAM SELAMA PROSES IMPORT
            container.classList.add("blur-loading");

            fetch("app/controller/csv-control/csv-absen.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        showToast(data.message, "success");
                        loadAbsensi();
                    } else {
                        showToast(data.message, "error");
                    }
                })
                .catch(() => {
                    showToast("Terjadi kesalahan saat import CSV", "error");
                })
                .finally(() => {
                    // 🔵 KEMBALIKAN TOMBOL NORMAL
                    btnImport.disabled = false;
                    btnImport.innerHTML = `<i class="fa fa-upload"></i> Import CSV`;

                    // 🔵 HILANGKAN EFEK BLUR
                    container.classList.remove("blur-loading");
                });
        });


        // Auto reload filter
        filterSite.addEventListener("change", loadAbsensi);
        filterJabatan.addEventListener("change", loadAbsensi);
        tglAwalInput.addEventListener("change", loadAbsensi);
        tglAkhirInput.addEventListener("change", loadAbsensi);
    });
</script>