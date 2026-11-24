<?php
// Bulan berjalan sebagai filter default (YYYY-MM)
$bulanIni = date("Y-m");
?>

<div class="container-xxl mt-3 fade-in">

    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Data Karyawan /</span> Absensi
    </h4>
    <!-- <button class="btn btn-success mb-3" id="btnTambahAbsensi">
        <i class="fa fa-plus"></i> Tambah Absensi
    </button> -->



    <!-- Filter Tanggal -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <label for="filterSite" class="form-label fw-bold text-primary">
                <i class="fa fa-location fa-lg"></i> Site
            </label>
            <select id="filterSite" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site">
                <option value="">-- Pilih Site --</option>
                <?php foreach (data_site() as $d) {
                    echo "<option value='{$d['id_site']}'>{$d['nama_site']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterJabatan" class="form-label fw-bold text-primary">
                <i class="fa fa-user fa-lg"></i> Jabatan
            </label>
            <select id="filterJabatan" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan jabatan">
                <option value="">-- Semua Jabatan --</option>
                <?php foreach (data_jabatan() as $d) {
                    echo "<option value='{$d['id_jabatan']}'>{$d['nama_jabatan']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterBulan" class="form-label fw-bold text-primary">
                <i class="fa fa-calendar fa-lg"></i> Bulan
            </label>
            <input type="month" id="filterBulan" class="form-control form-control-sm" value="<?= $bulanIni ?>" data-bs-toggle="tooltip" title="Pilih bulan periode absensi">
        </div>
    </div>

    <!-- Tabel Absensi -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fa fa-table fa-lg"></i> Data Absensi</h5>
            <button type="button" class="btn btn-warning btn-sm" id="btnExportExcel">
                <i class="fa fa-file-excel"></i> Export Excel
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="searchKaryawan" class="form-label fw-bold text-primary">
                        <i class="fa fa-search fa-lg"></i> Cari Nama / NIK / ID
                    </label>
                    <input type="search" id="searchKaryawan" class="form-control form-control-sm" placeholder="Contoh: ipm-00090" data-bs-toggle="tooltip" title="Ketik nama, NIK, atau ID karyawan untuk memfilter tabel">
                </div>
            </div>
            <!-- Spinner Loading -->
            <div class="text-center" id="loadingSpinner" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat data...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data absensi...</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped MyTableAbsensi" id="tabelAbsensi">
                    <thead class="table-info">
                        <tr>
                            <th class="text-center"><i class="fa fa-hashtag"></i> No</th>
                            <th class="text-center"><i class="fa fa-user"></i> Nama Lengkap</th>
                            <th class="text-center"><i class="fa fa-calendar text-primary"></i> Kalender</th>
                            <th class="text-center"><i class="fa fa-building"></i> Departemen</th>
                            <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                            <th class="text-center"><i class="fa fa-map-marker"></i> Site</th>
                            <th class="text-center"><i class="fa fa-check-circle text-success"></i> Hadir</th>
                            <th class="text-center"><i class="fa fa-times-circle text-danger"></i> Mangkir</th>
                            <th class="text-center"><i class="fa fa-plane text-purple"></i> Cuti</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetailAbsensi" tabindex="-1" aria-labelledby="modalDetailAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalDetailAbsensiLabel"><i class="fa fa-info-circle"></i> Detail Absensi</h5>
                <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailAbsensiBody">
                <!-- Akan di-load via fetch -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat detail...</span>
                    </div>
                    <p>Memuat detail absensi...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Absen (di dalam detail) -->
<div class="modal fade" id="modalFormAbsenDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAbsenDetail">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white">Form Absensi</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- id_absen untuk edit (kosong saat tambah) -->
                    <input type="hidden" name="id_absen" id="form_id_absen_detail" value="">
                    <input type="hidden" name="id_karyawan" id="form_id_karyawan_detail" value="">
                    <input type="hidden" name="tgl_absen" id="form_tgl_detail" value="">

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <select class="form-select" name="ket_absen" id="form_ket_detail">
                            <option value="">-- Pilih Keterangan --</option>
                            <option value="HADIR">HADIR</option>
                            <option value="OFF">SPL/OFF</option>
                            <option value="IZIN">IZIN</option>
                            <option value="SAKIT">SAKIT</option>
                            <option value="CUTI">CUTI</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jam Kerja</label>
                        <input type="number" step="0.1" class="form-control" name="jam_kerja" id="form_jk_detail" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rekap Kehadiran Harian -->
<div class="modal fade" id="modalKalenderAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fa fa-calendar"></i> Rekap Kehadiran — <span id="kalNama">-</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-2" id="kalPeriode">-</div>
                <div class="alert alert-info py-2 mb-3 small" id="kalSummary">Memuat...</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Nilai Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody id="kalBody">
                            <tr><td colspan="3" class="text-center">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    let currentIdKaryawan = null;

    document.addEventListener("DOMContentLoaded", () => {
        const tabelBody = document.querySelector("#tabelAbsensi tbody");
        const modalEl = document.getElementById("modalDetailAbsensi");
        const modal = new bootstrap.Modal(modalEl);
        const modalBody = document.getElementById("detailAbsensiBody");
        const filterBulan = document.getElementById("filterBulan");

        // Filter bulan (YYYY-MM) -> rentang tanggal 1 s/d akhir bulan
        function rangeBulan() {
            const ym = filterBulan.value;
            if (!ym) return null;
            const parts = ym.split("-");
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10);
            if (!y || !m) return null;
            const last = new Date(y, m, 0).getDate();
            const mm = String(m).padStart(2, "0");
            return {
                a: `${y}-${mm}-01`,
                b: `${y}-${mm}-${String(last).padStart(2, "0")}`
            };
        }
        const filterSite = document.getElementById("filterSite");
        const filterJabatan = document.getElementById("filterJabatan");

        // Event delegation untuk klik row
        tabelBody.addEventListener("click", function(e) {
            // Tombol kalender: buka kalender, jangan buka detail
            const btnCal = e.target.closest(".btnKalender");
            if (btnCal) {
                openKalender(btnCal.dataset.id);
                return;
            }

            const tr = e.target.closest("tr");
            if (!tr || !tr.dataset.id) return;

            currentIdKaryawan = tr.dataset.id;

            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p>Memuat detail absensi...</p>
                </div>
            `;
            modal.show();
            loadDetailAbsensi();
        });

        window.loadAbsensi = function() {
            const site = filterSite.value;
            const jabatan = filterJabatan.value;
            const range = rangeBulan();

            if (!site) return showToast("Pilih Site terlebih dahulu!", "info");
            if (!range) return showToast("Pilih Bulan terlebih dahulu!", "info");
            const tgl_a = range.a;
            const tgl_b = range.b;

            const spinner = document.getElementById("loadingSpinner");
            spinner.style.display = "block";

            const formData = new FormData();
            formData.append("tgl_a", tgl_a);
            formData.append("tgl_b", tgl_b);
            formData.append("site", site);
            formData.append("jabatan", jabatan);

            fetch("app/controller/get-data/get-absensi.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableAbsensi")) {
                        $(".MyTableAbsensi").DataTable().destroy();
                    }

                    tabelBody.innerHTML = html;
                    const dt = $(".MyTableAbsensi").DataTable({
                        responsive: true,
                        pageLength: 10,
                        destroy: true
                    });
                    // Terapkan lagi kata kunci pencarian khusus (Nama/NIK/ID) setelah reload
                    const kw = document.getElementById("searchKaryawan")?.value || "";
                    if (kw) dt.search(kw).draw();
                })
                .finally(() => {
                    spinner.style.display = "none";
                });
        };


        // Pencarian khusus Nama / NIK / ID (contoh: ipm-00090)
        document.getElementById("searchKaryawan").addEventListener("input", function() {
            if ($.fn.DataTable.isDataTable(".MyTableAbsensi")) {
                $(".MyTableAbsensi").DataTable().search(this.value).draw();
            }
        });

        // Auto load saat halaman terbuka jika filter lengkap
        if (filterSite.value && filterBulan.value) {
            loadAbsensi();
        }


        // Event Export Excel
        document.getElementById("btnExportExcel").addEventListener("click", function() {
            const site = filterSite.value;
            const jabatan = filterJabatan.value;
            const range = rangeBulan();

            if (!site) return showToast("Pilih Site terlebih dahulu sebelum export!", "info");
            if (!range) return showToast("Pilih Bulan terlebih dahulu sebelum export!", "info");
            const tgl_a = range.a;
            const tgl_b = range.b;

            // Arahkan ke file export dengan membawa parameter filter
            const exportUrl = `app/controller/export/export-absensi.php?site=${site}&jabatan=${jabatan}&tgl_a=${tgl_a}&tgl_b=${tgl_b}`;
            window.open(exportUrl, '_blank');
        });

        // Auto reload filter
        filterSite.addEventListener("change", loadAbsensi);
        filterJabatan.addEventListener("change", loadAbsensi);
        filterBulan.addEventListener("change", loadAbsensi);


        // ============ REKAP KEHADIRAN HARIAN (TABEL) ============
        const kalBody = document.getElementById("kalBody");
        const kalModal = new bootstrap.Modal(document.getElementById("modalKalenderAbsensi"));

        function fmtNilaiHadir(v) {
            return String(Math.round((+v || 0) * 100) / 100);
        }

        window.openKalender = function(idKaryawan) {
            document.getElementById("kalNama").textContent = "Memuat...";
            document.getElementById("kalPeriode").textContent = "-";
            document.getElementById("kalSummary").textContent = "Memuat data kehadiran...";
            kalBody.innerHTML = `<tr><td colspan="3" class="text-center"><div class="spinner-border text-primary"></div></td></tr>`;

            if (!idKaryawan) {
                showToast("Pilih karyawan terlebih dahulu!", "info");
                return;
            }
            // Jangan fetch bila filter bulan kosong (server menjawab "Parameter tidak lengkap").
            const range = rangeBulan();
            if (!range) {
                showToast("Pilih Bulan terlebih dahulu!", "info");
                return;
            }
            kalModal.show();

            const url = `app/controller/get-data/get-absensi-kalender.php?id_karyawan=${idKaryawan}&tgl_a=${range.a}&tgl_b=${range.b}`;
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.status !== "success") {
                        kalBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">${res.message || "Gagal memuat data"}</td></tr>`;
                        return;
                    }
                    renderKalender(res);
                })
                .catch(() => {
                    kalBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Terjadi kesalahan sistem.</td></tr>`;
                });
        };

        function renderKalender(res) {
            document.getElementById("kalNama").textContent = `${res.nama} (${res.nik})`;
            document.getElementById("kalPeriode").textContent = `Periode ${res.periode.dari} s/d ${res.periode.sampai} • ${res.jabatan}`;

            const t = res.totals;
            // Total nilai kehadiran = hari berbayar (hadir normal + SPL),
            // sama dengan angka yang dipakai rumus gaji.
            const totalHadir = Math.round(((+t.hadir_jam || 0) + (+t.spl_jam || 0)) * 100) / 100;
            document.getElementById("kalSummary").innerHTML =
                `Hadir: <b>${totalHadir}</b> • ` +
                `Mangkir: <b>${t.mangkir}</b> • Cuti: <b>${t.cuti}</b>`;

            let html = "";
            res.days.forEach((d, idx) => {
                const tahun = d.month_key.split("-")[0];
                html += `<tr>
                    <td class="text-center">${idx + 1}</td>
                    <td class="text-center">${d.day} ${d.month} ${tahun}</td>
                    <td class="text-center">${fmtNilaiHadir(d.jam)}</td>
                </tr>`;
            });

            kalBody.innerHTML = html;
        }

        // Reload Detail Absensi
        function loadDetailAbsensi() {            if (!currentIdKaryawan) return;
            const range = rangeBulan();
            if (!range) return;

            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary"></div>
                    <p>Memuat detail absensi...</p>
                </div>
            `;

            fetch(`app/controller/get-data/get-absensi-detail.php?id_karyawan=${currentIdKaryawan}&tgl_a=${range.a}&tgl_b=${range.b}`)
                .then(res => res.text())
                .then(html => {
                    modalBody.innerHTML = html;
                });
        }



        // FOR ACTION BUTTON IN DETAIL ABSENSI
        (function() {
            console.log("Script detail absensi loaded");

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

            // Inisialisasi modal form
            const modalForm = new bootstrap.Modal(
                document.getElementById("modalFormAbsenDetail")
            );

            const formAbsen = document.getElementById("formAbsenDetail");


            // Fungsi Update jam by keterangan 
            const ketSelectEl = document.getElementById("form_ket_detail");
            const jamEl = document.getElementById("form_jk_detail");

            function updateFieldsByKeterangan() {
                const ketSelect = ketSelectEl.value;

                if (["IZIN", "SAKIT", "CUTI"].includes(ketSelect)) {
                    jamEl.value = "";
                    jamEl.disabled = true;
                } else {
                    jamEl.disabled = false;
                }
            }

            ketSelectEl.addEventListener("change", updateFieldsByKeterangan);


            document.addEventListener("click", async function(e) {

                // TAMBAH
                const btnTambah = e.target.closest(".btnTambahDetail");
                if (btnTambah) {
                    const id_karyawan = btnTambah.dataset.id_karyawan;
                    const tgl = btnTambah.dataset.tgl;

                    document.getElementById("form_id_absen_detail").value = "";
                    document.getElementById("form_id_karyawan_detail").value = id_karyawan;
                    document.getElementById("form_tgl_detail").value = tgl;
                    document.getElementById("form_ket_detail").value = "";
                    document.getElementById("form_jk_detail").value = "";

                    modalForm.show();
                    return;
                }

                // EDIT
                const btnEdit = e.target.closest(".btnEditDetail");
                if (btnEdit) {
                    document.getElementById("form_id_absen_detail").value = btnEdit.dataset.id_absen;
                    document.getElementById("form_id_karyawan_detail").value = btnEdit.dataset.id_karyawan;
                    document.getElementById("form_tgl_detail").value = btnEdit.dataset.tgl;
                    document.getElementById("form_ket_detail").value = btnEdit.dataset.ket;
                    document.getElementById("form_jk_detail").value = btnEdit.dataset.jk;

                    modalForm.show();
                    return;
                }

                // HAPUS
                const btnHapus = e.target.closest(".btnHapusDetail");
                if (btnHapus) {
                    if (!(await confirmDialog("Yakin hapus data?"))) return;

                    const formData = new FormData();
                    formData.append("action", "delete");
                    formData.append("id_absen", btnHapus.dataset.id_absen);

                    fetch("app/controller/act-absen.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            showToast(res.msg, "success");
                            loadAbsensi();
                            loadDetailAbsensi();
                        });
                }

            });



            // ================= Submit Form =================
            document.getElementById("formAbsenDetail").addEventListener("submit", async function(e) {
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
                        modalForm.hide();
                        loadAbsensi();
                        loadDetailAbsensi();
                    }
                } catch (err) {
                    console.error(err);
                    showToast("Gagal menyimpan data!", "error");
                }
            });


        })();
    });
</script>