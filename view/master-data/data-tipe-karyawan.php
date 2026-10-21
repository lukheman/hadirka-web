<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Tipe Karyawan
        </h4>

        <div class="pb-3">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTipe">
                <i class="bx bx-plus me-1"></i> Tambah Tipe Karyawan
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Tipe Karyawan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableTipe">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Tipe</th>
                                <th class="text-center">Jam Kerja</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTipeKaryawan"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahTipe">
        <div class="modal-dialog">
            <form id="formTambahTipe" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tipe Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Tipe <span class="text-danger">*</span></label>
                    <input type="text" name="nama_tipe" class="form-control" placeholder="cth: Kontrak, Harian, Magang" required>

                    <div class="mt-3">
                        <label>Jam Kerja Mulai</label>
                        <input type="time" name="jam_kerja_mulai" class="form-control" placeholder="cth: 08:00">
                    </div>
                    <div class="mt-3">
                        <label>Jam Kerja Selesai</label>
                        <input type="time" name="jam_kerja_selesai" class="form-control" placeholder="cth: 16:00">
                    </div>
                    <div class="mt-3 form-text">Kosongkan keduanya jika tidak ada jam kerja bawaan.</div>

                    <div class="mt-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="cth: Karyawan kontrak bulanan"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditTipe">
        <div class="modal-dialog">
            <form id="formEditTipe" class="modal-content">
                <input type="hidden" name="id_tipe_karyawan">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Tipe Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Nama Tipe <span class="text-danger">*</span></label>
                    <input type="text" name="nama_tipe" class="form-control" required>

                    <div class="mt-3">
                        <label>Jam Kerja Mulai</label>
                        <input type="time" name="jam_kerja_mulai" class="form-control">
                    </div>
                    <div class="mt-3">
                        <label>Jam Kerja Selesai</label>
                        <input type="time" name="jam_kerja_selesai" class="form-control">
                    </div>

                    <div class="mt-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Edit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const tbody = document.getElementById("tbodyTipeKaryawan");
        const formTambah = document.getElementById("formTambahTipe");
        const formEdit = document.getElementById("formEditTipe");

        function loadTipeKaryawan() {
            fetch("app/controller/get-data/get-tipe-karyawan.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableTipe")) {
                        $(".MyTableTipe").DataTable().destroy();
                    }
                    tbody.innerHTML = html;

                    $(".MyTableTipe").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                });
        }

        formTambah.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formTambah);
            fd.append("action", "add");

            fetch("app/controller/act-tipe-karyawan.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast(res.msg, "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahTipe")).hide();
                        formTambah.reset();
                        loadTipeKaryawan();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        formEdit.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formEdit);
            fd.append("action", "edit");

            fetch("app/controller/act-tipe-karyawan.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast(res.msg, "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditTipe")).hide();
                        loadTipeKaryawan();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        document.addEventListener("click", async e => {
            const btnEdit = e.target.closest ? e.target.closest(".btnEditTipe") : null;
            if (btnEdit) {
                formEdit.querySelector("input[name=id_tipe_karyawan]").value = btnEdit.dataset.id;
                formEdit.querySelector("input[name=nama_tipe]").value = btnEdit.dataset.nama;
                formEdit.querySelector("input[name=jam_kerja_mulai]").value = btnEdit.dataset.jamMulai || "";
                formEdit.querySelector("input[name=jam_kerja_selesai]").value = btnEdit.dataset.jamSelesai || "";
                formEdit.querySelector("textarea[name=keterangan]").value = btnEdit.dataset.keterangan || "";

                new bootstrap.Modal(document.getElementById("modalEditTipe")).show();
                return;
            }

            const btnDel = e.target.closest ? e.target.closest(".btnDeleteTipe") : null;
            if (btnDel) {
                let id = btnDel.dataset.id;
                let nama = btnDel.dataset.nama;

                if (await confirmDialog(`Yakin ingin menghapus tipe karyawan "${nama}"?`)) {
                    fetch("app/controller/act-tipe-karyawan.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=delete&id_tipe_karyawan=" + id
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast(res.msg, "success");
                                loadTipeKaryawan();
                            } else {
                                showToast(res.msg, "error");
                            }
                        });
                }
            }
        });

        loadTipeKaryawan();
    });
</script>
