<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Master Data /</span> Import & Manajemen Karier
        </h4>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-import"></i> Form Import Excel</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportPayroll" enctype="multipart/form-data">
                    <p class="instruction-text mb-3">
                        Unggah file Excel/CSV sesuai format kolom untuk memperbarui database secara masal.
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Upload File Excel Karier</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="col-md-5 mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import Data
                            </button>
                            <a href="/assets/template-csv/Format_Impor_Data_Karier.xlsx" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                </form>
                <div id="importResult" class="mt-3"></div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="mb-2">
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKarier">
                    <i class="fa fa-plus"></i> Tambah Karier Manual
                </button>
                <!-- Tombol Export Baru -->
                <button class="btn btn-info btn-sm" onclick="exportKarierCSV()">
                    <i class="fa fa-file-export"></i> Export CSV
                </button>
                <button class="btn btn-warning btn-sm" id="btnExtendBatch">
                    <i class="fa fa-calendar-check"></i> Batch Perpanjang (+6 Bulan)
                </button>
            </div>

            <div class="d-flex align-items-center gap-2 mb-2">
                <select id="filterSite" class="form-select form-select-sm">
                    <option value="">-- Semua Site --</option>
                    <?php
                    $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                    while ($s = $sites->fetch_assoc()) {
                        echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                    }
                    ?>
                </select>

                <select id="filterJabatan" class="form-select form-select-sm">
                    <option value="">-- Semua Jabatan --</option>
                    <?php
                    $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                    while ($j = $jabatans->fetch_assoc()) {
                        echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                    }
                    ?>
                </select>

                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="Aktif" selected>Karyawan Aktif</option>
                    <option value="Habis Kontrak">Habis Kontrak</option>
                    <option value="Pensiun">Pensiun</option>
                    <option value="PHK">PHK</option>
                    <option value="Resign">Resign</option>
                    <option value="Black List">Black List</option>
                    <option value="">-- Semua Status --</option>
                </select>

                <select id="filterBulan" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter Bulan Habis Kontrak Tahun Sekarang">
                    <option value="">-- Semua Bulan --</option>
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

        <div class="card">
            <h5 class="card-header">Data Master Karier (Semua Karyawan)</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableKarier">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center"><input type="checkbox" id="checkAllExtend"></th>
                                <th class="text-center">No</th>
                                <th class="text-center">NIK</th>
                                <th class="text-center">Nama</th>
                                <th class="text-center">Site</th>
                                <th class="text-center">Departemen</th>
                                <th class="text-center">Jabatan</th>
                                <th class="text-center">Gaji Pokok</th>
                                <th class="text-center">Upah Lembur</th>
                                <th class="text-center">Pot Izin</th>
                                <th class="text-center">Pot Mangkir</th>
                                <th class="text-center">Tgl Mulai</th>
                                <th class="text-center">Tgl Selesai</th>
                                <th class="text-center">No Surat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKarier"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalTambahKarier">
        <div class="modal-dialog modal-xl">
            <form id="formTambahKarier" class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Tambah Karier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label>Pilih Karyawan</label>
                            <select name="id_karyawan" class="form-control" required>
                                <option value="">-- Pilih Karyawan --</option>
                                <?php
                                $karyawans = $koneksi->query("SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan ORDER BY nama_lengkap ASC");
                                while ($k = $karyawans->fetch_assoc()) {
                                    // Gunakan ID asli, hapus encrypt() untuk menghindari Fatal Error jika function.php tidak terbaca di view ini
                                    echo "<option value='{$k['id_karyawan']}'>{$k['nik_karyawan']} - {$k['nama_lengkap']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <option value="">-- Pilih Site --</option>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch_assoc()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control select-search" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control select-search" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>

                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="text" name="gaji_pokok" class="form-control nominal-mask" required>
                        </div>

                        <div class="col-md-3">
                            <label>Upah Lembur</label>
                            <input type="text" name="lembur" class="form-control nominal-mask" required>
                        </div>

                        <div class="col-md-3">
                            <label>Potongan Izin</label>
                            <input type="text" name="pot_izin" class="form-control nominal-mask">
                        </div>

                        <div class="col-md-3">
                            <label>Potongan Mangkir</label>
                            <input type="text" name="pot_mangkir" class="form-control nominal-mask">
                        </div>

                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="fa fa-file-contract"></i> Preview No Surat:
                                <strong id="addNoSuratPreview">&mdash;</strong>
                                <br><small class="text-muted">Nomor final diterbitkan sistem saat simpan.</small>
                            </div>
                            <div id="addNoSuratWarning"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditKarier">
        <div class="modal-dialog modal-xl">
            <form id="formEditKarier" class="modal-content">
                <input type="hidden" name="id_karier">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Edit Karier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch_assoc()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control select-search" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control select-search" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>

                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="text" name="gaji_pokok" class="form-control nominal-mask" required>
                        </div>

                        <div class="col-md-3">
                            <label>Upah Lembur</label>
                            <input type="text" name="lembur" class="form-control nominal-mask" required>
                        </div>

                        <div class="col-md-3">
                            <label>Potongan Izin</label>
                            <input type="text" name="pot_izin" class="form-control nominal-mask">
                        </div>

                        <div class="col-md-3">
                            <label>Potongan Mangkir</label>
                            <input type="text" name="pot_mangkir" class="form-control nominal-mask">
                        </div>

                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/../master-data/modal-karier-tunjangan-potongan.php'; ?>

    <!-- Modal Preview Surat Kontrak (sebelum simpan) -->
    <div class="modal fade" id="modalPreviewKarier">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-file-contract"></i> Preview Surat Kontrak</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2">
                        <i class="fa fa-info-circle"></i> Periksa kembali data kontrak sebelum disimpan.
                        Nomor surat final diterbitkan sistem saat simpan.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tbody id="tbodyPreviewKarier"></tbody>
                        </table>
                    </div>
                    <div id="previewKarierWarning"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
                    <button type="button" class="btn btn-success" id="btnKonfirmasiKarier">
                        <i class="fa fa-save"></i> Simpan Kontrak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // HAPUS const karyawanId = "..." di sini
        const formImport = document.getElementById("formImportPayroll");
        const importResult = document.getElementById("importResult");
        const tbody = document.getElementById("tbodyKarier");

        // --- HELPER UNTUK MEMFORMAT ANGKA SAAT MODAL EDIT DIBUKA ---
        function formatIndo(val) {
            if (val === undefined || val === null || val === '') return "";
            let parts = val.toString().split(".");
            // Beri titik pada komponen ribuan
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            // Gabungkan kembali dengan koma desimal jika ada pecahan
            return parts.join(",");
        }

        // --- 1. LISTENER FORMAT MASKING OTOMATIS SAAT MENGETIK ---
        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                let input = e.target;
                let cursorPos = input.selectionStart;
                let prevLength = input.value.length;

                // Bersihkan semua karakter selain angka dan koma desimal
                let val = input.value.replace(/[^0-9,]/g, "");

                // Pecah berdasarkan koma desimal
                let parts = val.split(",");

                // Format bagian angka bulat dengan titik ribuan
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                // Batasi hanya boleh ada 1 koma desimal
                if (parts.length > 2) {
                    input.value = parts[0] + "," + parts[1];
                } else {
                    input.value = parts.join(",");
                }

                // Sesuaikan kembali posisi kursor ketikan agar tidak melompat
                let diff = input.value.length - prevLength;
                input.setSelectionRange(cursorPos + diff, cursorPos + diff);
            }
        });

        // --- SELECT2 BISA DICARI UNTUK DEPARTEMEN & JABATAN (dalam modal) ---
        // Select2 di dalam modal Bootstrap WAJIB memakai dropdownParent.
        // Tanpa itu dropdown menempel ke <body> dan tertutup backdrop modal
        // sehingga terlihat seperti "tidak ada data".
        // Init global di index.php sengaja melewati select dalam modal, jadi
        // inisialisasi di sini langsung saat DOM siap (tanpa menunggu window
        // load) + dipastikan ulang setiap modal dibuka.
        function initKarierSelect2() {
            ["#modalTambahKarier", "#modalEditKarier"].forEach(function(modal) {
                $(modal + " .select-search").each(function() {
                    if ($(this).hasClass("select2-hidden-accessible")) $(this).select2("destroy");
                    $(this).select2({
                        dropdownParent: $(modal),
                        width: "100%",
                        placeholder: "Ketik untuk mencari...",
                        allowClear: true
                    });
                });
            });
        }
        initKarierSelect2();
        ["modalTambahKarier", "modalEditKarier"].forEach(function(id) {
            var modalEl = document.getElementById(id);
            if (modalEl) {
                modalEl.addEventListener("shown.bs.modal", function() {
                    // Pengaman: init select yang belum terpasang Select2.
                    $(this).find(".select-search").each(function() {
                        if (!$(this).hasClass("select2-hidden-accessible")) {
                            $(this).select2({
                                dropdownParent: $(this).closest(".modal"),
                                width: "100%",
                                placeholder: "Ketik untuk mencari...",
                                allowClear: true
                            });
                        }
                    });
                });
            }
        });

        // --- 2. LOAD DATA TABEL KARIER (DENGAN FILTER) ---
        function loadKarier() {
            let site = document.getElementById("filterSite").value;
            let jabatan = document.getElementById("filterJabatan").value;
            let status = document.getElementById("filterStatus").value;
            let bulan = document.getElementById("filterBulan").value;

            // Buat URL dengan parameter GET
            let url = `app/controller/get-data/get-karier.php?site=${site}&jabatan=${jabatan}&status=${status}&bulan=${bulan}`;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableKarier")) {
                        $(".MyTableKarier").DataTable().destroy();
                    }
                    tbody.innerHTML = html;

                    // KOREKSI: Konfigurasi digabung di sini agar tidak memicu error double init
                    $(".MyTableKarier").DataTable({
                        responsive: true,
                        pageLength: 10,
                        columnDefs: [{
                            orderable: false,
                            targets: 0
                        }]
                    });
                })
                .catch(err => console.error("Gagal meload data tabel: ", err));
        }

        // --- TRIGGER FILTER SAAT DROPDOWN DIUBAH ---
        document.getElementById("filterSite").addEventListener("change", loadKarier);
        document.getElementById("filterJabatan").addEventListener("change", loadKarier);
        document.getElementById("filterStatus").addEventListener("change", loadKarier);
        document.getElementById("filterBulan").addEventListener("change", loadKarier);

        // --- 3. PROSES IMPORT EXCEL (SUDAH DIPERBAIKI UNTUK MENAMPILKAN ERROR) ---
        formImport.addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            let btnImport = document.querySelector("#formImportPayroll button[type=submit]");

            btnImport.disabled = true;
            importResult.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin"></i> Memproses file, mohon tunggu...
        </div>
    `;

            fetch("app/controller/csv-control/csv-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.text())
                .then(text => {
                    btnImport.disabled = false;
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        showToast("Response bukan JSON!", "error");
                        importResult.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan format response server.</div>`;
                        return;
                    }

                    if (data.success) {
                        // Jika ada baris yang gagal dihitung oleh backend
                        if (data.errors && data.errors.length > 0) {
                            showToast(`Import selesai. Berhasil: ${data.berhasil}, Gagal: ${data.gagal}`, "warning");

                            // Cetak daftar baris yang error ke dalam id="importResult"
                            let errorHtml = `
                        <div class="alert alert-warning mt-3">
                            <h6 class="fw-bold"><i class="fas fa-exclamation-triangle text-danger"></i> Beberapa Baris Gagal Diimport (${data.gagal} data):</h6>
                            <ul class="mb-0" style="max-height: 200px; overflow-y: auto;">
                    `;

                            data.errors.forEach(err => {
                                errorHtml += `<li>${err}</li>`;
                            });

                            errorHtml += `
                            </ul>
                        </div>
                    `;
                            importResult.innerHTML = errorHtml;
                        } else {
                            // Jika sukses total tanpa ada baris yang gagal
                            showToast(`Import berhasil! Semua data masuk (${data.berhasil} data)`, "success");
                            importResult.innerHTML = `<div class="alert alert-success mt-3"><i class="fas fa-check-circle"></i> Semua data (${data.berhasil}) berhasil diimport tanpa error.</div>`;
                            this.reset();
                        }

                        loadKarier(); // RELOAD TABEL UTOMATIS
                    } else {
                        // Ini jika file belum dipilih atau format salah (Error Fatal)
                        showToast(data.error || "Import gagal!", "error");
                        importResult.innerHTML = `<div class="alert alert-danger mt-3"><i class="fas fa-times-circle"></i> ${data.error || "Import gagal!"}</div>`;
                    }
                })
                .catch(err => {
                    btnImport.disabled = false;
                    showToast("Request error: " + err.message, "error");
                    importResult.innerHTML = `<div class="alert alert-danger mt-3">Koneksi/Sistem bermasalah.</div>`;
                });
        });

        // --- 4. TAMBAH KARIER MANUAL (DENGAN PREVIEW SURAT KONTRAK) ---
        function escPreview(s) {
            return String(s == null || s === "" ? "-" : s).replace(/&/g, "&amp;")
                .replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        function selText(form, name) {
            const sel = form.querySelector("select[name=" + name + "]");
            return sel && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex].text : "-";
        }

        function renderPreviewKarier(form, info) {
            const fd = new FormData(form);
            const rp = v => (v == null || v === "" || v === "0") ? "-" : "Rp " + v;
            const tplCell = info.template
                ? `<a href="app/uploads/kontrak/${encodeURIComponent(info.template.file)}" target="_blank">${escPreview(info.template.nama_asli || info.template.file)}</a>`
                : `<span class="badge bg-warning text-dark">Belum ada template untuk jabatan ini</span>`;
            const noCell = info.no_surat_preview
                ? `<strong>${escPreview(info.no_surat_preview)}</strong><br><small class="text-muted">Estimasi &mdash; nomor final terbit saat simpan</small>`
                : `<span class="badge bg-danger">Kode jabatan belum diisi</span>`;
            const rows = [
                ["Karyawan", escPreview(selText(form, "id_karyawan"))],
                ["Site", escPreview(selText(form, "id_site"))],
                ["Departemen", escPreview(selText(form, "id_departemen"))],
                ["Jabatan", escPreview(selText(form, "id_jabatan")) + (info.kode_jabatan ? ` <small class="text-muted">(${escPreview(info.kode_jabatan)})</small>` : "")],
                ["Gaji Pokok", rp(fd.get("gaji_pokok"))],
                ["Upah Lembur", rp(fd.get("lembur"))],
                ["Potongan Izin", rp(fd.get("pot_izin"))],
                ["Potongan Mangkir", rp(fd.get("pot_mangkir"))],
                ["Tgl Mulai", escPreview(fd.get("tgl_mulai"))],
                ["Tgl Selesai", escPreview(fd.get("tgl_selesai"))],
                ["No Surat", noCell],
                ["Template Kontrak", tplCell]
            ];
            document.getElementById("tbodyPreviewKarier").innerHTML = rows.map(r =>
                `<tr><th style="width:30%">${r[0]}</th><td>${r[1]}</td></tr>`).join("");
            const ok = !!info.no_surat_preview;
            document.getElementById("btnKonfirmasiKarier").disabled = !ok;
            document.getElementById("previewKarierWarning").innerHTML = ok ? "" :
                `<div class="alert alert-danger py-2 mb-0">Simpan diblokir: lengkapi kode jabatan terlebih dahulu.</div>`;
            new bootstrap.Modal(document.getElementById("modalPreviewKarier")).show();
        }

        // --- PREVIEW NO SURAT LIVE DI MODAL TAMBAH ---
        let addNoSuratSeq = 0;
        function refreshAddNoSurat() {
            const form = document.getElementById("formTambahKarier");
            const prev = document.getElementById("addNoSuratPreview");
            const warn = document.getElementById("addNoSuratWarning");
            if (!form || !prev) return;
            const idJabatan = form.querySelector("select[name=id_jabatan]").value;
            const tglMulai = form.querySelector("input[name=tgl_mulai]").value;
            if (!idJabatan) {
                prev.textContent = "—";
                if (warn) warn.innerHTML = "";
                return;
            }
            const my = ++addNoSuratSeq;
            prev.textContent = "Memuat...";
            const pf = new FormData();
            pf.append("action", "preview_info");
            pf.append("id_jabatan", idJabatan);
            pf.append("tgl_mulai", tglMulai || "");
            fetch("app/controller/act-template-kontrak.php", { method: "POST", body: pf })
                .then(res => res.json())
                .then(res => {
                    if (my !== addNoSuratSeq) return;
                    if (res.status !== "success") { prev.textContent = "—"; return; }
                    if (res.no_surat_preview) {
                        prev.textContent = res.no_surat_preview;
                        if (warn) warn.innerHTML = "";
                    } else {
                        prev.textContent = "—";
                        if (warn) warn.innerHTML = `<div class="alert alert-warning py-2 mt-2 mb-0">Jabatan belum punya kode &mdash; lengkapi kode jabatan agar nomor bisa diterbitkan.</div>`;
                    }
                })
                .catch(() => { if (my === addNoSuratSeq) prev.textContent = "—"; });
        }

        // --- AUTO-ISI NILAI DARI DETAIL KONTRAK SAAT JABATAN DIPILIH ---
        // Hanya mengisi field yang masih kosong/nol agar input manual tidak tertimpa.
        function autoIsiNilaiKontrak() {
            const form = document.getElementById("formTambahKarier");
            if (!form || typeof formatIndo !== "function") return;
            const idJabatan = form.querySelector("select[name=id_jabatan]").value;
            if (!idJabatan) return;
            const pf = new FormData();
            pf.append("action", "preview_info");
            pf.append("id_jabatan", idJabatan);
            fetch("app/controller/act-template-kontrak.php", { method: "POST", body: pf })
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success" || !res.detail) return;
                    const map = {
                        gaji_pokok: "gaji_pokok",
                        lembur: "upah_lembur",
                        pot_izin: "potongan_izin",
                        pot_mangkir: "potongan_mangkir"
                    };
                    Object.keys(map).forEach(name => {
                        const el = form.querySelector(`input[name=${name}]`);
                        if (!el) return;
                        const angka = String(el.value || "").replace(/[^0-9]/g, "").replace(/^0+/, "");
                        if (angka === "") el.value = formatIndo(res.detail[map[name]] ?? 0);
                    });
                })
                .catch(() => {});
        }

        (function bindAddNoSurat() {
            const form = document.getElementById("formTambahKarier");
            if (!form || form.dataset.nosuratBound) return;
            form.dataset.nosuratBound = "1";
            // Pakai jQuery .on agar change dari Select2 ikut tertangkap.
            $(form).find("select[name=id_jabatan]").on("change", () => {
                refreshAddNoSurat();
                autoIsiNilaiKontrak();
            });
            $(form).find("input[name=tgl_mulai]").on("change", refreshAddNoSurat);
            document.getElementById("modalTambahKarier").addEventListener("show.bs.modal", refreshAddNoSurat);
        })();

        document.getElementById("formTambahKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            const form = this;
            if (!form.checkValidity()) { form.reportValidity(); return; }
            const fd = new FormData(form);
            // id_karyawan diambil otomatis dari dropdown <select name="id_karyawan">
            // Select2 menyembunyikan select asli sehingga validasi required bawaan terlewat — cek manual.
            for (const [k, label] of [["id_site", "Site"], ["id_departemen", "Departemen"], ["id_jabatan", "Jabatan"]]) {
                if (!fd.get(k)) { showToast("Pilih " + label + " terlebih dahulu.", "warning"); return; }
            }

            const pf = new FormData();
            pf.append("action", "preview_info");
            pf.append("id_jabatan", fd.get("id_jabatan"));
            pf.append("tgl_mulai", fd.get("tgl_mulai") || "");

            fetch("app/controller/act-template-kontrak.php", {
                    method: "POST",
                    body: pf
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") {
                        showToast(res.msg || "Gagal memuat preview.", "error");
                        return;
                    }
                    renderPreviewKarier(form, res);
                })
                .catch(() => showToast("Terjadi kesalahan sistem.", "error"));
        });

        document.getElementById("btnKonfirmasiKarier").addEventListener("click", function() {
            const btn = this;
            btn.disabled = true;
            const fd = new FormData(document.getElementById("formTambahKarier"));
            fd.append("action", "add");

            fetch("app/controller/act-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadKarier();
                        showToast("Karier berhasil ditambah!" + (res.no_surat ? " No: " + res.no_surat : ""), "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalPreviewKarier")).hide();
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahKarier")).hide();
                        document.getElementById("formTambahKarier").reset();
                        $("#formTambahKarier select.select-search").val("").trigger("change");
                        document.getElementById("addNoSuratPreview").textContent = "—";
                        document.getElementById("addNoSuratWarning").innerHTML = "";
                    } else {
                        showToast(res.msg, "error");
                    }
                })
                .catch(() => showToast("Terjadi kesalahan sistem.", "error"))
                .finally(() => { btn.disabled = false; });
        });

        // --- 5. TAMPILKAN MODAL EDIT ---
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditKarier");
            if (!btn) return;

            const form = document.getElementById("formEditKarier");
            form.querySelector("input[name=id_karier]").value = btn.dataset.id;
            form.querySelector("select[name=id_site]").value = btn.dataset.id_site;
            form.querySelector("select[name=id_departemen]").value = btn.dataset.id_departemen;
            form.querySelector("select[name=id_jabatan]").value = btn.dataset.id_jabatan;
            // Sinkronkan tampilan Select2
            $(form.querySelector("select[name=id_departemen]")).trigger("change");
            $(form.querySelector("select[name=id_jabatan]")).trigger("change");

            // KOREKSI: Gunakan fungsi formatIndo agar data pecahan langsung bermasker titik koma di modal edit
            form.querySelector("input[name=gaji_pokok]").value = formatIndo(btn.dataset.gaji_pokok);
            form.querySelector("input[name=lembur]").value = formatIndo(btn.dataset.lembur);
            form.querySelector("input[name=pot_izin]").value = formatIndo(btn.dataset.pot_izin);
            form.querySelector("input[name=pot_mangkir]").value = formatIndo(btn.dataset.pot_mangkir);

            form.querySelector("input[name=tgl_mulai]").value = btn.dataset.tgl_mulai;
            form.querySelector("input[name=tgl_selesai]").value = btn.dataset.tgl_selesai;

            new bootstrap.Modal(document.getElementById("modalEditKarier")).show();
        });

        // --- 6. SUBMIT EDIT KARIER ---
        document.getElementById("formEditKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "edit");

            fetch("app/controller/act-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        showToast("Karier berhasil diupdate!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditKarier")).hide();
                        loadKarier();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // --- 7. DELETE KARIER ---
        document.addEventListener("click", async e => {
            let btn = e.target.closest(".btnDeleteKarier");
            if (!btn) return;

            if (await confirmDialog("Yakin hapus karier ini?")) {
                fetch("app/controller/act-karier.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "action=delete&id_karier=" + btn.dataset.id
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil menghapus!", "success");
                            loadKarier();
                        } else {
                            showToast(res.msg, "error");
                        }
                    })
                    .catch(err => {
                        showToast("Terjadi kesalahan: " + err, "error");
                    });
            }
        });

        // --- 8. LOAD DATA TABEL SAAT PERTAMA KALI DIBUKA ---
        window.reloadKarierTable = loadKarier;
        loadKarier();

        // --- 9. EXPORT CSV KARIER ---
        window.exportKarierCSV = function() {
            // Ambil value dari filter saat ini
            let site = document.getElementById("filterSite").value;
            let jabatan = document.getElementById("filterJabatan").value;
            let status = document.getElementById("filterStatus").value;
            let bulan = document.getElementById("filterBulan").value;

            // AMBIL DATA ID YANG DICENTANG (Mencakup Semua Halaman Pagination)
            let table = $(".MyTableKarier").DataTable();
            let selectedIds = [];
            table.$(".chk-extend:checked").each(function() {
                selectedIds.push($(this).val());
            });

            // Gabungkan array menjadi string, misal: "12,15,20"
            let idsString = selectedIds.join(',');

            // Buka tab/jendela baru ke URL export dengan membawa parameter filter DAN ids
            let url = `app/controller/export/export-karier.php?site=${site}&jabatan=${jabatan}&status=${status}&bulan=${bulan}&ids=${idsString}`;
            window.location.href = url;
        };

        // --- CHECKBOX ALL TOGGLE (Mencakup Semua Halaman Datatables) ---
        document.addEventListener("change", e => {
            if (e.target.id === "checkAllExtend") {
                let isChecked = e.target.checked;
                // Gunakan API DataTable untuk mencentang semua checkbox di semua halaman (pagination)
                let table = $(".MyTableKarier").DataTable();
                table.$(".chk-extend").prop("checked", isChecked);
            }
        });

        // --- BATCH EXTEND CONTRACT ---
        document.getElementById("btnExtendBatch").addEventListener("click", async () => {
            let table = $(".MyTableKarier").DataTable();
            let selected = [];

            // Ambil semua checkbox yang dicentang di seluruh halaman
            table.$(".chk-extend:checked").each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                showToast("Pilih minimal satu karyawan untuk diperpanjang!", "error");
                return;
            }

            if (await confirmDialog(`Yakin ingin memperpanjang kontrak ${selected.length} karyawan terpilih selama 6 bulan?\n(Sistem hanya akan memproses karyawan berstatus Aktif)`)) {
                let fd = new FormData();
                fd.append("action", "extend_batch");
                selected.forEach(id => fd.append("id_karier[]", id));

                fetch("app/controller/act-karier.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast(res.msg, "success");
                            document.getElementById("checkAllExtend").checked = false;
                            loadKarier();
                        } else {
                            showToast(res.msg, "error");
                        }
                    })
                    .catch(err => showToast("Terjadi kesalahan sistem.", "error"));
            }
        });

        // --- SINGLE EXTEND CONTRACT (Untuk import-karier.php) ---
        document.addEventListener("click", async e => {
            let btn = e.target.closest(".btnExtendKarier");
            if (!btn) return;

            let nama = btn.dataset.nama;
            if (await confirmDialog(`Perpanjang kontrak ${nama} selama 6 bulan?\n(Hanya bisa jika status karyawan Aktif)`)) {
                let fd = new FormData();
                fd.append("action", "extend_single");
                fd.append("id_karier", btn.dataset.id);

                fetch("app/controller/act-karier.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast(res.msg, "success");
                            loadKarier();
                        } else {
                            showToast(res.msg, "error");
                        }
                    });
            }
        });

    });
</script>