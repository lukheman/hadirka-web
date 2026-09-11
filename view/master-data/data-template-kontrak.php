<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Manajemen /</span> Template Kontrak
        </h4>

        <div class="card mt-3">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Daftar Template Kontrak
                <button class="btn btn-sm btn-primary" id="btnTambahTemplate">
                    <i class="fas fa-upload"></i> Upload Template
                </button>
            </h5>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Satu template bisa berlaku untuk <strong>beberapa jabatan</strong>
                    sekaligus. File harus Word (.doc/.docx), maksimal 2 MB.
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableTemplate">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" style="width: 5%;"><i class="fa fa-hashtag"></i> No</th>
                                <th class="text-center"><i class="fa fa-file-word"></i> File Template</th>
                                <th class="text-center"><i class="fa fa-hdd"></i> Ukuran</th>
                                <th class="text-center"><i class="fa fa-briefcase"></i> Berlaku Untuk</th>
                                <th class="text-center"><i class="fa fa-calendar"></i> Tgl Upload</th>
                                <th class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTemplate">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUploadTemplate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="formUploadTemplate" class="modal-content" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fas fa-upload"></i> Upload Template Kontrak</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih File Word <span class="text-danger">*</span></label>
                        <input type="file" name="file_pdf" class="form-control" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <small class="text-muted">Format .doc/.docx, maksimal 2 MB.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Berlaku Untuk Jabatan <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="checkAllJabatan">
                            <label class="form-check-label fw-bold" for="checkAllJabatan">Pilih Semua</label>
                        </div>
                        <div id="listJabatan" class="border rounded p-3" style="max-height: 260px; overflow-y: auto;">
                            <span class="text-muted">Memuat jabatan...</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveTemplate">
                        <i class="fas fa-save"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const API = "app/controller/act-template-kontrak.php";

        function formatBytes(b) {
            b = parseInt(b) || 0;
            if (b < 1024) return b + " B";
            if (b < 1048576) return (b / 1024).toFixed(1) + " KB";
            return (b / 1048576).toFixed(2) + " MB";
        }

        function escHtml(s) {
            return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        // Checkbox daftar jabatan
        function loadJabatan() {
            fetch(API, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=list_jabatan"
                })
                .then(res => res.json())
                .then(res => {
                    const box = document.getElementById("listJabatan");
                    if (res.status !== "success" || !res.data.length) {
                        box.innerHTML = '<span class="text-danger">Gagal memuat jabatan.</span>';
                        return;
                    }
                    box.innerHTML = res.data.map(j =>
                        `<div class="form-check">
                            <input type="checkbox" class="form-check-input chk-jabatan" name="id_jabatan[]"
                                value="${j.id_jabatan}" id="jab_${j.id_jabatan}">
                            <label class="form-check-label" for="jab_${j.id_jabatan}">${escHtml(j.nama_jabatan)}</label>
                        </div>`
                    ).join("");
                    document.getElementById("checkAllJabatan").checked = false;
                })
                .catch(() => {
                    document.getElementById("listJabatan").innerHTML =
                        '<span class="text-danger">Gagal memuat jabatan.</span>';
                });
        }

        document.getElementById("checkAllJabatan").addEventListener("change", function() {
            document.querySelectorAll(".chk-jabatan").forEach(c => c.checked = this.checked);
        });

        // Tabel template
        function loadTable() {
            fetch(API, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=list"
                })
                .then(res => res.json())
                .then(res => {
                    if ($.fn.DataTable.isDataTable(".MyTableTemplate")) {
                        $(".MyTableTemplate").DataTable().destroy();
                    }
                    const tbody = document.getElementById("tbodyTemplate");
                    if (res.status !== "success") {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Gagal memuat data.</td></tr>`;
                        return;
                    }
                    if (!res.data.length) {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Belum ada template. Klik Upload Template.</td></tr>`;
                        return;
                    }
                    tbody.innerHTML = res.data.map((r, i) => {
                        const badges = (r.jabatan && r.jabatan.length)
                            ? r.jabatan.map(j => `<span class="badge bg-primary me-1 mb-1">${escHtml(j.nama_jabatan)}</span>`).join("")
                            : '<span class="badge bg-secondary">Tanpa jabatan</span>';
                        return `<tr>
                            <td class="text-center">${i + 1}</td>
                            <td>
                                <i class="fas fa-file-word text-primary"></i>
                                <strong>${escHtml(r.nama_asli || r.file)}</strong><br>
                                <small class="text-muted">${escHtml(r.file)}</small>
                            </td>
                            <td class="text-center">${formatBytes(r.ukuran)}</td>
                            <td>${badges}<br><small class="text-muted">${r.jml_jabatan} jabatan</small></td>
                            <td class="text-center">${escHtml(r.created_at || "-")}</td>
                            <td class="text-center">
                                <a href="app/uploads/kontrak/${escHtml(r.file)}" target="_blank"
                                    class="btn btn-sm btn-info" title="Lihat file"><i class="fas fa-eye"></i></a>
                                <button class="btn btn-sm btn-danger btnDeleteTemplate" data-id="${r.id_template}"
                                    data-nama="${escHtml(r.nama_asli || r.file)}" title="Hapus template">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    }).join("");
                    $(".MyTableTemplate").DataTable({ responsive: true });
                })
                .catch(() => {
                    document.getElementById("tbodyTemplate").innerHTML =
                        `<tr><td colspan="6" class="text-center text-danger">Gagal menghubungi server.</td></tr>`;
                });
        }

        document.getElementById("btnTambahTemplate").addEventListener("click", () => {
            document.getElementById("formUploadTemplate").reset();
            loadJabatan();
            new bootstrap.Modal(document.getElementById("modalUploadTemplate")).show();
        });

        document.getElementById("formUploadTemplate").addEventListener("submit", async function(e) {
            e.preventDefault();
            if (!document.querySelector(".chk-jabatan:checked")) {
                showToast("Pilih minimal satu jabatan.", "warning");
                return;
            }
            const btn = document.getElementById("btnSaveTemplate");
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';

            try {
                const fd = new FormData(this);
                fd.append("action", "upload");
                const res = await fetch(API, { method: "POST", body: fd });
                const data = await res.json();
                if (data.status === "success") {
                    showToast(data.msg || "Template berhasil diupload.", "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalUploadTemplate")).hide();
                    loadTable();
                } else {
                    showToast(data.msg || "Gagal mengupload.", "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan sistem.", "error");
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Upload';
            }
        });

        document.addEventListener("click", async e => {
            const btn = e.target.closest(".btnDeleteTemplate");
            if (!btn) return;
            if (await confirmDialog(`Hapus template "${btn.dataset.nama}" beserta relasinya?`)) {
                const fd = new FormData();
                fd.append("action", "delete");
                fd.append("id_template", btn.dataset.id);
                try {
                    const res = await fetch(API, { method: "POST", body: fd });
                    const data = await res.json();
                    if (data.status === "success") {
                        showToast(data.msg || "Template berhasil dihapus.", "success");
                        loadTable();
                    } else {
                        showToast(data.msg || "Gagal menghapus.", "error");
                    }
                } catch (err) {
                    showToast("Terjadi kesalahan sistem.", "error");
                }
            }
        });

        loadTable();
    });
</script>
