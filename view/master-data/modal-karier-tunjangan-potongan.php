<?php
// Partial: modal Tunjangan & Potongan untuk halaman karier.
// Dipakai oleh view/master-data/data-karier.php dan view/master-akses/import-karier.php
// agar tombol Tunjangan/Potongan membuka modal tanpa redirect halaman.
?>
<!-- Modal Tunjangan per Karier -->
<div class="modal fade" id="modalKarierTunjangan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fa fa-money-bill"></i> Tunjangan - <span id="labelTunjanganNama">-</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formModalTunjangan" class="row g-2 align-items-end">
                    <input type="hidden" name="id_tunjangan" id="modalTunjanganId">
                    <input type="hidden" name="id_karier" id="modalTunjanganKarierEnc">
                    <div class="col-md-6">
                        <label class="form-label">Nama Tunjangan</label>
                        <select name="nama_tunjangan" id="modalTunjanganNama" class="form-select" required>
                            <option value="">-- Pilih Tunjangan --</option>
                            <option value="Tunjangan Daerah">Tunjangan Daerah</option>
                            <option value="Tunjangan Hidup">Tunjangan Hidup</option>
                            <option value="Tunjangan Makan">Tunjangan Makan</option>
                            <option value="Tunjangan Skill">Tunjangan Skill</option>
                            <option value="Bonus Kehadiran">Bonus Kehadiran</option>
                            <option value="Tunjangan Posisi">Tunjangan Posisi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah_tunjangan" id="modalTunjanganJumlah" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-success" id="btnModalTunjanganSubmit">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="btnModalTunjanganCancel">Batal</button>
                    </div>
                </form>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm w-100">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" style="width:5%">No</th>
                                <th class="text-center">Nama Tunjangan</th>
                                <th class="text-center">Jumlah (Rp)</th>
                                <th class="text-center" style="width:15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyModalTunjangan"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Potongan per Karier -->
<div class="modal fade" id="modalKarierPotongan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fa fa-scissors"></i> Potongan - <span id="labelPotonganNama">-</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formModalPotongan" class="row g-2 align-items-end">
                    <input type="hidden" name="id_potongan" id="modalPotonganId">
                    <input type="hidden" name="id_karier" id="modalPotonganKarierEnc">
                    <div class="col-md-6">
                        <label class="form-label">Nama Potongan</label>
                        <input type="text" name="jenis_potongan" id="modalPotonganNama" class="form-control" placeholder="Contoh: BPJS" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah_potongan" id="modalPotonganJumlah" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-success" id="btnModalPotonganSubmit">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="btnModalPotonganCancel">Batal</button>
                    </div>
                </form>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm w-100">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" style="width:5%">No</th>
                                <th class="text-center">Nama Potongan</th>
                                <th class="text-center">Jumlah (Rp)</th>
                                <th class="text-center" style="width:15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyModalPotongan"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload File Kontrak per Karier -->
