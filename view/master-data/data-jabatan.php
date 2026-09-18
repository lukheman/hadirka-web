<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Jabatan
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahJabatan">
                <i class="fa fa-plus"></i> Tambah Jabatan
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Jabatan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableJabatan">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Kode Jabatan</th>
                                <th>Nama Jabatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyJabatan"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahJabatan">
        <div class="modal-dialog">
            <form id="formTambahJabatan" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Kode Jabatan</label>
                    <input type="text" name="kode_jabatan" class="form-control" maxlength="50" placeholder="Ex: JBT-001">
                    <label class="mt-2">Nama Jabatan</label>
                    <input type="text" name="nama_jabatan" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditJabatan">
        <div class="modal-dialog">
            <form id="formEditJabatan" class="modal-content">
                <input type="hidden" name="id_jabatan">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Kode Jabatan</label>
                    <input type="text" name="kode_jabatan" class="form-control" maxlength="50" placeholder="Ex: JBT-001">
                    <label class="mt-2">Nama Jabatan</label>
                    <input type="text" name="nama_jabatan" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const tbody = document.getElementById("tbodyJabatan");
    const formTambah = document.getElementById("formTambahJabatan");
    const formEdit = document.getElementById("formEditJabatan");

    // =================== LOAD DATA ===================
    function loadJabatan() {
        fetch("app/controller/get-data/get-jabatan.php")
            .then(res => res.text())
            .then(html => {

                if ($.fn.DataTable.isDataTable(".MyTableJabatan")) {
                    $(".MyTableJabatan").DataTable().destroy();
                }

                tbody.innerHTML = html;

                $(".MyTableJabatan").DataTable({
                    responsive: true,
                    pageLength: 10
                });
            });
    }

    // =================== TAMBAH ===================
    formTambah.addEventListener("submit", e => {
        e.preventDefault();

        let fd = new FormData(formTambah);
        fd.append("action", "add");

        fetch("app/controller/act-jabatan.php", {
            method: "POST",
            body: fd
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                showToast("Jabatan berhasil ditambah!", "success");
                bootstrap.Modal.getInstance(document.getElementById("modalTambahJabatan")).hide();
                formTambah.reset();
                loadJabatan();
            } else {
                showToast(res.msg, "error");
            }
        });
    });

    // =================== UPDATE ===================
    formEdit.addEventListener("submit", e => {
        e.preventDefault();

        let fd = new FormData(formEdit);
        fd.append("action", "edit");

        fetch("app/controller/act-jabatan.php", {
            method: "POST",
            body: fd
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                showToast("Jabatan berhasil diupdate!", "success");
                bootstrap.Modal.getInstance(document.getElementById("modalEditJabatan")).hide();
                loadJabatan();
            } else {
                showToast(res.msg, "error");
            }
        });
    });

    // =================== EDIT & DELETE HANDLER ===================
    document.addEventListener("click", async e => {

        // --- EDIT ---
        const btnEJ = e.target.closest ? e.target.closest(".btnEditJabatan") : null;
        if (btnEJ) {
            let btn = btnEJ;

            formEdit.querySelector("input[name=id_jabatan]").value = btn.dataset.id;
            formEdit.querySelector("input[name=kode_jabatan]").value = btn.dataset.kode || "";
            formEdit.querySelector("input[name=nama_jabatan]").value = btn.dataset.nama;

            new bootstrap.Modal(document.getElementById("modalEditJabatan")).show();
        }

        // --- DELETE ---
        const btnDJ = e.target.closest ? e.target.closest(".btnDeleteJabatan") : null;
        if (btnDJ) {
            let id = btnDJ.dataset.id;

            if (await confirmDialog("Yakin hapus jabatan?")) {
                fetch("app/controller/act-jabatan.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=delete&id_jabatan=" + id
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Berhasil menghapus!", "success");
                        loadJabatan();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
            }
        }
    });

    loadJabatan();
});
</script>
