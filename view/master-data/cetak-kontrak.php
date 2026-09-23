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
                    Tambahkan <strong>baris per jabatan</strong> → pilih <strong>template</strong> → <strong>jumlah</strong> tiap baris.
                    Periode (Tgl Mulai + Durasi) berlaku untuk semua baris.
                    Nomor kontrak berurutan langsung <strong>dikonsumsi</strong> dari counter
                    (<code>tb_nomor_urut</code>) dan tidak bisa dibatalkan.
                    Isian personal (nama, NIK, alamat) berupa titik-titik.
                </div>

                <form id="formCetakKontrak" action="app/controller/act-cetak-kontrak.php" method="POST" target="_blank">
                    <input type="hidden" name="action" value="generate">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" id="cetakTglMulai" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Durasi Kontrak</label>
                            <select name="durasi_bulan" id="cetakDurasi" class="form-select" required>
                                <option value="3">3 Bulan</option>
                                <option value="6" selected>6 Bulan</option>
                                <option value="12">12 Bulan (1 Tahun)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tgl Selesai <small class="text-muted">(otomatis)</small></label>
                            <input type="text" id="cetakTglSelesai" class="form-control" readonly placeholder="—">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun Nomor</label>
                            <input type="number" name="tahun" id="cetakTahun" class="form-control" value="<?= date('Y') ?>" min="2000" max="2100" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0">Daftar Jabatan <small class="text-muted">(maks 10 baris, total maks 100 file)</small></label>
                        <button type="button" class="btn btn-sm btn-primary" id="btnAddBaris">
                            <i class="fa fa-plus"></i> Tambah Jabatan
                        </button>
                    </div>
                    <div id="repeaterJabatan"></div>
                    <select id="cetakJabatanSource" class="d-none" aria-hidden="true" tabindex="-1">
                        <option value="">-- Pilih Jabatan --</option>
                        <?php
                        $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                        while ($j = $jabatans->fetch_assoc()) {
                            $__kj = trim((string)($j['kode_jabatan'] ?? ''));
                            echo "<option value='{$j['id_jabatan']}'>" . htmlspecialchars($j['nama_jabatan'] . ($__kj !== '' ? " ($__kj)" : '')) . "</option>";
                        }
                        ?>
                    </select>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success" id="btnCetakKontrak">
                            <i class="fa fa-download"></i> Generate &amp; Unduh ZIP
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
        const inMulai = document.getElementById("cetakTglMulai");
        const inDurasi = document.getElementById("cetakDurasi");
        const inSelesai = document.getElementById("cetakTglSelesai");
        const boxRepeater = document.getElementById("repeaterJabatan");
        const jabOptionsHTML = document.getElementById("cetakJabatanSource").innerHTML;
        let globalNext = null;

        const NAMA_BULAN = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        // Tgl Selesai = Tgl Mulai + N bulan, mundur 1 hari
        // (sama seperti tombol durasi di form karier).
        function hitungTglSelesai() {
            if (!inMulai.value) { inSelesai.value = "—"; return ""; }
            const bulan = parseInt(inDurasi.value, 10) || 0;
            const d = new Date(inMulai.value + "T00:00:00");
            if (isNaN(d.getTime()) || bulan <= 0) { inSelesai.value = "—"; return ""; }
            const tanggal = d.getDate();
            d.setDate(1);
            d.setMonth(d.getMonth() + bulan);
            const maks = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
            d.setDate(Math.min(tanggal - 1, maks));
            const pad = n => String(n).padStart(2, "0");
            const iso = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            inSelesai.value = `${d.getDate()} ${NAMA_BULAN[d.getMonth() + 1]} ${d.getFullYear()}`;
            return iso;
        }

        function tglIndo(iso) {
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || "");
            if (!m) return "—";
            return `${parseInt(m[3], 10)} ${NAMA_BULAN[parseInt(m[2], 10)] || m[2]} ${m[1]}`;
        }

        function syncTahun() {
            const m = /^(\d{4})-\d{2}-\d{2}$/.exec(inMulai.value || "");
            if (m) inTahun.value = m[1];
        }

        function escHtml(s) {
            return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
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

        function refreshRanges() {
            const box = document.getElementById("cetakRangePreview");
            const rows = collectRows();
            let total = 0;
            rows.forEach(r => { total += rowJumlah(r); });
            if (globalNext == null) {
                rows.forEach(r => {
                    r.querySelector(".row-info").innerHTML = '<span class="text-muted">Menunggu info nomor...</span>';
                });
                box.innerHTML = `<div class="alert alert-info py-2 mb-0 mt-2">${rows.length} baris • ${total} file.</div>`;
                return;
            }
            let cursor = globalNext;
            const th = tahunStr();
            rows.forEach(r => {
                const n = rowJumlah(r);
                const kode = r.dataset.kode || "";
                const info = r.querySelector(".row-info");
                if (!kode) {
                    info.innerHTML = '<span class="text-muted">Pilih jabatan untuk melihat rentang nomor baris ini.</span>';
                } else {
                    const a = String(cursor).padStart(4, "0");
                    const b = String(cursor + n - 1).padStart(4, "0");
                    info.innerHTML = `Nomor: <strong>TPJS-KONTRAK-${escHtml(kode)}/${a}/${escHtml(th)}</strong>` +
                        (n > 1 ? ` s/d <strong>TPJS-KONTRAK-${escHtml(kode)}/${b}/${escHtml(th)}</strong>` : ``) +
                        ` <span class="text-muted">(${n} file)</span>`;
                }
                cursor += n;
            });
            const akhir = cursor - 1;
            box.innerHTML = `<div class="alert alert-warning py-2 mb-0 mt-2">` +
                `<i class="fa fa-info-circle"></i> Total <strong>${total} file</strong> dalam <strong>${rows.length} baris</strong>, ` +
                `nomor ${globalNext} s/d ${akhir} — counter maju ke <strong>${akhir}</strong>.<br>` +
                `<small>Periode: ${tglIndo(inMulai.value)} s/d ${tglIndo(hitungTglSelesai() || "")} ` +
                `(${escHtml(inDurasi.options[inDurasi.selectedIndex].text)})</small></div>`;
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
                    if (!res.templates.length) {
                        tplSel.innerHTML = `<option value="">Belum ada template untuk jabatan ini</option>`;
                    } else {
                        tplSel.innerHTML = res.templates.map(t =>
                            `<option value="${t.id_template}">${escHtml(t.nama_kontrak || t.nama_asli || ("Template #" + t.id_template))}</option>`
                        ).join("");
                    }
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
            div.className = "repeater-row border rounded p-2 mb-2";
            div.dataset.kode = "";
            div.innerHTML =
                `<div class="row g-2 align-items-end">` +
                `<div class="col-md-5"><label class="form-label">Jabatan <span class="text-danger">*</span></label>` +
                `<select name="id_jabatan[]" class="form-control select-search cetak-jabatan" required>${jabOptionsHTML}</select></div>` +
                `<div class="col-md-4"><label class="form-label">Template <span class="text-danger">*</span></label>` +
                `<select name="id_template[]" class="form-control cetak-template" required>` +
                `<option value="">-- Pilih jabatan terlebih dahulu --</option></select></div>` +
                `<div class="col-md-2"><label class="form-label">Jumlah</label>` +
                `<input type="number" name="jumlah[]" class="form-control cetak-jumlah" value="1" min="1" max="50" required></div>` +
                `<div class="col-md-1 text-end"><button type="button" class="btn btn-danger btn-sm btnDelBaris" title="Hapus baris">` +
                `<i class="fa fa-trash"></i></button></div>` +
                `</div><small class="row-info text-muted"></small>`;
            boxRepeater.appendChild(div);
            if (window.jQuery) {
                $(div).find(".cetak-jabatan").select2({
                    width: "100%", placeholder: "Pilih jabatan...", allowClear: true
                });
            }
            refreshRanges();
        }

        function loadCounter() {
            const fd = new FormData();
            fd.append("action", "counter");
            fetch(API, { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") throw new Error(res.msg || "Gagal memuat counter.");
                    globalNext = res.berikutnya;
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
            $(boxRepeater).on("change input", ".cetak-template, .cetak-jumlah", refreshRanges);
            $(boxRepeater).on("click", ".btnDelBaris", function() {
                if (collectRows().length <= 1) {
                    showToast("Minimal satu baris.", "warning");
                    return;
                }
                $(this).closest(".repeater-row").remove();
                refreshRanges();
            });
            $("#btnAddBaris").on("click", addRow);
        } else {
            boxRepeater.addEventListener("change", e => {
                if (e.target.classList.contains("cetak-jabatan")) loadRowInfo(e.target.closest(".repeater-row"));
                else refreshRanges();
            });
            boxRepeater.addEventListener("input", refreshRanges);
            boxRepeater.addEventListener("click", e => {
                const del = e.target.closest(".btnDelBaris");
                if (del) {
                    if (collectRows().length <= 1) { showToast("Minimal satu baris.", "warning"); return; }
                    del.closest(".repeater-row").remove();
                    refreshRanges();
                }
            });
            document.getElementById("btnAddBaris").addEventListener("click", addRow);
        }
        inTahun.addEventListener("input", refreshRangeCompat);
        inMulai.addEventListener("change", () => { syncTahun(); hitungTglSelesai(); refreshRanges(); });
        inDurasi.addEventListener("change", () => { hitungTglSelesai(); refreshRanges(); });
        function refreshRangeCompat() { refreshRanges(); }
        hitungTglSelesai();
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
            const ok = await confirmDialog(
                `Cetak ${total} file dalam ${rows.length} baris? Nomor kontrak akan langsung dikonsumsi dan tidak bisa dibatalkan.`
            );
            if (!ok) e.preventDefault();
        });
    });
</script>