<div class="modal fade" id="modalKarierKontrak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formModalKontrak" class="modal-content" enctype="multipart/form-data">
            <input type="hidden" name="id_karier" id="modalKontrakKarierId">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title text-white">
                    <i class="fa fa-file-contract"></i> File Kontrak - <span id="labelKontrakNama">-</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="kontrakFileSaatIni" class="alert alert-info py-2"></div>
                <div class="mb-3">
                    <label class="form-label" for="modalKontrakFile">Pilih File (PDF/JPG/PNG, maks 5MB)</label>
                    <input type="file" name="file_kontrak" id="modalKontrakFile" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto d-none" id="btnModalKontrakHapus">
                    <i class="fa fa-trash"></i> Hapus File
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    // Guard: partial ini di-include di 2 halaman berbeda, tapi script hanya boleh init sekali per page load.
    if (window.__karierTpModalInit) return;
    window.__karierTpModalInit = true;

    function resetTunjanganForm() {
        document.getElementById("modalTunjanganId").value = "";
        document.getElementById("modalTunjanganNama").value = "";
        document.getElementById("modalTunjanganJumlah").value = "";
        document.getElementById("btnModalTunjanganSubmit").innerHTML = '<i class="fa fa-save"></i> Simpan';
        document.getElementById("btnModalTunjanganCancel").classList.add("d-none");
    }

    function resetPotonganForm() {
        document.getElementById("modalPotonganId").value = "";
        document.getElementById("modalPotonganNama").value = "";
        document.getElementById("modalPotonganJumlah").value = "";
        document.getElementById("btnModalPotonganSubmit").innerHTML = '<i class="fa fa-save"></i> Simpan';
        document.getElementById("btnModalPotonganCancel").classList.add("d-none");
    }

    function loadModalTunjangan() {
        const enc = document.getElementById("modalTunjanganKarierEnc").value;
        if (!enc) return;
        fetch("app/controller/get-data/get-tunjangan.php?id=" + encodeURIComponent(enc))
            .then(res => res.text())
            .then(html => {
                document.getElementById("tbodyModalTunjangan").innerHTML = html;
            })
            .catch(err => console.error("Gagal memuat tunjangan:", err));
    }

    function loadModalPotongan() {
        const enc = document.getElementById("modalPotonganKarierEnc").value;
        if (!enc) return;
        fetch("app/controller/get-data/get-potongan-tetap.php?id=" + encodeURIComponent(enc))
            .then(res => res.text())
            .then(html => {
                document.getElementById("tbodyModalPotongan").innerHTML = html;
            })
            .catch(err => console.error("Gagal memuat potongan:", err));
    }

    window.openModalTunjangan = function(encId, nama) {
        document.getElementById("modalTunjanganKarierEnc").value = encId || "";
        document.getElementById("labelTunjanganNama").textContent = nama || "-";
        resetTunjanganForm();
        loadModalTunjangan();
        new bootstrap.Modal(document.getElementById("modalKarierTunjangan")).show();
    };

    window.openModalPotongan = function(encId, nama) {
        document.getElementById("modalPotonganKarierEnc").value = encId || "";
        document.getElementById("labelPotonganNama").textContent = nama || "-";
        resetPotonganForm();
        loadModalPotongan();
        new bootstrap.Modal(document.getElementById("modalKarierPotongan")).show();
    };

    function refreshKontrakInfo() {
        const file = document.getElementById("modalKontrakKarierId").dataset.file || "";
        const info = document.getElementById("kontrakFileSaatIni");
        const btnHapus = document.getElementById("btnModalKontrakHapus");
        if (file) {
            info.innerHTML = 'File saat ini: <a href="app/uploads/kontrak/' + encodeURIComponent(file) + '" target="_blank">' + file + '</a>';
            btnHapus.classList.remove("d-none");
        } else {
            info.textContent = "Belum ada file kontrak untuk karier ini.";
            btnHapus.classList.add("d-none");
        }
    }

    window.openModalKontrak = function(idKarier, nama, file) {
        const hidden = document.getElementById("modalKontrakKarierId");
        hidden.value = idKarier || "";
        hidden.dataset.file = file || "";
        document.getElementById("labelKontrakNama").textContent = nama || "-";
        document.getElementById("formModalKontrak").reset();
        // reset() mengosongkan hidden juga, kembalikan nilainya
        hidden.value = idKarier || "";
        refreshKontrakInfo();
        new bootstrap.Modal(document.getElementById("modalKarierKontrak")).show();
    };

    function reloadKarierTable() {
        if (typeof window.reloadKarierTable === "function") window.reloadKarierTable();
    }

    document.addEventListener("click", e => {
        const btnT = e.target.closest(".btnTunjangan");
        if (btnT) {
            window.openModalTunjangan(btnT.dataset.idKarierEnc, btnT.dataset.nama);
            return;
        }
        const btnP = e.target.closest(".btnPotongan");
        if (btnP) {
            window.openModalPotongan(btnP.dataset.idKarierEnc, btnP.dataset.nama);
            return;
        }
        const btnK = e.target.closest(".btnUploadKontrak");
        if (btnK) {
            window.openModalKontrak(btnK.dataset.id, btnK.dataset.nama, btnK.dataset.file);
            return;
        }

        // Edit tunjangan (baris di dalam modal, HTML dari get-tunjangan.php)
        const btnEditT = e.target.closest("#tbodyModalTunjangan .btnEditTunjangan");
        if (btnEditT) {
            document.getElementById("modalTunjanganId").value = btnEditT.dataset.id || "";
            document.getElementById("modalTunjanganNama").value = btnEditT.dataset.nama || "";
            document.getElementById("modalTunjanganJumlah").value = btnEditT.dataset.jumlah || "";
            document.getElementById("btnModalTunjanganSubmit").innerHTML = '<i class="fa fa-save"></i> Update';
            document.getElementById("btnModalTunjanganCancel").classList.remove("d-none");
            return;
        }

        // Edit potongan (baris di dalam modal, HTML dari get-potongan-tetap.php)
        const btnEditP = e.target.closest("#tbodyModalPotongan .btnEditPotongan");
        if (btnEditP) {
            document.getElementById("modalPotonganId").value = btnEditP.dataset.id || "";
            document.getElementById("modalPotonganNama").value = btnEditP.dataset.nama || "";
            document.getElementById("modalPotonganJumlah").value = btnEditP.dataset.jumlah || "";
            document.getElementById("btnModalPotonganSubmit").innerHTML = '<i class="fa fa-save"></i> Update';
            document.getElementById("btnModalPotonganCancel").classList.remove("d-none");
            return;
        }

        // Hapus tunjangan
        const btnDelT = e.target.closest("#tbodyModalTunjangan .btnDeleteTunjangan");
        if (btnDelT) {
            (async () => {
                if (await confirmDialog("Yakin ingin menghapus tunjangan ini?")) {
                    const fd = new FormData();
                    fd.append("action", "delete");
                    fd.append("id_tunjangan", btnDelT.dataset.id);
                    fetch("app/controller/act-tunjangan.php", { method: "POST", body: fd })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("Tunjangan berhasil dihapus!", "success");
                                loadModalTunjangan();
                            } else {
                                showToast("Gagal: " + (res.message || "hapus gagal"), "error");
                            }
                        })
                        .catch(err => console.error("Error:", err));
                }
            })();
            return;
        }

        // Hapus potongan
        const btnDelP = e.target.closest("#tbodyModalPotongan .btnDeletePotongan");
        if (btnDelP) {
            (async () => {
                if (await confirmDialog("Yakin ingin menghapus potongan ini?")) {
                    const fd = new FormData();
                    fd.append("action", "delete");
                    fd.append("id_potongan", btnDelP.dataset.id);
                    fetch("app/controller/act-potongan-tetap.php", { method: "POST", body: fd })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("Potongan berhasil dihapus!", "success");
                                loadModalPotongan();
                            } else {
                                showToast("Gagal menghapus potongan", "error");
                            }
                        })
                        .catch(err => console.error("Error:", err));
                }
            })();
            return;
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
        const formT = document.getElementById("formModalTunjangan");
        if (formT && !formT.dataset.bound) {
            formT.dataset.bound = "1";
            formT.addEventListener("submit", function(e) {
                e.preventDefault();
                const isEdit = document.getElementById("modalTunjanganId").value !== "";
                const fd = new FormData(this);
                fd.append("action", isEdit ? "edit" : "add");
                // Untuk add, backend butuh id_karier (sudah ada di hidden input).
                // Untuk edit, backend hanya butuh id_tunjangan (id_karier diabaikan).
                fetch("app/controller/act-tunjangan.php", { method: "POST", body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast(isEdit ? "Tunjangan berhasil diupdate!" : "Tunjangan berhasil ditambah!", "success");
                            resetTunjanganForm();
                            loadModalTunjangan();
                        } else {
                            showToast("Gagal: " + (res.message || "simpan gagal"), "error");
                        }
                    })
                    .catch(err => console.error("Error:", err));
            });
        }

        const formP = document.getElementById("formModalPotongan");
        if (formP && !formP.dataset.bound) {
            formP.dataset.bound = "1";
            formP.addEventListener("submit", function(e) {
                e.preventDefault();
                const isEdit = document.getElementById("modalPotonganId").value !== "";
                const fd = new FormData(this);
                fd.append("action", isEdit ? "edit" : "add");
                fetch("app/controller/act-potongan-tetap.php", { method: "POST", body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast(isEdit ? "Potongan berhasil diupdate!" : "Potongan berhasil ditambah!", "success");
                            resetPotonganForm();
                            loadModalPotongan();
                        } else {
                            showToast("Gagal menyimpan potongan", "error");
                        }
                    })
                    .catch(err => console.error("Error:", err));
            });
        }

        const cancelT = document.getElementById("btnModalTunjanganCancel");
        if (cancelT) cancelT.addEventListener("click", resetTunjanganForm);

        const cancelP = document.getElementById("btnModalPotonganCancel");
        if (cancelP) cancelP.addEventListener("click", resetPotonganForm);

        const formK = document.getElementById("formModalKontrak");
        if (formK && !formK.dataset.bound) {
            formK.dataset.bound = "1";
            formK.addEventListener("submit", function(e) {
                e.preventDefault();
                const fileInput = document.getElementById("modalKontrakFile");
                if (!fileInput.value) {
                    showToast("Pilih file kontrak terlebih dahulu.", "warning");
                    return;
                }
                const fd = new FormData(this);
                fd.append("action", "upload");
                fetch("app/controller/act-kontrak.php", { method: "POST", body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("File kontrak berhasil diunggah!", "success");
                            document.getElementById("modalKontrakKarierId").dataset.file = res.file || "";
                            refreshKontrakInfo();
                            reloadKarierTable();
                            bootstrap.Modal.getInstance(document.getElementById("modalKarierKontrak")).hide();
                        } else {
                            showToast("Gagal: " + (res.message || "upload gagal"), "error");
                        }
                    })
                    .catch(err => console.error("Error:", err));
            });
        }

        const btnHapusK = document.getElementById("btnModalKontrakHapus");
        if (btnHapusK && !btnHapusK.dataset.bound) {
            btnHapusK.dataset.bound = "1";
            btnHapusK.addEventListener("click", async () => {
                const idKarier = document.getElementById("modalKontrakKarierId").value;
                if (!idKarier) return;
                if (await confirmDialog("Yakin ingin menghapus file kontrak ini?")) {
                    const fd = new FormData();
                    fd.append("action", "delete");
                    fd.append("id_karier", idKarier);
                    fetch("app/controller/act-kontrak.php", { method: "POST", body: fd })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("File kontrak berhasil dihapus!", "success");
                                document.getElementById("modalKontrakKarierId").dataset.file = "";
                                refreshKontrakInfo();
                                reloadKarierTable();
                            } else {
                                showToast("Gagal: " + (res.message || "hapus gagal"), "error");
                            }
                        })
                        .catch(err => console.error("Error:", err));
                }
            });
        }
    });
})();
</script>
