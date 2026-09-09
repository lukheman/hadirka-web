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

    <div class="modal fade" id="modalTambahTunjangan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formTambahTunjangan" class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Tambah Tunjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tunjangan</label>
                        <select name="nama_tunjangan" class="form-select" required>
                            <option value="">-- Pilih Tunjangan --</option>
                            <option value="Tunjangan Daerah">Tunjangan Daerah</option>
                            <option value="Tunjangan Hidup">Tunjangan Hidup</option>
                            <option value="Tunjangan Makan">Tunjangan Makan</option>
                            <option value="Tunjangan Skill">Tunjangan Skill</option>
                            <option value="Bonus Kehadiran">Bonus Kehadiran</option>
                            <option value="Tunjangan Posisi">Tunjangan Posisi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah_tunjangan" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditTunjangan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditTunjangan" class="modal-content">
                <input type="hidden" name="id_tunjangan">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Tunjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tunjangan</label>
                        <select name="nama_tunjangan" class="form-select" required>
                            <option value="Tunjangan Daerah">Tunjangan Daerah</option>
                            <option value="Tunjangan Hidup">Tunjangan Hidup</option>
                            <option value="Tunjangan Makan">Tunjangan Makan</option>
                            <option value="Tunjangan Skill">Tunjangan Skill</option>
                            <option value="Bonus Kehadiran">Bonus Kehadiran</option>
                            <option value="Tunjangan Posisi">Tunjangan Posisi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah_tunjangan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Data</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Ambil ID Karier dari URL PHP
        const karierId = "<?= $_GET['id'] ?>";
        const tbody = document.getElementById("tbodyTunjangan");

        // Fungsi Load Data
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

        // Tambah Data
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
                    } else {
                        showToast("Gagal menambah data: " + res.message, "error");
                    }
                })
                .catch(err => console.error("Error:", err));
        });

        // Munculkan Modal Edit & Isi Data
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditTunjangan");
            if (!btn) return;

            const form = document.getElementById("formEditTunjangan");
            form.querySelector("input[name=id_tunjangan]").value = btn.dataset.id;
            form.querySelector("select[name=nama_tunjangan]").value = btn.dataset.nama;
            form.querySelector("input[name=jumlah_tunjangan]").value = btn.dataset.jumlah;

            let modalEdit = new bootstrap.Modal(document.getElementById("modalEditTunjangan"));
            modalEdit.show();
        });

        // Update Data
        document.getElementById("formEditTunjangan").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "edit");

            fetch("app/controller/act-tunjangan.php", {
                    method: "POST",
                    body: fd
                })
                .then(async res => {
                    const text = await res.text(); // Ambil teks mentah dulu
                    try {
                        return JSON.parse(text); // Coba ubah ke JSON
                    } catch (err) {
                        console.error("Respon bukan JSON:", text); // Ini akan memunculkan error PHP yang sebenarnya di Console
                        throw new Error("Server mengembalikan format yang salah");
                    }
                })
                .then(res => {
                    if (res.status == "success") {
                        loadTunjangan();
                        showToast("Berhasil!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditTunjangan")).hide();
                        this.reset();

                    } else {
                        showToast("Gagal: " + res.message, "error");
                    }
                })
                .catch(err => console.error("Error Detail:", err));
        });

        // Hapus Data
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnDeleteTunjangan");
            if (!btn) return;

            if (confirm("Yakin ingin menghapus tunjangan ini?")) {
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

        // Jalankan load data pertama kali
        loadTunjangan();
    });
</script>