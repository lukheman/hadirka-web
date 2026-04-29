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
                            <a href="assets/template-csv/Format_Template_Import_Data_Karier.xlsx" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                </form>
                <div id="importResult" class="mt-3"></div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKarier">
                    <i class="fa fa-plus"></i> Tambah Karier Manual
                </button>
                <!-- Tombol Export Baru -->
                <button class="btn btn-info btn-sm" onclick="exportKarierCSV()">
                    <i class="fa fa-file-export"></i> Export CSV
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($_SESSION['site'] == 0) { ?>
                    <select id="filterSite" class="form-select form-select-sm">
                        <option value="">-- Semua Site --</option>
                        <?php 
                        $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                        while ($s = $sites->fetch_assoc()) {
                            echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                        }
                        ?>
                    </select>
                <?php } else { ?>
                    <input id="filterSite" type="hidden" value="<?= $_SESSION['site'] ?>">
                <?php } ?>

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
                                <th class="text-center">No</th>
                                <th class="text-center">NIK</th>
                                <th class="text-center">Nama</th>
                                <th class="text-center">Site</th>
                                <th class="text-center">Departemen</th>
                                <th class="text-center">Jabatan</th>
                                <th class="text-center">Gaji Pokok</th>
                                <th class="text-center">OT Pertama</th>
                                <th class="text-center">OT Kedua</th>
                                <th class="text-center">SPL</th>
                                <th class="text-center">OT1 SPL</th>
                                <th class="text-center">OT2 SPL</th>
                                <th class="text-center">Pot Izin</th>
                                <th class="text-center">Pot Mangkir</th>
                                <th class="text-center">Tgl Mulai</th>
                                <th class="text-center">Tgl Selesai</th>
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

                        <div class="col-md-3">
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

                        <div class="col-md-3">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="number" name="gaji_pokok" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>

                        <div class="col-md-3">
                            <label>OT Pertama (8 Jam)</label>
                            <input type="number" name="ot_pertama" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>OT Kedua (9 - Dst)</label>
                            <input type="number" name="ot_kedua" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>SPL</label>
                            <input type="number" name="spl" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>OT1 SPL</label>
                            <input type="number" name="ot1_spl" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>OT2 SPL</label>
                            <input type="number" name="ot2_spl" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Potongan Izin</label>
                            <input type="number" name="pot_izin" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Potongan Mangkir</label>
                            <input type="number" name="pot_mangkir" class="form-control">
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
                        <div class="col-md-3">
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

                        <div class="col-md-3">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control" required>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="number" name="gaji_pokok" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>

                        <div class="col-md-3">
                            <label>OT Pertama (8 Jam)</label>
                            <input type="number" name="ot_pertama" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>OT Kedua (9 - Dst)</label>
                            <input type="number" name="ot_kedua" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>SPL</label>
                            <input type="number" name="spl" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>OT1 SPL</label>
                            <input type="number" name="ot1_spl" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>OT2 SPL</label>
                            <input type="number" name="ot2_spl" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Potongan Izin</label>
                            <input type="number" name="pot_izin" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Potongan Mangkir</label>
                            <input type="number" name="pot_mangkir" class="form-control">
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
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // HAPUS const karyawanId = "..." di sini
        const formImport = document.getElementById("formImportPayroll");
        const importResult = document.getElementById("importResult");
        const tbody = document.getElementById("tbodyKarier");

        // --- 1. LOAD DATA TABEL KARIER (DENGAN FILTER) ---
        function loadKarier() {
            let site = document.getElementById("filterSite").value;
            let jabatan = document.getElementById("filterJabatan").value;
            let status = document.getElementById("filterStatus").value;
            let bulan = document.getElementById("filterBulan").value;

            // Buat URL dengan parameter GET
            let url = `app/controller/get-data/get-karier.php?site=${site}&jabatan=${jabatan}&status=${status}`;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableKarier")) {
                        $(".MyTableKarier").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $(".MyTableKarier").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                })
                .catch(err => console.error("Gagal meload data tabel: ", err));
        }

        // --- TRIGGER FILTER SAAT DROPDOWN DIUBAH ---
        document.getElementById("filterSite").addEventListener("change", loadKarier);
        document.getElementById("filterJabatan").addEventListener("change", loadKarier);
        document.getElementById("filterStatus").addEventListener("change", loadKarier);
        document.getElementById("filterBulan").addEventListener("change", loadKarier);

        // --- 2. PROSES IMPORT EXCEL ---
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
                        importResult.innerHTML = "";
                        return;
                    }

                    if (data.success) {
                        showToast(`Import berhasil! Data masuk: ${data.berhasil}, Gagal: ${data.gagal}`, "success");
                        loadKarier(); // RELOAD TABEL OTOMATIS SETELAH IMPORT
                        this.reset();
                    } else {
                        showToast(data.error || "Import gagal!", "error");
                    }
                    importResult.innerHTML = "";
                })
                .catch(err => {
                    btnImport.disabled = false;
                    showToast("Request error: " + err.message, "error");
                    importResult.innerHTML = "";
                });
        });

        // --- 3. TAMBAH KARIER MANUAL ---
        document.getElementById("formTambahKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "add");
            // HAPUS fd.append("id_karyawan", karyawanId) karena id_karyawan sudah diambil otomatis dari dropdown <select name="id_karyawan">

            fetch("app/controller/act-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadKarier();
                        showToast("Karier berhasil ditambah!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahKarier")).hide();
                        this.reset();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // --- 4. TAMPILKAN MODAL EDIT ---
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditKarier");
            if (!btn) return;

            const form = document.getElementById("formEditKarier");
            form.querySelector("input[name=id_karier]").value = btn.dataset.id;
            form.querySelector("select[name=id_site]").value = btn.dataset.id_site;
            form.querySelector("select[name=id_departemen]").value = btn.dataset.id_departemen;
            form.querySelector("select[name=id_jabatan]").value = btn.dataset.id_jabatan;
            form.querySelector("input[name=gaji_pokok]").value = btn.dataset.gaji_pokok;

            form.querySelector("input[name=ot_pertama]").value = btn.dataset.ot_pertama;
            form.querySelector("input[name=ot_kedua]").value = btn.dataset.ot_kedua;
            form.querySelector("input[name=spl]").value = btn.dataset.spl;
            form.querySelector("input[name=ot1_spl]").value = btn.dataset.ot1_spl;
            form.querySelector("input[name=ot2_spl]").value = btn.dataset.ot2_spl;
            form.querySelector("input[name=pot_izin]").value = btn.dataset.pot_izin;
            form.querySelector("input[name=pot_mangkir]").value = btn.dataset.pot_mangkir;
            form.querySelector("input[name=tgl_mulai]").value = btn.dataset.tgl_mulai;
            form.querySelector("input[name=tgl_selesai]").value = btn.dataset.tgl_selesai;

            new bootstrap.Modal(document.getElementById("modalEditKarier")).show();
        });

        // --- 5. SUBMIT EDIT KARIER ---
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

        // --- 6. DELETE KARIER ---
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnDeleteKarier");
            if (!btn) return;

            if (confirm("Yakin hapus karier ini?")) {
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

        // --- 7. LOAD DATA TABEL SAAT PERTAMA KALI DIBUKA ---
        loadKarier();

        // --- 8. EXPORT CSV KARIER ---
        window.exportKarierCSV = function() {
            // Ambil value dari filter saat ini
            let site = document.getElementById("filterSite").value;
            let jabatan = document.getElementById("filterJabatan").value;
            let status = document.getElementById("filterStatus").value;
            let bulan = document.getElementById("filterBulan").value;

            // Buka tab/jendela baru ke URL export dengan membawa parameter filter
            let url = `app/controller/export/export-karier.php?site=${site}&jabatan=${jabatan}&status=${status}&bulan=${bulan}`;
            window.location.href = url;
        };

    });
</script>