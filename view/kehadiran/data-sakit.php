<?php
// data-sakit.php
?>
<div class="container-xxl mt-3">

    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Data Kehadiran /</span> Sakit
    </h4>

    <div class="pb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

            <div>
                <button class="btn btn-sm btn-success" id="btnTambahSakit">
                    <i class="fa fa-plus"></i> Tambah Sakit
                </button>
            </div>

            <div class="d-flex align-items-center gap-2" style="max-width: 600px;">
                <select id="filterSite" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site">
                    <option value="">-- Pilih Site --</option>
                    <?php foreach (data_site() as $d): ?>
                        <option value="<?= $d['id_site'] ?>"><?= $d['nama_site'] ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="date" id="tglAwal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" data-bs-toggle="tooltip" title="Pilih tanggal untuk filter data sakit">
            </div>

        </div>
    </div>

    <div class="card">
        <h5 class="card-header"><i class="fa fa-table"></i> Data Sakit</h5>
        <div class="card-body">

            <div class="text-center" id="loadingSpinner" style="display:none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat data sakit...</span>
                </div>
                <p class="mt-2">Memuat data sakit...</p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped MyTableSakit" id="tabelSakit" width="100%">
                    <thead class="table-info">
                        <tr>
                            <th class="text-center"><i class="fa fa-hashtag"></i> No</th>
                            <th class="text-center"><i class="fa fa-user"></i> Nama Lengkap</th>
                            <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                            <th class="text-center"><i class="fa fa-map-marker"></i> Site</th>
                            <th class="text-center"><i class="fa fa-calendar"></i> Tanggal</th>
                            <!-- <th class="text-center"><i class="fa fa-calendar"></i> Tgl Akhir Sakit</th> -->
                            <th class="text-center"><i class="fa fa-building"></i> Ket</th>
                            <th class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<!-- MODAL TAMBAH / EDIT -->
