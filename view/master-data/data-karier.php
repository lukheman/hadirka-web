<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <?php
        require_once __DIR__ . '/../../app/config/function.php';
        $breadcrumb_nama = '-';
        if (!empty($_GET['id'])) {
            $id_karyawan_bc = (int)decrypt($_GET['id']);
            if ($id_karyawan_bc > 0) {
                $stmt_bc = $koneksi->prepare("SELECT nama_lengkap FROM tb_karyawan WHERE id_karyawan = ?");
                $stmt_bc->execute([$id_karyawan_bc]);
                $row_bc = $stmt_bc->fetch();
                if ($row_bc) {
                    $breadcrumb_nama = $row_bc['nama_lengkap'];
                }
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
                                <th class="text-center">Gaji Pokok (Harian)</th>
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
                    <ul class="nav nav-tabs" id="tabTambahKarier" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-add-data-tab" data-bs-toggle="tab" data-bs-target="#tab-add-data" type="button" role="tab" aria-controls="tab-add-data" aria-selected="true">Data Karier</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tab-add-data" role="tabpanel" aria-labelledby="tab-add-data-tab">
                    <div class="row g-3">
                        <!-- Site -->
                        <div class="col-md-4">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <option value="">-- Pilih Site --</option>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Departemen -->
                        <div class="col-md-4">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control select-search" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-4">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control select-search" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch()) {
                                    $__kj = trim((string)($j['kode_jabatan'] ?? ''));
                                    echo "<option value='{$j['id_jabatan']}'>" . htmlspecialchars($j['nama_jabatan'] . ($__kj !== '' ? " ($__kj)" : '')) . "</option>";
                                }
                                ?>
                            </select>
                        </div>



                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Harian</b>
                            <hr>
                        </div>

                        <!-- Gaji Pokok (harian: gaji = hadir x gaji pokok) -->
                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="text" name="gaji_pokok" class="form-control nominal-mask" required>
                        </div>

                        <!-- Tgl Mulai -->
                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Tgl Selesai -->
                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <small class="text-muted me-1">Durasi:</small>
                                <button type="button" class="btn btn-primary btn-sm btnDurasiKontrak" data-bulan="3">3 Bln</button>
                                <button type="button" class="btn btn-success btn-sm btnDurasiKontrak" data-bulan="6">6 Bln</button>
                                <button type="button" class="btn btn-warning btn-sm btnDurasiKontrak" data-bulan="12">1 Thn</button>
                            </div>
                        </div>

                        <!-- Template Kontrak (pilih bila jabatan punya lebih dari satu) -->
                        <div class="col-md-12">
                            <label>Template Kontrak <small class="text-muted">(ganti template menimpa nilai di bawah)</small></label>
                            <select name="id_template" class="form-control">
                                <option value="">-- Pilih jabatan terlebih dahulu --</option>
                            </select>
                        </div>

                        <!-- Nomor urut manual (opsional, hanya 4 digit) -->
                        <div class="col-md-12">
                            <label>Nomor Urut <small class="text-muted">(opsional — 4 digit, kosongkan untuk otomatis)</small></label>
                            <input type="text" name="no_urut" class="form-control" maxlength="4" inputmode="numeric" placeholder="cth: 0007">
                        </div>

                        <!-- No Surat -->
                        <div class="col-md-12">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="fa fa-file-contract"></i> Preview No Surat:
                                <strong id="addNoSuratPreview">&mdash;</strong>
                                <br><small class="text-muted">Kosongkan Nomor Urut untuk nomor otomatis.</small>
                            </div>
                            <div id="addNoSuratWarning"></div>
                        </div>
                    </div>
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
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Edit Karier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs" id="tabEditKarier" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-edit-data-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-data" type="button" role="tab" aria-controls="tab-edit-data" aria-selected="true">Data Karier</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tab-edit-data" role="tabpanel" aria-labelledby="tab-edit-data-tab">
                            <form id="formEditKarier">
                                <input type="hidden" name="id_karier">
                                <div class="row g-3">

                        <!-- Site -->
                        <div class="col-md-4">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Departemen -->
                        <div class="col-md-4">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control select-search" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-4">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control select-search" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch()) {
                                    $__kj = trim((string)($j['kode_jabatan'] ?? ''));
                                    echo "<option value='{$j['id_jabatan']}'>" . htmlspecialchars($j['nama_jabatan'] . ($__kj !== '' ? " ($__kj)" : '')) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Harian</b>
                            <hr>
                        </div>

                        <!-- Gaji Basic (harian: gaji = hadir x gaji pokok) -->
                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="text" name="gaji_pokok" class="form-control nominal-mask" required>
                        </div>

                        <!-- Tanggal -->
                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <small class="text-muted me-1">Durasi:</small>
                                <button type="button" class="btn btn-primary btn-sm btnDurasiKontrak" data-bulan="3">3 Bln</button>
                                <button type="button" class="btn btn-success btn-sm btnDurasiKontrak" data-bulan="6">6 Bln</button>
                                <button type="button" class="btn btn-warning btn-sm btnDurasiKontrak" data-bulan="12">1 Thn</button>
                            </div>
                        </div>

                        <!-- No Surat manual -->
                        <div class="col-md-12">
                            <label>No Surat</label>
                            <input type="text" name="no_surat" class="form-control" maxlength="100" placeholder="Kosongkan bila tanpa nomor">
                        </div>

                    </div>
                            <div class="mt-2 text-end">
                                <button class="btn btn-primary"><i class="fa fa-save"></i> Edit</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

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

        // --- SELECT2 BISA DICARI UNTUK DEPARTEMEN & JABATAN (dalam modal) ---
        // Select2 di dalam modal Bootstrap WAJIB memakai dropdownParent.
        // Tanpa itu dropdown menempel ke <body> dan tertutup backdrop modal
        // sehingga terlihat seperti "tidak ada data".
        // Init global di index.php sengaja melewati select dalam modal, jadi
        // inisialisasi di sini langsung saat DOM siap (tanpa menunggu window
        // load) + dipastikan ulang setiap modal dibuka.
        function initKarierSelect2() {
            ["#modalTambahKarier", "#modalEditKarier"].forEach(function(modal) {
                $(modal + " .select-search").each(function() {
                    if ($(this).hasClass("select2-hidden-accessible")) $(this).select2("destroy");
                    $(this).select2({
                        dropdownParent: $(modal),
                        width: "100%",
                        placeholder: "Ketik untuk mencari...",
                        allowClear: true
                    });
                });
            });
        }
        initKarierSelect2();
        ["modalTambahKarier", "modalEditKarier"].forEach(function(id) {
            var modalEl = document.getElementById(id);
            if (modalEl) {
                modalEl.addEventListener("shown.bs.modal", function() {
                    // Pengaman: init select yang belum terpasang Select2.
                    $(this).find(".select-search").each(function() {
                        if (!$(this).hasClass("select2-hidden-accessible")) {
                            $(this).select2({
                                dropdownParent: $(this).closest(".modal"),
                                width: "100%",
                                placeholder: "Ketik untuk mencari...",
                                allowClear: true
                            });
                        }
                    });
                });
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
            const manualNo = composeManualNoSurat(form, info);
            const tpl = chosenTemplate(form, info);
            const fmtRpPrev = v => "Rp " + Number(v || 0).toLocaleString("id-ID");
            const tplCell = tpl
                ? `<a href="app/uploads/template-kontrak/${encodeURIComponent(tpl.file)}" target="_blank">${escPreview(tpl.nama_kontrak || tpl.nama_asli || tpl.file)}</a>`
                : `<span class="badge bg-warning text-dark">Belum ada template untuk jabatan ini</span>`;
            const noCell = manualNo
                ? `<strong>${escPreview(manualNo)}</strong><br><small class="text-muted">Nomor urut manual</small>`
                : (info.no_surat_preview
                    ? `<strong>${escPreview(info.no_surat_preview)}</strong><br><small class="text-muted">Estimasi &mdash; nomor final terbit saat simpan</small>`
                    : `<span class="badge bg-danger">Kode jabatan belum diisi</span>`);
            const rows = [
                ["Karyawan", escPreview(karyawanNama)],
                ["Site", escPreview(selText(form, "id_site"))],
                ["Departemen", escPreview(selText(form, "id_departemen"))],
                ["Jabatan", escPreview(selText(form, "id_jabatan")) + (info.kode_jabatan ? ` <small class="text-muted">(${escPreview(info.kode_jabatan)})</small>` : "")],
                ["Gaji Pokok (Harian)", rp(fd.get("gaji_pokok"))],
                ["Tgl Mulai", escPreview(fd.get("tgl_mulai"))],
                ["Tgl Selesai", escPreview(fd.get("tgl_selesai"))],
                ["No Surat", noCell],
                ["Template Kontrak", tplCell]
            ];
            document.getElementById("tbodyPreviewKarier").innerHTML = rows.map(r =>
                `<tr><th style="width:30%">${r[0]}</th><td>${r[1]}</td></tr>`).join("");
            const ok = !!(manualNo || info.no_surat_preview);
            document.getElementById("btnKonfirmasiKarier").disabled = !ok;
            document.getElementById("previewKarierWarning").innerHTML = ok ? "" :
                `<div class="alert alert-danger py-2 mb-0">Simpan diblokir: isi Nomor Urut manual atau lengkapi kode jabatan terlebih dahulu.</div>`;
            new bootstrap.Modal(document.getElementById("modalPreviewKarier")).show();
        }

        // --- PREVIEW NO SURAT LIVE DI MODAL TAMBAH ---
        // --- NOMOR URUT MANUAL (hanya 4 digit, input di atas preview) ---
        // Bila diisi, nomor lengkap disusun sebagai
        // TPJS-KONTRAK-{kode_jabatan}/{urut}/{tahun} memakai kode dari server.
        function manualUrutVal(form) {
            const el = form.querySelector("input[name=no_urut]");
            return el ? el.value.replace(/\D/g, "").slice(0, 4) : "";
        }
        function composeManualNoSurat(form, info) {
            const urut = manualUrutVal(form);
            if (!urut || !info || !info.kode_jabatan) return "";
            const tgl = form.querySelector("input[name=tgl_mulai]").value || "";
            const tahun = /^\d{4}/.test(tgl) ? tgl.slice(0, 4) : String(new Date().getFullYear());
            return `TPJS-KONTRAK-${info.kode_jabatan}/${urut.padStart(4, "0")}/${tahun}`;
        }

        // --- DURASI KONTRAK CEPAT: isi Tgl Selesai dari Tgl Mulai + N bulan ---
        // Konvensi akhir periode: tanggal mulai + N bulan, mundur 1 hari
        // (cth: mulai 2026-09-01 + 6 bln = selesai 2027-02-28).
        function setDurasiKontrak(form, bulan) {
            const mulaiEl = form.querySelector("input[name=tgl_mulai]");
            const selesaiEl = form.querySelector("input[name=tgl_selesai]");
            if (!mulaiEl || !selesaiEl) return;
            if (!mulaiEl.value) {
                showToast("Isi Tgl Mulai terlebih dahulu.", "warning");
                mulaiEl.focus();
                return;
            }
            const d = new Date(mulaiEl.value + "T00:00:00");
            if (isNaN(d.getTime())) { showToast("Tgl Mulai tidak valid.", "error"); return; }
            const tanggal = d.getDate();
            d.setDate(1);
            d.setMonth(d.getMonth() + bulan);
            const maks = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
            d.setDate(Math.min(tanggal - 1, maks));
            const pad = n => String(n).padStart(2, "0");
            selesaiEl.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            selesaiEl.dispatchEvent(new Event("change", { bubbles: true }));
        }
        document.addEventListener("click", e => {
            const btn = e.target.closest(".btnDurasiKontrak");
            if (!btn) return;
            const bulan = parseInt(btn.dataset.bulan, 10) || 0;
            const form = btn.closest("form");
            if (form && bulan > 0) setDurasiKontrak(form, bulan);
        });

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
                    const manualFull = composeManualNoSurat(form, res);
                    if (manualFull) {
                        prev.textContent = manualFull;
                        if (warn) warn.innerHTML = `<div class="alert alert-secondary py-2 mt-2 mb-0">Nomor urut manual akan dipakai — counter otomatis tidak berkurang.</div>`;
                    } else if (res.no_surat_preview) {
                        prev.textContent = res.no_surat_preview;
                        if (warn) warn.innerHTML = "";
                    } else {
                        prev.textContent = "—";
                        if (warn) warn.innerHTML = `<div class="alert alert-warning py-2 mt-2 mb-0">Jabatan belum punya kode &mdash; lengkapi kode jabatan agar nomor bisa diterbitkan.</div>`;
                    }
                })
                .catch(() => { if (my === addNoSuratSeq) prev.textContent = "—"; });
        }

        // --- TEMPLATE KONTRAK PILIHAN SAAT JABATAN DIPILIH ---
        // Bila satu jabatan punya beberapa template, user memilih yang dipakai;
        // nilai kontrak mengisi field yang masih kosong agar input manual tidak tertimpa.
        let karierTemplates = [];
        function templateLabel(t) {
            return t.nama_kontrak || t.nama_asli || t.file || ("Template #" + t.id_template);
        }
        function renderTemplateOptions(form, keepSelected) {
            const sel = form.querySelector("select[name=id_template]");
            if (!sel) return;
            const prev = keepSelected ? sel.value : "";
            if (!karierTemplates.length) {
                sel.innerHTML = `<option value="">Belum ada template untuk jabatan ini</option>`;
                return;
            }
            sel.innerHTML = karierTemplates.map(t =>
                `<option value="${t.id_template}">${escPreview(templateLabel(t))}</option>`
            ).join("");
            const stillThere = prev && karierTemplates.some(t => String(t.id_template) === String(prev));
            sel.value = stillThere ? prev : String(karierTemplates[0].id_template);
        }
        function isiNilaiDariTemplate(form, force) {
            const sel = form.querySelector("select[name=id_template]");
            const t = karierTemplates.find(x => sel && String(x.id_template) === String(sel.value)) || karierTemplates[0];
            if (!t || !t.detail || typeof formatIndo !== "function") return;
            const map = {
                gaji_pokok: "gaji_pokok"
            };
            Object.keys(map).forEach(name => {
                const el = form.querySelector(`input[name=${name}]`);
                if (!el) return;
                // Isi bila kolom masih kosong, kecuali ganti template eksplisit (force) yang menimpa nilai.
                const angka = String(el.value || "").replace(/[^0-9]/g, "").replace(/^0+/, "");
                if (angka === "" || force) el.value = formatIndo(t.detail[map[name]] ?? 0);
            });
        }
        function chosenTemplate(form, info) {
            if (info && Array.isArray(info.templates)) karierTemplates = info.templates;
            const sel = form.querySelector("select[name=id_template]");
            return karierTemplates.find(x => sel && String(x.id_template) === String(sel.value)) ||
                karierTemplates[0] || (info && info.template) || null;
        }

        // --- TEMPLATE KONTRAK: nilai gaji pokok mengisi otomatis dari template ---
        function escAttr(s) {
            return String(s ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }
        // --- AUTO-ISI NILAI DARI DETAIL KONTRAK SAAT JABATAN DIPILIH ---
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
                    if (res.status !== "success") return;
                    karierTemplates = Array.isArray(res.templates) ? res.templates : [];
                    renderTemplateOptions(form, false);
                    isiNilaiDariTemplate(form);
                })
                .catch(() => {});
        }

        (function bindAddNoSurat() {
            const form = document.getElementById("formTambahKarier");
            if (!form || form.dataset.nosuratBound) return;
            form.dataset.nosuratBound = "1";
            // Pakai jQuery .on agar change dari Select2 ikut tertangkap.
            $(form).find("select[name=id_jabatan]").on("change", () => {
                refreshAddNoSurat();
                autoIsiNilaiKontrak();
            });
            $(form).find("select[name=id_template]").on("change", () => {
                isiNilaiDariTemplate(form, true);
            });
            $(form).find("input[name=tgl_mulai]").on("change", refreshAddNoSurat);
            $(form).find("input[name=no_urut]").on("input", refreshAddNoSurat);
            document.getElementById("modalTambahKarier").addEventListener("show.bs.modal", () => {
                refreshAddNoSurat();
            });
        })();

        document.getElementById("formTambahKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            const form = this;
            if (!form.checkValidity()) { form.reportValidity(); return; }
            const fd = new FormData(form);
            // Select2 menyembunyikan select asli sehingga validasi required bawaan terlewat — cek manual.
            for (const [k, label] of [["id_site", "Site"], ["id_departemen", "Departemen"], ["id_jabatan", "Jabatan"]]) {
                if (!fd.get(k)) { showToast("Pilih " + label + " terlebih dahulu.", "warning"); return; }
            }

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
                        karierTemplates = [];
                        renderTemplateOptions(document.getElementById("formTambahKarier"), false);
                        $("#formTambahKarier select.select-search").val("").trigger("change");
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
            // Sinkronkan tampilan Select2
            $(form.querySelector("select[name=id_departemen]")).trigger("change");
            $(form.querySelector("select[name=id_jabatan]")).trigger("change");

            // KOREKSI: Gunakan fungsi formatIndo di data-karier.php
            form.querySelector("input[name=gaji_pokok]").value = formatIndo(btn.dataset.gaji_pokok);

            form.querySelector("input[name=tgl_mulai]").value = btn.dataset.tgl_mulai;
            form.querySelector("input[name=tgl_selesai]").value = btn.dataset.tgl_selesai;
            form.querySelector("input[name=no_surat]").value = btn.dataset.no_surat || "";

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


        // --- UPLOAD FILE KONTRAK (tombol di kolom aksi) ---
        document.addEventListener("click", e => {
            const btn = e.target.closest(".btnUploadKontrak");
            if (!btn) return;
            window.uploadKontrakKarier(btn, loadKarier);
        });

        // --- Load awal ---
        window.reloadKarierTable = loadKarier;
        loadKarier();

    });
</script>