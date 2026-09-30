<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Departemen
        </h4>

        <div class="pb-3">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahDepartemen">
                <i class="bx bx-plus me-1"></i> Tambah Departemen
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Departemen</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTable">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Departemen</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDepartemen"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahDepartemen">
        <div class="modal-dialog">
            <form id="formTambahDepartemen" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Departemen</label>
                    <input type="text" name="nama_departemen" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditDepartemen">
        <div class="modal-dialog">
            <form id="formEditDepartemen" class="modal-content">
                <input type="hidden" name="id_departemen">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Departemen</label>
                    <input type="text" name="nama_departemen" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const tbody = document.getElementById("tbodyDepartemen");
        const formTambah = document.getElementById("formTambahDepartemen");
        const formEdit = document.getElementById("formEditDepartemen");

        function loadDepartemen() {
            fetch("app/controller/get-data/get-departemen.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTable")) {
                        $(".MyTable").DataTable().destroy();
                    }
                    tbody.innerHTML = html;

                    $(".MyTable").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                });
        }

        formTambah.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formTambah);
            fd.append("action", "add");

            fetch("app/controller/act-departemen.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Departemen berhasil ditambah!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahDepartemen")).hide();
                        formTambah.reset();
                        loadDepartemen();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        formEdit.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formEdit);
            fd.append("action", "edit");

            fetch("app/controller/act-departemen.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Departemen berhasil diupdate!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditDepartemen")).hide();
                        loadDepartemen();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        document.addEventListener("click", async e => {
            const btnEdit = e.target.closest ? e.target.closest(".btnEditDepart") : null;
            if (btnEdit) {
                let btn = btnEdit;

                formEdit.querySelector("input[name=id_departemen]").value = btn.dataset.id;
                formEdit.querySelector("input[name=nama_departemen]").value = btn.dataset.nama;

                new bootstrap.Modal(document.getElementById("modalEditDepartemen")).show();
                return;
            }

            const btnDel = e.target.closest ? e.target.closest(".btnDeleteDepart") : null;
            if (btnDel) {
                let id = btnDel.dataset.id;

                if (await confirmDialog("Yakin hapus departemen?")) {
                    fetch("app/controller/act-departemen.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=delete&id_departemen=" + id
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("Berhasil menghapus!", "success");
                                loadDepartemen();
                            } else {
                                showToast(res.msg, "error");
                            }
                        });
                }
            }
        });

        loadDepartemen();
    });
</script>