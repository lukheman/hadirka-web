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
    });
})();
</script>
