<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Kelola COA (Kategori Keuangan)
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahCOA">
                <i class="fa fa-plus"></i> Tambah Akun
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data COA</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableCOA">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>Kelompok</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCOA"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalTambahCOA">
            <div class="modal-dialog">
                <form id="formTambahCOA" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Akun</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2"><label>Kode Akun</label><input type="text" name="kode_akun" class="form-control" required></div>
                        <div class="mb-2"><label>Nama Akun</label><input type="text" name="nama_akun" class="form-control" required></div>
                        <div class="mb-2"><label>Kelompok</label>
                            <select name="kelompok" class="form-select" required>
                                <option value="Aset">Aset</option>
                                <option value="Kewajiban">Kewajiban</option>
                                <option value="Ekuitas">Ekuitas</option>
                                <option value="Pendapatan">Pendapatan</option>
                                <option value="Beban">Beban</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-success">Simpan</button></div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalEditCOA">
            <div class="modal-dialog">
                <form id="formEditCOA" class="modal-content">
                    <input type="hidden" name="id_kategori">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Akun</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2"><label>Kode Akun</label><input type="text" name="kode_akun" class="form-control" required></div>
                        <div class="mb-2"><label>Nama Akun</label><input type="text" name="nama_akun" class="form-control" required></div>
                        <div class="mb-2"><label>Kelompok</label>
                            <select name="kelompok" class="form-select" required>
                                <option value="Aset">Aset</option>
                                <option value="Kewajiban">Kewajiban</option>
                                <option value="Ekuitas">Ekuitas</option>
                                <option value="Pendapatan">Pendapatan</option>
                                <option value="Beban">Beban</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tbody = document.getElementById("tbodyCOA");
        const formTambah = document.getElementById("formTambahCOA");
        const formEdit = document.getElementById("formEditCOA");

        function loadCOA() {
            fetch("app/controller/get-data/get-coa.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableCOA")) $(".MyTableCOA").DataTable().destroy();
                    tbody.innerHTML = html;
                    $(".MyTableCOA").DataTable({
                        responsive: true
                    });
                });
        }

        // Aksi Submit (Tambah & Edit)
        const handleForm = (form, action) => {
            form.addEventListener("submit", e => {
                e.preventDefault();
                let fd = new FormData(form);
                fd.append("action", action);
                fetch("app/controller/act-coa.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            // Panggil fungsi toast yang ada di sistem Anda
                            showToast(`Data berhasil ${action === 'add' ? 'ditambah' : 'diupdate'}!`, "success");
                            bootstrap.Modal.getInstance(form.closest('.modal')).hide();
                            loadCOA();
                            form.reset();
                        } else {
                            showToast("Gagal memproses data!", "error");
                        }
                    });
            });
        };
        handleForm(formTambah, "add");
        handleForm(formEdit, "edit");

        // Handler Edit & Delete
        document.addEventListener("click", async e => {
            if (e.target.classList.contains("btnEditCOA")) {
                let btn = e.target;
                formEdit.id_kategori.value = btn.dataset.id;
                formEdit.kode_akun.value = btn.dataset.kode;
                formEdit.nama_akun.value = btn.dataset.nama;
                formEdit.kelompok.value = btn.dataset.kelompok;
                new bootstrap.Modal(document.getElementById("modalEditCOA")).show();
            }
            if (e.target.classList.contains("btnDeleteCOA")) {
                if (await confirmDialog("Hapus akun ini?")) {
                    let fd = new FormData();
                    fd.append("action", "delete");
                    fd.append("id_kategori", e.target.dataset.id);
                    fetch("app/controller/act-coa.php", {
                            method: "POST",
                            body: fd
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("Data berhasil dihapus!", "success");
                                loadCOA();
                            } else {
                                showToast("Gagal menghapus data!", "error");
                            }
                        });
                }
            }
        });

        loadCOA();
    });
</script>