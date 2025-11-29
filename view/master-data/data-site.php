<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Site
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSite">
                <i class="fa fa-plus"></i> Tambah Site
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Database Karyawan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTable">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Site</th>
                                <th>Lokasi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodySite"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahSite">
        <div class="modal-dialog">
            <form id="formTambahSite" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Site</label>
                    <input type="text" name="nama_site" class="form-control mb-3" required>

                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditSite">
        <div class="modal-dialog">
            <form id="formEditSite" class="modal-content">
                <input type="hidden" name="id_site">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Site</label>
                    <input type="text" name="nama_site" class="form-control mb-3" required>

                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tbody = document.getElementById("tbodySite");
            const formTambah = document.getElementById("formTambahSite");
            const formEdit = document.getElementById("formEditSite");

            // === LOAD DATA ===
            function loadSite() {
                fetch("app/controller/get-data/get-site.php")
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

            // === TAMBAH ===
            formTambah.addEventListener("submit", e => {
                e.preventDefault();
                let fd = new FormData(formTambah);
                fd.append("action", "add");

                fetch("app/controller/act-site.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil menambah site!", "success");
                            bootstrap.Modal.getInstance(document.getElementById("modalTambahSite")).hide();
                            formTambah.reset();
                            loadSite();
                        } else {
                            showToast(res.msg, "error");
                        }
                    });
            });

            // === EDIT ===
            formEdit.addEventListener("submit", e => {
                e.preventDefault();
                let fd = new FormData(formEdit);
                fd.append("action", "edit");

                fetch("app/controller/act-site.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil update!", "success");
                            bootstrap.Modal.getInstance(document.getElementById("modalEditSite")).hide();
                            loadSite();
                        } else {
                            showToast(res.msg, "error");
                        }
                    });
            });

            // === OPEN MODAL EDIT ===
            document.addEventListener("click", (e) => {
                if (e.target.classList.contains("btnEdit")) {
                    let btn = e.target;

                    formEdit.querySelector("input[name=id_site]").value = btn.dataset.id;
                    formEdit.querySelector("input[name=nama_site]").value = btn.dataset.nama;
                    formEdit.querySelector("input[name=lokasi]").value = btn.dataset.lokasi;

                    new bootstrap.Modal(document.getElementById("modalEditSite")).show();
                }

                if (e.target.classList.contains("btnDelete")) {
                    let id = e.target.dataset.id;

                    if (confirm("Yakin menghapus site?")) {
                        fetch("app/controller/act-site.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: "action=delete&id_site=" + id
                            })
                            .then(res => res.json())
                            .then(res => {
                                if (res.status === "success") {
                                    showToast("Berhasil menghapus!", "success");
                                    loadSite();
                                } else {
                                    showToast(res.msg, "error");
                                }
                            });
                    }
                }
            });

            // === INIT ===
            loadSite();
        });
    </script>