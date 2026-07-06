<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Master Data /</span> Kelola Karyawan Internal
        </h4>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-import"></i> Form Import Excel Karyawan</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportKaryawan" enctype="multipart/form-data">
                    <p class="instruction-text mb-3">
                        Format Excel/CSV: <strong>A</strong>=NIK | <strong>B</strong>=Nama | <strong>C</strong>=Jabatan | <strong>D</strong>=Gaji Pokok | <strong>E</strong>=Bank | <strong>F</strong>=No Rek | <strong>G</strong>=Atas Nama | <strong>H</strong>=Nama Site
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Upload File Excel Karyawan</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="col-md-5 mt-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import Data</button>
                            <a href="/assets/template-csv/Format_Karyawan_Internal.xlsx" class="btn btn-outline-secondary ml-2"><i class="fas fa-download"></i> Download Template</a>
                        </div>
                    </div>
                </form>
                <div id="importResult" class="mt-3"></div>
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFormKaryawan" onclick="tambahKarierManual()"><i class="fa fa-plus"></i> Tambah Karyawan Manual</button>
                <button class="btn btn-info btn-sm" onclick="exportKaryawanExcel()"><i class="fa fa-file-export"></i> Export Master Karyawan</button>
            </div>

            <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalExportGaji">
                <i class="fa fa-money-check-alt"></i> Export Gaji Bersih (Pokok + Bonus)
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Master Karyawan Internal</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover MyTableKaryawan" id="tableKaryawan" style="width:100%">
                        <thead class="table-info">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Site (Lokasi)</th>
                                <th>Jabatan</th>
                                <th>Gaji Pokok</th>
                                <th>Rekening Bank</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKaryawan"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFormKaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formKaryawan" class="modal-content">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_karyawan_internal" id="idKaryawan">

            <div class="modal-header bg-success text-white" id="modalHeaderBg">
                <h5 class="modal-title text-white" id="modalTitle"><i class="fa fa-user"></i> Form Karyawan Internal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">NIK Karyawan</label>
                    <input type="text" name="nik_karyawan_internal" id="nik_karyawan_internal" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Karyawan</label>
                    <input type="text" name="nama_karyawan" id="nama_karyawan" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pilih Site (Lokasi)</label>
                    <select name="id_site" id="id_site" class="form-select" required>
                        <option value="">-- Pilih Site --</option>
                        <?php
                        $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                        while ($s = $sites->fetch_assoc()) {
                            echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gaji Pokok</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="gaji_pokok" id="gaji_pokok" class="form-control nominal-mask" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Bank</label>
                    <input type="text" name="nama_bank" id="nama_bank" class="form-control" placeholder="Contoh: MANDIRI, BCA, BNI">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor Rekening</label>
                    <input type="text" name="no_rek" id="no_rek" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Atas Nama Rekening</label>
                    <input type="text" name="space" id="atas_nama_rek" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" id="btnSubmitForm"><i class="fa fa-save"></i> Simpan Data</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalExportGaji" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form id="formExport" method="GET" action="app/controller/export/export-gaji-internal.php" class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white"><i class="fa fa-file-excel"></i> Export Gaji Bersih Periode</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Pilih Bulan & Tahun Gaji</label>
                    <input type="month" name="bulan_tahun" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-download"></i> Download Excel (.xlsx)</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tbody = document.getElementById("tbodyKaryawan");

        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                let input = e.target;
                let val = input.value.replace(/\D/g, "");
                input.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });

        window.tambahKarierManual = function() {
            document.getElementById("formKaryawan").reset();
            document.getElementById("formAction").value = "add";
            document.getElementById("modalTitle").innerHTML = '<i class="fa fa-plus"></i> Tambah Karyawan Internal';
            document.getElementById("modalHeaderBg").className = "modal-header bg-success text-white";
            document.getElementById("btnSubmitForm").className = "btn btn-success";
        }

        function loadKaryawan() {
            fetch("app/controller/get-data/get-karyawan-internal.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable("#tableKaryawan")) {
                        $("#tableKaryawan").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $("#tableKaryawan").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                });
        }
        loadKaryawan();

        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditKaryawan");
            if (!btn) return;
            let d = btn.dataset;

            document.getElementById("formAction").value = "edit";
            document.getElementById("idKaryawan").value = d.id;
            document.getElementById("nik_karyawan_internal").value = d.nik;
            document.getElementById("id_site").value = d.site; // Isi otomatis data site terpilih
            document.getElementById("nama_karyawan").value = d.nama;
            document.getElementById("jabatan").value = d.jabatan;
            document.getElementById("gaji_pokok").value = d.gaji.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            document.getElementById("nama_bank").value = d.bank;
            document.getElementById("no_rek").value = d.rek;
            document.getElementById("atas_nama_rek").value = d.atasnama;

            document.getElementById("modalTitle").innerHTML = '<i class="fa fa-edit"></i> Edit Karyawan Internal';
            document.getElementById("modalHeaderBg").className = "modal-header bg-primary text-white";
            document.getElementById("btnSubmitForm").className = "btn btn-primary";

            let modal = new bootstrap.Modal(document.getElementById("modalFormKaryawan"));
            modal.show();
        });

        document.getElementById("formKaryawan").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.set("gaji_pokok", fd.get("gaji_pokok").replace(/\./g, ""));

            fetch("app/controller/act-karyawan-internal.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast(res.msg, "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalFormKaryawan")).hide();
                        loadKaryawan();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnDeleteKaryawan");
            if (btn && confirm("Yakin ingin menghapus karyawan internal ini?\nSemua log bonus karyawan ini juga akan dihapus permanen.")) {
                let fd = new FormData();
                fd.append("action", "delete");
                fd.append("id_karyawan_internal", btn.dataset.id);

                fetch("app/controller/act-karyawan-internal.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast(res.msg, "success");
                            loadKaryawan();
                        } else {
                            showToast(res.msg, "error");
                        }
                    });
            }
        });

        document.getElementById("formImportKaryawan").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            let resultDiv = document.getElementById("importResult");
            resultDiv.innerHTML = `<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Sedang mengimport data file Excel, mohon tunggu...</div>`;

            fetch("app/controller/csv-control/csv-karyawan-internal.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast("Proses import selesai!", "success");
                        resultDiv.innerHTML = `<div class="alert alert-success"><i class="fa fa-check-circle"></i> Berhasil diimport: ${data.berhasil} data. Gagal: ${data.gagal} data.</div>`;
                        loadKaryawan();
                        this.reset();
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Error: ${data.error}</div>`;
                    }
                });
        });

        window.exportKaryawanExcel = function() {
            window.location.href = "app/controller/export/export-karyawan-internal.php";
        };
    });
</script>