<div class="modal fade" id="modalSakit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formSakit" class="modal-content">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_sakit" id="formIdSakit" value="">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalSakitTitle">Tambah Sakit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 form-group mb-2">
                        <label class="form-label">Site</label>
                        <select name="id_site" id="formSite" class="form-select" required>
                            <option value="">-- Pilih Site --</option>
                            <?php foreach (data_site() as $s): ?>
                                <option value="<?= $s['id_site'] ?>"><?= $s['nama_site'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 form-group mb-2">
                        <label class="form-label">Jabatan</label>
                        <select name="id_jabatan" id="formJabatan" class="form-select" required disabled>
                            <option value="">-- Pilih Jabatan --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group mb-2">
                        <label class="form-label">Karyawan</label>
                        <select name="id_karyawan" id="formKaryawan" class="form-select" required disabled>
                            <option value="">-- Pilih Karyawan --</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label class="form-label">Tanggal Awal</label>
                        <input type="date" name="tgl_awal_sakit" id="formTglAwal" class="form-control" required>
                    </div>

                    <div class="col-md-6 form-group mb-2">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir_sakit" id="formTglAkhir" class="form-control" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="ket_sakit" id="formKet" class="form-control" placeholder="Contoh: Sakit Magh / Pulando saja">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success" type="submit" id="formSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- KONFIRM HAPUS -->
<div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="formDelete" class="modal-content">
            <input type="hidden" name="id_sakit" id="deleteId">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Hapus Sakit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin menghapus data sakit ini?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-danger" type="submit">Hapus</button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tbody = document.querySelector("#tabelSakit tbody");
        const filterSite = document.getElementById("filterSite");
        const tglAwal = document.getElementById("tglAwal");
        const spinner = document.getElementById("loadingSpinner");

        const formSite = document.getElementById("formSite");
        const formJabatan = document.getElementById("formJabatan");
        const formKaryawan = document.getElementById("formKaryawan");

        // Tooltips
        [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].forEach(el => new bootstrap.Tooltip(el));

        // ================= LOAD TABEL =================
        window.loadSakit = async function() {
            const site = filterSite.value.trim();
            const tgl = tglAwal.value.trim();

            if (!site) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Pilih Site terlebih dahulu!</td></tr>`;
                return;
            }

            if ($.fn.DataTable.isDataTable("#tabelSakit")) {
                $("#tabelSakit").DataTable().clear().destroy();
            }

            spinner.style.display = "block";
            tbody.innerHTML = `<tr><td colspan="8" class="text-center">Memuat data...</td></tr>`;

            try {
                const res = await fetch("app/controller/get-data/get-sakit.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        site,
                        tgl
                    })
                });
                const html = await res.text();
                spinner.style.display = "none";
                tbody.innerHTML = html;

                const valid = [...tbody.querySelectorAll("tr")].some(tr => tr.querySelectorAll("td").length === 8);
                if (valid) {
                    $("#tabelSakit").DataTable({
                        autoWidth: false,
                        responsive: true,
                        pageLength: 10,
                        destroy: true,
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_",
                            info: "Menampilkan _START_ - _END_ dari _TOTAL_"
                        },
                        drawCallback: function() {
                            this.api().columns.adjust();
                        }
                    });
                }
            } catch (err) {
                spinner.style.display = "none";
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Gagal memuat data sakit!</td></tr>`;
                toastr.error("Terjadi kesalahan ketika memuat data sakit.");
                console.error(err);
            }
        };

        // ================= TAMBAH MODAL =================
        document.getElementById("btnTambahSakit").addEventListener("click", () => {
            document.getElementById("formAction").value = "add";
            document.getElementById("modalSakitTitle").textContent = "Tambah Sakit";
            document.getElementById("formIdSakit").value = "";
            formSite.value = "";
            formJabatan.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
            formJabatan.disabled = true;
            formKaryawan.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
            formKaryawan.disabled = true;
            document.getElementById("formTglAwal").value = "";
            document.getElementById("formTglAkhir").value = "";
            document.getElementById("formKet").value = "";
            new bootstrap.Modal(document.getElementById("modalSakit")).show();
        });

        // ================= SITE → JABATAN → KARYAWAN =================
        formSite.addEventListener("change", async () => {
            const site = formSite.value;
            formJabatan.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
            formKaryawan.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
            formJabatan.disabled = true;
            formKaryawan.disabled = true;

            if (!site) return;

            try {
                const res = await fetch("app/controller/get-data/get-jabatan-by-site.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        site
                    })
                });
                const data = await res.json();
                data.forEach(j => {
                    const opt = document.createElement("option");
                    opt.value = j.id_jabatan;
                    opt.textContent = j.nama_jabatan;
                    formJabatan.appendChild(opt);
                });
                formJabatan.disabled = false;
            } catch (err) {
                console.error(err);
            }
        });

        formJabatan.addEventListener("change", async () => {
            const site = formSite.value;
            const jabatan = formJabatan.value;
            formKaryawan.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
            formKaryawan.disabled = true;

            if (!site || !jabatan) return;

            try {
                const res = await fetch("app/controller/get-data/get-karyawan-by-site-jabatan.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        site,
                        jabatan
                    })
                });
                const data = await res.json();
                data.forEach(k => {
                    const opt = document.createElement("option");
                    opt.value = k.id_karyawan;
                    opt.textContent = k.nama_lengkap;
                    formKaryawan.appendChild(opt);
                });
                formKaryawan.disabled = false;
            } catch (err) {
                console.error(err);
            }
        });

        // ================= EDIT & DELETE =================
        document.addEventListener("click", e => {
            if (e.target.closest(".btnEditSakit")) {
                const btn = e.target.closest(".btnEditSakit");
                const id = btn.dataset.id;
                const site = btn.dataset.site;
                const jabatan = btn.dataset.jabatan;
                const karyawan = btn.dataset.karyawan;
                const awal = btn.dataset.awal;
                const akhir = btn.dataset.akhir;
                const ket = btn.dataset.ket;

                document.getElementById("formAction").value = "edit";
                document.getElementById("modalSakitTitle").textContent = "Edit Sakit";
                document.getElementById("formIdSakit").value = id;
                formSite.value = site;
                formSite.dispatchEvent(new Event('change')); // load jabatan
                setTimeout(() => {
                    formJabatan.value = jabatan;
                    formJabatan.dispatchEvent(new Event('change')); // load karyawan
                    setTimeout(() => formKaryawan.value = karyawan, 300);
                }, 300);
                document.getElementById("formTglAwal").value = awal;
                document.getElementById("formTglAkhir").value = akhir;
                document.getElementById("formKet").value = ket || "";
                new bootstrap.Modal(document.getElementById("modalSakit")).show();
            }

            if (e.target.closest(".btnDeleteSakit")) {
                const btn = e.target.closest(".btnDeleteSakit");
                document.getElementById("deleteId").value = btn.dataset.id;
                new bootstrap.Modal(document.getElementById("modalDelete")).show();
            }
        });

        // ================= SUBMIT FORM =================
        document.getElementById("formSakit").addEventListener("submit", async ev => {
            ev.preventDefault();
            const fd = new FormData(ev.target);
            const awal = fd.get("tgl_awal_sakit");
            const akhir = fd.get("tgl_akhir_sakit");
            if (!awal || !akhir || new Date(awal) > new Date(akhir)) {
                return showToast("Periksa tanggal: Tanggal awal harus <= tanggal akhir", "error");
            }

            try {
                const res = await fetch("app/controller/act-sakit.php", {
                    method: "POST",
                    body: fd
                });
                const j = await res.json();
                if (j.status === "success") {
                    showToast(j.message || "Berhasil", "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalSakit")).hide();
                    loadSakit();
                } else {
                    showToast(j.message || "Gagal menyimpan", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Terjadi kesalahan server", "error");
            }
        });

        // ================= DELETE FORM =================
        document.getElementById("formDelete").addEventListener("submit", async ev => {
            ev.preventDefault();
            const id = document.getElementById("deleteId").value;
            const fd = new URLSearchParams({
                action: "delete",
                id_sakit: id
            });
            try {
                const res = await fetch("app/controller/act-sakit.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: fd
                });
                const j = await res.json();
                if (j.status === "success") {
                    showToast(j.message || "Terhapus", "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalDelete")).hide();
                    loadSakit();
                } else {
                    showToast(j.message || "Gagal hapus", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Terjadi kesalahan server", "error");
            }
        });


        // ================= FILTER =================
        filterSite.addEventListener("change", loadSakit);
        tglAwal.addEventListener("change", loadSakit);

        loadSakit();
    });
</script>

<style>
    #tabelSakit th,
    #tabelSakit td {
        white-space: nowrap;
    }

    .table-fade {
        animation: fadeIn .35s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(6px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }
</style>