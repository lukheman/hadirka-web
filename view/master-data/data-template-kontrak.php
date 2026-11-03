<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Manajemen /</span> Template Kontrak
        </h4>

        <div class="card mt-3">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Daftar Template Kontrak
                <button class="btn btn-sm btn-primary" id="btnTambahTemplate">
                    <i class="bx bx-upload me-1"></i> Upload Template
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
                                <th class="text-center"><i class="fa fa-file-contract"></i> Nama Kontrak</th>
                                <th class="text-center"><i class="fa fa-tags"></i> Jenis Kontrak</th>
                                <th class="text-center"><i class="fa fa-briefcase"></i> Berlaku Untuk</th>
                                <th class="text-center"><i class="fa fa-file"></i> File</th>
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
                    <h5 class="modal-title text-white"><i class="bx bx-upload me-1"></i> Upload Template Kontrak</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle"></i> Nilai kontrak, tunjangan, dan potongan sudah tercantum di dalam file Word yang diupload — tidak perlu diinput ulang di sini.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kontrak <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kontrak" class="form-control" maxlength="150" placeholder="cth: Kontrak Kerja Operator 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kontrak <span class="text-danger">*</span></label>
                        <select name="jenis_kontrak" class="form-control" required>
                            <option value="KARYAWAN BARU">KARYAWAN BARU</option>
                            <option value="PERPANJANGAN">PERPANJANGAN</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Word <span class="text-danger">*</span></label>
                        <input type="file" name="file_pdf" class="form-control" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <small class="text-muted">Format .doc/.docx, maksimal 2 MB. Dokumen wajib memuat placeholder <code>${nomor_kontrak}</code>.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Berlaku Untuk Jabatan <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="cariJabatan" class="form-control" placeholder="Cari jabatan..." autocomplete="off">
                        </div>
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveTemplate">
                        <i class="bx bx-save me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditTemplate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bx bx-edit me-1"></i> Edit Template Kontrak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle"></i> Nilai kontrak, tunjangan, dan potongan sudah tercantum di dalam file Word — cukup perbarui file bila ada perubahan nilai.
                    </div>
                    <form id="formEditTemplate" enctype="multipart/form-data">
                        <input type="hidden" name="id_template" id="editTemplateId">
                        <div class="mb-3">
                            <label class="form-label">Nama Kontrak <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kontrak" id="editTemplateNama" class="form-control" maxlength="150" placeholder="cth: Kontrak Kerja Operator 2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kontrak <span class="text-danger">*</span></label>
                            <select name="jenis_kontrak" id="editTemplateJenis" class="form-control" required>
                                <option value="KARYAWAN BARU">KARYAWAN BARU</option>
                                <option value="PERPANJANGAN">PERPANJANGAN</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ganti File Word <small class="text-muted">(opsional — kosongkan bila tidak diganti)</small></label>
                            <input type="file" name="file_pdf" class="form-control" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            <small class="text-muted">File saat ini: <span id="editTemplateFile">-</span>. Format .doc/.docx, maksimal 2 MB. File pengganti wajib memuat placeholder <code>${nomor_kontrak}</code>.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Berlaku Untuk Jabatan <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" id="cariJabatanEdit" class="form-control" placeholder="Cari jabatan..." autocomplete="off">
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="checkAllJabatanEdit">
                                <label class="form-check-label fw-bold" for="checkAllJabatanEdit">Pilih Semua</label>
                            </div>
                            <div id="listJabatanEdit" class="border rounded p-3" style="max-height: 260px; overflow-y: auto;">
                                <span class="text-muted">Memuat jabatan...</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-warning" id="btnUpdateTemplate">
                                <i class="bx bx-save me-1"></i> Simpan Template
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const API = "app/controller/act-template-kontrak.php";

        function escHtml(s) {
            return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        // Label jabatan: "Nama Jabatan (KODE)" bila kode ada, sederhanakan bila kosong.
        function jabatanLabel(j) {
            const nama = (j && j.nama_jabatan) || "-";
            const kode = String((j && j.kode_jabatan) || "").trim();
            return kode ? `${nama} (${kode})` : nama;
        }

        // Checkbox daftar jabatan
        function loadJabatan() {
            const box = document.getElementById("listJabatan");
            const btnSave = document.getElementById("btnSaveTemplate");
            btnSave.disabled = true;
            fetch(API, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=list_jabatan"
                })
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("list_jabatan bukan JSON:", text);
                        throw new Error("Respon server tidak valid.");
                    }
                })
                .then(res => {
                    if (res.status !== "success") {
                        throw new Error(res.msg || res.message || "Gagal memuat jabatan.");
                    }
                    if (!res.data.length) {
                        box.innerHTML = '<span class="text-muted">Belum ada data jabatan. Tambahkan dulu di menu Data Jabatan.</span>';
                        return;
                    }
                    box.innerHTML = res.data.map(j =>
                        `<div class="form-check">
                            <input type="checkbox" class="form-check-input chk-jabatan" name="id_jabatan[]"
                                value="${j.id_jabatan}" id="jab_${j.id_jabatan}">
                            <label class="form-check-label" for="jab_${j.id_jabatan}">${escHtml(jabatanLabel(j))}</label>
                        </div>`
                    ).join("");
                    document.getElementById("checkAllJabatan").checked = false;
                    const cari = document.getElementById("cariJabatan");
                    if (cari) { cari.value = ""; filterJabatan("listJabatan", ""); }
                    btnSave.disabled = false;
                })
                .catch(err => {
                    console.error("loadJabatan:", err);
                    box.innerHTML =
                        '<span class="text-danger">Gagal memuat jabatan: ' + escHtml(err.message || err) + '</span>';
                });
        }

        document.getElementById("checkAllJabatan").addEventListener("change", function() {
            document.querySelectorAll("#listJabatan .chk-jabatan").forEach(c => {
                if (c.closest(".form-check").style.display !== "none") c.checked = this.checked;
            });
        });

        // Pencarian jabatan pada kedua daftar checkbox (upload & edit).
        function filterJabatan(boxId, keyword) {
            const box = document.getElementById(boxId);
            if (!box) return;
            const key = String(keyword || "").toLowerCase().trim();
            let visible = 0;
            box.querySelectorAll(".form-check").forEach(wrap => {
                const label = wrap.querySelector("label");
                const match = !key || (label && label.textContent.toLowerCase().includes(key));
                wrap.style.display = match ? "" : "none";
                if (match) visible++;
            });
            let empty = box.querySelector(".jabatan-empty");
            if (!visible) {
                if (!empty) {
                    empty = document.createElement("div");
                    empty.className = "jabatan-empty text-muted text-center py-2";
                    empty.textContent = "Jabatan tidak ditemukan.";
                    box.appendChild(empty);
                }
                empty.style.display = "";
            } else if (empty) {
                empty.style.display = "none";
            }
        }
        document.getElementById("cariJabatan").addEventListener("input", function() {
            filterJabatan("listJabatan", this.value);
        });
        document.getElementById("cariJabatanEdit").addEventListener("input", function() {
            filterJabatan("listJabatanEdit", this.value);
        });

        // Checkbox daftar jabatan untuk modal edit (dengan pra-pilih tautan saat ini)
        function loadJabatanEdit(selectedIds) {
            const box = document.getElementById("listJabatanEdit");
            const selected = new Set((selectedIds || []).map(String));
            box.innerHTML = '<span class="text-muted">Memuat jabatan...</span>';
            fetch(API, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=list_jabatan"
                })
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("list_jabatan bukan JSON:", text);
                        throw new Error("Respon server tidak valid.");
                    }
                })
                .then(res => {
                    if (res.status !== "success") {
                        throw new Error(res.msg || res.message || "Gagal memuat jabatan.");
                    }
                    if (!res.data.length) {
                        box.innerHTML = '<span class="text-muted">Belum ada data jabatan. Tambahkan dulu di menu Data Jabatan.</span>';
                        return;
                    }
                    box.innerHTML = res.data.map(j =>
                        `<div class="form-check">
                            <input type="checkbox" class="form-check-input chk-jabatan-edit" name="id_jabatan[]"
                                value="${j.id_jabatan}" id="jabedit_${j.id_jabatan}"${selected.has(String(j.id_jabatan)) ? " checked" : ""}>
                            <label class="form-check-label" for="jabedit_${j.id_jabatan}">${escHtml(jabatanLabel(j))}</label>
                        </div>`
                    ).join("");
                    const all = box.querySelectorAll(".chk-jabatan-edit");
                    document.getElementById("checkAllJabatanEdit").checked =
                        all.length > 0 && box.querySelectorAll(".chk-jabatan-edit:checked").length === all.length;
                    const cariEdit = document.getElementById("cariJabatanEdit");
                    if (cariEdit) { cariEdit.value = ""; filterJabatan("listJabatanEdit", ""); }
                })
                .catch(err => {
                    console.error("loadJabatanEdit:", err);
                    box.innerHTML =
                        '<span class="text-danger">Gagal memuat jabatan: ' + escHtml(err.message || err) + '</span>';
                });
        }

        document.getElementById("checkAllJabatanEdit").addEventListener("change", function() {
            document.querySelectorAll("#listJabatanEdit .chk-jabatan-edit").forEach(c => {
                if (c.closest(".form-check").style.display !== "none") c.checked = this.checked;
            });
        });

        // Buka modal edit template (nama, file, jabatan) dengan prefill
        document.addEventListener("click", e => {
            const btn = e.target.closest(".btnEditTemplate");
            if (!btn) return;
            document.getElementById("formEditTemplate").reset();
            document.getElementById("editTemplateId").value = btn.dataset.id;
            document.getElementById("editTemplateNama").value = btn.dataset.namaKontrak || "";
            document.getElementById("editTemplateFile").textContent = btn.dataset.file || "-";
            const jenisAwal = String(btn.dataset.jenis || "KARYAWAN BARU").toUpperCase();
            document.getElementById("editTemplateJenis").value =
                jenisAwal === "PERPANJANGAN" ? "PERPANJANGAN" : "KARYAWAN BARU";
            const ids = (btn.dataset.jabatanIds || "").split(",").map(s => s.trim()).filter(Boolean);
            loadJabatanEdit(ids);
            new bootstrap.Modal(document.getElementById("modalEditTemplate")).show();
        });

        document.getElementById("formEditTemplate").addEventListener("submit", async function(e) {
            e.preventDefault();
            if (!document.querySelector(".chk-jabatan-edit:checked")) {
                showToast("Pilih minimal satu jabatan.", "warning");
                return;
            }
            const btn = document.getElementById("btnUpdateTemplate");
            btn.disabled = true;
            try {
                const fd = new FormData(this);
                fd.append("action", "update");
                const res = await (await fetch(API, { method: "POST", body: fd })).json();
                if (res.status === "success") {
                    showToast(res.msg || "Template berhasil diperbarui.", "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalEditTemplate")).hide();
                    loadTable();
                } else {
                    showToast(res.msg || "Gagal menyimpan.", "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan sistem.", "error");
            } finally {
                btn.disabled = false;
            }
        });

        // Badge jenis kontrak: KARYAWAN BARU (hijau) / PERPANJANGAN (kuning).
        function jenisBadge(jenis) {
            const j = String(jenis || "").toUpperCase();
            if (j === "PERPANJANGAN") return `<span class="badge bg-warning text-dark">PERPANJANGAN</span>`;
            if (j === "KARYAWAN BARU") return `<span class="badge bg-success">KARYAWAN BARU</span>`;
            return `<span class="badge bg-secondary">${escHtml(jenis || "-")}</span>`;
        }

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
                        const daftarJab = (r.jabatan && r.jabatan.length) ? r.jabatan : [];
                        const maksTampil = 3;
                        const badges = daftarJab.length
                            ? daftarJab.slice(0, maksTampil).map(j => `<span class="badge bg-primary me-1 mb-1">${escHtml(jabatanLabel(j))}</span>`).join("") +
                              (daftarJab.length > maksTampil
                                  ? `<span class="badge bg-secondary me-1 mb-1" title="${escHtml(daftarJab.map(jabatanLabel).join(", "))}">+${daftarJab.length - maksTampil} lainnya</span>`
                                  : "")
                            : '<span class="badge bg-secondary">Tanpa jabatan</span>';
                        const ukuranKb = r.ukuran ? (Number(r.ukuran) / 1024).toFixed(0) + " KB" : "-";
                        const fileInfo = `<small><strong>${escHtml(r.nama_asli || r.file || "-")}</strong><br>` +
                            `<span class="text-muted">${ukuranKb}</span></small>`;
                        return `<tr>
                            <td class="text-center">${i + 1}</td>
                            <td><strong>${escHtml(r.nama_kontrak || "-")}</strong></td>
                            <td class="text-center">${jenisBadge(r.jenis_kontrak)}</td>
                            <td>${badges}<br><small class="text-muted">${r.jml_jabatan} jabatan</small></td>
                            <td>${fileInfo}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                <a href="app/uploads/template-kontrak/${escHtml(r.file)}" target="_blank"
                                    class="btn btn-sm btn-info" title="Lihat file"><i class="bx bx-show me-1"></i>Lihat</a>
                                <button type="button" class="btn btn-sm btn-warning btnEditTemplate" data-id="${r.id_template}"
                                    data-nama-kontrak="${escHtml(r.nama_kontrak || "")}"
                                    data-jenis="${escHtml(r.jenis_kontrak || "KARYAWAN BARU")}"
                                    data-file="${escHtml(r.nama_asli || r.file)}"
                                    data-jabatan-ids="${(r.jabatan || []).map(j => j.id_jabatan).join(",")}"
                                    title="Edit template"><i class="bx bx-edit me-1"></i>Edit</button>
                                <button type="button" class="btn btn-sm btn-danger btnDeleteTemplate" data-id="${r.id_template}"
                                    data-nama="${escHtml(r.nama_kontrak || r.nama_asli || r.file)}" title="Hapus template">
                                    <i class="bx bx-trash me-1"></i>Hapus
                                </button>
                                </div>
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
            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Mengupload...';

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
                btn.innerHTML = '<i class="bx bx-save me-1"></i> Upload';
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
