<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Manajemen /</span> Dokumen Kontrak (Upload Massal)
        </h4>

        <div class="card mt-3">
            <h5 class="card-header">Daftar Jabatan & Status Kontrak</h5>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Upload PDF di sini akan otomatis menerapkannya ke <strong>semua karyawan aktif</strong> pada jabatan tersebut.
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableKontrak">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" style="width: 5%;"><i class="fa fa-hashtag"></i> No</th>
                                <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                                <th class="text-center"><i class="fa fa-users"></i> Karyawan Aktif</th>
                                <th class="text-center"><i class="fa fa-file"></i> Status Template</th>
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

    <div class="modal fade" id="modalUploadKontrak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formUploadKontrak" class="modal-content" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_massal">
                <input type="hidden" name="id_jabatan" id="upload_id_jabatan">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fas fa-upload"></i> Upload Kontrak Jabatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Target Jabatan</label>
                        <input type="text" id="upload_nama_jabatan" class="form-control bg-light fw-bold" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Karyawan Terdampak</label>
                        <input type="text" id="upload_total_karyawan" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File PDF</label>
                        <input type="file" name="file_pdf" class="form-control" accept="application/pdf" required>
                        <small class="text-danger">* File ini akan otomatis ditautkan ke semua karyawan aktif di jabatan ini.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveUpload">
                        <i class="fas fa-save"></i> Upload Massal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Fungsi Load Tabel
        function loadKontrak() {
            fetch("app/controller/get-data/get-kontrak.php")
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

        // Trigger Modal Upload
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnUploadKontrak");
            if (!btn) return;

            let total = parseInt(btn.dataset.total);
            if (total === 0) {
                showToast("Tidak ada karyawan aktif di jabatan ini.", "warning");
                return;
            }

            document.getElementById("upload_id_jabatan").value = btn.dataset.id_jabatan;
            document.getElementById("upload_nama_jabatan").value = btn.dataset.nama_jabatan;
            document.getElementById("upload_total_karyawan").value = total + " Orang";
            document.querySelector("#formUploadKontrak input[name=file_pdf]").value = "";

            new bootstrap.Modal(document.getElementById("modalUploadKontrak")).show();
        });

        // Submit Form Upload Massal
        document.getElementById("formUploadKontrak").addEventListener("submit", async function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            let btnSave = document.getElementById("btnSaveUpload");

            if (!(await confirmDialog("File ini akan menimpa file kontrak lama pada karyawan di jabatan ini. Lanjutkan?"))) return;

            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';

            fetch("app/controller/act-kontrak.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<i class="fas fa-save"></i> Upload Massal';

                    if (res.status === "success") {
                        showToast(`Berhasil! ${res.affected} kontrak karyawan diperbarui.`, "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalUploadKontrak")).hide();
                        loadKontrak(); 
                    } else {
                        showToast(res.msg, "error");
                    }
                })
                .catch(err => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<i class="fas fa-save"></i> Upload Massal';
                    showToast("Terjadi kesalahan sistem", "error");
                });
        });

        // Trigger Hapus Massal
        document.addEventListener("click", async e => {
            let btn = e.target.closest(".btnHapusFile");
            if (!btn) return;

            let idJabatan = btn.dataset.id_jabatan;
            let namaJabatan = btn.dataset.nama_jabatan;

            if (await confirmDialog(`Hapus file kontrak untuk semua karyawan dengan jabatan ${namaJabatan}?`)) {
                let fd = new FormData();
                fd.append("action", "delete_massal");
                fd.append("id_jabatan", idJabatan);

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
                        showToast("File kontrak massal berhasil dihapus!", "success");
                        loadKontrak();
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

        loadKontrak();
    });
</script>