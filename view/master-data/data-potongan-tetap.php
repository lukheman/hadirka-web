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
                    <table class="table table-bordered MyTablePotongan">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama Potongan</th>
                                <th class="text-center">Jumlah (Rp)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPotongan"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahPotongan">
        <div class="modal-dialog">
            <form id="formTambahPotongan" class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Tambah Potongan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Potongan</label>
                    <input type="text" name="jenis_potongan" class="form-control" required>

                    <label class="mt-2">Jumlah</label>
                    <input type="number" name="jumlah_potongan" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditPotongan">
        <div class="modal-dialog">
            <form id="formEditPotongan" class="modal-content">
                <input type="hidden" name="id_potongan">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Potongan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Potongan</label>
                    <input type="text" name="jenis_potongan" class="form-control" required>

                    <label class="mt-2">Jumlah</label>
                    <input type="number" name="jumlah_potongan" class="form-control" required>
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
        const tbody = document.getElementById("tbodyPotongan");

        // Load Potongan
        function loadPotongan() {
            fetch("app/controller/get-data/get-potongan-tetap.php?id=" + karierId)
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

        // Add
        document.getElementById("formTambahPotongan").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "add");
            fd.append("id_karier", karierId);

            fetch("app/controller/act-potongan-tetap.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadPotongan();
                        showToast("Potongan berhasil ditambah!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahPotongan")).hide();
                        this.reset();
                    }
                });
        });

        // Edit
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditPotongan");
            if (!btn) return;

            const form = document.getElementById("formEditPotongan");

            form.querySelector("input[name=id_potongan]").value = btn.dataset.id;
            form.querySelector("input[name=jenis_potongan]").value = btn.dataset.nama;
            form.querySelector("input[name=jumlah_potongan]").value = btn.dataset.jumlah;

            new bootstrap.Modal(document.getElementById("modalEditPotongan")).show();
        });

        document.getElementById("formEditPotongan").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "edit");

            fetch("app/controller/act-potongan-tetap.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadPotongan();
                        showToast("Potongan berhasil diupdate!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditPotongan")).hide();
                    }
                });
        });

        // Delete
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnDeletePotongan");
            if (!btn) return;

            if (confirm("Yakin hapus potongan?")) {

                let fd = new FormData();
                fd.append("action", "delete");
                fd.append("id_potongan", btn.dataset.id);

                fetch("app/controller/act-potongan-tetap.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status == "success") {
                            showToast("Berhasil menghapus!", "success");
                            loadPotongan();
                        }
                    });
            }
        });

        loadPotongan();

    });
</script>