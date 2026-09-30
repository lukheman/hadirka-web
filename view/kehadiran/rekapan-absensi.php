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

<style>
    .kal-scroll {
        overflow-x: auto;
    }
    .kal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
        min-width: 720px;
    }
    .kal-head {
        text-align: center;
        font-weight: 700;
        font-size: 0.8rem;
        color: #566a7f;
        padding: 6px 0;
        background: #f0f2f4;
        border-radius: 6px;
    }
    .kal-month {
        grid-column: 1 / -1;
        font-weight: 700;
        color: #fff;
        background: #696cff;
        border-radius: 6px;
        padding: 4px 12px;
        margin-top: 4px;
        font-size: 0.85rem;
    }
    .kal-day {
        min-height: 88px;
        border-radius: 8px;
        padding: 6px;
        border: 1px solid #e3e6ea;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .kal-day.empty {
        background: transparent;
        border-style: dashed;
    }
    .kal-date {
        font-weight: 700;
        font-size: 0.95rem;
        line-height: 1;
    }
    .kal-date small {
        font-weight: 400;
        color: #8592a3;
    }
    .kal-status {
        font-size: 0.68rem;
        font-weight: 700;
        border-radius: 20px;
        padding: 1px 8px;
        display: inline-block;
        width: fit-content;
    }
    .kal-jam {
        font-size: 0.7rem;
        color: #566a7f;
        line-height: 1.3;
    }
    .kal-Masuk { background: #e8fadf; border-color: #71dd37; }
    .kal-Masuk .kal-status { background: #71dd37; color: #fff; }
    .kal-SPL { background: #fff2d6; border-color: #ffab00; }
    .kal-SPL .kal-status { background: #ffab00; color: #fff; }
    .kal-OFF { background: #f1f2f4; border-color: #d9dee3; }
    .kal-OFF .kal-status { background: #8592a3; color: #fff; }
    .kal-Izin { background: #e7e7ff; border-color: #696cff; }
    .kal-Izin .kal-status { background: #696cff; color: #fff; }
    .kal-Sakit { background: #d7f5fc; border-color: #03c3ec; }
    .kal-Sakit .kal-status { background: #03c3ec; color: #fff; }
    .kal-Cuti { background: #ede4f7; border-color: #6f42c1; }
    .kal-Cuti .kal-status { background: #6f42c1; color: #fff; }
    .kal-Mangkir { background: #ffe0db; border-color: #ff3e1d; }
    .kal-Mangkir .kal-status { background: #ff3e1d; color: #fff; }
    .kal-Resign, .kal-New { background: #e9eaec; border-color: #8592a3; }
    .kal-Resign .kal-status, .kal-New .kal-status { background: #566a7f; color: #fff; }
    .kal-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .kal-legend span {
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 20px;
        padding: 2px 10px;
        color: #fff;
    }
</style>

<div class="container-xxl mt-3 fade-in">

    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Data Karyawan /</span> Absensi
    </h4>
    <!-- <button class="btn btn-success mb-3" id="btnTambahAbsensi">
        <i class="fa fa-plus"></i> Tambah Absensi
    </button> -->


    <!-- Import CSV -->
    <div class="mb-4 p-3 bg-light rounded-3 shadow-sm">
        <p class="instruction-text mb-3">
            Unggah file Excel/CSV sesuai format kolom untuk memperbarui database secara masal. Dengan Ketentuan Warna:
        </p>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class='badge' style="background-color: yellow; color: black;">SPL / OFF</span>
            <span class='badge bg-primary'>IZIN</span>
            <span class='badge bg-info'>SAKIT / SD</span>
            <span class='badge bg-warning'>CUTI / CM</span>
            <span class='badge bg-danger'>MANGKIR / RESIGN</span>
        </div>
        <label for="file_csv" class="form-label fw-bold text-primary">
            <i class="fa fa-upload fa-lg"></i> Import File Excel Absensi
        </label>
        <form id="formImportCSV" enctype="multipart/form-data">
            <div class="input-group mb-2">
                <input type="file" name="file_csv" id="file_csv" class="form-control" required data-bs-toggle="tooltip" title="Pilih file Excel untuk import data absensi">
                <button type="submit" class="btn btn-success" id="btnImportCSV" data-bs-toggle="tooltip" title="Klik untuk mengimpor data dari file CSV">
                    <i class="fa fa-upload"></i> Import CSV
                </button>
            </div>
        </form>
        <a href="assets/template-csv/Format_Impor_Rekapitulasi_Absensi.xlsx" style="font-size: 2vh;">Download Template Format .xlsx</a>
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fa fa-table fa-lg"></i> Data Absensi</h5>
            <button type="button" class="btn btn-warning btn-sm" id="btnExportExcel">
                <i class="fa fa-file-excel"></i> Export Excel
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="searchKaryawan" class="form-label fw-bold text-primary">
                        <i class="fa fa-search fa-lg"></i> Cari Nama / NIK / ID
                    </label>
                    <input type="search" id="searchKaryawan" class="form-control form-control-sm" placeholder="Contoh: tjs-00090" data-bs-toggle="tooltip" title="Ketik nama, NIK, atau ID karyawan untuk memfilter tabel">
                </div>
            </div>
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
                            <th class="text-center"><i class="fa fa-calendar text-primary"></i> Kalender</th>
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
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalDetailAbsensiLabel"><i class="fa fa-info-circle"></i> Detail Absensi</h5>
                <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- Modal Form Absen (di dalam detail) -->
<div class="modal fade" id="modalFormAbsenDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAbsenDetail">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white">Form Absensi</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- id_absen untuk edit (kosong saat tambah) -->
                    <input type="hidden" name="id_absen" id="form_id_absen_detail" value="">
                    <input type="hidden" name="id_karyawan" id="form_id_karyawan_detail" value="">
                    <input type="hidden" name="tgl_absen" id="form_tgl_detail" value="">

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <select class="form-select" name="ket_absen" id="form_ket_detail">
                            <option value="">-- Pilih Keterangan --</option>
                            <option value="HADIR">HADIR</option>
                            <option value="OFF">SPL/OFF</option>
                            <option value="IZIN">IZIN</option>
                            <option value="SAKIT">SAKIT</option>
                            <option value="CUTI">CUTI</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jam Kerja</label>
                        <input type="number" step="0.1" class="form-control" name="jam_kerja" id="form_jk_detail" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kalender Rekap Bulanan -->
<div class="modal fade" id="modalKalenderAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fa fa-calendar"></i> Rekap Kalender — <span id="kalNama">-</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="small text-muted" id="kalPeriode">-</div>
                    <div class="kal-legend" id="kalLegend"></div>
                </div>
                <div class="alert alert-info py-2 mb-3 small" id="kalSummary">Memuat...</div>
                <div class="kal-scroll">
                    <div class="kal-grid" id="kalGrid"></div>
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
    let currentIdKaryawan = null;

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
            // Tombol kalender: buka kalender, jangan buka detail
            const btnCal = e.target.closest(".btnKalender");
            if (btnCal) {
                openKalender(btnCal.dataset.id);
                return;
            }

            const tr = e.target.closest("tr");
            if (!tr || !tr.dataset.id) return;

            currentIdKaryawan = tr.dataset.id;

            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p>Memuat detail absensi...</p>
                </div>
            `;
            modal.show();
            loadDetailAbsensi();
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

            const formData = new FormData();
            formData.append("tgl_a", tgl_a);
            formData.append("tgl_b", tgl_b);
            formData.append("site", site);
            formData.append("jabatan", jabatan);

            fetch("app/controller/get-data/get-absensi.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableAbsensi")) {
                        $(".MyTableAbsensi").DataTable().destroy();
                    }

                    tabelBody.innerHTML = html;
                    const dt = $(".MyTableAbsensi").DataTable({
                        responsive: true,
                        pageLength: 10,
                        destroy: true
                    });
                    // Terapkan lagi kata kunci pencarian khusus (Nama/NIK/ID) setelah reload
                    const kw = document.getElementById("searchKaryawan")?.value || "";
                    if (kw) dt.search(kw).draw();
                })
                .finally(() => {
                    spinner.style.display = "none";
                });
        };


        // Pencarian khusus Nama / NIK / ID (contoh: tjs-00090)
        document.getElementById("searchKaryawan").addEventListener("input", function() {
            if ($.fn.DataTable.isDataTable(".MyTableAbsensi")) {
                $(".MyTableAbsensi").DataTable().search(this.value).draw();
            }
        });

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

        // Event Export Excel
        document.getElementById("btnExportExcel").addEventListener("click", function() {
            const site = filterSite.value;
            const jabatan = filterJabatan.value;
            const tgl_a = tglAwalInput.value;
            const tgl_b = tglAkhirInput.value;

            if (!site) return showToast("Pilih Site terlebih dahulu sebelum export!", "info");
            if (!tgl_a || !tgl_b) return showToast("Tanggal tidak boleh kosong!", "info");
            if (new Date(tgl_a) > new Date(tgl_b)) return showToast("Tanggal awal tidak boleh lebih besar dari tanggal akhir!", "info");

            // Arahkan ke file export dengan membawa parameter filter
            const exportUrl = `app/controller/export/export-absensi.php?site=${site}&jabatan=${jabatan}&tgl_a=${tgl_a}&tgl_b=${tgl_b}`;
            window.open(exportUrl, '_blank');
        });

        // Auto reload filter
        filterSite.addEventListener("change", loadAbsensi);
        filterJabatan.addEventListener("change", loadAbsensi);
        tglAwalInput.addEventListener("change", loadAbsensi);
        tglAkhirInput.addEventListener("change", loadAbsensi);


        // ============ KALENDER REKAP BULANAN ============
        const kalGrid = document.getElementById("kalGrid");
        const kalModal = new bootstrap.Modal(document.getElementById("modalKalenderAbsensi"));
        const KAL_COLOR = {
            "Masuk": "#71dd37", "SPL": "#ffab00", "OFF": "#8592a3",
            "Izin": "#696cff", "Sakit": "#03c3ec", "Cuti": "#6f42c1",
            "Mangkir": "#ff3e1d", "Resign": "#566a7f", "New": "#566a7f"
        };
        const KAL_NAMA_HARI = ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"];
        const KAL_NAMA_BULAN = {
            "01": "Januari", "02": "Februari", "03": "Maret", "04": "April",
            "05": "Mei", "06": "Juni", "07": "Juli", "08": "Agustus",
            "09": "September", "10": "Oktober", "11": "November", "12": "Desember"
        };

        function kalStatusKey(ket) {
            if (ket.indexOf("Izin") === 0) return "Izin";
            if (ket.indexOf("Sakit") === 0) return "Sakit";
            return ket;
        }

        window.openKalender = function(idKaryawan) {
            document.getElementById("kalNama").textContent = "Memuat...";
            document.getElementById("kalPeriode").textContent = "-";
            document.getElementById("kalSummary").textContent = "Memuat data kalender...";
            kalGrid.innerHTML = `<div class="text-center py-4" style="grid-column:1/-1"><div class="spinner-border text-primary"></div></div>`;
            kalModal.show();

            const url = `app/controller/get-data/get-absensi-kalender.php?id_karyawan=${idKaryawan}&tgl_a=${tglAwalInput.value}&tgl_b=${tglAkhirInput.value}`;
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") {
                        kalGrid.innerHTML = `<div class="alert alert-danger" style="grid-column:1/-1">${res.message || "Gagal memuat data"}</div>`;
                        return;
                    }
                    renderKalender(res);
                })
                .catch(() => {
                    kalGrid.innerHTML = `<div class="alert alert-danger" style="grid-column:1/-1">Terjadi kesalahan sistem.</div>`;
                });
        };

        function renderKalender(res) {
            document.getElementById("kalNama").textContent = `${res.nama} (${res.nik})`;
            document.getElementById("kalPeriode").textContent = `Periode ${res.periode.dari} s/d ${res.periode.sampai} • ${res.jabatan}`;

            const t = res.totals;
            document.getElementById("kalSummary").innerHTML =
                `Hadir: <b>${t.hadir_hari} hari (${t.hadir_jam} jam)</b> • ` +
                `SPL: <b>${t.spl_hari} hari (${t.spl_jam} jam)</b> • ` +
                `OT1: <b>${t.ot1}</b> • OT2: <b>${t.ot2}</b> • ` +
                `Mangkir: <b>${t.mangkir}</b> • Izin: <b>${t.izin}</b> • ` +
                `Sakit: <b>${t.sakit}</b> • Cuti: <b>${t.cuti}</b>`;

            document.getElementById("kalLegend").innerHTML = Object.keys(KAL_COLOR)
                .map(k => `<span style="background:${KAL_COLOR[k]}">${k}</span>`).join("");

            let html = KAL_NAMA_HARI.map(h => `<div class="kal-head">${h}</div>`).join("");
            let lastMonth = "";

            res.days.forEach((d, idx) => {
                // Pembatas bulan saat ganti bulan
                if (d.month_key !== lastMonth) {
                    const [y, m] = d.month_key.split("-");
                    html += `<div class="kal-month">${KAL_NAMA_BULAN[m]} ${y}</div>`;
                    lastMonth = d.month_key;
                }
                // Sel kosong penyelarasan minggu pertama tiap bulan
                if (idx === 0 || res.days[idx - 1].month_key !== d.month_key) {
                    for (let i = 1; i < d.weekday; i++) html += `<div class="kal-day empty"></div>`;
                }

                const key = kalStatusKey(d.ket);
                const jamInfo = (d.ket === "Masuk" || d.ket === "SPL")
                    ? `<div class="kal-jam">${d.normal} jam${d.ot1 > 0 ? ` • OT1 ${d.ot1}` : ""}${d.ot2 > 0 ? ` • OT2 ${d.ot2}` : ""}</div>`
                    : "";
                const title = `${d.day} ${d.month} — ${d.ket}` +
                    ((d.ket === "Masuk" || d.ket === "SPL") ? ` (${d.jam} jam, normal ${d.normal}, OT1 ${d.ot1}, OT2 ${d.ot2})` : "");

                html += `<div class="kal-day kal-${key}" title="${title}">
                    <div class="kal-date">${d.day} <small>${d.month}</small></div>
                    <span class="kal-status">${d.ket}</span>
                    ${jamInfo}
                </div>`;
            });

            kalGrid.innerHTML = html;
        }

        // Reload Detail Absensi
        function loadDetailAbsensi() {            if (!currentIdKaryawan) return;

            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary"></div>
                    <p>Memuat detail absensi...</p>
                </div>
            `;

            fetch(`app/controller/get-data/get-absensi-detail.php?id_karyawan=${currentIdKaryawan}&tgl_a=${tglAwalInput.value}&tgl_b=${tglAkhirInput.value}`)
                .then(res => res.text())
                .then(html => {
                    modalBody.innerHTML = html;
                });
        }



        // FOR ACTION BUTTON IN DETAIL ABSENSI
        (function() {
            console.log("Script detail absensi loaded");

            // mapping untuk UI behaviour (jam selalu tampil)
            const ketMapping = {
                "HADIR": {
                    showJam: true
                },
                "SPL/OFF": {
                    showJam: true
                },
                "IZIN": {
                    showJam: false
                },
                "SAKIT": {
                    showJam: false
                },
                "CUTI": {
                    showJam: false
                },
            };

            // Inisialisasi modal form
            const modalForm = new bootstrap.Modal(
                document.getElementById("modalFormAbsenDetail")
            );

            const formAbsen = document.getElementById("formAbsenDetail");


            // Fungsi Update jam by keterangan 
            const ketSelectEl = document.getElementById("form_ket_detail");
            const jamEl = document.getElementById("form_jk_detail");

            function updateFieldsByKeterangan() {
                const ketSelect = ketSelectEl.value;

                if (["IZIN", "SAKIT", "CUTI"].includes(ketSelect)) {
                    jamEl.value = "";
                    jamEl.disabled = true;
                } else {
                    jamEl.disabled = false;
                }
            }

            ketSelectEl.addEventListener("change", updateFieldsByKeterangan);


            document.addEventListener("click", async function(e) {

                // TAMBAH
                const btnTambah = e.target.closest(".btnTambahDetail");
                if (btnTambah) {
                    const id_karyawan = btnTambah.dataset.id_karyawan;
                    const tgl = btnTambah.dataset.tgl;

                    document.getElementById("form_id_absen_detail").value = "";
                    document.getElementById("form_id_karyawan_detail").value = id_karyawan;
                    document.getElementById("form_tgl_detail").value = tgl;
                    document.getElementById("form_ket_detail").value = "";
                    document.getElementById("form_jk_detail").value = "";

                    modalForm.show();
                    return;
                }

                // EDIT
                const btnEdit = e.target.closest(".btnEditDetail");
                if (btnEdit) {
                    document.getElementById("form_id_absen_detail").value = btnEdit.dataset.id_absen;
                    document.getElementById("form_id_karyawan_detail").value = btnEdit.dataset.id_karyawan;
                    document.getElementById("form_tgl_detail").value = btnEdit.dataset.tgl;
                    document.getElementById("form_ket_detail").value = btnEdit.dataset.ket;
                    document.getElementById("form_jk_detail").value = btnEdit.dataset.jk;

                    modalForm.show();
                    return;
                }

                // HAPUS
                const btnHapus = e.target.closest(".btnHapusDetail");
                if (btnHapus) {
                    if (!(await confirmDialog("Yakin hapus data?"))) return;

                    const formData = new FormData();
                    formData.append("action", "delete");
                    formData.append("id_absen", btnHapus.dataset.id_absen);

                    fetch("app/controller/act-absen.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            showToast(res.msg, "success");
                            loadAbsensi();
                            loadDetailAbsensi();
                        });
                }

            });



            // ================= Submit Form =================
            document.getElementById("formAbsenDetail").addEventListener("submit", async function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                // decide action add/edit
                const action = formData.get("id_absen") ? "edit" : "add";
                formData.append("action", action);

                try {
                    const res = await fetch("app/controller/act-absen.php", {
                        method: "POST",
                        body: formData
                    });
                    const data = await res.json();
                    showToast(data.msg || (data.message ? data.message : JSON.stringify(data)), "success");
                    if (data.status === "success") {
                        modalForm.hide();
                        loadAbsensi();
                        loadDetailAbsensi();
                    }
                } catch (err) {
                    console.error(err);
                    showToast("Gagal menyimpan data!", "error");
                }
            });


        })();
    });
</script>