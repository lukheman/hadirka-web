<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Manajemen /</span> Template Kontrak
        </h4>

        <div class="card mt-3">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Daftar Template Kontrak
                <button class="btn btn-sm btn-primary" id="btnTambahTemplate">
                    <i class="fas fa-upload"></i> Upload Template
                </button>
            </h5>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Satu template bisa berlaku untuk <strong>beberapa jabatan</strong>
                    sekaligus. File harus Word (.doc/.docx), maksimal 2 MB.
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableTemplate">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" style="width: 5%;"><i class="fa fa-hashtag"></i> No</th>
                                <th class="text-center"><i class="fa fa-file-contract"></i> Nama Kontrak</th>
                                <th class="text-center"><i class="fa fa-briefcase"></i> Berlaku Untuk</th>
                                <th class="text-center"><i class="fa fa-money-bill"></i> Nilai Kontrak</th>
                                <th class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTemplate">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUploadTemplate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="formUploadTemplate" class="modal-content" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fas fa-upload"></i> Upload Template Kontrak</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs" id="tabUploadTemplate" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-up-template-tab" data-bs-toggle="tab" data-bs-target="#tab-up-template" type="button" role="tab" aria-controls="tab-up-template" aria-selected="true">Template</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-up-nilai-tab" data-bs-toggle="tab" data-bs-target="#tab-up-nilai" type="button" role="tab" aria-controls="tab-up-nilai" aria-selected="false">Nilai Kontrak</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-up-tunjangan-tab" data-bs-toggle="tab" data-bs-target="#tab-up-tunjangan" type="button" role="tab" aria-controls="tab-up-tunjangan" aria-selected="false">Tunjangan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-up-potongan-tab" data-bs-toggle="tab" data-bs-target="#tab-up-potongan" type="button" role="tab" aria-controls="tab-up-potongan" aria-selected="false">Potongan</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tab-up-template" role="tabpanel" aria-labelledby="tab-up-template-tab">
                    <div class="mb-3">
                        <label class="form-label">Nama Kontrak <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kontrak" class="form-control" maxlength="150" placeholder="cth: Kontrak Kerja Operator 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Word <span class="text-danger">*</span></label>
                        <input type="file" name="file_pdf" class="form-control" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <small class="text-muted">Format .doc/.docx, maksimal 2 MB.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Berlaku Untuk Jabatan <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="checkAllJabatan">
                            <label class="form-check-label fw-bold" for="checkAllJabatan">Pilih Semua</label>
                        </div>
                        <div id="listJabatan" class="border rounded p-3" style="max-height: 260px; overflow-y: auto;">
                            <span class="text-muted">Memuat jabatan...</span>
                        </div>
                    </div>
                        </div>
                        <div class="tab-pane fade" id="tab-up-nilai" role="tabpanel" aria-labelledby="tab-up-nilai-tab">
                    <label class="form-label fw-bold">Nilai Kontrak</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="gaji_pokok" class="form-control nominal-mask" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upah Lembur</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="upah_lembur" class="form-control nominal-mask" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Potongan Izin</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="potongan_izin" class="form-control nominal-mask" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Potongan Mangkir</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="potongan_mangkir" class="form-control nominal-mask" value="0">
                            </div>
                        </div>
                    </div>
                        </div>
                        <div class="tab-pane fade" id="tab-up-tunjangan" role="tabpanel" aria-labelledby="tab-up-tunjangan-tab">
                            <div class="alert alert-info py-2">
                                <i class="fa fa-info-circle"></i> Opsional — baris kosong tidak disimpan.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm w-100">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Nama Tunjangan</th>
                                            <th style="width:18%">Jenis</th>
                                            <th style="width:22%">Jumlah (Rp)</th>
                                            <th style="width:18%">Tampil</th>
                                            <th class="text-center" style="width:10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyUploadTunjangan"></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="btnUploadRowTunjangan"><i class="fa fa-plus"></i> Tambah Tunjangan</button>
                        </div>
                        <div class="tab-pane fade" id="tab-up-potongan" role="tabpanel" aria-labelledby="tab-up-potongan-tab">
                            <div class="alert alert-info py-2">
                                <i class="fa fa-info-circle"></i> Opsional — baris kosong tidak disimpan.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm w-100">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Nama Potongan</th>
                                            <th style="width:30%">Jumlah (Rp)</th>
                                            <th class="text-center" style="width:10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyUploadPotongan"></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="btnUploadRowPotongan"><i class="fa fa-plus"></i> Tambah Potongan</button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveTemplate">
                        <i class="fas fa-save"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditTemplate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-pen"></i> Edit Template Kontrak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs" id="tabEditTemplate" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-et-template-tab" data-bs-toggle="tab" data-bs-target="#tab-et-template" type="button" role="tab" aria-controls="tab-et-template" aria-selected="true">Template</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-et-nilai-tab" data-bs-toggle="tab" data-bs-target="#tab-et-nilai" type="button" role="tab" aria-controls="tab-et-nilai" aria-selected="false">Nilai Kontrak</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-et-tunjangan-tab" data-bs-toggle="tab" data-bs-target="#tab-et-tunjangan" type="button" role="tab" aria-controls="tab-et-tunjangan" aria-selected="false">Tunjangan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-et-potongan-tab" data-bs-toggle="tab" data-bs-target="#tab-et-potongan" type="button" role="tab" aria-controls="tab-et-potongan" aria-selected="false">Potongan</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tab-et-template" role="tabpanel" aria-labelledby="tab-et-template-tab">
                            <form id="formEditTemplate" enctype="multipart/form-data">
                                <input type="hidden" name="id_template" id="editTemplateId">
                                <div class="mb-3">
                                    <label class="form-label">Nama Kontrak <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_kontrak" id="editTemplateNama" class="form-control" maxlength="150" placeholder="cth: Kontrak Kerja Operator 2026" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ganti File Word <small class="text-muted">(opsional — kosongkan bila tidak diganti)</small></label>
                                    <input type="file" name="file_pdf" class="form-control" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                    <small class="text-muted">File saat ini: <span id="editTemplateFile">-</span>. Format .doc/.docx, maksimal 2 MB.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Berlaku Untuk Jabatan <span class="text-danger">*</span></label>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="checkAllJabatanEdit">
                                        <label class="form-check-label fw-bold" for="checkAllJabatanEdit">Pilih Semua</label>
                                    </div>
                                    <div id="listJabatanEdit" class="border rounded p-3" style="max-height: 260px; overflow-y: auto;">
                                        <span class="text-muted">Memuat jabatan...</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-warning" id="btnUpdateTemplate">
                                        <i class="fas fa-save"></i> Simpan Template
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="tab-et-nilai" role="tabpanel" aria-labelledby="tab-et-nilai-tab">
                            <div class="alert alert-info py-2 mb-2" id="infoNilaiSekarang">
                                <i class="fas fa-info-circle"></i> <span>Nilai saat ini: -</span>
                            </div>
                            <form id="formEditDetail">
                                <input type="hidden" name="id_template">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label">Gaji Pokok</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="gaji_pokok" class="form-control nominal-mask">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Upah Lembur</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="upah_lembur" class="form-control nominal-mask">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Potongan Izin</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="potongan_izin" class="form-control nominal-mask">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Potongan Mangkir</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="potongan_mangkir" class="form-control nominal-mask">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 text-end">
                                    <button type="submit" class="btn btn-warning" id="btnSaveDetail">
                                        <i class="fas fa-save"></i> Simpan Nilai
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="tab-et-tunjangan" role="tabpanel" aria-labelledby="tab-et-tunjangan-tab">
                            <h6 class="fw-bold"><i class="fa fa-money-bill"></i> Tunjangan</h6>
                            <form id="formTplTunjangan" class="row g-2 align-items-end">
                                <input type="hidden" name="id_template" id="tplTunjanganTemplateId">
                                <input type="hidden" name="id_tunjangan" id="tplTunjanganId">
                                <div class="col-md-3">
                                    <label class="form-label">Nama Tunjangan</label>
                                    <select name="nama_tunjangan" id="tplTunjanganNama" class="form-select" required>
                                        <option value="">-- Pilih Tunjangan --</option>
                                        <option value="Tunjangan Daerah">Tunjangan Daerah</option>
                                        <option value="Tunjangan Hidup">Tunjangan Hidup</option>
                                        <option value="Tunjangan Makan">Tunjangan Makan</option>
                                        <option value="Tunjangan Skill">Tunjangan Skill</option>
                                        <option value="Bonus Kehadiran">Bonus Kehadiran</option>
                                        <option value="Tunjangan Posisi">Tunjangan Posisi</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jenis</label>
                                    <select name="jenis_tunjangan" id="tplTunjanganJenis" class="form-select" required>
                                        <option value="tetap">Tetap</option>
                                        <option value="tidak_tetap">Tidak Tetap</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jumlah (Rp)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="jumlah_tunjangan" id="tplTunjanganJumlah" class="form-control nominal-mask" placeholder="0" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tampil di Template</label>
                                    <select name="tampil_template" id="tplTunjanganTampil" class="form-select" required>
                                        <option value="1">Ya, tampilkan</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-1">
                                    <button type="submit" class="btn btn-success" id="btnTplTunjanganSubmit">
                                        <i class="fa fa-save"></i> Simpan
                                    </button>
                                    <button type="button" class="btn btn-secondary d-none" id="btnTplTunjanganCancel">Batal</button>
                                </div>
                            </form>
                            <div class="table-responsive mt-2">
                                <table class="table table-bordered table-sm w-100">
                                    <thead class="table-info">
                                        <tr>
                                            <th class="text-center" style="width:5%">No</th>
                                            <th class="text-center">Nama Tunjangan</th>
                                            <th class="text-center">Jenis</th>
                                            <th class="text-center">Jumlah (Rp)</th>
                                            <th class="text-center">Tampil</th>
                                            <th class="text-center" style="width:15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyTplTunjangan"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-et-potongan" role="tabpanel" aria-labelledby="tab-et-potongan-tab">
                            <h6 class="fw-bold"><i class="fa fa-scissors"></i> Potongan</h6>
                            <form id="formTplPotongan" class="row g-2 align-items-end">
                                <input type="hidden" name="id_template" id="tplPotonganTemplateId">
                                <input type="hidden" name="id_potongan" id="tplPotonganId">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Potongan</label>
                                    <select name="jenis_potongan" id="tplPotonganNama" class="form-select" required>
                                        <option value="">-- Pilih Potongan --</option>
                                        <option value="BPJS Kesehatan">BPJS Kesehatan</option>
                                        <option value="BPJS Ketenagakerjaan">BPJS Ketenagakerjaan</option>
                                        <option value="JHT">JHT</option>
                                        <option value="PPh 21">PPh 21</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jumlah (Rp)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="jumlah_potongan" id="tplPotonganJumlah" class="form-control nominal-mask" placeholder="0" required>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex gap-1">
                                    <button type="submit" class="btn btn-success" id="btnTplPotonganSubmit">
                                        <i class="fa fa-save"></i> Simpan
                                    </button>
                                    <button type="button" class="btn btn-secondary d-none" id="btnTplPotonganCancel">Batal</button>
                                </div>
                            </form>
                            <div class="table-responsive mt-2">
                                <table class="table table-bordered table-sm w-100">
                                    <thead class="table-info">
                                        <tr>
                                            <th class="text-center" style="width:5%">No</th>
                                            <th class="text-center">Nama Potongan</th>
                                            <th class="text-center">Jumlah (Rp)</th>
                                            <th class="text-center" style="width:15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyTplPotongan"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const API = "app/controller/act-template-kontrak.php";

        function escHtml(s) {
            return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        // Label jabatan: "Nama Jabatan (KODE)" bila kode ada, sederhanakan bila kosong.
        function jabatanLabel(j) {
            const nama = (j && j.nama_jabatan) || "-";
            const kode = String((j && j.kode_jabatan) || "").trim();
            return kode ? `${nama} (${kode})` : nama;
        }

        // Checkbox daftar jabatan
        function loadJabatan() {
            const box = document.getElementById("listJabatan");
            const btnSave = document.getElementById("btnSaveTemplate");
            btnSave.disabled = true;
            fetch(API, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=list_jabatan"
                })
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("list_jabatan bukan JSON:", text);
                        throw new Error("Respon server tidak valid.");
                    }
                })
                .then(res => {
                    if (res.status !== "success") {
                        throw new Error(res.msg || res.message || "Gagal memuat jabatan.");
                    }
                    if (!res.data.length) {
                        box.innerHTML = '<span class="text-muted">Belum ada data jabatan. Tambahkan dulu di menu Data Jabatan.</span>';
                        return;
                    }
                    box.innerHTML = res.data.map(j =>
                        `<div class="form-check">
                            <input type="checkbox" class="form-check-input chk-jabatan" name="id_jabatan[]"
                                value="${j.id_jabatan}" id="jab_${j.id_jabatan}">
                            <label class="form-check-label" for="jab_${j.id_jabatan}">${escHtml(jabatanLabel(j))}</label>
                        </div>`
                    ).join("");
                    document.getElementById("checkAllJabatan").checked = false;
                    btnSave.disabled = false;
                })
                .catch(err => {
                    console.error("loadJabatan:", err);
                    box.innerHTML =
                        '<span class="text-danger">Gagal memuat jabatan: ' + escHtml(err.message || err) + '</span>';
                });
        }

        document.getElementById("checkAllJabatan").addEventListener("change", function() {
            document.querySelectorAll(".chk-jabatan").forEach(c => c.checked = this.checked);
        });

        // Checkbox daftar jabatan untuk modal edit (dengan pra-pilih tautan saat ini)
        function loadJabatanEdit(selectedIds) {
            const box = document.getElementById("listJabatanEdit");
            const selected = new Set((selectedIds || []).map(String));
            box.innerHTML = '<span class="text-muted">Memuat jabatan...</span>';
            fetch(API, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=list_jabatan"
                })
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("list_jabatan bukan JSON:", text);
                        throw new Error("Respon server tidak valid.");
                    }
                })
                .then(res => {
                    if (res.status !== "success") {
                        throw new Error(res.msg || res.message || "Gagal memuat jabatan.");
                    }
                    if (!res.data.length) {
                        box.innerHTML = '<span class="text-muted">Belum ada data jabatan. Tambahkan dulu di menu Data Jabatan.</span>';
                        return;
                    }
                    box.innerHTML = res.data.map(j =>
                        `<div class="form-check">
                            <input type="checkbox" class="form-check-input chk-jabatan-edit" name="id_jabatan[]"
                                value="${j.id_jabatan}" id="jabedit_${j.id_jabatan}"${selected.has(String(j.id_jabatan)) ? " checked" : ""}>
                            <label class="form-check-label" for="jabedit_${j.id_jabatan}">${escHtml(jabatanLabel(j))}</label>
                        </div>`
                    ).join("");
                    const all = box.querySelectorAll(".chk-jabatan-edit");
                    document.getElementById("checkAllJabatanEdit").checked =
                        all.length > 0 && box.querySelectorAll(".chk-jabatan-edit:checked").length === all.length;
                })
                .catch(err => {
                    console.error("loadJabatanEdit:", err);
                    box.innerHTML =
                        '<span class="text-danger">Gagal memuat jabatan: ' + escHtml(err.message || err) + '</span>';
                });
        }

        document.getElementById("checkAllJabatanEdit").addEventListener("change", function() {
            document.querySelectorAll(".chk-jabatan-edit").forEach(c => c.checked = this.checked);
        });

        // Buka modal edit template (template, nilai kontrak, tunjangan, potongan) dengan prefill
        document.addEventListener("click", e => {
            const btn = e.target.closest(".btnEditTemplate");
            if (!btn) return;
            document.getElementById("formEditTemplate").reset();
            document.getElementById("editTemplateId").value = btn.dataset.id;
            document.getElementById("editTemplateNama").value = btn.dataset.namaKontrak || "";
            document.getElementById("editTemplateFile").textContent = btn.dataset.file || "-";
            const ids = (btn.dataset.jabatanIds || "").split(",").map(s => s.trim()).filter(Boolean);
            loadJabatanEdit(ids);
            const formD = document.getElementById("formEditDetail");
            formD.querySelector("input[name=id_template]").value = btn.dataset.id;
            formD.querySelector("input[name=gaji_pokok]").value = formatIndo(btn.dataset.gajiPokok);
            formD.querySelector("input[name=upah_lembur]").value = formatIndo(btn.dataset.upahLembur);
            formD.querySelector("input[name=potongan_izin]").value = formatIndo(btn.dataset.potonganIzin);
            formD.querySelector("input[name=potongan_mangkir]").value = formatIndo(btn.dataset.potonganMangkir);
            const fmtRpNow = v => "Rp " + Number(v || 0).toLocaleString("id-ID");
            const infoNow = document.getElementById("infoNilaiSekarang");
            if (infoNow) {
                infoNow.querySelector("span").innerHTML =
                    `Nilai saat ini: Gaji <strong>${fmtRpNow(btn.dataset.gajiPokok)}</strong> • ` +
                    `Lembur <strong>${fmtRpNow(btn.dataset.upahLembur)}</strong> • ` +
                    `Pot Izin <strong>${fmtRpNow(btn.dataset.potonganIzin)}</strong> • ` +
                    `Pot Mangkir <strong>${fmtRpNow(btn.dataset.potonganMangkir)}</strong>`;
            }
            document.getElementById("tplTunjanganTemplateId").value = btn.dataset.id;
            document.getElementById("tplPotonganTemplateId").value = btn.dataset.id;
            resetTplTunjanganForm();
            resetTplPotonganForm();
            loadTplTunjangan();
            loadTplPotongan();
            const tabTpl = document.querySelector('#tabEditTemplate button[data-bs-target="#tab-et-template"]');
            if (tabTpl) bootstrap.Tab.getOrCreateInstance(tabTpl).show();
            new bootstrap.Modal(document.getElementById("modalEditTemplate")).show();
        });

        document.getElementById("formEditTemplate").addEventListener("submit", async function(e) {
            e.preventDefault();
            if (!document.querySelector(".chk-jabatan-edit:checked")) {
                showToast("Pilih minimal satu jabatan.", "warning");
                return;
            }
            const btn = document.getElementById("btnUpdateTemplate");
            btn.disabled = true;
            try {
                const fd = new FormData(this);
                fd.append("action", "update");
                const res = await (await fetch(API, { method: "POST", body: fd })).json();
                if (res.status === "success") {
                    showToast(res.msg || "Template berhasil diperbarui.", "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalEditTemplate")).hide();
                    loadTable();
                } else {
                    showToast(res.msg || "Gagal menyimpan.", "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan sistem.", "error");
            } finally {
                btn.disabled = false;
            }
        });

        // Tabel template
        function loadTable() {
            fetch(API, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=list"
                })
                .then(res => res.json())
                .then(res => {
                    if ($.fn.DataTable.isDataTable(".MyTableTemplate")) {
                        $(".MyTableTemplate").DataTable().destroy();
                    }
                    const tbody = document.getElementById("tbodyTemplate");
                    if (res.status !== "success") {
                        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data.</td></tr>`;
                        return;
                    }
                    if (!res.data.length) {
                        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada template. Klik Upload Template.</td></tr>`;
                        return;
                    }
                    tbody.innerHTML = res.data.map((r, i) => {
                        const daftarJab = (r.jabatan && r.jabatan.length) ? r.jabatan : [];
                        const maksTampil = 3;
                        const badges = daftarJab.length
                            ? daftarJab.slice(0, maksTampil).map(j => `<span class="badge bg-primary me-1 mb-1">${escHtml(jabatanLabel(j))}</span>`).join("") +
                              (daftarJab.length > maksTampil
                                  ? `<span class="badge bg-secondary me-1 mb-1" title="${escHtml(daftarJab.map(jabatanLabel).join(", "))}">+${daftarJab.length - maksTampil} lainnya</span>`
                                  : "")
                            : '<span class="badge bg-secondary">Tanpa jabatan</span>';
                        const d = r.detail || {};
                        const fmtRp = v => "Rp " + Number(v || 0).toLocaleString("id-ID");
                        const nilai = `<small>Gaji: <strong>${fmtRp(d.gaji_pokok)}</strong><br>` +
                            `Lembur: <strong>${fmtRp(d.upah_lembur)}</strong><br>` +
                            `Pot Izin: <strong>${fmtRp(d.potongan_izin)}</strong><br>` +
                            `Pot Mangkir: <strong>${fmtRp(d.potongan_mangkir)}</strong><br>` +
                            `<span class="text-muted">Tunjangan: ${r.jml_tunjangan ?? 0} • Potongan: ${r.jml_potongan ?? 0}</span></small>`;
                        return `<tr>
                            <td class="text-center">${i + 1}</td>
                            <td><strong>${escHtml(r.nama_kontrak || "-")}</strong></td>
                            <td>${badges}<br><small class="text-muted">${r.jml_jabatan} jabatan</small></td>
                            <td>${nilai}</td>
                            <td class="text-center">
                                <a href="app/uploads/template-kontrak/${escHtml(r.file)}" target="_blank"
                                    class="btn btn-sm btn-info" title="Lihat file"><i class="fas fa-eye"></i></a>
                                <button class="btn btn-sm btn-primary btnEditTemplate" data-id="${r.id_template}"
                                    data-nama-kontrak="${escHtml(r.nama_kontrak || "")}"
                                    data-file="${escHtml(r.nama_asli || r.file)}"
                                    data-jabatan-ids="${(r.jabatan || []).map(j => j.id_jabatan).join(",")}"
                                    data-gaji-pokok="${escHtml(d.gaji_pokok ?? 0)}"
                                    data-upah-lembur="${escHtml(d.upah_lembur ?? 0)}"
                                    data-potongan-izin="${escHtml(d.potongan_izin ?? 0)}"
                                    data-potongan-mangkir="${escHtml(d.potongan_mangkir ?? 0)}"
                                    title="Edit template, nilai & tunjangan"><i class="fas fa-pen"></i></button>
                                <button class="btn btn-sm btn-danger btnDeleteTemplate" data-id="${r.id_template}"
                                    data-nama="${escHtml(r.nama_kontrak || r.nama_asli || r.file)}" title="Hapus template">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    }).join("");
                    $(".MyTableTemplate").DataTable({ responsive: true });
                })
                .catch(() => {
                    document.getElementById("tbodyTemplate").innerHTML =
                        `<tr><td colspan="5" class="text-center text-danger">Gagal menghubungi server.</td></tr>`;
                });
        }

        // Format masking nominal (titik ribuan, koma desimal) ala halaman karier
        function formatIndo(val) {
            if (val === undefined || val === null || val === '') return "0";
            let parts = val.toString().split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            return parts.join(",");
        }
        document.addEventListener("input", e => {
            if (e.target.classList && e.target.classList.contains("nominal-mask")) {
                let input = e.target;
                let cursorPos = input.selectionStart;
                let prevLength = input.value.length;
                let val = input.value.replace(/[^0-9,]/g, "");
                let parts = val.split(",");
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                input.value = parts.length > 2 ? parts[0] + "," + parts[1] : parts.join(",");
                let diff = input.value.length - prevLength;
                try { input.setSelectionRange(cursorPos + diff, cursorPos + diff); } catch (err) {}
            }
        });

        // Simpan nilai kontrak (upsert, modal tetap terbuka)
        document.getElementById("formEditDetail").addEventListener("submit", async function(e) {
            e.preventDefault();
            const btn = document.getElementById("btnSaveDetail");
            btn.disabled = true;
            try {
                const fd = new FormData(this);
                fd.append("action", "save_detail");
                const res = await fetch(API, { method: "POST", body: fd });
                const data = await res.json();
                if (data.status === "success") {
                    showToast(data.msg || "Nilai kontrak berhasil disimpan.", "success");
                    loadTable();
                } else {
                    showToast(data.msg || "Gagal menyimpan.", "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan sistem.", "error");
            } finally {
                btn.disabled = false;
            }
        });

        // --- Tunjangan milik detail kontrak ---
        const fmtModalRp = v => "Rp " + Number(v || 0).toLocaleString("id-ID");

        function resetTplTunjanganForm() {
            document.getElementById("tplTunjanganId").value = "";
            document.getElementById("tplTunjanganNama").value = "";
            document.getElementById("tplTunjanganJenis").value = "tetap";
            document.getElementById("tplTunjanganJumlah").value = "";
            document.getElementById("tplTunjanganTampil").value = "1";
            document.getElementById("btnTplTunjanganSubmit").innerHTML = '<i class="fa fa-save"></i> Simpan';
            document.getElementById("btnTplTunjanganCancel").classList.add("d-none");
        }

        function loadTplTunjangan() {
            const id = document.getElementById("tplTunjanganTemplateId").value;
            if (!id) return;
            const fd = new FormData();
            fd.append("action", "list_tunjangan");
            fd.append("id_template", id);
            fetch(API, { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    const tbody = document.getElementById("tbodyTplTunjangan");
                    if (res.status !== "success" || !res.data.length) {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Belum ada tunjangan.</td></tr>`;
                        return;
                    }
                    tbody.innerHTML = res.data.map((t, i) => `<tr>
                        <td class="text-center">${i + 1}</td>
                        <td>${escHtml(t.nama_tunjangan)}</td>
                        <td class="text-center">${t.jenis_tunjangan === "tidak_tetap"
                            ? '<span class="badge bg-warning text-dark">Tidak Tetap</span>'
                            : '<span class="badge bg-success">Tetap</span>'}</td>
                        <td class="text-end">${fmtModalRp(t.jumlah_tunjangan)}${["tunjangan makan", "tunjangan uang makan"].includes(String(t.nama_tunjangan || "").toLowerCase().trim()) ? "/hari" : ""}</td>
                        <td class="text-center">${String(t.tampil_template ?? "1") === "0"
                            ? '<span class="badge bg-secondary">Tidak</span>'
                            : '<span class="badge bg-info text-dark">Ya</span>'}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning btnTplTunjanganEdit"
                                data-id="${t.id_tunjangan}" data-nama="${escHtml(t.nama_tunjangan)}"
                                data-jenis="${escHtml(t.jenis_tunjangan || "tetap")}"
                                data-jumlah="${escHtml(t.jumlah_tunjangan)}"
                                data-tampil="${escHtml(t.tampil_template ?? "1")}">Edit</button>
                            <button class="btn btn-sm btn-danger btnTplTunjanganDel"
                                data-id="${t.id_tunjangan}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`).join("");
                })
                .catch(() => {
                    document.getElementById("tbodyTplTunjangan").innerHTML =
                        `<tr><td colspan="6" class="text-center text-danger">Gagal memuat.</td></tr>`;
                });
        }

        // --- Potongan milik detail kontrak ---
        function resetTplPotonganForm() {
            document.getElementById("tplPotonganId").value = "";
            document.getElementById("tplPotonganNama").value = "";
            document.getElementById("tplPotonganJumlah").value = "";
            document.getElementById("btnTplPotonganSubmit").innerHTML = '<i class="fa fa-save"></i> Simpan';
            document.getElementById("btnTplPotonganCancel").classList.add("d-none");
        }

        function loadTplPotongan() {
            const id = document.getElementById("tplPotonganTemplateId").value;
            if (!id) return;
            const fd = new FormData();
            fd.append("action", "list_potongan");
            fd.append("id_template", id);
            fetch(API, { method: "POST", body: fd })
                .then(res => res.json())
                .then(res => {
                    const tbody = document.getElementById("tbodyTplPotongan");
                    if (res.status !== "success" || !res.data.length) {
                        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Belum ada potongan.</td></tr>`;
                        return;
                    }
                    tbody.innerHTML = res.data.map((p, i) => `<tr>
                        <td class="text-center">${i + 1}</td>
                        <td>${escHtml(p.jenis_potongan)}</td>
                        <td class="text-end">${fmtModalRp(p.jumlah_potongan)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning btnTplPotonganEdit"
                                data-id="${p.id_potongan}" data-nama="${escHtml(p.jenis_potongan)}"
                                data-jumlah="${escHtml(p.jumlah_potongan)}">Edit</button>
                            <button class="btn btn-sm btn-danger btnTplPotonganDel"
                                data-id="${p.id_potongan}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`).join("");
                })
                .catch(() => {
                    document.getElementById("tbodyTplPotongan").innerHTML =
                        `<tr><td colspan="4" class="text-center text-danger">Gagal memuat.</td></tr>`;
                });
        }

        document.addEventListener("click", e => {
            const btnET = e.target.closest(".btnTplTunjanganEdit");
            if (btnET) {
                document.getElementById("tplTunjanganId").value = btnET.dataset.id;
                document.getElementById("tplTunjanganNama").value = btnET.dataset.nama || "";
                document.getElementById("tplTunjanganJenis").value = btnET.dataset.jenis || "tetap";
                document.getElementById("tplTunjanganJumlah").value = formatIndo(btnET.dataset.jumlah);
                document.getElementById("tplTunjanganTampil").value = String(btnET.dataset.tampil ?? "1") === "0" ? "0" : "1";
                document.getElementById("btnTplTunjanganSubmit").innerHTML = '<i class="fa fa-save"></i> Update';
                document.getElementById("btnTplTunjanganCancel").classList.remove("d-none");
                return;
            }
            const btnEP = e.target.closest(".btnTplPotonganEdit");
            if (btnEP) {
                document.getElementById("tplPotonganId").value = btnEP.dataset.id;
                document.getElementById("tplPotonganNama").value = btnEP.dataset.nama || "";
                document.getElementById("tplPotonganJumlah").value = formatIndo(btnEP.dataset.jumlah);
                document.getElementById("btnTplPotonganSubmit").innerHTML = '<i class="fa fa-save"></i> Update';
                document.getElementById("btnTplPotonganCancel").classList.remove("d-none");
                return;
            }
        });

        document.addEventListener("click", async e => {
            const btnDT = e.target.closest(".btnTplTunjanganDel");
            if (btnDT) {
                if (await confirmDialog("Yakin hapus tunjangan ini?")) {
                    const fd = new FormData();
                    fd.append("action", "delete_tunjangan");
                    fd.append("id_template", document.getElementById("tplTunjanganTemplateId").value);
                    fd.append("id_tunjangan", btnDT.dataset.id);
                    const res = await (await fetch(API, { method: "POST", body: fd })).json();
                    showToast(res.msg || "Selesai.", res.status === "success" ? "success" : "error");
                    if (res.status === "success") { loadTplTunjangan(); loadTable(); }
                }
                return;
            }
            const btnDP = e.target.closest(".btnTplPotonganDel");
            if (btnDP) {
                if (await confirmDialog("Yakin hapus potongan ini?")) {
                    const fd = new FormData();
                    fd.append("action", "delete_potongan");
                    fd.append("id_template", document.getElementById("tplPotonganTemplateId").value);
                    fd.append("id_potongan", btnDP.dataset.id);
                    const res = await (await fetch(API, { method: "POST", body: fd })).json();
                    showToast(res.msg || "Selesai.", res.status === "success" ? "success" : "error");
                    if (res.status === "success") { loadTplPotongan(); loadTable(); }
                }
            }
        });

        document.getElementById("formTplTunjangan").addEventListener("submit", async function(e) {
            e.preventDefault();
            const isEdit = document.getElementById("tplTunjanganId").value !== "";
            const fd = new FormData(this);
            fd.append("action", "save_tunjangan");
            try {
                const res = await (await fetch(API, { method: "POST", body: fd })).json();
                if (res.status === "success") {
                    showToast(res.msg || "Berhasil.", "success");
                    resetTplTunjanganForm();
                    loadTplTunjangan();
                    loadTable();
                } else {
                    showToast(res.msg || "Gagal menyimpan.", "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan sistem.", "error");
            }
        });

        document.getElementById("formTplPotongan").addEventListener("submit", async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append("action", "save_potongan");
            try {
                const res = await (await fetch(API, { method: "POST", body: fd })).json();
                if (res.status === "success") {
                    showToast(res.msg || "Berhasil.", "success");
                    resetTplPotonganForm();
                    loadTplPotongan();
                    loadTable();
                } else {
                    showToast(res.msg || "Gagal menyimpan.", "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan sistem.", "error");
            }
        });

        document.getElementById("btnTplTunjanganCancel").addEventListener("click", resetTplTunjanganForm);
        document.getElementById("btnTplPotonganCancel").addEventListener("click", resetTplPotonganForm);

        // --- BARIS TUNJANGAN & POTONGAN PADA MODAL UPLOAD ---
        // Ikut terkirim bersama form (array), disimpan dalam transaksi upload yang sama.
        function addUploadRowTunjangan(d) {
            d = d || {};
            const tb = document.getElementById("tbodyUploadTunjangan");
            if (!tb) return;
            const jenis = d.jenis_tunjangan === "tidak_tetap" ? "tidak_tetap" : "tetap";
            const tampil = String(d.tampil_template ?? "1") === "0" ? "0" : "1";
            const daftarNama = ["Tunjangan Daerah", "Tunjangan Hidup", "Tunjangan Makan",
                "Tunjangan Skill", "Bonus Kehadiran", "Tunjangan Posisi"];
            const namaDipilih = d.nama_tunjangan || "";
            const tr = document.createElement("tr");
            tr.innerHTML =
                `<td><select name="tunjangan_nama[]" class="form-select form-select-sm">` +
                `<option value="">-- Pilih --</option>` +
                daftarNama.map(nm => `<option value="${nm}"${nm === namaDipilih ? " selected" : ""}>${nm}</option>`).join("") +
                `</select></td>` +
                `<td><select name="tunjangan_jenis[]" class="form-select form-select-sm">` +
                `<option value="tetap"${jenis === "tetap" ? " selected" : ""}>Tetap</option>` +
                `<option value="tidak_tetap"${jenis === "tidak_tetap" ? " selected" : ""}>Tidak Tetap</option></select></td>` +
                `<td><input type="text" name="tunjangan_jumlah[]" class="form-control form-control-sm nominal-mask" placeholder="0"></td>` +
                `<td><select name="tunjangan_tampil[]" class="form-select form-select-sm">` +
                `<option value="1"${tampil === "1" ? " selected" : ""}>Ya</option>` +
                `<option value="0"${tampil === "0" ? " selected" : ""}>Tidak</option></select></td>` +
                `<td class="text-center"><button type="button" class="btn btn-danger btn-sm btnDelUploadRow" title="Hapus baris"><i class="fa fa-trash"></i></button></td>`;
            tb.appendChild(tr);
        }
        function addUploadRowPotongan(d) {
            d = d || {};
            const tb = document.getElementById("tbodyUploadPotongan");
            if (!tb) return;
            const daftarPot = ["BPJS KESEHATAN 1%", "BPJS KETENAGAKERJAAN 2%", "PPH21"];
            const potDipilih = d.jenis_potongan || "";
            const tr = document.createElement("tr");
            tr.innerHTML =
                `<td><select name="potongan_nama[]" class="form-select form-select-sm">` +
                `<option value="">-- Pilih --</option>` +
                daftarPot.map(nm => `<option value="${nm}"${nm === potDipilih ? " selected" : ""}>${nm}</option>`).join("") +
                `</select></td>` +
                `<td><input type="text" name="potongan_jumlah[]" class="form-control form-control-sm nominal-mask" placeholder="0"></td>` +
                `<td class="text-center"><button type="button" class="btn btn-danger btn-sm btnDelUploadRow" title="Hapus baris"><i class="fa fa-trash"></i></button></td>`;
            tb.appendChild(tr);
        }
        function resetUploadTpRows() {
            const tbT = document.getElementById("tbodyUploadTunjangan");
            const tbP = document.getElementById("tbodyUploadPotongan");
            if (tbT) tbT.innerHTML = "";
            if (tbP) tbP.innerHTML = "";
            const tabFirst = document.querySelector('#tabUploadTemplate button[data-bs-target="#tab-up-template"]');
            if (tabFirst) bootstrap.Tab.getOrCreateInstance(tabFirst).show();
        }
        // Baris bawaan agar pengguna tinggal mengatur nilainya (baris bisa dihapus bila tak perlu).
        function seedUploadTpRows() {
            ["Tunjangan Daerah", "Tunjangan Hidup", "Tunjangan Makan", "Tunjangan Skill",
             "Bonus Kehadiran", "Tunjangan Posisi"].forEach(nm => {
                addUploadRowTunjangan({ nama_tunjangan: nm, jenis_tunjangan: nm === "Bonus Kehadiran" ? "tidak_tetap" : "tetap" });
            });
            ["BPJS KESEHATAN 1%", "BPJS KETENAGAKERJAAN 2%", "PPH21"].forEach(nm => {
                addUploadRowPotongan({ jenis_potongan: nm });
            });
        }
        document.addEventListener("click", e => {
            const del = e.target.closest(".btnDelUploadRow");
            if (del) { del.closest("tr").remove(); return; }
            if (e.target.closest("#btnUploadRowTunjangan")) { addUploadRowTunjangan(); return; }
            if (e.target.closest("#btnUploadRowPotongan")) { addUploadRowPotongan(); return; }
        });

        document.getElementById("btnTambahTemplate").addEventListener("click", () => {
            document.getElementById("formUploadTemplate").reset();
            resetUploadTpRows();
            seedUploadTpRows();
            loadJabatan();
            new bootstrap.Modal(document.getElementById("modalUploadTemplate")).show();
        });

        document.getElementById("formUploadTemplate").addEventListener("submit", async function(e) {
            e.preventDefault();
            if (!document.querySelector(".chk-jabatan:checked")) {
                showToast("Pilih minimal satu jabatan.", "warning");
                return;
            }
            const btn = document.getElementById("btnSaveTemplate");
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';

            try {
                const fd = new FormData(this);
                fd.append("action", "upload");
                const res = await fetch(API, { method: "POST", body: fd });
                const data = await res.json();
                if (data.status === "success") {
                    const extra = ((data.tunjangan || 0) + (data.potongan || 0) > 0) ?
                        ` (${data.tunjangan || 0} tunjangan, ${data.potongan || 0} potongan)` : "";
                    showToast((data.msg || "Template berhasil diupload.") + extra, "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalUploadTemplate")).hide();
                    loadTable();
                } else {
                    showToast(data.msg || "Gagal mengupload.", "error");
                }
            } catch (err) {
                showToast("Terjadi kesalahan sistem.", "error");
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Upload';
            }
        });

        document.addEventListener("click", async e => {
            const btn = e.target.closest(".btnDeleteTemplate");
            if (!btn) return;
            if (await confirmDialog(`Hapus template "${btn.dataset.nama}" beserta relasinya?`)) {
                const fd = new FormData();
                fd.append("action", "delete");
                fd.append("id_template", btn.dataset.id);
                try {
                    const res = await fetch(API, { method: "POST", body: fd });
                    const data = await res.json();
                    if (data.status === "success") {
                        showToast(data.msg || "Template berhasil dihapus.", "success");
                        loadTable();
                    } else {
                        showToast(data.msg || "Gagal menghapus.", "error");
                    }
                } catch (err) {
                    showToast("Terjadi kesalahan sistem.", "error");
                }
            }
        });

        loadTable();
    });
</script>
