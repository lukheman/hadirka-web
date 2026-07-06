<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Sistem Keuangan /</span> Kelola Bonus Karyawan Internal</h4>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-excel"></i> Form Import Excel Bonus</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportBonus" enctype="multipart/form-data">
                    <p class="instruction-text mb-2">
                        Format Import: <strong>A</strong>=NIK Karyawan | <strong>B</strong>=Tanggal (YYYY-MM-DD) | <strong>C</strong>=Nominal | <strong>D</strong>=Keterangan
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Upload File Excel Bonus</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="col-md-5 mt-2">
                            <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Import Bonus</button>
                            <a href="/assets/template-csv/Format_Bonus_Internal.xlsx" class="btn btn-outline-secondary ml-2"><i class="fas fa-download"></i> Template Excel</a>
                        </div>
                    </div>
                </form>
                <div id="importBonusResult" class="mt-3"></div>
            </div>
        </div>

        <div class="mb-3 d-flex gap-2">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalBonus" onclick="tambahBonusManual()"><i class="fa fa-plus-circle"></i> Tambah Bonus Manual</button>
            <button class="btn btn-info btn-sm" onclick="exportBonusExcel()"><i class="fa fa-file-export"></i> Export Log Semua Bonus</button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tableBonus" style="width:100%">
                        <thead class="table-info">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Tanggal Bonus</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyBonus"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBonus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formBonus" class="modal-content">
            <input type="hidden" name="action" id="b_action" value="add">
            <input type="hidden" name="id_bonus" id="id_bonus">
            <div class="modal-header bg-success text-white" id="modalBonusHeader">
                <h5 class="modal-title text-white" id="modalBonusTitle">Form Kelola Bonus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label">Pilih Karyawan Internal</label>
                    <select name="id_karyawan_internal" id="b_karyawan" class="form-select select2-init" required>
                        <option value="">-- Pilih Karyawan --</option>
                        <?php 
                        $k = $koneksi->query("SELECT id_karyawan_internal, nik_karyawan_internal, nama_karyawan FROM tb_karyawan_internal ORDER BY nik_karyawan_internal ASC");
                        while($r = $k->fetch_assoc()){ 
                            echo "<option value='{$r['id_karyawan_internal']}'>{$r['nik_karyawan_internal']} - {$r['nama_karyawan']}</option>"; 
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Tanggal Pemberian Bonus</label>
                    <input type="date" name="tgl_bonus" id="b_tgl" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Nominal Bonus (Rp)</label>
                    <input type="text" name="nominal" id="b_nominal" class="form-control nominal-mask" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan / Alasan</label>
                    <textarea name="ket_bonus" id="b_ket" class="form-control" rows="3" placeholder="Contoh: Bonus Project Web / Lembur Khusus"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" id="btnSubmitBonus">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Inisialisasi Select2 pencarian karyawan di modal
    $('.select2-init').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalBonus'), width:'100%' });

    function loadBonus() {
        fetch("app/controller/get-data/get-bonus-internal.php")
            .then(res => res.text())
            .then(html => {
                if ($.fn.DataTable.isDataTable("#tableBonus")) $("#tableBonus").DataTable().destroy();
                document.getElementById("tbodyBonus").innerHTML = html;
                $("#tableBonus").DataTable({ order: [[3, 'desc']] });
            });
    }
    loadBonus();

    window.tambahBonusManual = function() {
        document.getElementById("formBonus").reset();
        $('#b_karyawan').val(null).trigger('change');
        document.getElementById("b_action").value = "add";
        document.getElementById("modalBonusTitle").innerText = "Tambah Bonus Karyawan";
        document.getElementById("modalBonusHeader").className = "modal-header bg-success text-white";
        document.getElementById("btnSubmitBonus").className = "btn btn-success";
    }

    // Listener Edit
    document.addEventListener("click", e => {
        let btn = e.target.closest(".btnEditBonus");
        if (!btn) return;
        let d = btn.dataset;

        document.getElementById("b_action").value = "edit";
        document.getElementById("id_bonus").value = d.id;
        $('#b_karyawan').val(d.karyawan).trigger('change');
        document.getElementById("b_tgl").value = d.tgl;
        document.getElementById("b_nominal").value = d.nominal.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        document.getElementById("b_ket").value = d.ket;

        document.getElementById("modalBonusTitle").innerText = "Edit Data Bonus";
        document.getElementById("modalBonusHeader").className = "modal-header bg-primary text-white";
        document.getElementById("btnSubmitBonus").className = "btn btn-primary";
        bootstrap.Modal.getOrCreateInstance(document.getElementById("modalBonus")).show();
    });

    // Simpan CRUD
    document.getElementById("formBonus").addEventListener("submit", function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.set("nominal", fd.get("nominal").replace(/\./g, ""));

        fetch("app/controller/act-bonus-internal.php", { method: "POST", body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === "success") {
                    showToast(res.msg, "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalBonus")).hide();
                    loadBonus();
                } else showToast(res.msg, "error");
            });
    });

    // Import Excel Bonus
    document.getElementById("formImportBonus").addEventListener("submit", function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        let divRes = document.getElementById("importBonusResult");
        divRes.innerHTML = `<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Memproses berkas Excel bonus...</div>`;

        fetch("app/controller/csv-control/csv-bonus-internal.php", { method: "POST", body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast("Import bonus sukses!", "success");
                    divRes.innerHTML = `<div class="alert alert-success">Sukses masuk: ${data.berhasil} data. Gagal: ${data.gagal} data.</div>`;
                    loadBonus();
                    this.reset();
                } else divRes.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            });
    });

    window.exportBonusExcel = function() {
        window.location.href = "app/controller/export/export-bonus-internal.php";
    }
});
</script>