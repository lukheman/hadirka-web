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
                    Pilih <strong>jabatan</strong> → <strong>template</strong> → <strong>durasi</strong> → <strong>jumlah</strong>.
                    Nomor kontrak berurutan langsung <strong>dikonsumsi</strong> dari counter
                    (<code>tb_nomor_urut</code>) dan tidak bisa dibatalkan.
                    Isian personal (nama, NIK, alamat) berupa titik-titik, sedangkan
                    periode kontrak terisi sesuai Tgl Mulai + Durasi pilihan.
                </div>

                <form id="formCetakKontrak" action="app/controller/act-cetak-kontrak.php" method="POST" target="_blank">
                    <input type="hidden" name="action" value="generate">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <select name="id_jabatan" id="cetakJabatan" class="form-control select-search" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    $__kj = trim((string)($j['kode_jabatan'] ?? ''));
                                    echo "<option value='{$j['id_jabatan']}'>" . htmlspecialchars($j['nama_jabatan'] . ($__kj !== '' ? " ($__kj)" : '')) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Template <span class="text-danger">*</span></label>
                            <select name="id_template" id="cetakTemplate" class="form-control" required>
                                <option value="">-- Pilih jabatan terlebih dahulu --</option>
                            </select>
                        </div>
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
                        <div class="col-md-3">
                            <label class="form-label">Jumlah <small class="text-muted">(1–50)</small></label>
                            <input type="number" name="jumlah" id="cetakJumlah" class="form-control" value="1" min="1" max="50" required>
                        </div>
                        <div class="col-md-9 d-flex align-items-end">
                            <button type="submit" class="btn btn-success" id="btnCetakKontrak">
                                <i class="fa fa-download"></i> Generate &amp; Unduh ZIP
                            </button>
                        </div>
                    </div>
                </form>

                <hr>
                <h6 class="fw-bold">Posisi Nomor Kontrak</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm w-100">
                        <tbody id="tbodyNomorInfo">
                            <tr><td class="text-center text-muted">Pilih jabatan untuk melihat posisi nomor.</td></tr>
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
        const selJab = document.getElementById("cetakJabatan");
        const selTpl = document.getElementById("cetakTemplate");
        const inTahun = document.getElementById("cetakTahun");
        const inJumlah = document.getElementById("cetakJumlah");
        const inMulai = document.getElementById("cetakTglMulai");
        const inDurasi = document.getElementById("cetakDurasi");
        const inSelesai = document.getElementById("cetakTglSelesai");
        let infoData = null;

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

        function refreshRange() {
            const box = document.getElementById("cetakRangePreview");
            if (!infoData || !infoData.kode_jabatan) { box.innerHTML = ""; return; }
            let n = parseInt(inJumlah.value, 10) || 0;
            if (n < 1) n = 1;
            if (n > 50) n = 50;
            const mulai = infoData.berikutnya;
            const akhir = mulai + n - 1;
            const th = (inTahun.value || "").replace(/\D/g, "").slice(0, 4) || String(new Date().getFullYear());
            const fmt = s => `TPJS-KONTRAK-${escHtml(infoData.kode_jabatan)}/${String(s).padStart(4, "0")}/${escHtml(th)}`;
            box.innerHTML = `<div class="alert alert-warning py-2 mb-0 mt-2">` +
                `<i class="fa fa-info-circle"></i> Akan memakai nomor <strong>${fmt(mulai)}</strong>` +
                (n > 1 ? ` s/d <strong>${fmt(akhir)}</strong> (${n} nomor)` : ` (1 nomor)`) +
                ` — counter maju ke <strong>${akhir}</strong>.<br>` +
                `<small>Periode: ${tglIndo(inMulai.value)} s/d ${tglIndo(hitungTglSelesai() || "")} ` +
                `(${escHtml(inDurasi.options[inDurasi.selectedIndex].text)})</small></div>`;
        }

        function loadInfo() {
            const id = selJab.value;
            infoData = null;
            document.getElementById("tbodyNomorInfo").innerHTML =
                `<tr><td class="text-center text-muted">Memuat...</td></tr>`;
            document.getElementById("cetakRangePreview").innerHTML = "";
            if (!id) {
                selTpl.innerHTML = `<option value="">-- Pilih jabatan terlebih dahulu --</option>`;
                document.getElementById("tbodyNomorInfo").innerHTML =
                    `<tr><td class="text-center text-muted">Pilih jabatan untuk melihat posisi nomor.</td></tr>`;
                return;
            }
            const fd = new FormData();
            fd.append("action", "info");
            fd.append("id_jabatan", id);
            fetch(API, { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") {
                        throw new Error(res.msg || "Gagal memuat info.");
                    }
                    infoData = res;
                    if (!res.templates.length) {
                        selTpl.innerHTML = `<option value="">Belum ada template untuk jabatan ini</option>`;
                    } else {
                        selTpl.innerHTML = res.templates.map(t =>
                            `<option value="${t.id_template}">${escHtml(t.nama_kontrak || t.nama_asli || ("Template #" + t.id_template))}</option>`
                        ).join("");
                    }
                    const kode = res.kode_jabatan ? escHtml(res.kode_jabatan) : '<span class="badge bg-danger">Kode kosong</span>';
                    document.getElementById("tbodyNomorInfo").innerHTML =
                        `<tr><th style="width:35%">Jabatan</th><td>${escHtml(res.nama_jabatan)} (${kode})</td></tr>` +
                        `<tr><th>Template tersedia</th><td>${res.templates.length} template</td></tr>` +
                        `<tr><th>Terakhir (counter)</th><td><strong>${res.terakhir_counter}</strong></td></tr>` +
                        `<tr><th>Terbesar (data)</th><td><strong>${res.terbesar_data}</strong></td></tr>` +
                        `<tr><th>Berikutnya</th><td><strong>${res.berikutnya}</strong></td></tr>`;
                    refreshRange();
                })
                .catch(err => {
                    document.getElementById("tbodyNomorInfo").innerHTML =
                        `<tr><td class="text-center text-danger">${escHtml(err.message || err)}</td></tr>`;
                });
        }

        // Pakai jQuery .on agar change dari Select2 ikut tertangkap
        // (Select2 memicu 'change' jQuery yg tidak sampai ke addEventListener native).
        if (window.jQuery) {
            $(selJab).on("change", loadInfo);
        } else {
            selJab.addEventListener("change", loadInfo);
        }
        inJumlah.addEventListener("input", refreshRange);
        inTahun.addEventListener("input", refreshRange);
        inMulai.addEventListener("change", () => { syncTahun(); hitungTglSelesai(); refreshRange(); });
        inDurasi.addEventListener("change", () => { hitungTglSelesai(); refreshRange(); });
        hitungTglSelesai();

        document.getElementById("formCetakKontrak").addEventListener("submit", async function(e) {
            if (!selJab.value) {
                e.preventDefault();
                showToast("Pilih jabatan terlebih dahulu.", "warning");
                return;
            }
            if (!selTpl.value) {
                e.preventDefault();
                showToast("Pilih template terlebih dahulu.", "warning");
                return;
            }
            const ok = await confirmDialog(
                "Nomor kontrak akan langsung dikonsumsi dan tidak bisa dibatalkan. Lanjutkan cetak?"
            );
            if (!ok) e.preventDefault();
        });
    });
</script>
