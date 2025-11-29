<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="fw-bold">
            <span class="text-muted fw-light">Karier /</span> Tunjangan
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTunjangan">
                <i class="fa fa-plus"></i> Tambah Tunjangan
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Tunjangan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableTunjangan">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama Tunjangan</th>
                                <th class="text-center">Jumlah (Rp)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTunjangan"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahTunjangan">
        <div class="modal-dialog">
            <form id="formTambahTunjangan" class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Tambah Tunjangan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Tunjangan</label>
                    <input type="text" name="nama_tunjangan" class="form-control" required>

                    <label class="mt-2">Jumlah</label>
                    <input type="number" name="jumlah_tunjangan" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditTunjangan">
        <div class="modal-dialog">
            <form id="formEditTunjangan" class="modal-content">
                <input type="hidden" name="id_tunjangan">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Tunjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Tunjangan</label>
                    <input type="text" name="nama_tunjangan" class="form-control" required>

                    <label class="mt-2">Jumlah</label>
                    <input type="number" name="jumlah_tunjangan" class="form-control" required>
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

        const karierId = "<?= $_GET['id'] ?>";
        const tbody = document.getElementById("tbodyTunjangan");

        // Load Tunjangan
        function loadTunjangan() {
            fetch("app/controller/get-data/get-tunjangan.php?id=" + karierId)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableTunjangan")) {
                        $(".MyTableTunjangan").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $(".MyTableTunjangan").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                });
        }

        // Add
        document.getElementById("formTambahTunjangan").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "add");
            fd.append("id_karier", karierId);

            fetch("app/controller/act-tunjangan.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadTunjangan();
                        showToast("Tunjangan berhasil ditambah!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahTunjangan")).hide();
                        this.reset();
                    }
                });
        });

        // Edit
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditTunjangan");
            if (!btn) return;

            const form = document.getElementById("formEditTunjangan");

            form.querySelector("input[name=id_tunjangan]").value = btn.dataset.id;
            form.querySelector("input[name=nama_tunjangan]").value = btn.dataset.nama;
            form.querySelector("input[name=jumlah_tunjangan]").value = btn.dataset.jumlah;

            new bootstrap.Modal(document.getElementById("modalEditTunjangan")).show();
        });

        document.getElementById("formEditTunjangan").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "edit");

            fetch("app/controller/act-tunjangan.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadTunjangan();
                        showToast("Tunjangan berhasil diupdate!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditTunjangan")).hide();
                    }
                });
        });

        // Delete
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnDeleteTunjangan");
            if (!btn) return;

            if (confirm("Yakin hapus tunjangan?")) {

                let fd = new FormData();
                fd.append("action", "delete");
                fd.append("id_tunjangan", btn.dataset.id);

                fetch("app/controller/act-tunjangan.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status == "success") {
                            showToast("Berhasil menghapus!", "success");
                            loadTunjangan();
                        }
                    });
            }
        });

        loadTunjangan();

    });
</script>