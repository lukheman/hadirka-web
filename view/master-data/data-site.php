<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Site
        </h4>

        <div class="alert alert-info py-2" role="alert">
            <i class="bx bx-map-pin me-1"></i>
            Isi <strong>titik koordinat</strong> site agar karyawan dapat absen lewat aplikasi mobile (GPS + selfie).
            Site tanpa koordinat tidak akan muncul di aplikasi.
        </div>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSite">
                <i class="fa fa-plus"></i> Tambah Site
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Daftar Site</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTable">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Site</th>
                                <th>Kode</th>
                                <th>Lokasi</th>
                                <th>Titik Geofence</th>
                                <th class="text-center">Aplikasi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodySite"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahSite">
        <div class="modal-dialog">
            <form id="formTambahSite" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Site</label>
                    <input type="text" name="nama_site" class="form-control mb-3" required>

                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control mb-3" required>

                    <label>Kode Lokasi <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="kode_lokasi" class="form-control mb-3" placeholder="Contoh: TJS-01">

                    <div class="row">
                        <div class="col-6">
                            <label>Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control mb-1" placeholder="-4.123456">
                        </div>
                        <div class="col-6">
                            <label>Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control mb-1" placeholder="121.123456">
                        </div>
                    </div>
                    <small class="text-muted d-block mb-3">Titik koordinat kantor (untuk validasi radius absen aplikasi).</small>

                    <label>Radius Absen (meter)</label>
                    <input type="number" step="any" name="radius_meter" class="form-control mb-3" value="80">

                    <label>Deskripsi Lokasi <small class="text-muted">(opsional)</small></label>
                    <textarea name="deskripsi_lokasi" class="form-control mb-3" rows="2" placeholder="Patokan / catatan lokasi"></textarea>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="aktifTambah" checked>
                        <label class="form-check-label" for="aktifTambah">Aktif (dikirim ke aplikasi mobile)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditSite">
        <div class="modal-dialog">
            <form id="formEditSite" class="modal-content">
                <input type="hidden" name="id_site">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Site</label>
                    <input type="text" name="nama_site" class="form-control mb-3" required>

                    <label>Lokasi</label>
                    <input type="text" name="lokasi" class="form-control mb-3" required>

                    <label>Kode Lokasi <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="kode_lokasi" class="form-control mb-3" placeholder="Contoh: TJS-01">

                    <div class="row">
                        <div class="col-6">
                            <label>Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control mb-1" placeholder="-4.123456">
                        </div>
                        <div class="col-6">
                            <label>Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control mb-1" placeholder="121.123456">
                        </div>
                    </div>
                    <small class="text-muted d-block mb-3">Titik koordinat kantor (untuk validasi radius absen aplikasi).</small>

                    <label>Radius Absen (meter)</label>
                    <input type="number" step="any" name="radius_meter" class="form-control mb-3" value="80">

                    <label>Deskripsi Lokasi <small class="text-muted">(opsional)</small></label>
                    <textarea name="deskripsi_lokasi" class="form-control mb-3" rows="2" placeholder="Patokan / catatan lokasi"></textarea>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="aktifEdit">
                        <label class="form-check-label" for="aktifEdit">Aktif (dikirim ke aplikasi mobile)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Edit</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tbody = document.getElementById("tbodySite");
            const formTambah = document.getElementById("formTambahSite");
            const formEdit = document.getElementById("formEditSite");

            // === LOAD DATA ===
            function loadSite() {
                fetch("app/controller/get-data/get-site.php")
                    .then(res => res.text())
                    .then(html => {
                        if ($.fn.DataTable.isDataTable(".MyTable")) {
                            $(".MyTable").DataTable().destroy();
                        }

                        tbody.innerHTML = html;

                        $(".MyTable").DataTable({
                            responsive: true,
                            pageLength: 10
                        });
                    });
            }

            // === TAMBAH ===
            formTambah.addEventListener("submit", e => {
                e.preventDefault();
                let fd = new FormData(formTambah);
                fd.append("action", "add");
                // Checkbox tidak terkirim saat tidak dicentang -> normalisasi di server ke 0
                if (!formTambah.querySelector("input[name=is_aktif]").checked) fd.set("is_aktif", "0");

                fetch("app/controller/act-site.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil menambah site!", "success");
                            bootstrap.Modal.getInstance(document.getElementById("modalTambahSite")).hide();
                            formTambah.reset();
                            loadSite();
                        } else {
                            showToast(res.msg, "error");
                        }
                    });
            });

            // === EDIT ===
            formEdit.addEventListener("submit", e => {
                e.preventDefault();
                let fd = new FormData(formEdit);
                fd.append("action", "edit");
                if (!formEdit.querySelector("input[name=is_aktif]").checked) fd.set("is_aktif", "0");

                fetch("app/controller/act-site.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil update!", "success");
                            bootstrap.Modal.getInstance(document.getElementById("modalEditSite")).hide();
                            loadSite();
                        } else {
                            showToast(res.msg, "error");
                        }
                    });
            });

            // === OPEN MODAL EDIT ===
            document.addEventListener("click", async (e) => {
                if (e.target.classList.contains("btnEdit")) {
                    let btn = e.target.dataset;

                    formEdit.querySelector("input[name=id_site]").value = btn.id;
                    formEdit.querySelector("input[name=nama_site]").value = btn.nama;
                    formEdit.querySelector("input[name=lokasi]").value = btn.lokasi;
                    formEdit.querySelector("input[name=kode_lokasi]").value = btn.kode || "";
                    formEdit.querySelector("input[name=latitude]").value = btn.lat || "";
                    formEdit.querySelector("input[name=longitude]").value = btn.lng || "";
                    formEdit.querySelector("input[name=radius_meter]").value = btn.radius || 80;
                    formEdit.querySelector("textarea[name=deskripsi_lokasi]").value = btn.deskripsi || "";
                    formEdit.querySelector("input[name=is_aktif]").checked = btn.aktif === "1";

                    new bootstrap.Modal(document.getElementById("modalEditSite")).show();
                }

                if (e.target.classList.contains("btnDelete")) {
                    let id = e.target.dataset.id;

                    if (await confirmDialog("Yakin menghapus site?")) {
                        fetch("app/controller/act-site.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: "action=delete&id_site=" + id
                            })
                            .then(res => res.json())
                            .then(res => {
                                if (res.status === "success") {
                                    showToast("Berhasil menghapus!", "success");
                                    loadSite();
                                } else {
                                    showToast(res.msg, "error");
                                }
                            });
                    }
                }
            });

            // === INIT ===
            loadSite();
        });
    </script>
