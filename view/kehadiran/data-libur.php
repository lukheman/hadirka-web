<!-- libur.php -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Hari Libur
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahLibur">
                <i class="fa fa-plus"></i> Tambah Libur
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Hari Libur</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableLibur">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Tanggal Awal</th>
                                <th>Tanggal Akhir</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLibur"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahLibur" tabindex="-1">
    <div class="modal-dialog">
        <form id="formTambahLibur" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Hari Libur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" name="tgl_awal_libur" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir_libur" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="ket_libur" class="form-control" placeholder="Contoh: Libur Nasional" required>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditLibur" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEditLibur" class="modal-content">
            <input type="hidden" name="id_libur">

            <div class="modal-header">
                <h5 class="modal-title">Edit Hari Libur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" name="tgl_awal_libur" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir_libur" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="ket_libur" class="form-control" required>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tbody = document.getElementById("tbodyLibur");
        const formTambah = document.getElementById("formTambahLibur");
        const formEdit = document.getElementById("formEditLibur");

        // ========== LOAD DATA ==========
        function loadLibur() {
            fetch("app/controller/get-data/get-libur.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableLibur")) {
                        $(".MyTableLibur").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $(".MyTableLibur").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                });
        }

        // ========== TAMBAH ==========
        formTambah.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formTambah);
            fd.append("action", "add");

            fetch("app/controller/act-libur.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Libur berhasil ditambah!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahLibur")).hide();
                        formTambah.reset();
                        loadLibur();
                    } else {
                        showToast(res.msg || "Gagal menambah", "error");
                    }
                });
        });

        // ========== EDIT (buka modal & isi) ==========
        document.addEventListener("click", e => {
            if (e.target.classList.contains("btnEditLibur")) {
                const btn = e.target;
                const id = btn.dataset.id;
                const awal = btn.dataset.awal;
                const akhir = btn.dataset.akhir;
                const ket = btn.dataset.ket;

                formEdit.querySelector("input[name=id_libur]").value = id;
                formEdit.querySelector("input[name=tgl_awal_libur]").value = awal;
                formEdit.querySelector("input[name=tgl_akhir_libur]").value = akhir;
                formEdit.querySelector("input[name=ket_libur]").value = ket;

                new bootstrap.Modal(document.getElementById("modalEditLibur")).show();
            }
        });

        // ========== UPDATE ==========
        formEdit.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formEdit);
            fd.append("action", "edit");

            fetch("app/controller/act-libur.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Data berhasil diupdate!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditLibur")).hide();
                        loadLibur();
                    } else {
                        showToast(res.msg || "Gagal update", "error");
                    }
                });
        });

        // ========== DELETE ==========
        document.addEventListener("click", async e => {
            if (e.target.classList.contains("btnDeleteLibur")) {
                const id = e.target.dataset.id;
                if (!(await confirmDialog("Yakin hapus libur ini?"))) return;

                fetch("app/controller/act-libur.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "action=delete&id_libur=" + encodeURIComponent(id)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil dihapus!", "success");
                            loadLibur();
                        } else {
                            showToast(res.msg || "Gagal hapus", "error");
                        }
                    });
            }
        });

        // init
        loadLibur();
    });
</script>