<?php
// Pastikan ID Karier ada
$id_karier_global = isset($_GET['id']) ? $_GET['id'] : '';
?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Karier /</span> Potongan
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPotongan">
                <i class="fa fa-plus"></i> Tambah Potongan
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Potongan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTablePotongan w-100">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th class="text-center">Nama Potongan</th>
                                <th class="text-center">Jumlah (Rp)</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPotongan">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahPotongan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formTambahPotongan" class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Tambah Potongan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Potongan</label>
                        <input type="text" name="jenis_potongan" class="form-control" placeholder="Contoh: BPJS" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah_potongan" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditPotongan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditPotongan" class="modal-content">
                <input type="hidden" name="id_potongan">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Edit Potongan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Potongan</label>
                        <input type="text" name="jenis_potongan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah_potongan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const karierId = "<?= $id_karier_global ?>";
    const tbody = document.getElementById("tbodyPotongan");
    const modalEditElement = document.getElementById("modalEditPotongan");
    const modalEdit = new bootstrap.Modal(modalEditElement);
    const modalTambah = new bootstrap.Modal(document.getElementById("modalTambahPotongan"));

    // Function: Load Data
    function loadPotongan() {
        fetch(`app/controller/get-data/get-potongan-tetap.php?id=${karierId}`)
            .then(res => res.text())
            .then(html => {
                if ($.fn.DataTable.isDataTable(".MyTablePotongan")) {
                    $(".MyTablePotongan").DataTable().destroy();
                }
                tbody.innerHTML = html;
                $(".MyTablePotongan").DataTable({
                    responsive: true,
                    pageLength: 10
                });
            });
    }

    // Event: Tambah Data
    document.getElementById("formTambahPotongan").addEventListener("submit", function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append("action", "add");
        fd.append("id_karier", karierId);

        fetch("app/controller/act-potongan-tetap.php", { method: "POST", body: fd })
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    loadPotongan();
                    modalTambah.hide();
                    this.reset();
                    showToast("Data berhasil disimpan", "success");
                }
            });
    });

    // Event: Buka Modal Edit (Delegation)
    document.addEventListener("click", e => {
        let btn = e.target.closest(".btnEditPotongan");
        if (!btn) return;

        const form = document.getElementById("formEditPotongan");
        form.querySelector("input[name=id_potongan]").value = btn.dataset.id;
        form.querySelector("input[name=jenis_potongan]").value = btn.dataset.nama;
        form.querySelector("input[name=jumlah_potongan]").value = btn.dataset.jumlah;

        modalEdit.show();
    });

    // Event: Update Data
    document.getElementById("formEditPotongan").addEventListener("submit", function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append("action", "edit");

        fetch("app/controller/act-potongan-tetap.php", { method: "POST", body: fd })
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    loadPotongan();
                    modalEdit.hide();
                    showToast("Data berhasil diupdate", "success");
                }
            });
    });

    // Event: Delete Data
    document.addEventListener("click", e => {
        let btn = e.target.closest(".btnDeletePotongan");
        if (!btn) return;

        if (confirm("Yakin ingin menghapus potongan ini?")) {
            let fd = new FormData();
            fd.append("action", "delete");
            fd.append("id_potongan", btn.dataset.id);

            fetch("app/controller/act-potongan-tetap.php", { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        loadPotongan();
                        showToast("Data berhasil dihapus", "success");
                    }
                });
        }
    });

    loadPotongan();
});
</script>