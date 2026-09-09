<div class="container-xxl mt-3">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Data Kehadiran /</span> Absen Karyawan
        </h4>
        <div>
            <button class="btn btn-success btn-sm me-2" id="btnTambahAbsen">
                <i class="bi bi-plus-circle-fill"></i> Tambah
            </button>
            <button class="btn btn-primary btn-sm" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-3 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filter Data</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Site -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-geo-alt-fill me-1"></i>Site
                    </label>
                    <select id="filterSite" class="form-select form-select-sm">
                        <option value="">-- Pilih Site --</option>
                        <?php
                        $q = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                        while ($d = $q->fetch_assoc()):
                        ?>
                            <option value="<?= $d['id_site']; ?>"><?= $d['nama_site']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Tanggal -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-calendar-date me-1"></i>Tanggal
                    </label>
                    <input type="date" id="filterTanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Status -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i>Status
                    </label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        <option value="HADIR">HADIR</option>
                        <option value="OFF">SPL/OFF</option>
                        <option value="SAKIT">SAKIT</option>
                        <option value="IZIN">IZIN</option>
                        <option value="CUTI">CUTI</option>
                        <option value="MANGKIR">MANGKIR</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="bi bi-table me-2"></i>Data Absensi</h6>
            <small>Total Records: <span id="totalRecords">0</span></small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" id="tableContainer">
                <table id="tableAbsen" class="table table-hover table-striped table-bordered align-middle mb-0" style="width:100%">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width:50px"><i class="bi bi-hash me-1"></i>No</th>
                            <th><i class="bi bi-person-circle me-1"></i>Nama</th>
                            <th><i class="bi bi-briefcase-fill me-1"></i>Jabatan</th>
                            <th><i class="bi bi-geo-alt-fill me-1"></i>Site</th>
                            <th><i class="bi bi-calendar-event me-1"></i>Tanggal</th>
                            <th><i class="bi bi-clock-fill me-1"></i>Jam Kerja</th>
                            <th><i class="bi bi-info-circle-fill me-1"></i>Keterangan</th>
                            <th style="width:120px"><i class="bi bi-gear-fill me-1"></i>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelAbsen" class="text-center">
                        <!-- Data akan dimuat di sini -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
            <small class="text-muted">Data diperbarui secara real-time</small>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Absensi -->
