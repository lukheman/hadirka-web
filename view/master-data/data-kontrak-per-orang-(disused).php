<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Data Karyawan /</span> Manajemen Dokumen Kontrak
        </h4>

        <div class="pb-3 d-flex justify-content-end gap-2">
            <div style="width: 200px;">
                <select id="filterSiteKontrak" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan Site">
                    <option value="">-- Semua Site --</option>
                </select>
            </div>

            <div style="width: 200px;">
                <select id="filterJabatanKontrak" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan Jabatan">
                    <option value="">-- Semua Jabatan --</option>
                </select>
            </div>

            <div style="width: 200px;">
                <select id="filterStatusKontrak" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan status karyawan">
                    <option value="Aktif" selected>Karyawan Aktif</option>
                    <option value="Semua">-- Semua Status --</option>
                    <option value="Habis Kontrak">Habis Kontrak</option>
                    <option value="Pensiun">Pensiun</option>
                    <option value="PHK">PHK</option>
                    <option value="Resign">Resign</option>
                    <option value="Black List">Black List</option>
                </select>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Daftar Kontrak Kerja</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableKontrak">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center"><i class="fa fa-hashtag"></i> No</th>
                                <th class="text-center"><i class="fa fa-id-card"></i> NIK</th>
                                <th class="text-center"><i class="fa fa-user"></i> Nama Karyawan</th>
                                <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                                <th class="text-center"><i class="fa fa-calendar" aria-hidden="true"></i> Periode Kontrak</th>
                                <th class="text-center"><i class="fa fa-file" aria-hidden="true"></i> Status Dokumen</th>
                                <th class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKontrak">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditKontrak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditKontrak" class="modal-content" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_karier" id="edit_id_karier">
                <input type="hidden" name="id_karyawan" id="edit_id_karyawan">

                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Data Kontrak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Karyawan</label>
                        <input type="text" id="edit_nama_karyawan" class="form-control bg-light" readonly>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" id="edit_tgl_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" id="edit_tgl_selesai" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File PDF (Opsional)</label>
                        <input type="file" name="file_pdf" class="form-control" accept="application/pdf">
                        <small class="text-danger">* Biarkan kosong jika tidak ingin mengubah file PDF.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnSaveEdit">
                        <i class="fas fa-save"></i> Update Kontrak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalUploadKontrak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formUploadKontrak" class="modal-content" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="id_karyawan" id="upload_id_karyawan">
                <input type="hidden" name="id_karier" id="upload_id_karier">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fas fa-upload"></i> Upload Kontrak Kerja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Karyawan</label>
                        <input type="text" id="upload_nama_karyawan" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File PDF</label>
                        <input type="file" name="file_pdf" class="form-control" accept="application/pdf" required>
                        <small class="text-danger">* Format wajib .pdf (Maksimal 2MB)</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveUpload">
                        <i class="fas fa-save"></i> Simpan File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Fungsi untuk meload data tabel kontrak (bisa kamu pisah ke file get-kontrak.php)
        // Fungsi untuk meload data tabel kontrak dengan filter
        // 1. Fungsi Load Dropdown Site & Jabatan
        function loadFilters() {
            fetch("app/controller/get-data/get-site-departemen-jabatan.php")
                .then(res => res.json())
                .then(data => {
                    let siteOpt = '<option value="">-- Semua Site --</option>';
                    data.site.forEach(s => {
                        siteOpt += `<option value="${s.id_site}">${s.nama_site}</option>`;
                    });
                    document.getElementById("filterSiteKontrak").innerHTML = siteOpt;

                    let jabatanOpt = '<option value="">-- Semua Jabatan --</option>';
                    data.jabatan.forEach(j => {
                        jabatanOpt += `<option value="${j.id_jabatan}">${j.nama_jabatan}</option>`;
                    });
                    document.getElementById("filterJabatanKontrak").innerHTML = jabatanOpt;
                })
                .catch(err => console.error("Gagal meload filter:", err));
        }

        // 2. Fungsi Load Tabel Kontrak dengan Filter
        function loadKontrak() {
            // Ambil value dari ketiga dropdown filter
            let status = document.getElementById("filterStatusKontrak").value;
            let site = document.getElementById("filterSiteKontrak").value;
            let jabatan = document.getElementById("filterJabatanKontrak").value;

            // Gabungkan parameter via GET
            let url = `app/controller/get-data/get-kontrak.php?status=${encodeURIComponent(status)}&site=${encodeURIComponent(site)}&jabatan=${encodeURIComponent(jabatan)}`;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableKontrak")) {
                        $(".MyTableKontrak").DataTable().destroy();
                    }
                    document.getElementById("tbodyKontrak").innerHTML = html;
                    $(".MyTableKontrak").DataTable({
                        responsive: true
                    });
                });
        }

        // 3. Event Listener untuk Dropdown Filter (Otomatis reload tabel saat diganti)
        document.getElementById("filterStatusKontrak").addEventListener("change", loadKontrak);
        document.getElementById("filterSiteKontrak").addEventListener("change", loadKontrak);
        document.getElementById("filterJabatanKontrak").addEventListener("change", loadKontrak);

        // Trigger Modal Edit
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditKontrak");
            if (!btn) return;

            document.getElementById("edit_id_karier").value = btn.dataset.id_karier;
            document.getElementById("edit_id_karyawan").value = btn.dataset.id_karyawan;
            document.getElementById("edit_nama_karyawan").value = btn.dataset.nama;
            document.getElementById("edit_tgl_mulai").value = btn.dataset.tgl_mulai;

            // Format handle null atau 0000-00-00 dari database
            let tglSelesai = btn.dataset.tgl_selesai;
            document.getElementById("edit_tgl_selesai").value = (tglSelesai !== "null" && tglSelesai !== "0000-00-00" && tglSelesai) ? tglSelesai : "";

            // Kosongkan file uploader
            document.querySelector("#formEditKontrak input[name=file_pdf]").value = "";

            new bootstrap.Modal(document.getElementById("modalEditKontrak")).show();
        });

        // Proses Submit form Edit
        document.getElementById("formEditKontrak").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            let btnSave = document.getElementById("btnSaveEdit");

            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            fetch("app/controller/act-kontrak.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<i class="fas fa-save"></i> Update Kontrak';

                    if (res.status === "success") {
                        showToast("Data kontrak berhasil diperbarui!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditKontrak")).hide();
                        loadKontrak(); // Reload tabel
                    } else {
                        showToast(res.msg, "error");
                    }
                })
                .catch(err => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<i class="fas fa-save"></i> Update Kontrak';
                    showToast("Terjadi kesalahan sistem", "error");
                });
        });

        // Trigger Modal Upload (Kode ini yang terhapus sebelumnya)
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnUploadKontrak");
            if (!btn) return;

            // Isi form modal dengan data dari tombol
            document.getElementById("upload_id_karier").value = btn.dataset.id_karier;
            document.getElementById("upload_id_karyawan").value = btn.dataset.id_karyawan;
            document.getElementById("upload_nama_karyawan").value = btn.dataset.nama;

            // Kosongkan input file sebelumnya
            document.querySelector("#formUploadKontrak input[name=file_pdf]").value = "";

            // Tampilkan modal
            new bootstrap.Modal(document.getElementById("modalUploadKontrak")).show();
        });

        // Proses Submit form Upload
        document.getElementById("formUploadKontrak").addEventListener("submit", function(e) {
            e.preventDefault();

            let fd = new FormData(this);
            let btnSave = document.getElementById("btnSaveUpload");

            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';

            fetch("app/controller/act-kontrak.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<i class="fas fa-save"></i> Simpan File';

                    if (res.status === "success") {
                        showToast("File kontrak berhasil diupload!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalUploadKontrak")).hide();
                        loadKontrak(); // Reload tabel agar tombol "Lihat PDF" muncul
                    } else {
                        showToast(res.msg, "error");
                    }
                })
                .catch(err => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<i class="fas fa-save"></i> Simpan File';
                    showToast("Terjadi kesalahan sistem", "error");
                });
        });

        // Trigger Hapus File PDF
        document.addEventListener("click", async e => {
            let btn = e.target.closest(".btnHapusFile");
            if (!btn) return;

            let idKarier = btn.dataset.id_karier;
            let nama = btn.dataset.nama;

            if (await confirmDialog(`Apakah Anda yakin ingin menghapus file kontrak milik ${nama}?`)) {
                let fd = new FormData();
                fd.append("action", "delete_file");
                fd.append("id_karier", idKarier);

                // Ubah icon jadi loading
                let iconAsli = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                fetch("app/controller/act-kontrak.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("File kontrak berhasil dihapus!", "success");
                        loadKontrak(); // Reload tabel
                    } else {
                        showToast(res.msg, "error");
                        btn.innerHTML = iconAsli;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    showToast("Terjadi kesalahan server.", "error");
                    btn.innerHTML = iconAsli;
                    btn.disabled = false;
                });
            }
        });

        loadFilters();
        loadKontrak();
    });
</script>