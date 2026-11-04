<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Master Data /</span> Arsip Kontrak
        </h4>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div class="text-muted small">
                <i class="fa fa-file-pdf"></i> Daftar karyawan &amp; arsip dokumen kontrak (PDF).
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="filterSite" class="form-select form-select-sm">
                    <option value="">-- Semua Site --</option>
                    <?php
                    $sites = $koneksi->query("SELECT id_site, nama_site FROM tb_site ORDER BY nama_site ASC");
                    while ($s = $sites->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$s['id_site']}'>" . htmlspecialchars($s['nama_site']) . "</option>";
                    }
                    ?>
                </select>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="Aktif" selected>Karyawan Aktif</option>
                    <option value="Habis Kontrak">Habis Kontrak</option>
                    <option value="Pensiun">Pensiun</option>
                    <option value="PHK">PHK</option>
                    <option value="Resign">Resign</option>
                    <option value="Black List">Black List</option>
                </select>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Arsip Kontrak Karyawan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableArsip">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">NIK</th>
                                <th class="text-center">Nama</th>
                                <th class="text-center">Site</th>
                                <th class="text-center">Jabatan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Arsip</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyArsip"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Upload -->
    <div class="modal fade" id="modalUploadArsip" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formUploadArsip" class="modal-content" enctype="multipart/form-data">
                <input type="hidden" name="id_karyawan" id="uploadArsipKaryawanId">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">
                        <i class="fa fa-upload"></i> Upload Kontrak - <span id="labelArsipNama">-</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="uploadArsipFile">Pilih File (PDF, maks 10MB)</label>
                        <input type="file" name="file_kontrak" id="uploadArsipFile" class="form-control" accept=".pdf,application/pdf" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="uploadArsipKet">Keterangan <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="keterangan" id="uploadArsipKet" class="form-control" maxlength="255" placeholder="cth: Kontrak 2026-2027">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Riwayat -->
    <div class="modal fade" id="modalRiwayatArsip" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white">
                        <i class="fa fa-list"></i> Riwayat Arsip - <span id="labelRiwayatNama">-</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-info">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama File</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Ukuran</th>
                                    <th class="text-center">Tgl Upload</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyRiwayatArsip"></tbody>
                        </table>
                    </div>
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
        const tbody = document.getElementById("tbodyArsip");

        function loadArsip() {
            const site = document.getElementById("filterSite").value;
            const status = document.getElementById("filterStatus").value;
            const url = `app/controller/get-data/get-arsip-kontrak.php?site=${encodeURIComponent(site)}&status=${encodeURIComponent(status)}`;
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableArsip")) {
                        $(".MyTableArsip").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $(".MyTableArsip").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                })
                .catch(() => showToast("Gagal memuat data arsip.", "error"));
        }

        document.getElementById("filterSite").addEventListener("change", loadArsip);
        document.getElementById("filterStatus").addEventListener("change", loadArsip);

        function formatSize(bytes) {
            bytes = Number(bytes || 0);
            if (bytes <= 0) return "-";
            if (bytes < 1024) return bytes + " B";
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
            return (bytes / (1024 * 1024)).toFixed(2) + " MB";
        }

        function escHtml(s) {
            return String(s ?? "-").replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        // Buka modal upload
        document.addEventListener("click", e => {
            const btnU = e.target.closest(".btnUploadArsip");
            if (btnU) {
                document.getElementById("uploadArsipKaryawanId").value = btnU.dataset.id || "";
                document.getElementById("labelArsipNama").textContent = btnU.dataset.nama || "-";
                document.getElementById("formUploadArsip").reset();
                document.getElementById("uploadArsipKaryawanId").value = btnU.dataset.id || "";
                new bootstrap.Modal(document.getElementById("modalUploadArsip")).show();
                return;
            }
            const btnR = e.target.closest(".btnRiwayatArsip");
            if (btnR) {
                openRiwayat(btnR.dataset.id, btnR.dataset.nama);
                return;
            }
        });

        // Submit upload (PDF saja)
        document.getElementById("formUploadArsip").addEventListener("submit", function(e) {
            e.preventDefault();
            const fileInput = document.getElementById("uploadArsipFile");
            if (!fileInput.value) {
                showToast("Pilih file PDF terlebih dahulu.", "warning");
                return;
            }
            const f = fileInput.files[0];
            if (f && !/\.pdf$/i.test(f.name)) {
                showToast("Format file harus PDF.", "error");
                return;
            }
            if (f && f.size > 10 * 1024 * 1024) {
                showToast("Ukuran file maksimal 10MB.", "error");
                return;
            }
            const btn = this.querySelector("button[type=submit]");
            if (btn) btn.disabled = true;
            const fd = new FormData(this);
            fd.append("action", "upload");
            fetch("app/controller/act-arsip-kontrak.php", { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Dokumen kontrak berhasil diunggah!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalUploadArsip")).hide();
                        loadArsip();
                    } else {
                        showToast(res.message || "Upload gagal.", "error");
                    }
                })
                .catch(() => showToast("Terjadi kesalahan sistem.", "error"))
                .finally(() => { if (btn) btn.disabled = false; });
        });

        // Riwayat arsip per karyawan
        function openRiwayat(idKaryawan, nama) {
            document.getElementById("labelRiwayatNama").textContent = nama || "-";
            const tb = document.getElementById("tbodyRiwayatArsip");
            tb.innerHTML = `<tr><td colspan="6" class="text-center">Memuat...</td></tr>`;
            new bootstrap.Modal(document.getElementById("modalRiwayatArsip")).show();
            fetch(`app/controller/get-data/get-arsip-kontrak.php?action=list&id_karyawan=${encodeURIComponent(idKaryawan)}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") {
                        tb.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${escHtml(res.message || "Gagal memuat riwayat.")}</td></tr>`;
                        return;
                    }
                    if (!res.data.length) {
                        tb.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Belum ada arsip untuk karyawan ini.</td></tr>`;
                        return;
                    }
                    tb.innerHTML = res.data.map((d, i) => `
                        <tr>
                            <td class="text-center">${i + 1}</td>
                            <td><a href="app/uploads/arsip-kontrak/${encodeURIComponent(d.file)}" target="_blank">${escHtml(d.nama_asli || d.file)}</a></td>
                            <td>${escHtml(d.keterangan || "-")}</td>
                            <td class="text-center">${formatSize(d.ukuran)}</td>
                            <td class="text-center">${escHtml(d.created_at || "-")}</td>
                            <td class="text-center text-nowrap">
                                <a href="app/uploads/arsip-kontrak/${encodeURIComponent(d.file)}" target="_blank" class="btn btn-sm btn-success" title="Lihat PDF"><i class="fa fa-eye"></i></a>
                                <button type="button" class="btn btn-sm btn-danger btnHapusArsip" data-id="${d.id_arsip}" data-karyawan="${encodeURIComponent(idKaryawan)}" title="Hapus arsip"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>`).join("");
                })
                .catch(() => { tb.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Gagal memuat riwayat.</td></tr>`; });
        }


        // Hapus satu arsip (delegasi, karena baris riwayat dirender dinamis)
        document.addEventListener("click", async e => {
            const btn = e.target.closest(".btnHapusArsip");
            if (!btn) return;
            if (!(await confirmDialog("Yakin hapus arsip ini? File PDF ikut terhapus."))) return;
            const fd = new FormData();
            fd.append("action", "delete");
            fd.append("id_arsip", btn.dataset.id);
            fetch("app/controller/act-arsip-kontrak.php", { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Arsip berhasil dihapus!", "success");
                        loadArsip();
                        const nama = document.getElementById("labelRiwayatNama").textContent;
                        openRiwayat(decodeURIComponent(btn.dataset.karyawan), nama);
                    } else {
                        showToast(res.message || "Hapus gagal.", "error");
                    }
                })
                .catch(() => showToast("Terjadi kesalahan sistem.", "error"));
        });

        window.reloadArsipTable = loadArsip;
        loadArsip();
    });
</script>
