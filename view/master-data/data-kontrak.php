<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Data Karyawan /</span> Manajemen Dokumen Kontrak
        </h4>

        <div class="pb-3 d-flex justify-content-end">
            <div style="width: 250px;">
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
                        <thead class="table-info text-center">
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Periode Kontrak</th>
                                <th>Status Dokumen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKontrak">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUploadKontrak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formUploadKontrak" class="modal-content" enctype="multipart/form-data">
                <input type="hidden" name="id_karier" id="upload_id_karier">
                <input type="hidden" name="id_karyawan" id="upload_id_karyawan">

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
    function loadKontrak() {
        // Ambil value dari dropdown filter
        let status = document.getElementById("filterStatusKontrak").value;

        // Kirim parameter status via GET
        fetch("app/controller/get-data/get-kontrak.php?status=" + encodeURIComponent(status))
            .then(res => res.text())
            .then(html => {
                if ($.fn.DataTable.isDataTable(".MyTableKontrak")) {
                    $(".MyTableKontrak").DataTable().destroy();
                }
                document.getElementById("tbodyKontrak").innerHTML = html;
                $(".MyTableKontrak").DataTable({ responsive: true });
            });
    }

    // Tambahkan Event Listener agar saat dropdown diganti, tabel langsung mereload
    document.getElementById("filterStatusKontrak").addEventListener("change", function() {
        loadKontrak();
    });

    // Trigger Modal Upload
    document.addEventListener("click", e => {
        let btn = e.target.closest(".btnUploadKontrak");
        if (!btn) return;

        // Isi form modal dengan data dari tombol
        document.getElementById("upload_id_karier").value = btn.dataset.id_karier;
        document.getElementById("upload_id_karyawan").value = btn.dataset.id_karyawan;
        document.getElementById("upload_nama_karyawan").value = btn.dataset.nama;
        
        // Kosongkan input file sebelumnya
        document.querySelector("input[name=file_pdf]").value = "";

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

    loadKontrak();
});
</script>