<div class="modal fade" id="modalAbsen" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAbsen">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Form Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- sesuaikan name dengan backend act-absen.php -->
                    <input type="hidden" id="idAbsen" name="id_absen">

                    <!-- Site (hanya untuk UI/filter, tidak disimpan di tb_absen) -->
                    <div class="mb-2">
                        <label>Site</label>
                        <select class="form-select" id="site" name="site">
                            <option value="">-- Pilih Site --</option>
                            <?php
                            $q = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                            while ($s = $q->fetch_assoc()) {
                                echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Jabatan -->
                    <div class="mb-2">
                        <label>Jabatan</label>
                        <select class="form-select" id="jabatan" name="jabatan">
                            <option value="">-- Pilih Jabatan --</option>
                        </select>
                    </div>

                    <!-- Karyawan (name harus id_karyawan) -->
                    <div class="mb-2">
                        <label>Karyawan</label>
                        <select class="form-select" id="karyawan" name="id_karyawan" required>
                            <option value="">-- Pilih Karyawan --</option>
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div class="mb-2">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="tgl" name="tgl_absen" required>
                    </div>

                    <!-- Keterangan (select untuk pilihan umum) -->
                    <div class="mb-2">
                        <label>Keterangan</label>
                        <select class="form-select" id="ket" name="ket_absen">
                            <option value="">-- Pilih Keterangan --</option>
                            <option value="HADIR">HADIR</option>
                            <option value="OFF">SPL/OFF</option>
                            <option value="IZIN">IZIN</option>
                            <option value="SAKIT">SAKIT</option>
                            <option value="CUTI">CUTI</option>
                        </select>
                    </div>

                    <!-- Jam Kerja (name sesuai backend) -->
                    <div class="mb-2" id="fieldJamKerja">
                        <label>Jam Kerja</label>
                        <input type="text" class="form-control" id="jam" name="jam_kerja">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        // mapping untuk UI behaviour (jam selalu tampil)
        const ketMapping = {
            "HADIR": {
                showJam: true
            },
            "SPL/OFF": {
                showJam: true
            },
            "IZIN": {
                showJam: false
            },
            "SAKIT": {
                showJam: false
            },
            "CUTI": {
                showJam: false
            },
        };

        const modalEl = document.getElementById("modalAbsen");
        const modal = new bootstrap.Modal(modalEl);

        function updateFieldsByKeterangan() {
            const ketSelect = document.getElementById("ket").value;
            const config = ketMapping[ketSelect] || {
                showJam: true
            };

            // jam selalu tampil (user requested)
            document.getElementById("fieldJamKerja").style.display = config.showJam ? "block" : "none";

            // jika OFF, kosongkan jam
            if (!config.showJam) {
                document.getElementById("jam").value = "";
            }
        }

        // event change pada select ket
        document.getElementById("ket").addEventListener("change", updateFieldsByKeterangan);

        // ================= Load Jabatan & Karyawan =================
        async function loadJabatan(siteId, selectedJabId = null) {
            if (!siteId) return [];
            const res = await fetch("app/controller/get-data/get-jabatan-by-site.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    site: siteId
                })
            });
            const data = await res.json();
            let html = '<option value="">-- Pilih Jabatan --</option>';
            data.forEach(j => {
                html += `<option value="${j.id_jabatan}" ${selectedJabId && j.id_jabatan==selectedJabId ? 'selected' : ''}>${j.nama_jabatan}</option>`;
            });
            document.getElementById("jabatan").innerHTML = html;
            return data;
        }

        async function loadKaryawan(siteId, jabId, selectedKaryawanId = null) {
            if (!siteId || !jabId) return [];
            const res = await fetch("app/controller/get-data/get-karyawan-by-site-jabatan.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    site: siteId,
                    jabatan: jabId
                })
            });
            const data = await res.json();
            let html = '<option value="">-- Pilih Karyawan --</option>';
            data.forEach(k => {
                html += `<option value="${k.id_karyawan}" ${selectedKaryawanId && k.id_karyawan==selectedKaryawanId ? 'selected' : ''}>${k.nama_lengkap}</option>`;
            });
            document.getElementById("karyawan").innerHTML = html;
            return data;
        }

        document.getElementById("site").addEventListener("change", async function() {
            const siteId = this.value;
            await loadJabatan(siteId);
            document.getElementById("karyawan").innerHTML = '<option value="">-- Pilih Karyawan --</option>';
        });

        document.getElementById("jabatan").addEventListener("change", async function() {
            const siteId = document.getElementById("site").value;
            const jabId = this.value;
            await loadKaryawan(siteId, jabId);
        });

        // ================= Load Data Absen =================
        async function loadData() {
            const site = document.getElementById("filterSite").value;
            const tgl = document.getElementById("filterTanggal").value;
            const status = document.getElementById("filterStatus").value;

            const tbody = document.getElementById("tabelAbsen");
            tbody.innerHTML = "<tr><td colspan='8'>Loading...</td></tr>";

            try {
                const res = await fetch("app/controller/get-data/get-absen.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        site,
                        tgl,
                        status
                    })
                });
                const html = await res.text();
                tbody.innerHTML = html;
                const total = html.trim() ? document.querySelectorAll("#tabelAbsen tr").length : 0;
                document.getElementById("totalRecords").textContent = total;
            } catch (err) {
                console.error(err);
                tbody.innerHTML = "<tr><td colspan='8'>Terjadi kesalahan saat memuat data!</td></tr>";
            }
        }

        // ================= Tambah Absensi =================
        document.getElementById("btnTambahAbsen").addEventListener("click", () => {
            document.getElementById("formAbsen").reset();
            document.getElementById("idAbsen").value = "";
            document.getElementById("ket").value = "";
            updateFieldsByKeterangan();
            modal.show();
        });

        // ================= Edit Absensi =================
        document.addEventListener("click", async function(e) {
            // handle both icon or button click
            const btn = e.target.closest(".btn-edit");
            if (!btn) return;

            const tr = btn.closest("tr");
            const siteId = tr.querySelector(".td-site")?.dataset.siteId || "";
            const jabId = tr.querySelector(".td-jabatan")?.dataset.jabatanId || "";
            const karyawanId = tr.querySelector(".td-nama")?.dataset.karyawanId || "";
            const ketDb = tr.querySelector(".td-ket")?.dataset.ket ?? "";
            const jamDb = tr.querySelector(".td-jam")?.dataset.jam ?? "";
            const tglDb = tr.querySelector(".td-tanggal")?.dataset.tgl ?? "";

            // populate form (names match act-absen.php)
            document.getElementById("idAbsen").value = btn.dataset.id || "";
            document.getElementById("site").value = siteId;

            await loadJabatan(siteId, jabId);
            await loadKaryawan(siteId, jabId, karyawanId);

            document.getElementById("karyawan").value = karyawanId;
            document.getElementById("tgl").value = tglDb;

            // set select ket to DB value if matches (otherwise leave blank)
            // we use select named ket_select for UI only
            document.getElementById("ket").value = ketDb;
            
            // jam kerja
            document.getElementById("jam").value = jamDb;

            updateFieldsByKeterangan();
            modal.show();
        });

        // ================= Submit Form =================
        document.getElementById("formAbsen").addEventListener("submit", async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // decide action add/edit
            const action = formData.get("id_absen") ? "edit" : "add";
            formData.append("action", action);

            try {
                const res = await fetch("app/controller/act-absen.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();
                showToast(data.msg || (data.message ? data.message : JSON.stringify(data)), "success");
                if (data.status === "success") {
                    modal.hide();
                    loadData();
                }
            } catch (err) {
                console.error(err);
                showToast("Gagal menyimpan data!", "error");
            }
        });

        // ================= Delete =================
        document.addEventListener("click", async function(e) {
            const btn = e.target.closest(".btn-delete");
            if (!btn) return;
            if (!(await confirmDialog("Yakin ingin menghapus data ini?"))) return;

            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append("action", "delete");
            formData.append("id_absen", id);

            try {
                const res = await fetch("app/controller/act-absen.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();
                showToast(data.msg || (data.message ? data.message : JSON.stringify(data)), "berhasil");
                if (data.status === "success") loadData();
            } catch (err) {
                console.error(err);
                showToast("Gagal menghapus data!", "error");
            }
        });

        // Filters
        document.getElementById("filterSite").addEventListener("change", loadData);
        document.getElementById("filterTanggal").addEventListener("change", loadData);
        document.getElementById("filterStatus").addEventListener("change", loadData);
        document.getElementById("refreshBtn").addEventListener("click", loadData);

        // initial load
        loadData();

    });
</script>