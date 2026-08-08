<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Sanksi & Indisipliner /</span> Master Pelanggaran
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggaran">
                <i class="fa fa-plus"></i> Tambah Jenis Pelanggaran
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Master Pelanggaran & Bobot Sanksi</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tablePelanggaran">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th>Kategori Pelanggaran</th>
                                <th>Keterangan Default</th>
                                <th class="text-center" style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPelanggaran"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahPelanggaran">
        <div class="modal-dialog">
            <form id="formTambahPelanggaran" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Tambah Pelanggaran</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Kategori Pelanggaran</label>
                        <input type="text" name="nama_pelanggaran" class="form-control" placeholder="Contoh: Terlambat, Tidak pakai APD" required>
                    </div>
                    <div class="mb-3">
                        <label>Keterangan Surat (Default)</label>
                        <textarea name="keterangan_default" class="form-control" rows="3" placeholder="Teks template untuk dicetak di surat..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- --- TAMBAHAN MODAL EDIT --- -->
    <div class="modal fade" id="modalEditPelanggaran">
        <div class="modal-dialog">
            <form id="formEditPelanggaran" class="modal-content">
                <input type="hidden" name="id_pelanggaran" id="edit_id_pelanggaran">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Edit Pelanggaran</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Kategori Pelanggaran</label>
                        <input type="text" name="nama_pelanggaran" id="edit_nama_pelanggaran" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Keterangan Surat (Default)</label>
                        <textarea name="keterangan_default" id="edit_keterangan_default" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
    <!-- --------------------------- -->
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tbody = document.getElementById("tbodyPelanggaran");
        const formTambah = document.getElementById("formTambahPelanggaran");
        const formEdit = document.getElementById("formEditPelanggaran"); // Form Edit

        function loadData() {
            fetch("app/controller/surat/get-pelanggaran.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable("#tablePelanggaran")) {
                        $("#tablePelanggaran").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $("#tablePelanggaran").DataTable({
                        responsive: true
                    });
                });
        }

        // Aksi Tambah Data
        formTambah.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formTambah);
            fd.append("action", "add");
            fetch("app/controller/surat/act-pelanggaran.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Pelanggaran berhasil ditambah!", "success");
                        bootstrap.Modal.getInstance(document.getElementById('modalTambahPelanggaran')).hide();
                        formTambah.reset();
                        loadData();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // --- Aksi Edit Data (Update) ---
        formEdit.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formEdit);
            fd.append("action", "edit");
            fetch("app/controller/surat/act-pelanggaran.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Pelanggaran berhasil diperbarui!", "success");
                        bootstrap.Modal.getInstance(document.getElementById('modalEditPelanggaran')).hide();
                        loadData();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // Delegation Event Klik untuk Edit dan Hapus
        document.addEventListener("click", e => {
            // Logika Klik Edit
            let btnEdit = e.target.closest(".btnEditPel");
            if (btnEdit) {
                document.getElementById("edit_id_pelanggaran").value = btnEdit.dataset.id;
                document.getElementById("edit_nama_pelanggaran").value = btnEdit.dataset.nama;
                document.getElementById("edit_keterangan_default").value = btnEdit.dataset.ket;

                new bootstrap.Modal(document.getElementById("modalEditPelanggaran")).show();
            }

            // Logika Klik Hapus
            let btnDel = e.target.closest(".btnDeletePel");
            if (btnDel) {
                if (confirm("Hapus master pelanggaran ini?")) {
                    let fd = new FormData();
                    fd.append("action", "delete");
                    fd.append("id_pelanggaran", btnDel.dataset.id);
                    fetch("app/controller/surat/act-pelanggaran.php", {
                            method: "POST",
                            body: fd
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("Data dihapus!", "success");
                                loadData();
                            } else {
                                showToast(res.msg, "error");
                            }
                        });
                }
            }
        });

        loadData();
    });
</script>