<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <?php
        require_once __DIR__ . '/../../app/config/function.php';
        $breadcrumb_nama = '-';
        if (!empty($_GET['id'])) {
            $id_karyawan_bc = (int)decrypt($_GET['id']);
            if ($id_karyawan_bc > 0) {
                $stmt_bc = $koneksi->prepare("SELECT nama_lengkap FROM tb_karyawan WHERE id_karyawan = ?");
                $stmt_bc->bind_param("i", $id_karyawan_bc);
                $stmt_bc->execute();
                $row_bc = $stmt_bc->get_result()->fetch_assoc();
                if ($row_bc) {
                    $breadcrumb_nama = $row_bc['nama_lengkap'];
                }
                $stmt_bc->close();
            }
        }
        ?>
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Data Karyawan / Karier /</span> <?= htmlspecialchars($breadcrumb_nama) ?>
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKarier">
                <i class="fa fa-plus"></i> Tambah Karier
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Karier</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableKarier">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Site</th>
                                <th class="text-center">Departemen</th>
                                <th class="text-center">Jabatan</th>
                                <th class="text-center">Gaji Pokok</th>
                                <th class="text-center">Upah Lembur</th>
                                <th class="text-center">Pot Izin</th>
                                <th class="text-center">Pot Mangkir</th>
                                <th class="text-center">Tgl Mulai</th>
                                <th class="text-center">Tgl Selesai</th>
                                <th class="text-center">No Surat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKarier"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahKarier">
        <div class="modal-dialog modal-xl"> <!-- modal lebih lebar -->
            <form id="formTambahKarier" class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Tambah Karier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Site -->
                        <div class="col-md-4">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <option value="">-- Pilih Site --</option>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch_assoc()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Departemen -->
                        <div class="col-md-4">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-4">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>



                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>

                        <!-- Gaji Pokok -->
                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="text" name="gaji_pokok" class="form-control nominal-mask" required>
                        </div>

                        <!-- Lembur -->
                        <div class="col-md-3">
                            <label>Upah Lembur</label>
                            <input type="text" name="lembur" class="form-control nominal-mask" required>
                        </div>

                        <!-- Pot Izin -->
                        <div class="col-md-3">
                            <label>Potongan Izin</label>
                            <input type="text" name="pot_izin" class="form-control nominal-mask">
                        </div>

                        <!-- Pot Mangkir -->
                        <div class="col-md-3">
                            <label>Potongan Mangkir</label>
                            <input type="text" name="pot_mangkir" class="form-control nominal-mask">
                        </div>

                        <!-- Tgl Mulai -->
                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>

                        <!-- Tgl Selesai -->
                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>

                        <!-- No Surat -->
                        <div class="col-md-12">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="fa fa-file-contract"></i> Preview No Surat:
                                <strong id="addNoSuratPreview">&mdash;</strong>
                                <br><small class="text-muted">Nomor final diterbitkan sistem saat simpan.</small>
                            </div>
                            <div id="addNoSuratWarning"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditKarier">
        <div class="modal-dialog modal-xl"> <!-- ukuran sama seperti modal tambah -->
            <form id="formEditKarier" class="modal-content">
                <input type="hidden" name="id_karier">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Edit Karier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Site -->
                        <div class="col-md-4">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch_assoc()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Departemen -->
                        <div class="col-md-4">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control" required>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-4">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>

                        <!-- Gaji Basic -->
                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="text" name="gaji_pokok" class="form-control nominal-mask" required>
                        </div>

                        <!-- Lembur -->
                        <div class="col-md-3">
                            <label>Upah Lembur</label>
                            <input type="text" name="lembur" class="form-control nominal-mask" required>
                        </div>

                        <!-- Potongan -->
                        <div class="col-md-3">
                            <label>Potongan Izin</label>
                            <input type="text" name="pot_izin" class="form-control nominal-mask">
                        </div>

                        <div class="col-md-3">
                            <label>Potongan Mangkir</label>
                            <input type="text" name="pot_mangkir" class="form-control nominal-mask">
                        </div>

                        <!-- Tanggal -->
                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/modal-karier-tunjangan-potongan.php'; ?>

    <!-- Modal Preview Surat Kontrak (sebelum simpan) -->
    <div class="modal fade" id="modalPreviewKarier">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-file-contract"></i> Preview Surat Kontrak</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2">
                        <i class="fa fa-info-circle"></i> Periksa kembali data kontrak sebelum disimpan.
                        Nomor surat final diterbitkan sistem saat simpan.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tbody id="tbodyPreviewKarier"></tbody>
                        </table>
                    </div>
                    <div id="previewKarierWarning"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
                    <button type="button" class="btn btn-success" id="btnKonfirmasiKarier">
                        <i class="fa fa-save"></i> Simpan Kontrak
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const karyawanId = "<?= $_GET['id'] ?>";
        const karyawanNama = <?= json_encode($breadcrumb_nama ?? '-') ?>;
        const tbody = document.getElementById("tbodyKarier");

        // --- HELPER FORMAT DESIMAL INDONESIA ---
        function formatIndo(val) {
            if (val === undefined || val === null || val === '') return "";
            let parts = val.toString().split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            return parts.join(",");
        }

        // --- LISTENER MASKING NOMINAL OTOMATIS ---
        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                let input = e.target;
                let cursorPos = input.selectionStart;
                let prevLength = input.value.length;

                let val = input.value.replace(/[^0-9,]/g, "");
                let parts = val.split(",");
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                if (parts.length > 2) {
                    input.value = parts[0] + "," + parts[1];
                } else {
                    input.value = parts.join(",");
                }

                let diff = input.value.length - prevLength;
                input.setSelectionRange(cursorPos + diff, cursorPos + diff);
            }
        });

        // --- LOAD DATA ---
        function loadKarier() {
            fetch("app/controller/get-data/get-karier.php?id=" + encodeURIComponent(karyawanId))
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableKarier")) {
                        $(".MyTableKarier").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $(".MyTableKarier").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                });
        }

        // --- TAMBAH DENGAN PREVIEW SURAT KONTRAK ---
        function escPreview(s) {
            return String(s == null || s === "" ? "-" : s).replace(/&/g, "&amp;")
                .replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        function selText(form, name) {
            const sel = form.querySelector("select[name=" + name + "]");
            return sel && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex].text : "-";
        }

        function renderPreviewKarier(form, info) {
            const fd = new FormData(form);
            const rp = v => (v == null || v === "" || v === "0") ? "-" : "Rp " + v;
            const tplCell = info.template
                ? `<a href="app/uploads/kontrak/${encodeURIComponent(info.template.file)}" target="_blank">${escPreview(info.template.nama_asli || info.template.file)}</a>`
                : `<span class="badge bg-warning text-dark">Belum ada template untuk jabatan ini</span>`;
            const noCell = info.no_surat_preview
                ? `<strong>${escPreview(info.no_surat_preview)}</strong><br><small class="text-muted">Estimasi &mdash; nomor final terbit saat simpan</small>`
                : `<span class="badge bg-danger">Kode jabatan belum diisi</span>`;
            const rows = [
                ["Karyawan", escPreview(karyawanNama)],
                ["Site", escPreview(selText(form, "id_site"))],
                ["Departemen", escPreview(selText(form, "id_departemen"))],
                ["Jabatan", escPreview(selText(form, "id_jabatan")) + (info.kode_jabatan ? ` <small class="text-muted">(${escPreview(info.kode_jabatan)})</small>` : "")],
                ["Gaji Pokok", rp(fd.get("gaji_pokok"))],
                ["Upah Lembur", rp(fd.get("lembur"))],
                ["Potongan Izin", rp(fd.get("pot_izin"))],
                ["Potongan Mangkir", rp(fd.get("pot_mangkir"))],
                ["Tgl Mulai", escPreview(fd.get("tgl_mulai"))],
                ["Tgl Selesai", escPreview(fd.get("tgl_selesai"))],
                ["No Surat", noCell],
                ["Template Kontrak", tplCell]
            ];
            document.getElementById("tbodyPreviewKarier").innerHTML = rows.map(r =>
                `<tr><th style="width:30%">${r[0]}</th><td>${r[1]}</td></tr>`).join("");
            const ok = !!info.no_surat_preview;
            document.getElementById("btnKonfirmasiKarier").disabled = !ok;
            document.getElementById("previewKarierWarning").innerHTML = ok ? "" :
                `<div class="alert alert-danger py-2 mb-0">Simpan diblokir: lengkapi kode jabatan terlebih dahulu.</div>`;
            new bootstrap.Modal(document.getElementById("modalPreviewKarier")).show();
        }

        // --- PREVIEW NO SURAT LIVE DI MODAL TAMBAH ---
        let addNoSuratSeq = 0;
        function refreshAddNoSurat() {
            const form = document.getElementById("formTambahKarier");
            const prev = document.getElementById("addNoSuratPreview");
            const warn = document.getElementById("addNoSuratWarning");
            if (!form || !prev) return;
            const idJabatan = form.querySelector("select[name=id_jabatan]").value;
            const tglMulai = form.querySelector("input[name=tgl_mulai]").value;
            if (!idJabatan) {
                prev.textContent = "—";
                if (warn) warn.innerHTML = "";
                return;
            }
            const my = ++addNoSuratSeq;
            prev.textContent = "Memuat...";
            const pf = new FormData();
            pf.append("action", "preview_info");
            pf.append("id_jabatan", idJabatan);
            pf.append("tgl_mulai", tglMulai || "");
            fetch("app/controller/act-template-kontrak.php", { method: "POST", body: pf })
                .then(res => res.json())
                .then(res => {
                    if (my !== addNoSuratSeq) return;
                    if (res.status !== "success") { prev.textContent = "—"; return; }
                    if (res.no_surat_preview) {
                        prev.textContent = res.no_surat_preview;
                        if (warn) warn.innerHTML = "";
                    } else {
                        prev.textContent = "—";
                        if (warn) warn.innerHTML = `<div class="alert alert-warning py-2 mt-2 mb-0">Jabatan belum punya kode &mdash; lengkapi kode jabatan agar nomor bisa diterbitkan.</div>`;
                    }
                })
                .catch(() => { if (my === addNoSuratSeq) prev.textContent = "—"; });
        }

        // --- AUTO-ISI NILAI DARI DETAIL KONTRAK SAAT JABATAN DIPILIH ---
        // Hanya mengisi field yang masih kosong/nol agar input manual tidak tertimpa.
        function autoIsiNilaiKontrak() {
            const form = document.getElementById("formTambahKarier");
            if (!form || typeof formatIndo !== "function") return;
            const idJabatan = form.querySelector("select[name=id_jabatan]").value;
            if (!idJabatan) return;
            const pf = new FormData();
            pf.append("action", "preview_info");
            pf.append("id_jabatan", idJabatan);
            fetch("app/controller/act-template-kontrak.php", { method: "POST", body: pf })
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success" || !res.detail) return;
                    const map = {
                        gaji_pokok: "gaji_pokok",
                        lembur: "upah_lembur",
                        pot_izin: "potongan_izin",
                        pot_mangkir: "potongan_mangkir"
                    };
                    Object.keys(map).forEach(name => {
                        const el = form.querySelector(`input[name=${name}]`);
                        if (!el) return;
                        const angka = String(el.value || "").replace(/[^0-9]/g, "").replace(/^0+/, "");
                        if (angka === "") el.value = formatIndo(res.detail[map[name]] ?? 0);
                    });
                })
                .catch(() => {});
        }

        (function bindAddNoSurat() {
            const form = document.getElementById("formTambahKarier");
            if (!form || form.dataset.nosuratBound) return;
            form.dataset.nosuratBound = "1";
            form.querySelector("select[name=id_jabatan]").addEventListener("change", () => {
                refreshAddNoSurat();
                autoIsiNilaiKontrak();
            });
            form.querySelector("input[name=tgl_mulai]").addEventListener("change", refreshAddNoSurat);
            document.getElementById("modalTambahKarier").addEventListener("show.bs.modal", refreshAddNoSurat);
        })();

        document.getElementById("formTambahKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            const form = this;
            if (!form.checkValidity()) { form.reportValidity(); return; }
            const fd = new FormData(form);

            const pf = new FormData();
            pf.append("action", "preview_info");
            pf.append("id_jabatan", fd.get("id_jabatan"));
            pf.append("tgl_mulai", fd.get("tgl_mulai") || "");

            fetch("app/controller/act-template-kontrak.php", {
                    method: "POST",
                    body: pf
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") {
                        showToast(res.msg || "Gagal memuat preview.", "error");
                        return;
                    }
                    renderPreviewKarier(form, res);
                })
                .catch(() => showToast("Terjadi kesalahan sistem.", "error"));
        });

        document.getElementById("btnKonfirmasiKarier").addEventListener("click", function() {
            const btn = this;
            btn.disabled = true;
            const fd = new FormData(document.getElementById("formTambahKarier"));
            fd.append("action", "add");
            fd.append("id_karyawan", karyawanId);

            fetch("app/controller/act-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadKarier();
                        showToast("Karier berhasil ditambah!" + (res.no_surat ? " No: " + res.no_surat : ""), "success");
                        // toastr.success("Berhasil menghapus!");
                        bootstrap.Modal.getInstance(document.getElementById("modalPreviewKarier")).hide();
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahKarier")).hide();
                        document.getElementById("formTambahKarier").reset();
                        document.getElementById("addNoSuratPreview").textContent = "—";
                        document.getElementById("addNoSuratWarning").innerHTML = "";
                    } else {
                        showToast(res.msg, "error");
                    }
                })
                .catch(() => showToast("Terjadi kesalahan sistem.", "error"))
                .finally(() => { btn.disabled = false; });
        });

        // --- EDIT ---
        document.getElementById("formEditKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "edit");
            fetch("app/controller/act-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        showToast("Karier berhasil diupdate!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditKarier")).hide();
                        loadKarier();
                        console.log("Response:", text); // lihat apa yang dikirim PHP
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // --- EDIT BUTTON HANDLER ---
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditKarier");
            if (!btn) return;

            const form = document.getElementById("formEditKarier");
            form.querySelector("input[name=id_karier]").value = btn.dataset.id;
            form.querySelector("select[name=id_site]").value = btn.dataset.id_site;
            form.querySelector("select[name=id_departemen]").value = btn.dataset.id_departemen;
            form.querySelector("select[name=id_jabatan]").value = btn.dataset.id_jabatan;

            // KOREKSI: Gunakan fungsi formatIndo di data-karier.php
            form.querySelector("input[name=gaji_pokok]").value = formatIndo(btn.dataset.gaji_pokok);
            form.querySelector("input[name=lembur]").value = formatIndo(btn.dataset.lembur);
            form.querySelector("input[name=pot_izin]").value = formatIndo(btn.dataset.pot_izin);
            form.querySelector("input[name=pot_mangkir]").value = formatIndo(btn.dataset.pot_mangkir);

            form.querySelector("input[name=tgl_mulai]").value = btn.dataset.tgl_mulai;
            form.querySelector("input[name=tgl_selesai]").value = btn.dataset.tgl_selesai;

            new bootstrap.Modal(document.getElementById("modalEditKarier")).show();
        });

        // --- DELETE (opsional) ---
        document.addEventListener("click", async e => {
            let btn = e.target.closest(".btnDeleteKarier");
            if (!btn) return;

            if (await confirmDialog("Yakin hapus karier?")) {
                fetch("app/controller/act-karier.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "action=delete&id_karier=" + btn.dataset.id
                    })
                    .then(res => res.json()) // <--- gunakan json, bukan text
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil menghapus!", "success");
                            loadKarier(); // reload table
                        } else {
                            showToast(res.msg, "error");
                        }
                    })
                    .catch(err => {
                        showToast("Terjadi kesalahan: " + err, "error");
                    });
            }
        });

        // --- SINGLE EXTEND CONTRACT (Untuk data-karier.php) ---
        document.addEventListener("click", async e => {
            let btn = e.target.closest(".btnExtendKarier");
            if (!btn) return;

            let nama = btn.dataset.nama;
            if (await confirmDialog(`Perpanjang kontrak ${nama} selama 6 bulan?\n(Hanya bisa jika status karyawan Aktif)`)) {
                let fd = new FormData();
                fd.append("action", "extend_single");
                fd.append("id_karier", btn.dataset.id);

                fetch("app/controller/act-karier.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast(res.msg, "success");
                            loadKarier();
                        } else {
                            showToast(res.msg, "error");
                        }
                    });
            }
        });


        // --- Load awal ---
        window.reloadKarierTable = loadKarier;
        loadKarier();

    });
</script>