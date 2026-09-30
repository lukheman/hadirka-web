<style>
    .fade-in { animation: fadeIn 300ms ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0.2rem 0.6rem; }
    .modal-body { max-height: 70vh; overflow-y: auto; }
    .info-table { width: 100%; margin-bottom: 0; }
    .info-table td { padding: 0.2rem 0 !important; vertical-align: top; color: #566a7f; }
    .info-table td:first-child { width: 35%; font-weight: 600; white-space: nowrap; }
    .stat-card { border: 0; border-left: 4px solid var(--bs-secondary); }
    .stat-number { font-size: 1.4rem; font-weight: 700; line-height: 1; }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y py-4">
        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Master Data /</span> Karyawan Tereliminasi
        </h4>

        <div class="alert alert-secondary d-flex align-items-center py-2">
            <i class="fa fa-info-circle me-2"></i>
            <div>Daftar karyawan yang telah dieliminasi (keluar). Data di sini bersifat arsip — gunakan <strong>Detail</strong> untuk melihat, <strong><i class="fa fa-edit"></i></strong> untuk koreksi data keluar, atau <strong><i class="fa fa-undo"></i></strong> untuk mengembalikan menjadi Aktif.</div>
        </div>

        <div class="row g-2 mb-3" id="statTereliminasi">
            <div class="col-6 col-md-2">
                <div class="card stat-card border-secondary h-100"><div class="card-body py-2">
                    <div class="text-muted small">Total</div>
                    <div class="stat-number" id="statTotal">0</div>
                </div></div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card stat-card border-warning h-100" style="border-left-color:#ffc107!important"><div class="card-body py-2">
                    <div class="text-muted small">Resign</div>
                    <div class="stat-number text-warning" id="statResign">0</div>
                </div></div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card stat-card h-100" style="border-left-color:#6c757d!important"><div class="card-body py-2">
                    <div class="text-muted small">Habis Kontrak</div>
                    <div class="stat-number text-secondary" id="statHabis">0</div>
                </div></div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card stat-card h-100" style="border-left-color:#dc3545!important"><div class="card-body py-2">
                    <div class="text-muted small">PHK</div>
                    <div class="stat-number text-danger" id="statPhk">0</div>
                </div></div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card stat-card h-100" style="border-left-color:#0dcaf0!important"><div class="card-body py-2">
                    <div class="text-muted small">Pensiun</div>
                    <div class="stat-number text-info" id="statPensiun">0</div>
                </div></div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card stat-card h-100" style="border-left-color:#212529!important"><div class="card-body py-2">
                    <div class="text-muted small">Black List</div>
                    <div class="stat-number" id="statBlacklist">0</div>
                </div></div>
            </div>
        </div>

        <div class="pb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalImportTer" title="Import eliminasi massal dari Excel">
                    <i class="fa fa-upload"></i> Import Data
                </button>
                <button class="btn btn-sm btn-info" onclick="exportTereliminasi()" title="Export data tereliminasi ke Excel">
                    <i class="fa fa-file-export"></i> Export Data
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="tableTereliminasi.ajax.reload(null,false)" title="Muat ulang data">
                    <i class="fa fa-sync-alt"></i> Refresh
                </button>
                <select id="filterSiteTer" class="form-select form-select-sm w-auto" title="Filter berdasarkan site">
                        <option value="">-- Semua Site --</option>
                        <?php foreach (data_site() as $d) { ?>
                            <option value="<?= htmlspecialchars($d['id_site']) ?>"><?= htmlspecialchars($d['nama_site']) ?></option>
                        <?php } ?>
                    </select>
                    <select id="filterJabatanTer" class="form-select form-select-sm w-auto" title="Filter berdasarkan jabatan">
                        <option value="">-- Semua Jabatan --</option>
                        <?php foreach (data_jabatan() as $d) {
                            echo "<option value='" . htmlspecialchars($d['id_jabatan']) . "'>" . htmlspecialchars($d['nama_jabatan']) . "</option>";
                        } ?>
                    </select>
                    <select id="filterJenisTer" class="form-select form-select-sm w-auto" title="Filter berdasarkan jenis keluar">
                        <option value="">-- Semua Jenis Keluar --</option>
                        <option value="Habis Kontrak">Habis Kontrak</option>
                        <option value="Pensiun">Pensiun</option>
                        <option value="PHK">PHK</option>
                        <option value="Resign">Resign</option>
                        <option value="Black List">Black List</option>
                    </select>
                    <select id="filterBulanTer" class="form-select form-select-sm w-auto" title="Filter bulan keluar (tahun berjalan)">
                        <option value="">-- Semua Bulan Keluar --</option>
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
            </div>
        </div>

        <div class="card position-relative">
            <h5 class="card-header"><i class="fa fa-user-slash"></i> Database Karyawan Tereliminasi</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="tableTereliminasi" class="table table-hover table-bordered table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Foto</th>
                                <th class="text-center">Nama Karyawan</th>
                                <th class="text-center">NIK</th>
                                <th class="text-center">Site Terakhir</th>
                                <th class="text-center">Jabatan Terakhir</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Tgl Keluar</th>
                                <th class="text-center">Jenis Keluar</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailTer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title text-white"><i class="fa fa-user"></i> Detail Karyawan Tereliminasi - <span id="detailNamaTer">-</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailBodyTer"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKoreksiTer" tabindex="-1">
    <div class="modal-dialog">
        <form id="formKoreksiTer" class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fa fa-edit"></i> Koreksi Data Eliminasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>NAMA LENGKAP</label>
                    <input type="text" class="form-control" id="koreksi_nama" readonly>
                </div>
                <div class="form-group mb-3">
                    <label>NIK KARYAWAN</label>
                    <input type="text" name="nik_karyawan" id="koreksi_nik" class="form-control" readonly>
                </div>
                <div class="form-group mb-3">
                    <label>TANGGAL KELUAR</label>
                    <input type="date" name="tgl_keluar" id="koreksi_tgl" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">JENIS KELUAR</label>
                    <select class="form-control" name="id_jenis_keluar" id="koreksi_jenis" required>
                        <option value="">-- Pilih Jenis Keluar --</option>
                        <option value="1">Habis Kontrak</option>
                        <option value="2">Pensiun</option>
                        <option value="3">PHK</option>
                        <option value="4">Resign</option>
                        <option value="5">Black List</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label>KETERANGAN (OPSIONAL)</label>
                    <textarea name="alasan_keluar" id="koreksi_alasan" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning">Simpan Koreksi</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalImportTer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formImportTer" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fa fa-upload"></i> Import Karyawan Tereliminasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Unggah file Excel sesuai template untuk mengeliminasi banyak karyawan sekaligus.
                    NIK harus sudah terdaftar di Data Karyawan — baris dengan NIK tak dikenal akan dilaporkan gagal.
                </p>
                <label for="file_excel_ter" class="form-label"><i class="fa fa-file-excel"></i> Upload File (XLS/XLSX)</label>
                <input type="file" name="file_excel" id="file_excel_ter" class="form-control" accept=".xls,.xlsx" required>
                <a href="assets/template-csv/Format_Impor_Karyawan_Tereliminasi.xlsx" style="font-size: 2vh;">Download Template Format .xlsx</a>
                <div class="alert alert-info py-2 mt-2 mb-0">
                    Kolom template: <strong>No | NIK Karyawan | Nama Lengkap | Tanggal Keluar | Jenis Keluar</strong><br>
                    <small>Jenis Keluar: RESIGN, END KONTRAK, PHK, MENGUNDURKAN DIRI (tidak peka huruf besar/kecil).</small>
                </div>
                <div id="progressWrapperTer" class="progress mt-2" style="display:none;height:20px;">
                    <div id="progressBarTer" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0%;">0%</div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" type="submit" id="btnUploadTer">
                    <i class="fa fa-upload"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="importResultModalTer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="importResultModalTerLabel">
                    <i class="fa fa-clipboard-check me-2"></i>Hasil Import
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="card stat-card border-success h-100">
                            <div class="card-body">
                                <div class="text-muted small">Berhasil</div>
                                <div class="stat-number text-success" id="importResultSuccessTer">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-info h-100">
                            <div class="card-body">
                                <div class="text-muted small">Dilewati</div>
                                <div class="stat-number text-info" id="importResultSkippedTer">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-danger h-100">
                            <div class="card-body">
                                <div class="text-muted small">Gagal</div>
                                <div class="stat-number text-danger" id="importResultFailedTer">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="importResultDetailsTer" class="small" style="max-height:240px;overflow-y:auto;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewTer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fa fa-search me-2"></i>Validasi Data Eliminasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2">
                    <i class="fa fa-exclamation-triangle"></i>
                    Periksa daftar di bawah sebelum dieksekusi. Hanya baris <strong class="text-success">Siap</strong> yang akan diproses — baris <strong class="text-danger">Bermasalah</strong> dilewati otomatis.
                </div>
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-success fs-6">Siap: <span id="previewValidCountTer">0</span></span>
                    <span class="badge bg-danger fs-6">Bermasalah: <span id="previewInvalidCountTer">0</span></span>
                </div>
                <div class="table-responsive" style="max-height:50vh;overflow-y:auto;">
                    <table class="table table-sm table-bordered">
                        <thead class="table-info sticky-top">
                            <tr>
                                <th class="text-center">Baris</th>
                                <th class="text-center">NIK</th>
                                <th class="text-center">Nama (File &rarr; Database)</th>
                                <th class="text-center">Tgl Keluar</th>
                                <th class="text-center">Jenis &rarr; Status</th>
                                <th class="text-center">Hasil Validasi</th>
                            </tr>
                        </thead>
                        <tbody id="previewBodyTer"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnProsesTer">
                    <i class="fa fa-user-slash"></i> Ya, Eliminasi Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLihatFotoTer" tabindex="-1" aria-hidden="true">    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 p-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white fs-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="imgPreviewBesarTer" src="" alt="Foto Karyawan" class="img-fluid rounded shadow" style="max-height: 80vh; object-fit: contain;">
                <h5 id="namaFotoTer" class="text-white mt-3"></h5>
            </div>
        </div>
    </div>
</div>

<!-- jQuery global via CDN di <head> index.php -->
<script>
    let tableTereliminasi = null;

    $(document).ready(function() {
        tableTereliminasi = $('#tableTereliminasi').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "app/controller/get-data/get-karyawan-tereliminasi.php",
                type: "POST",
                data: function(d) {
                    d.site = $("#filterSiteTer").val();
                    d.jabatan = $("#filterJabatanTer").val();
                    d.jenis = $("#filterJenisTer").val();
                    d.bulan = $("#filterBulanTer").val();
                },
                dataSrc: function(json) {
                    updateStatTereliminasi(json.data || []);
                    return json.data || [];
                }
            },
            columns: [
                { data: "no", className: "text-center", width: "45px" },
                { data: "foto", className: "text-center", orderable: false },
                { data: "nama" },
                { data: "nik", className: "text-center" },
                { data: "site", className: "text-center" },
                { data: "jabatan", className: "text-center" },
                { data: "status_karyawan", className: "text-center" },
                { data: "tgl_keluar", className: "text-center" },
                { data: "jenis_keluar", className: "text-center" },
                { data: "action", orderable: false, className: "text-center" }
            ],
            order: [[7, "desc"]]
        });

        $("#filterSiteTer, #filterJabatanTer, #filterJenisTer, #filterBulanTer").on("change", function() {
            tableTereliminasi.ajax.reload(null, false);
        });

        // Detail
        $('#tableTereliminasi tbody').on('click', '.btnDetailTer', function() {
            let data = tableTereliminasi.row($(this).closest('tr')).data();
            if (!data) return;
            $('#detailNamaTer').text(data.nama);
            $('#detailBodyTer').html(data.detail);
            new bootstrap.Modal(document.getElementById('modalDetailTer')).show();
            let riwayatBody = document.getElementById('riwayatKarierBody');
            if (riwayatBody && data.id_karyawan) {
                fetch('app/controller/get-data/get-riwayat-karier.php?id_karyawan=' + encodeURIComponent(data.id_karyawan))
                    .then(res => res.text())
                    .then(html => { riwayatBody.innerHTML = html; })
                    .catch(() => { riwayatBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat riwayat karier.</td></tr>'; });
            }
        });

        // Zoom foto
        $('#tableTereliminasi tbody').on('click', 'img.zoom-foto', function() {
            let imgSrc = $(this).data('src');
            $('#imgPreviewBesarTer').attr('src', imgSrc);
            $('#namaFotoTer').text($(this).data('nama'));
            $('#modalLihatFotoTer').modal('show');
        });

        // Koreksi: ambil data karyawan + data eliminasi
        $(document).on("click", ".btnKoreksiTer", function() {
            let id = $(this).data("id");
            $.ajax({
                url: "app/controller/act-karyawan.php",
                type: "POST",
                data: { action: "get", id_karyawan: id },
                dataType: "json",
                success: function(res) {
                    if (res.status !== "ok") { showToast("Gagal mengambil data karyawan", "error"); return; }
                    $("#koreksi_nama").val(res.data.nama_lengkap);
                    $("#koreksi_nik").val(res.data.nik_karyawan);
                    // Ambil data eliminasi terkini
                    $.ajax({
                        url: "app/controller/act-eliminasi.php",
                        type: "POST",
                        data: { action: "get", id_karyawan: id },
                        dataType: "json",
                        success: function(r2) {
                            if (r2.status === "ok") {
                                $("#koreksi_tgl").val(r2.data.tgl_keluar || "");
                                $("#koreksi_jenis").val(r2.data.id_jenis_keluar || "");
                                $("#koreksi_alasan").val(r2.data.alasan_keluar || "");
                            } else {
                                $("#koreksi_tgl").val("");
                                $("#koreksi_jenis").val("");
                                $("#koreksi_alasan").val("");
                            }
                            $("#modalKoreksiTer").modal("show");
                        },
                        error: function() { $("#modalKoreksiTer").modal("show"); }
                    });
                }
            });
        });

        // Simpan koreksi (pakai endpoint eliminasi yang sama: insert-or-update)
        $("#formKoreksiTer").on("submit", async function(e) {
            e.preventDefault();
            if (!$("#koreksi_jenis").val()) { showToast("Jenis keluar harus dipilih!", "warning"); return; }
            if (!$("#koreksi_tgl").val()) { showToast("Tanggal keluar harus diisi!", "warning"); return; }
            if (!(await confirmDialog("Simpan koreksi data eliminasi untuk:\n" + $("#koreksi_nama").val() + "?"))) return;
            $.ajax({
                url: "app/controller/act-eliminasi.php",
                type: "POST",
                data: $(this).serialize() + "&action=eliminasi",
                dataType: "json",
                success: function(res) {
                    if (res.status === "ok") {
                        $("#modalKoreksiTer").modal("hide");
                        tableTereliminasi.ajax.reload(null, false);
                        showToast(res.msg, "success");
                    } else {
                        showToast(res.msg, "error");
                    }
                },
                error: function() { showToast("Terjadi kesalahan server.", "error"); }
            });
        });

        // Reaktivasi: kembalikan menjadi Aktif
        $(document).on("click", ".btnReaktivasi", async function() {
            let id = $(this).data("id");
            let nama = $(this).data("nama");
            if (!(await confirmDialog(
                "Kembalikan karyawan menjadi AKTIF?\n" + nama + "\n\nData eliminasi akan dihapus dan karyawan masuk kembali ke daftar aktif.",
                { title: "Reaktivasi Karyawan", confirmText: "Ya, Aktifkan", icon: "question" }
            ))) return;
            $.ajax({
                url: "app/controller/act-eliminasi.php",
                type: "POST",
                data: { action: "reaktivasi", id_karyawan: id },
                dataType: "json",
                success: function(res) {
                    if (res.status === "ok") {
                        tableTereliminasi.ajax.reload(null, false);
                        showToast(res.msg, "success");
                    } else {
                        showToast(res.msg, "error");
                    }
                },
                error: function() { showToast("Terjadi kesalahan server.", "error"); }
            });
        });

        // Import Excel tereliminasi
        function showImportResultTer(res, title) {
            title = title || "Hasil Import";
            document.getElementById('importResultModalTerLabel').innerHTML =
                '<i class="fa fa-clipboard-check me-2"></i>' + title;
            document.getElementById('importResultSuccessTer').textContent = res.berhasil || 0;
            document.getElementById('importResultSkippedTer').textContent = res.dilewati || 0;
            document.getElementById('importResultFailedTer').textContent = res.gagal || 0;

            const details = document.getElementById('importResultDetailsTer');
            details.innerHTML = '';

            const sections = [
                { title: 'Catatan', items: (res.debug || []).filter(item => item.includes('Perhatian')), className: 'text-warning' },
                { title: 'Detail error', items: res.errors || [], className: 'text-danger' }
            ];

            sections.forEach(section => {
                if (!section.items.length) return;
                const heading = document.createElement('div');
                heading.className = 'fw-bold mb-1 ' + section.className;
                heading.textContent = section.title;
                details.appendChild(heading);

                const list = document.createElement('ul');
                list.className = 'mb-3 ps-4';
                section.items.slice(0, 20).forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    list.appendChild(li);
                });
                if (section.items.length > 20) {
                    const more = document.createElement('div');
                    more.className = 'text-muted mb-2';
                    more.textContent = '... dan ' + (section.items.length - 20) + ' detail lainnya. Lihat console browser untuk detail lengkap.';
                    details.appendChild(more);
                }
                details.appendChild(list);
            });

            if (!details.children.length) {
                details.innerHTML = '<div class="text-success"><i class="fa fa-check-circle me-1"></i>Semua data berhasil diproses.</div>';
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('importResultModalTer')).show();
        }

        // Import Excel tereliminasi — 2 tahap: validasi (pratinjau) dulu, eksekusi setelah konfirmasi
        let pendingImportFileTer = null;

        function escPreviewTer(s) {
            return String(s == null ? "" : s).replace(/&/g, "&amp;")
                .replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        function fmtTglTer(ymd) {
            if (!ymd) return "-";
            const p = String(ymd).split("-");
            return p.length === 3 ? p[2] + "-" + p[1] + "-" + p[0] : ymd;
        }

        function renderPreviewTer(res) {
            document.getElementById('previewValidCountTer').textContent = res.valid || 0;
            document.getElementById('previewInvalidCountTer').textContent = res.invalid || 0;

            const tb = document.getElementById('previewBodyTer');
            const rows = res.rows || [];
            if (!rows.length) {
                tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Tidak ada baris data di file.</td></tr>';
            } else {
                tb.innerHTML = rows.map(function(r) {
                    let hasil;
                    if (r.valid) {
                        const aksi = r.sudah_tereliminasi
                            ? 'Update data eliminasi'
                            : escPreviewTer(r.status_saat_ini || 'Aktif') + ' &rarr; ' + escPreviewTer(r.status_baru);
                        hasil = '<span class="badge bg-success">Siap</span><br><small class="text-muted">' + aksi + '</small>'
                            + (r.warning ? '<br><small class="text-warning">' + escPreviewTer(r.warning) + '</small>' : '');
                    } else {
                        hasil = '<span class="badge bg-danger">Dilewati</span><br><small class="text-danger">' + escPreviewTer(r.error || '') + '</small>';
                    }
                    const namaCell = escPreviewTer(r.nama_file || '-')
                        + (r.nama_db ? '<br><small class="text-muted">&rarr; ' + escPreviewTer(r.nama_db) + '</small>' : '');
                    const jenisCell = escPreviewTer(r.jenis_raw || '-')
                        + (r.jenis_mapped ? '<br><small class="text-muted">&rarr; ' + escPreviewTer(r.jenis_mapped) + '</small>' : '');
                    return '<tr>'
                        + '<td class="text-center">' + (r.baris || '') + '</td>'
                        + '<td class="text-center">' + escPreviewTer(r.nik || '-') + '</td>'
                        + '<td>' + namaCell + '</td>'
                        + '<td class="text-center">' + fmtTglTer(r.tgl_keluar) + '</td>'
                        + '<td class="text-center">' + jenisCell + '</td>'
                        + '<td>' + hasil + '</td>'
                        + '</tr>';
                }).join('');
            }

            document.getElementById('btnProsesTer').disabled = !(res.valid > 0);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPreviewTer')).show();
        }

        // Tahap 1: upload -> validasi -> tampilkan pratinjau (notifikasi)
        $("#formImportTer").on("submit", function(e) {
            e.preventDefault();
            const fileInput = $("#file_excel_ter")[0];
            if (!fileInput.files.length) {
                showToast("Pilih file Excel terlebih dahulu.", "warning");
                return;
            }
            pendingImportFileTer = fileInput.files[0];

            const fd = new FormData();
            fd.append("file_excel", pendingImportFileTer);
            fd.append("action", "preview");

            const btn = $("#btnUploadTer");
            btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Memvalidasi...');

            $.ajax({
                url: "app/controller/csv-control/csv-tereliminasi.php",
                type: "POST",
                dataType: "json",
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.success && res.preview) {
                        $("#modalImportTer").modal("hide");
                        renderPreviewTer(res);
                    } else {
                        showImportResultTer({
                            berhasil: 0,
                            dilewati: 0,
                            gagal: 1,
                            errors: [res.error || "Validasi gagal."]
                        }, "Validasi Gagal");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Preview Error:", status, error, xhr.responseText);
                    showToast("Gagal memvalidasi file. Cek console browser (F12).", "error");
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="fa fa-upload"></i> Upload');
                }
            });
        });

        // Tahap 2: konfirmasi -> eksekusi eliminasi
        $("#btnProsesTer").on("click", async function() {
            if (!pendingImportFileTer) {
                showToast("File tidak ditemukan, ulangi upload.", "warning");
                return;
            }
            if (!(await confirmDialog("Eliminasi semua data yang berstatus Siap?\nBaris bermasalah akan dilewati.", { title: "Konfirmasi Eliminasi", confirmText: "Ya, Eliminasi", icon: "warning" }))) return;

            const fd = new FormData();
            fd.append("file_excel", pendingImportFileTer);
            fd.append("action", "import");

            const btn = $(this);
            btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: "app/controller/csv-control/csv-tereliminasi.php",
                type: "POST",
                dataType: "json",
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    console.log("Import tereliminasi:", res);
                    if (res.success) {
                        $("#modalPreviewTer").modal("hide");
                        $("#formImportTer")[0].reset();
                        pendingImportFileTer = null;
                        tableTereliminasi.ajax.reload(null, false);
                        showImportResultTer(res);
                        if (res.gagal > 0) {
                            showToast('Import selesai dengan ' + res.gagal + ' error. Cek detail!', "warning");
                        } else {
                            showToast('Semua ' + res.berhasil + ' data berhasil dieliminasi!', "success");
                        }
                    } else {
                        showImportResultTer({
                            berhasil: 0,
                            dilewati: 0,
                            gagal: 1,
                            errors: [res.error || "Terjadi kesalahan tidak diketahui"]
                        }, "Import Gagal");
                        showToast("Import gagal! Cek detail.", "error");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error, xhr.responseText);
                    showImportResultTer({
                        berhasil: 0,
                        dilewati: 0,
                        gagal: 1,
                        errors: ["Terjadi kesalahan server (" + status + "). Cek console browser (F12)."]
                    }, "Kesalahan Server");
                    showToast("Terjadi kesalahan server!", "error");
                },
                complete: function() {
                    btn.prop("disabled", false).html('<i class="fa fa-user-slash"></i> Ya, Eliminasi Sekarang');
                }
            });
        });

        window.exportTereliminasi = function() {
            let site = document.getElementById("filterSiteTer").value;
            let jabatan = document.getElementById("filterJabatanTer").value;
            let jenis = document.getElementById("filterJenisTer").value;
            let status = jenis !== "" ? jenis : "TERELIMINASI";
            let url = "app/controller/export/export-karyawan.php?site=" + encodeURIComponent(site)
                + "&jabatan=" + encodeURIComponent(jabatan)
                + "&status=" + encodeURIComponent(status);
            window.location.href = url;
        };
    });

    function updateStatTereliminasi(rows) {
        const count = s => rows.filter(r => (r.status_raw || "") === s).length;
        document.getElementById("statTotal").textContent = rows.length;
        document.getElementById("statResign").textContent = count("Resign");
        document.getElementById("statHabis").textContent = count("Habis Kontrak");
        document.getElementById("statPhk").textContent = count("PHK");
        document.getElementById("statPensiun").textContent = count("Pensiun");
        document.getElementById("statBlacklist").textContent = count("Black List");
    }
</script>
