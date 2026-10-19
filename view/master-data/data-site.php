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

                    <label>Link Google Maps</label>
                    <input type="url" name="google_maps_url" class="form-control mb-1 maps-url-input"
                        placeholder="https://www.google.com/maps/place/..." autocomplete="off">
                    <small class="text-muted d-block mb-2">Tempel link lokasi Google Maps. Latitude dan longitude akan diambil otomatis.</small>
                    <div class="site-map-preview mb-3" hidden>
                        <iframe title="Preview lokasi site" class="w-100 rounded border" style="height: 220px;" loading="lazy"></iframe>
                        <small class="site-map-coordinates text-muted d-block mt-1"></small>
                    </div>

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

                    <label>Link Google Maps</label>
                    <input type="url" name="google_maps_url" class="form-control mb-1 maps-url-input"
                        placeholder="https://www.google.com/maps/place/..." autocomplete="off">
                    <small class="text-muted d-block mb-2">Tempel link lokasi Google Maps. Latitude dan longitude akan diambil otomatis.</small>
                    <div class="site-map-preview mb-3" hidden>
                        <iframe title="Preview lokasi site" class="w-100 rounded border" style="height: 220px;" loading="lazy"></iframe>
                        <small class="site-map-coordinates text-muted d-block mt-1"></small>
                    </div>

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

            function extractCoordinates(value) {
                const text = decodeURIComponent(String(value || ''));
                const patterns = [
                    /!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/i,
                    /@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i,
                    /(?:[?&](?:q|ll)=)(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i
                ];

                for (const pattern of patterns) {
                    const match = text.match(pattern);
                    if (match) {
                        const latitude = Number(match[1]);
                        const longitude = Number(match[2]);
                        if (latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180) {
                            return { latitude, longitude };
                        }
                    }
                }
                return null;
            }

            function updateMapPreview(form) {
                const input = form.querySelector("input[name=google_maps_url]");
                const preview = form.querySelector(".site-map-preview");
                const frame = preview.querySelector("iframe");
                const coordinates = preview.querySelector(".site-map-coordinates");
                const point = extractCoordinates(input.value);

                if (!point) {
                    preview.hidden = true;
                    frame.removeAttribute("src");
                    coordinates.textContent = input.value.trim() ? "Koordinat tidak ditemukan dari link ini." : "";
                    return;
                }

                const lat = point.latitude.toFixed(7);
                const lng = point.longitude.toFixed(7);
                frame.src = `https://www.google.com/maps?q=${lat},${lng}&z=17&output=embed`;
                coordinates.textContent = `Latitude: ${lat} | Longitude: ${lng}`;
                preview.hidden = false;
            }

            document.querySelectorAll(".maps-url-input").forEach(input => {
                input.addEventListener("input", () => updateMapPreview(input.form));
                input.addEventListener("change", () => updateMapPreview(input.form));
            });

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
                            updateMapPreview(formTambah);
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
                    formEdit.querySelector("input[name=google_maps_url]").value = btn.lat && btn.lng
                        ? `https://www.google.com/maps?q=${btn.lat},${btn.lng}`
                        : "";
                    updateMapPreview(formEdit);
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
