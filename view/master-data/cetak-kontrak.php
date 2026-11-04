<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Manajemen /</span> Cetak Kontrak Blanko
        </h4>

        <div class="card mt-3">
            <h5 class="card-header">Cetak File Kontrak (Blanko)</h5>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Untuk karyawan baru yang datanya belum diinput.
                    Tambahkan <strong>baris per jabatan</strong> → pilih <strong>jenis kontrak</strong> (KARYAWAN BARU / PERPANJANGAN) →
                    pilih <strong>jabatan</strong> →
                    pilih <strong>template</strong> yang tersedia → <strong>jumlah</strong> tiap baris.
                    Nilai kontrak, tunjangan, dan potongan sudah tercantum di dalam file template.
                    Nomor kontrak berurutan langsung <strong>dikonsumsi</strong> dari counter
                    (<code>tb_nomor_urut</code>) dan tidak bisa dibatalkan.
                    Nomor mulai terisi otomatis mengikuti nomor berikutnya, tetapi bisa diubah manual.
                    Isian personal (nama, NIK, alamat) berupa titik-titik.
                </div>

                <form id="formCetakKontrak" action="app/controller/act-cetak-kontrak.php" method="POST" target="_blank">
                    <input type="hidden" name="action" value="generate">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tahun Nomor</label>
                            <input type="number" name="tahun" id="cetakTahun" class="form-control" value="<?= date('Y') ?>" min="2000" max="2100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Mulai <small class="text-muted">(berikutnya, bisa diubah)</small></label>
                            <input type="number" name="nomor_mulai" id="cetakNomorMulai" class="form-control" min="1" max="999999" placeholder="—">
                            <small class="text-muted" id="cetakNomorHint">Terisi otomatis dari counter; kosongkan untuk otomatis.</small>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold mb-0">Daftar Jabatan <small class="text-muted">(maks 10 baris, total maks 100 file)</small></label>
                    </div>
                    <div id="repeaterJabatan"></div>
                    <select id="cetakJabatanSource" class="d-none" aria-hidden="true" tabindex="-1">
                        <option value="">-- Pilih Jabatan --</option>
                        <?php
                        $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                         while ($j = $jabatans->fetch(PDO::FETCH_ASSOC)) {
                            $__kj = trim((string)($j['kode_jabatan'] ?? ''));
                            echo "<option value='{$j['id_jabatan']}'>" . htmlspecialchars($j['nama_jabatan'] . ($__kj !== '' ? " ($__kj)" : '')) . "</option>";
                        }
                        ?>
                    </select>

                    <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                        <button type="button" class="btn btn-primary" id="btnAddBaris">
                            <i class="bx bx-plus me-1"></i> Tambah Jabatan
                        </button>
                        <button type="submit" class="btn btn-success" id="btnCetakKontrak">
                            <i class="bx bx-download me-1"></i> Generate &amp; Unduh ZIP
                        </button>
                        <button type="button" class="btn btn-outline-danger ms-auto" id="btnHapusSemuaBaris">
                            <i class="bx bx-trash me-1"></i> Hapus Semua
                        </button>
                    </div>
                </form>

                <hr>
                <h6 class="fw-bold">Posisi Nomor Kontrak</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm w-100">
                        <tbody id="tbodyNomorInfo">
                            <tr><td class="text-center text-muted">Memuat posisi nomor...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="cetakRangePreview"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const API = "app/controller/act-cetak-kontrak.php";
        const inTahun = document.getElementById("cetakTahun");
        const boxRepeater = document.getElementById("repeaterJabatan");
        const jabOptionsHTML = document.getElementById("cetakJabatanSource").innerHTML;
        let globalNext = null;

        function escHtml(s) {
            return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        function fmtRpPrev(v) {
            return "Rp " + Number(v || 0).toLocaleString("id-ID");
        }

        function isMakan(nama) {
            return ["tunjangan makan", "tunjangan uang makan"].includes(String(nama || "").toLowerCase().trim());
        }

        // Filter jenis kontrak per baris ('' = semua jenis).
        function rowJenis(row) {
            const el = row.querySelector(".cetak-jenis");
            return el ? el.value : "";
        }

        function jenisBadgeHtml(jenis) {
            const j = String(jenis || "").toUpperCase();
            if (j === "PERPANJANGAN") return ' <span class="badge bg-warning text-dark">PERPANJANGAN</span>';
            if (j === "KARYAWAN BARU") return ' <span class="badge bg-success">KARYAWAN BARU</span>';
            return "";
        }

        function templateLabel(t) {
            const nama = t.nama_kontrak || t.nama_asli || ("Template #" + t.id_template);
            const j = String(t.jenis_kontrak || "").toUpperCase();
            return (j === "KARYAWAN BARU" || j === "PERPANJANGAN") ? `${nama} (${j})` : nama;
        }

        // Preview nilai kontrak + tunjangan + jabatan langsung di baris (bentuk tabel).
        function renderRowPreview(row) {
            const box = row.querySelector(".row-preview");
            const jabSel = row.querySelector(".cetak-jabatan");
            const tplSel = row.querySelector(".cetak-template");
            const jabText = jabSel.selectedIndex >= 0 ? jabSel.options[jabSel.selectedIndex].text : "-";
            const tpls = row._templates || [];
            const t = tpls.find(x => String(x.id_template) === String(tplSel.value));
            if (!jabSel.value || !t) { box.innerHTML = ""; return; }
            const d = t.detail || {};
            const tdRp = v => `<td class="text-end">${fmtRpPrev(v)}</td>`;
            const th = tahunStr();
            let nomorCell = '<span class="text-muted">—</span>';
            if (row._mulai != null && row.dataset.kode) {
                const a = String(row._mulai).padStart(4, "0");
                const b = String(row._akhir).padStart(4, "0");
                nomorCell = `<strong>IPM-KONTRAK-${escHtml(row.dataset.kode)}/${a}/${escHtml(th)}</strong>` +
                    (row._akhir > row._mulai
                        ? ` s/d <strong>IPM-KONTRAK-${escHtml(row.dataset.kode)}/${b}/${escHtml(th)}</strong>`
                        : "");
            }
            const tunjRows = (t.tunjangan || []).map(x => {
                const jenis = x.jenis_tunjangan === "tidak_tetap"
                    ? '<span class="badge bg-warning text-dark">Tidak Tetap</span>'
                    : '<span class="badge bg-success">Tetap</span>';
                const tampil = String(x.tampil_template ?? "1") === "0"
                    ? '<span class="badge bg-secondary">Tidak</span>'
                    : '<span class="badge bg-info text-dark">Ya</span>';
                return `<tr><td>${escHtml(x.nama_tunjangan)}</td><td class="text-center">${jenis}</td>` +
                    `<td class="text-end">${fmtRpPrev(x.jumlah_tunjangan)}${isMakan(x.nama_tunjangan) ? "/hari" : ""}</td>` +
                    `<td class="text-center">${tampil}</td></tr>`;
            }).join("");
            const potRows = (t.potongan || []).map(x =>
                `<tr><td>${escHtml(x.jenis_potongan)}</td>` +
                `<td class="text-end">${fmtRpPrev(x.jumlah_potongan)}</td></tr>`
            ).join("");
            box.innerHTML =
                `<div class="table-responsive mt-1"><table class="table table-bordered table-sm mb-0">` +
                `<tbody>` +
                `<tr><th style="width:28%">Nomor Kontrak</th><td>${nomorCell}</td></tr>` +
                `<tr><th style="width:28%">Jabatan</th><td>${escHtml(jabText)}</td></tr>` +
                `<tr><th>Template</th><td>${escHtml(t.nama_kontrak || t.nama_asli || ("Template #" + t.id_template))}${jenisBadgeHtml(t.jenis_kontrak)}</td></tr>` +
                `<tr><th>Gaji Pokok</th>${tdRp(d.gaji_pokok)}</tr>` +
                `<tr><th>Upah Lembur</th>${tdRp(d.upah_lembur)}</tr>` +
                `<tr><th>Potongan Izin</th>${tdRp(d.potongan_izin)}</tr>` +
                `<tr><th>Potongan Mangkir</th>${tdRp(d.potongan_mangkir)}</tr>` +
                `<tr><th>Tunjangan (${(t.tunjangan || []).length})</th><td>` +
                (tunjRows
                    ? `<table class="table table-bordered table-sm mb-0"><thead class="table-info">` +
                      `<tr><th>Nama</th><th class="text-center">Jenis</th><th class="text-end">Jumlah</th>` +
                      `<th class="text-center">Tampil</th></tr></thead><tbody>${tunjRows}</tbody></table>`
                    : '<span class="text-muted">-</span>') +
                `</td></tr>` +
                `<tr><th>Potongan (${(t.potongan || []).length})</th><td>` +
                (potRows
                    ? `<table class="table table-bordered table-sm mb-0"><thead class="table-info">` +
                      `<tr><th>Nama</th><th class="text-end">Jumlah</th></tr></thead><tbody>${potRows}</tbody></table>`
                    : '<span class="text-muted">-</span>') +
                `</td></tr>` +
                `</tbody></table></div>`;
        }

        function rowJumlah(row) {
            const el = row.querySelector(".cetak-jumlah");
            let n = parseInt(el.value, 10) || 0;
            if (n < 1) n = 1;
            if (n > 50) n = 50;
            return n;
        }

        function collectRows() {
            return [...boxRepeater.querySelectorAll(".repeater-row")];
        }

        function tahunStr() {
            return (inTahun.value || "").replace(/\D/g, "").slice(0, 4) || String(new Date().getFullYear());
        }

        // Nomor mulai efektif: isi manual bila valid (>=1), selain itu default counter.
        function manualStartVal() {
            const v = parseInt((document.getElementById("cetakNomorMulai") || {}).value, 10);
            return (v >= 1 && v <= 999999) ? v : 0;
        }
        function isManualStart() {
            return manualStartVal() > 0;
        }
        function effectiveStart() {
            const manual = manualStartVal();
            if (manual > 0) return manual;
            return globalNext;
        }

        function refreshRanges() {
            const box = document.getElementById("cetakRangePreview");
            const rows = collectRows();
            let total = 0;
            rows.forEach(r => { total += rowJumlah(r); });
            const mulaiPakai = effectiveStart();
            if (mulaiPakai == null) {
                rows.forEach(r => {
                    delete r._mulai;
                    delete r._akhir;
                    renderRowPreview(r);
                });
                box.innerHTML = `<div class="alert alert-info py-2 mb-0 mt-2">${rows.length} baris • ${total} file.</div>`;
                return;
            }
            let cursor = mulaiPakai;
            rows.forEach((r, idx) => {
                const n = rowJumlah(r);
                const jabSel = r.querySelector(".cetak-jabatan");
                const titleEl = r.querySelector(".card-title-text");
                if (titleEl) {
                    titleEl.textContent = (jabSel && jabSel.value && jabSel.selectedIndex >= 0)
                        ? `${idx + 1}. ${jabSel.options[jabSel.selectedIndex].text}`
                        : `Jabatan #${idx + 1}`;
                }
                if (!r.dataset.kode) {
                    delete r._mulai;
                    delete r._akhir;
                    r.querySelector(".row-info").innerHTML = '<span class="text-muted">Pilih jabatan untuk melihat rentang nomor baris ini.</span>';
                } else {
                    r._mulai = cursor;
                    r._akhir = cursor + n - 1;
                    r.querySelector(".row-info").innerHTML = "";
                }
                renderRowPreview(r);
                cursor += n;
            });
            const akhir = cursor - 1;
            const manual = isManualStart();
            const rawan = manual && globalNext != null && mulaiPakai < globalNext;
            box.innerHTML = `<div class="alert ${rawan ? "alert-danger" : "alert-warning"} py-2 mb-0 mt-2">` +
                `<i class="fa fa-info-circle"></i> Total <strong>${total} file</strong> dalam <strong>${rows.length} baris</strong>, ` +
                `nomor ${mulaiPakai} s/d ${akhir} (counter menyesuaikan, tidak mundur).` +
                (manual ? `<br><small>Nomor mulai diubah manual dari default ${globalNext}.</small>` : "") +
                (rawan ? `<br><small><strong>Perhatian:</strong> mulai di bawah nomor berikutnya (${globalNext}) — berisiko duplikat dengan nomor yang sudah terpakai.</small>` : "") +
                `</div>`;
        }

        function loadRowInfo(row) {
            const jabSel = row.querySelector(".cetak-jabatan");
            const tplSel = row.querySelector(".cetak-template");
            const id = jabSel.value;
            row.dataset.kode = "";
            if (!id) {
                tplSel.innerHTML = `<option value="">-- Pilih jabatan terlebih dahulu --</option>`;
                refreshRanges();
                return;
            }
            tplSel.innerHTML = `<option value="">Memuat template...</option>`;
            const fd = new FormData();
            fd.append("action", "info");
            fd.append("id_jabatan", id);
            fd.append("jenis_kontrak", rowJenis(row));
            fetch(API, { method: "POST", body: fd })
                .then(async res => {
                    const text = await res.text();
                    try { return JSON.parse(text); }
                    catch (e) { throw new Error("Respon server tidak valid."); }
                })
                .then(res => {
                    if (res.status !== "success") throw new Error(res.msg || "Gagal memuat info.");
                    if (globalNext == null && res.berikutnya) { globalNext = res.berikutnya; }
                    row.dataset.kode = res.kode_jabatan || "";
                    row._templates = res.templates || [];
                    const keepTpl = tplSel.value;
                    if (!res.templates.length) {
                        const jlbl = rowJenis(row) ? ` jenis ${rowJenis(row)}` : "";
                        tplSel.innerHTML = `<option value="">Belum ada template${escHtml(jlbl)} untuk jabatan ini</option>`;
                    } else {
                        tplSel.innerHTML = res.templates.map(t =>
                            `<option value="${t.id_template}">${escHtml(templateLabel(t))}</option>`
                        ).join("");
                        if (res.templates.some(t => String(t.id_template) === String(keepTpl))) tplSel.value = keepTpl;
                    }
                    renderRowPreview(row);
                    if (!res.kode_jabatan) {
                        row.querySelector(".row-info").innerHTML =
                            '<span class="badge bg-danger">Kode jabatan kosong — lengkapi dulu di Data Jabatan</span>';
                    }
                    refreshRanges();
                })
                .catch(err => {
                    row.querySelector(".row-info").innerHTML =
                        `<span class="text-danger">${escHtml(err.message || err)}</span>`;
                });
        }

        function addRow() {
            const rows = collectRows();
            if (rows.length >= 10) {
                showToast("Maksimal 10 baris per sekali cetak.", "warning");
                return;
            }
            const div = document.createElement("div");
            div.className = "card mb-3 repeater-row";
            div.dataset.kode = "";
            div.innerHTML =
                `<div class="card-header d-flex justify-content-between align-items-center py-2">` +
                `<strong class="card-title-text">Jabatan</strong>` +
                `<button type="button" class="btn btn-danger btn-sm btnDelBaris" title="Hapus kartu ini">` +
                `<i class="bx bx-trash me-1"></i>Hapus</button></div>` +
                `<div class="card-body">` +
                `<div class="row g-2">` +
                `<div class="col-md-3"><label class="form-label">Jenis Kontrak</label>` +
                `<select name="jenis_kontrak[]" class="form-control cetak-jenis">` +
                `<option value="">Semua Jenis</option>` +
                `<option value="KARYAWAN BARU">KARYAWAN BARU</option>` +
                `<option value="PERPANJANGAN">PERPANJANGAN</option></select></div>` +
                `<div class="col-md-4"><label class="form-label">Jabatan <span class="text-danger">*</span></label>` +
                `<select name="id_jabatan[]" class="form-control select-search cetak-jabatan" required>${jabOptionsHTML}</select></div>` +
                `<div class="col-md-3"><label class="form-label">Template <span class="text-danger">*</span></label>` +
                `<select name="id_template[]" class="form-control cetak-template" required>` +
                `<option value="">-- Pilih jabatan terlebih dahulu --</option></select></div>` +
                `<div class="col-md-2"><label class="form-label">Jumlah</label>` +
                `<input type="number" name="jumlah[]" class="form-control cetak-jumlah" value="1" min="1" max="50" required></div>` +
                `</div><small class="row-info text-muted"></small>` +
                `<div class="row-preview mt-1"></div>` +
                `</div>`;
            boxRepeater.appendChild(div);
            if (window.jQuery) {
                $(div).find(".cetak-jabatan").select2({
                    width: "100%", placeholder: "Pilih jabatan...", allowClear: true
                });
            }
            refreshRanges();
        }

        async function hapusSemuaBaris() {
            const rows = collectRows();
            if (!rows.length) return;
            if (!(await confirmDialog("Hapus semua baris jabatan?"))) return;
            boxRepeater.innerHTML = "";
            addRow();
        }

        function loadCounter() {
            const fd = new FormData();
            fd.append("action", "counter");
            fetch(API, { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") throw new Error(res.msg || "Gagal memuat counter.");
                    globalNext = res.berikutnya;
                    const inNomor = document.getElementById("cetakNomorMulai");
                    if (inNomor && !inNomor.value) inNomor.value = res.berikutnya;
                    document.getElementById("cetakNomorHint").textContent =
                        "Default " + res.berikutnya + " (counter " + res.terakhir_counter + " / data " + res.terbesar_data + "). Kosongkan untuk otomatis.";
                    document.getElementById("tbodyNomorInfo").innerHTML =
                        `<tr><th style="width:35%">Terakhir (counter)</th><td><strong>${res.terakhir_counter}</strong></td></tr>` +
                        `<tr><th>Terbesar (data)</th><td><strong>${res.terbesar_data}</strong></td></tr>` +
                        `<tr><th>Berikutnya</th><td><strong>${res.berikutnya}</strong></td></tr>`;
                    refreshRanges();
                })
                .catch(err => {
                    document.getElementById("tbodyNomorInfo").innerHTML =
                        `<tr><td class="text-center text-danger">${escHtml(err.message || err)}</td></tr>`;
                });
        }

        // Event delegation: jQuery .on agar change Select2 ikut tertangkap.
        if (window.jQuery) {
            $(boxRepeater).on("change", ".cetak-jabatan", function() {
                loadRowInfo($(this).closest(".repeater-row")[0]);
            });
            $(boxRepeater).on("change", ".cetak-jenis", function() {
                loadRowInfo($(this).closest(".repeater-row")[0]);
            });
            $(boxRepeater).on("change", ".cetak-template", function() {
                renderRowPreview($(this).closest(".repeater-row")[0]);
            });
            $(boxRepeater).on("change input", ".cetak-jumlah", refreshRanges);
            $(boxRepeater).on("click", ".btnDelBaris", function() {
                if (collectRows().length <= 1) {
                    showToast("Minimal satu kartu jabatan.", "warning");
                    return;
                }
                $(this).closest(".repeater-row").remove();
                refreshRanges();
            });
            $("#btnAddBaris").on("click", addRow);
            $("#btnHapusSemuaBaris").on("click", hapusSemuaBaris);
        } else {
            boxRepeater.addEventListener("change", e => {
                if (e.target.classList.contains("cetak-jabatan")) loadRowInfo(e.target.closest(".repeater-row"));
                else if (e.target.classList.contains("cetak-jenis")) loadRowInfo(e.target.closest(".repeater-row"));
                else refreshRanges();
            });
            boxRepeater.addEventListener("input", refreshRanges);
            boxRepeater.addEventListener("click", e => {
                const del = e.target.closest(".btnDelBaris");
                if (del) {
                    if (collectRows().length <= 1) { showToast("Minimal satu kartu jabatan.", "warning"); return; }
                    del.closest(".repeater-row").remove();
                    refreshRanges();
                }
            });
            document.getElementById("btnAddBaris").addEventListener("click", addRow);
            document.getElementById("btnHapusSemuaBaris").addEventListener("click", hapusSemuaBaris);
        }
        inTahun.addEventListener("input", refreshRangeCompat);
        document.getElementById("cetakNomorMulai").addEventListener("input", refreshRanges);
        function refreshRangeCompat() { refreshRanges(); }

        function hasPrintData() {
            return collectRows().some(row => {
                const jabatan = row.querySelector(".cetak-jabatan")?.value || "";
                const template = row.querySelector(".cetak-template")?.value || "";
                const jumlah = row.querySelector(".cetak-jumlah")?.value || "1";
                return jabatan !== "" || template !== "" || String(jumlah) !== "1";
            });
        }

        async function confirmLeave(event) {
            const link = event.target.closest("a[href]");
            if (!link || !hasPrintData()) return;
            const href = link.getAttribute("href") || "";
            if (!href || href.startsWith("#") || link.target === "_blank" || href.startsWith("javascript:")) return;
            event.preventDefault();
            event.stopPropagation();
            const leave = await confirmDialog("Data cetak kontrak yang sudah dibuat akan dihapus. Yakin ingin meninggalkan halaman ini?");
            if (!leave) return;
            boxRepeater.innerHTML = "";
            addRow();
            window.location.href = link.href;
        }

        document.addEventListener("click", confirmLeave, true);
        addRow();
        loadCounter();

        document.getElementById("formCetakKontrak").addEventListener("submit", async function(e) {
            const rows = collectRows();
            if (!rows.length) {
                e.preventDefault();
                showToast("Tambahkan minimal satu baris jabatan.", "warning");
                return;
            }
            let total = 0;
            for (let i = 0; i < rows.length; i++) {
                const jab = rows[i].querySelector(".cetak-jabatan").value;
                const tpl = rows[i].querySelector(".cetak-template").value;
                if (!jab) {
                    e.preventDefault();
                    showToast(`Baris ${i + 1}: pilih jabatan terlebih dahulu.`, "warning");
                    return;
                }
                if (!tpl) {
                    e.preventDefault();
                    showToast(`Baris ${i + 1}: pilih template terlebih dahulu.`, "warning");
                    return;
                }
                total += rowJumlah(rows[i]);
            }
            if (total > 100) {
                e.preventDefault();
                showToast("Total file maksimal 100 per sekali cetak.", "warning");
                return;
            }
            const noMulai = (document.getElementById("cetakNomorMulai").value || "").trim();
            if (noMulai !== "" && (!/^\d+$/.test(noMulai) || parseInt(noMulai, 10) < 1 || parseInt(noMulai, 10) > 999999)) {
                e.preventDefault();
                showToast("Nomor Mulai harus angka 1 s/d 999999, atau dikosongkan.", "warning");
                return;
            }
            // Rincian per jenis dari template terpilih tiap baris (untuk konfirmasi).
            const perJenis = {};
            rows.forEach(r => {
                const tplId = r.querySelector(".cetak-template").value;
                const t = (r._templates || []).find(x => String(x.id_template) === String(tplId));
                const j = t && t.jenis_kontrak ? String(t.jenis_kontrak).toUpperCase() : "";
                const key = (j === "KARYAWAN BARU" || j === "PERPANJANGAN") ? j : "Campuran";
                perJenis[key] = (perJenis[key] || 0) + rowJumlah(r);
            });
            const rincian = Object.entries(perJenis).map(([k, v]) => `${v} file ${k}`).join(" + ");
            const ok = await confirmDialog(
                `Cetak ${total} file (${rincian}) dalam ${rows.length} baris? Nomor kontrak akan langsung dikonsumsi dan tidak bisa dibatalkan.`
            );
            if (!ok) e.preventDefault();
        });
    });
</script>
