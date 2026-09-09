<style>
    .card {
        border: none;
        border-radius: 12px;
    }

    .import-section {
        border: 2px dashed #ffc107;
        background-color: #fff9e6 !important;
    }

    .table thead th {
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .format-badge {
        background: #e9ecef;
        color: #495057;
        padding: 4px 10px;
        border-radius: 6px;
        font-family: monospace;
        font-weight: bold;
    }

    .instruction-text {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .hover-lift {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .avatar {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-lg {
        width: 3.5rem;
        height: 3.5rem;
    }

    .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">
                    <span class="text-muted fw-light">Master Data /</span> Data Potongan Tidak Tetap
                </h4>
                <p class="text-muted mb-0">Kelola dan pantau data potongan tidak tetap karyawan secara efisien.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPotongan">
                <i class="fa fa-plus me-2"></i> Tambah Potongan Baru
            </button>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-success text-white shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg">
                                    <div class="avatar-initial bg-white bg-opacity-25 rounded">
                                        <i class="fa fa-hand-holding-usd fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-white">Total Data Potongan</h6>
                                <h4 class="mb-0 fw-bold text-white" id="totalPotonganCount">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-info text-white shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg">
                                    <div class="avatar-initial bg-white bg-opacity-25 rounded">
                                        <i class="fa fa-money-bill fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-white" id="labelNominal">Total Keseluruhan</h6>
                                <h4 class="mb-0 fw-bold text-white" id="totalNominal">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card import-section shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h5 class="fw-bold text-warning mb-2"><i class="fa fa-file-excel me-2"></i> Cepat Import Data</h5>
                                <p class="instruction-text mb-3">
                                    Unggah file Excel/CSV sesuai format kolom untuk memperbarui database secara masal. Dengan ketentuan:
                                </p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="format-badge">B: NIK</span>
                                    <span class="format-badge">E: Nominal</span>
                                    <span class="format-badge">F: Tgl Potong</span>
                                    <span class="format-badge">G: Jenis Potongan</span>
                                    <span class="format-badge">H: Ket</span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <form id="formImportPotongan" enctype="multipart/form-data">
                                    <div class="input-group">
                                        <input type="file" name="file_csv_potongan" id="file_csv_potongan" class="form-control" required>
                                        <button type="submit" class="btn btn-warning fw-bold" id="btnImportPotongan">
                                            <i class="fa fa-upload me-1"></i> Import
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <a href="assets/template-csv/Format_Impor_Data_Potongan_Tidak_Tetap.xlsx" style="font-size: 2vh;">Download Template Format .xlsx</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fa fa-list me-2 text-primary"></i> Data Potongan Tidak Tetap</h5>

                        <div class="d-flex align-items-center gap-2">
                            <select id="filterSite" class="form-select form-select-sm" style="width: 150px; cursor: pointer;">
                                <option value="">Semua Site</option>
                            </select>
                            <select id="filterJabatan" class="form-select form-select-sm" style="width: 150px; cursor: pointer;">
                                <option value="">Semua Jabatan</option>
                            </select>
                            <select id="filterBulan" class="form-select form-select-sm" style="width: 170px; cursor: pointer;">
                                <option value="">Semua Bulan</option>
                                <?php
                                $bulanArr = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];

                                $sqlBulan = "SELECT DISTINCT DATE_FORMAT(tgl_potongan, '%Y-%m') AS val_bulan FROM tb_potongan_tidak_tetap ORDER BY val_bulan DESC";
                                $resBulan = $koneksi->query($sqlBulan);

                                if ($resBulan && $resBulan->num_rows > 0) {
                                    $isFirst = true;
                                    while ($b = $resBulan->fetch_assoc()) {
                                        $val = $b['val_bulan'];
                                        $parts = explode('-', $val);
                                        $label = $bulanArr[$parts[1]] . " " . $parts[0];
                                        $selected = $isFirst ? "selected" : "";
                                        echo "<option value='$val' $selected>$label</option>";
                                        $isFirst = false;
                                    }
                                } else {
                                    $valNow = date('Y-m');
                                    $labelNow = $bulanArr[date('m')] . " " . date('Y');
                                    echo "<option value='$valNow' selected>$labelNow</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover table-bordered MyTable w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>NIK</th>
                                        <th>Karyawan</th>
                                        <th>Jabatan</th>
                                        <th>Tgl Potongan</th>
                                        <th>Jenis Potongan</th>
                                        <th>Nominal</th>
                                        <th>Keterangan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPotongan">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahPotongan">
    <div class="modal-dialog">
        <form id="formTambahPotongan" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Potongan Tidak Tetap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Nama Karyawan</label>
                <select name="id_karyawan" id="selectKaryawanTambah" class="form-control mb-3" required>
                    <option value="">Pilih Karyawan</option>
                    <?php
                    $sql = data_karyawan('nama_lengkap');
                    foreach ($sql as $data) { ?>
                        <option value="<?= $data['id_karyawan'] ?>"><?= $data['nik_karyawan'] . ' - ' . $data['nama_lengkap'] ?></option>
                    <?php } ?>
                </select>

                <label class="mt-3">Tanggal Potongan</label>
                <input type="date" name="tgl_potongan" class="form-control mb-3" required>

                <label>Jenis Potongan</label>
                <select name="jenis_potongan" class="form-select mb-3" required>
                    <option value="">Pilih Kategori...</option>
                    <option value="Denda">Denda</option>
                    <option value="Sumbangan">Sumbangan</option>
                    <option value="Titipan">Titipan</option>
                    <option value="Pinjaman Kios">Pinjaman Kios</option>
                    <option value="Potongan Lainnya">Potongan Lainnya</option>
                </select>

                <label>Jumlah Potongan</label>
                <input type="text" name="nominal_potongan" class="form-control mb-3 nominal-mask" required>

                <label>Keterangan Tambahan</label>
                <input type="text" name="ket_potongan" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditPotongan">
    <div class="modal-dialog">
        <form id="formEditPotongan" class="modal-content">
            <input type="hidden" name="id_potongan_tidak_tetap">
            <div class="modal-header">
                <h5 class="modal-title">Edit Potongan Tidak Tetap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Nama Karyawan</label>
                <select name="id_karyawan" id="selectKaryawanEdit" class="form-control mb-3" required>
                    <option value="">Pilih Karyawan</option>
                    <?php
                    $sql = data_karyawan('nik_karyawan');
                    foreach ($sql as $data) { ?>
                        <option value="<?= $data['id_karyawan'] ?>"><?= $data['nik_karyawan'] . ' - ' . $data['nama_lengkap'] ?></option>
                    <?php } ?>
                </select>

                <label class="mt-3">Tanggal Potongan</label>
                <input type="date" name="tgl_potongan" class="form-control mb-3" required>

                <label>Jenis Potongan</label>
                <select name="jenis_potongan" id="edit_jenis_potongan" class="form-select mb-3" required>
                    <option value="">Pilih Kategori...</option>
                    <option value="Denda">Denda</option>
                    <option value="Sumbangan">Sumbangan</option>
                    <option value="Titipan">Titipan</option>
                    <option value="Pinjaman Kios">Pinjaman Kios</option>
                    <option value="Potongan Lainnya">Potongan Lainnya</option>
                </select>

                <label>Jumlah Potongan</label>
                <input type="text" name="nominal_potongan" class="form-control mb-3 nominal-mask" required>

                <label>Keterangan Tambahan</label>
                <input type="text" name="ket_potongan" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("filterBulan").addEventListener("change", function() {
            loadPotongan();
        });

        const tbody = document.getElementById("tbodyPotongan");
        const formTambah = document.getElementById("formTambahPotongan");
        const formEdit = document.getElementById("formEditPotongan");
        const formImport = document.getElementById("formImportPotongan");

        // === INISIALISASI SELECT2 ===
        function initSelect2() {
            $('#selectKaryawanTambah').select2({
                dropdownParent: $('#modalTambahPotongan'),
                placeholder: 'Pilih Karyawan',
                allowClear: true,
                width: '100%'
            });

            $('#selectKaryawanEdit').select2({
                dropdownParent: $('#modalEditPotongan'),
                placeholder: 'Pilih Karyawan',
                allowClear: true,
                width: '100%'
            });
        }

        $('#modalTambahPotongan').on('hidden.bs.modal', function() {
            $('#selectKaryawanTambah').val('').trigger('change');
            formTambah.reset();
        });

        // === LOAD DATA ===
        function loadPotongan() {
            const bulan = document.getElementById("filterBulan").value;
            const site = document.getElementById("filterSite").value;
            const jabatan = document.getElementById("filterJabatan").value;

            tbody.innerHTML = '<tr><td colspan="9" class="text-center">Memuat data...</td></tr>';

            fetch(`app/controller/get-data/get-potongan-tidak-tetap.php?bulan=${bulan}&site=${site}&jabatan=${jabatan}`)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTable")) {
                        $(".MyTable").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $(".MyTable").DataTable({
                        responsive: true,
                        pageLength: 10,
                        language: {
                            search: "Cari:",
                            lengthMenu: "_MENU_ data per halaman",
                            zeroRecords: "Data tidak ditemukan",
                            info: "Menampilkan _PAGE_ dari _PAGES_"
                        }
                    });

                    updateStats();
                });
        }

        // === UPDATE STATS ===
        function updateStats() {
            const bulan = document.getElementById("filterBulan").value;
            const site = document.getElementById("filterSite").value;
            const jabatan = document.getElementById("filterJabatan").value;

            fetch(`app/controller/get-data/get-potongan-tidak-tetap-stats.php?bulan=${bulan}&site=${site}&jabatan=${jabatan}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    document.getElementById("totalPotonganCount").textContent = data.total || 0;
                    document.getElementById("totalNominal").textContent = "Rp " + (data.nominal || 0);
                    document.getElementById("labelNominal").textContent = data.teks_bulan;
                })
                .catch(error => {
                    console.error('Error updating stats:', error);
                });
        }

        // === IMPORT EXCEL/CSV ===
        if (formImport) {
            formImport.addEventListener("submit", e => {
                e.preventDefault();
                const btn = document.getElementById("btnImportPotongan");
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';

                let fd = new FormData(formImport);

                fetch("app/controller/csv-control/csv-potongan-tidak-tetap.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => {
                        if (!res.ok) throw new Error("File PHP tidak ditemukan atau Server Error (404/500)");
                        return res.json();
                    })
                    .then(res => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-upload me-1"></i> Import';

                        if (res.status === "success" || res.status === "warning") {
                            showToast(res.message, res.status === "warning" ? "warning" : "success");
                            loadPotongan();
                            formImport.reset();
                        } else {
                            showToast("Gagal: " + res.message, "error");
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-upload me-1"></i> Import';
                        console.error(err);
                        showToast("Terjadi kesalahan: " + err.message, "error");
                    });
            });
        }

        // =================== LISENER FORMAT OTOMATIS (MEMBERI TITIK) ===================
        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                let input = e.target;
                let cursorPos = input.selectionStart;
                let prevLength = input.value.length;
                let val = input.value.replace(/\D/g, "");
                let formatted = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                input.value = formatted;
                let diff = formatted.length - prevLength;
                input.setSelectionRange(cursorPos + diff, cursorPos + diff);
            }
        });

        // === TAMBAH ===
        formTambah.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formTambah);
            fd.append("action", "add");

            // --- PERBAIKAN: Bersihkan titik dari nominal sebelum dikirim ke PHP ---
            let nominalClean = fd.get("nominal_potongan").replace(/\./g, "");
            fd.set("nominal_potongan", nominalClean);
            // ------------------------------------------

            fetch("app/controller/act-potongan-tidak-tetap.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Berhasil menambah potongan!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahPotongan")).hide();
                        formTambah.reset();
                        $('#selectKaryawanTambah').val('').trigger('change');
                        loadPotongan();
                    } else showToast(res.msg, "error");
                });
        });

        // === EDIT ===
        formEdit.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formEdit);
            fd.append("action", "edit");

            // --- PERBAIKAN: Bersihkan titik dari nominal sebelum dikirim ke PHP ---
            let nominalClean = fd.get("nominal_potongan").replace(/\./g, "");
            fd.set("nominal_potongan", nominalClean);
            // ------------------------------------------

            fetch("app/controller/act-potongan-tidak-tetap.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Berhasil update potongan!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditPotongan")).hide();
                        loadPotongan();
                    } else showToast(res.msg, "error");
                });
        });

        // === EDIT & DELETE BUTTON ===
        document.addEventListener("click", async e => {
            const btnEdit = e.target.closest(".btnEdit");
            const btnDelete = e.target.closest(".btnDelete");

            // EDIT
            if (btnEdit) {
                formEdit.querySelector("input[name=id_potongan_tidak_tetap]").value = btnEdit.dataset.id;
                $('#selectKaryawanEdit').val(btnEdit.dataset.idkaryawan).trigger('change');
                formEdit.querySelector("input[name=tgl_potongan]").value = btnEdit.dataset.tglpotongan;
                formEdit.querySelector("select[name=jenis_potongan]").value = btnEdit.dataset.jenispotongan;

                // --- PERBAIKAN: Format nominal agar langsung muncul titiknya ---
                let rawNominal = btnEdit.dataset.potongan.toString();
                let angkaBulat = rawNominal.split(".")[0].replace(/\D/g, "");
                formEdit.querySelector("input[name=nominal_potongan]").value = angkaBulat.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                // -------------------------------------------------------------

                formEdit.querySelector("input[name=ket_potongan]").value = btnEdit.dataset.ket;

                new bootstrap.Modal(document.getElementById("modalEditPotongan")).show();
            }

            // DELETE
            if (btnDelete) {
                const id = btnDelete.dataset.id;
                if (await confirmDialog("Yakin menghapus data potongan ini?")) {
                    fetch("app/controller/act-potongan-tidak-tetap.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=delete&id_potongan_tidak_tetap=" + id
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("Berhasil menghapus potongan!", "success");
                                loadPotongan();
                            } else showToast(res.msg, "error");
                        });
                }
            }
        });

        initSelect2();
        loadPotongan();
    });
</script>