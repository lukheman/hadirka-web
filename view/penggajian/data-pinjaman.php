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

    /* Tambahan CSS untuk Kartu Statistik */
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
            <!-- Kelompok Kiri: Teks -->
            <div>
                <h4 class="fw-bold mb-0">
                    <span class="text-muted fw-light">Master Data /</span> Data Pinjaman
                </h4>
                <p class="text-muted mb-0">Kelola dan pantau data pinjaman karyawan secara efisien.</p>
            </div>

            <!-- Kelompok Kanan: Tombol -->
            <div class="d-flex gap-2">
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPinjaman">
                    <i class="fa fa-plus me-2"></i> Tambah Pinjaman
                </button>
                <button class="btn btn-warning shadow-sm" onclick="postingPinjaman()">
                    <i class="fa fa-send me-2"></i> Posting ke Keuangan
                </button>
            </div>
        </div>

        <!-- Panel Statistik -->
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
                                <h6 class="mb-0 text-white">Total Data Pinjaman</h6>
                                <h4 class="mb-0 fw-bold text-white" id="totalPinjaman">0</h4>
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
                                    <span class="format-badge">E: Nominal (Total)</span>
                                    <span class="format-badge">F: Tgl Pinjam</span>
                                    <span class="format-badge">G: Tgl Bayar (Awal)</span>
                                    <span class="format-badge">H: Ket</span>
                                    <span class="format-badge bg-warning text-dark">I: Tenor (Bulan)</span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <form id="formImportPinjaman" enctype="multipart/form-data">
                                    <div class="input-group">
                                        <input type="file" name="file_csv_pinjaman" id="file_csv_pinjaman" class="form-control" required>
                                        <button type="submit" class="btn btn-warning fw-bold" id="btnImportPinjaman">
                                            <i class="fa fa-upload me-1"></i> Import
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <a href="assets/template-csv/Format_Impor_Data_Pinjaman.xlsx" style="font-size: 2vh;">Download Template Format .xlsx</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fa fa-list me-2 text-primary"></i> Database Pinjaman</h5>

                        <select id="filterBulan" class="form-select form-select-sm" style="width: 170px; cursor: pointer;">
                            <option value="">Semua Bulan</option>
                            <?php
                            $bulanArr = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];

                            $sqlBulan = "SELECT DISTINCT DATE_FORMAT(tgl_pinjaman, '%Y-%m') AS val_bulan FROM tb_pinjaman ORDER BY val_bulan DESC";
                            $resBulan = $koneksi->query($sqlBulan);

                            if ($resBulan && $resBulan->num_rows > 0) {
                                $isFirst = true; // Auto-select bulan terbaru
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
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover table-bordered MyTable w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>NIK</th>
                                        <th>Karyawan</th>
                                        <th>Jabatan</th>
                                        <th>Tgl Pinjaman</th>
                                        <th>Tgl Bayar</th>
                                        <th>Nominal</th>
                                        <th>Keterangan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPinjaman">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahPinjaman">
    <div class="modal-dialog">
        <form id="formTambahPinjaman" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pinjaman</h5>
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

                <label class="mt-3">Tanggal Pinjaman (Pencairan)</label>
                <input type="date" name="tgl_pinjaman" class="form-control mb-3" required>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label>Total Pinjaman (Rp)</label>
                        <input type="text" name="nominal_pinjaman" id="inputPlafon" class="form-control mb-1 nominal-mask" required>
                    </div>
                    <div class="col-md-4">
                        <label>Cicil (Bulan)</label>
                        <input type="number" name="tenor" id="inputTenor" class="form-control mb-1" value="1" min="1" required>
                    </div>
                </div>
                <small class="text-muted d-block mb-3">* Biarkan angka 1 jika pinjaman langsung potong lunas.</small>

                <div class="alert alert-info py-2 mb-3" style="display: none;" id="infoCicilan">
                    Dipotong per bulan: <b id="textCicilan">Rp 0</b>
                </div>

                <label id="labelTglBayar">Tanggal Pembayaran</label>
                <input type="date" name="tgl_bayar_pinjaman" class="form-control mb-3" required>

                <label>Keterangan</label>
                <input type="text" name="ket_pinjaman" placeholder="Contoh: Panjar / SIO" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditPinjaman">
    <div class="modal-dialog">
        <form id="formEditPinjaman" class="modal-content">
            <input type="hidden" name="id_pinjaman">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pinjaman</h5>
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

                <label class="mt-3">Tanggal Pinjaman</label>
                <input type="date" name="tgl_pinjaman" class="form-control mb-3" required>

                <label>Tanggal Bayar</label>
                <input type="date" name="tgl_bayar_pinjaman" class="form-control mb-3">

                <label>Total Pinjaman</label>
                <input type="text" name="nominal_pinjaman" class="form-control mb-3 nominal-mask" required>

                <label>Keterangan</label>
                <input type="text" name="ket_pinjaman" class="form-control">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("filterBulan").addEventListener("change", function() {
            loadPinjaman();
        });

        const tbody = document.getElementById("tbodyPinjaman");
        const formTambah = document.getElementById("formTambahPinjaman");
        const formEdit = document.getElementById("formEditPinjaman");
        const formImport = document.getElementById("formImportPinjaman");

        // === EVENT LISTENER MASKING NOMINAL (VERSI ANTI-LOMPAT) ===
        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                let input = e.target;
                
                // Simpan posisi kursor saat ini
                let cursorPos = input.selectionStart;
                let prevLength = input.value.length;

                // Bersihkan dan format ulang angkanya
                let val = input.value.replace(/\D/g, "");
                let formatted = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                input.value = formatted;

                // Kembalikan posisi kursor ke tempat semula (disesuaikan dengan penambahan titik)
                let diff = formatted.length - prevLength;
                input.setSelectionRange(cursorPos + diff, cursorPos + diff);
            }
        });

        // === INISIALISASI SELECT2 ===
        function initSelect2() {
            $('#selectKaryawanTambah').select2({
                dropdownParent: $('#modalTambahPinjaman'),
                placeholder: 'Pilih Karyawan',
                allowClear: true,
                width: '100%'
            });

            $('#selectKaryawanEdit').select2({
                dropdownParent: $('#modalEditPinjaman'),
                placeholder: 'Pilih Karyawan',
                allowClear: true,
                width: '100%'
            });
        }

        // === RESET SELECT2 SAAT MODAL DITUTUP ===
        $('#modalTambahPinjaman').on('hidden.bs.modal', function() {
            $('#selectKaryawanTambah').val('').trigger('change');
        });

        // === LOAD DATA ===
        function loadPinjaman() {
            const bulan = document.getElementById("filterBulan").value;
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">Memuat data...</td></tr>';

            fetch("app/controller/get-data/get-pinjaman.php?bulan=" + bulan)
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

        // === UPDATE STATS PINJAMAN ===
        function updateStats() {
            const bulan = document.getElementById("filterBulan").value;
            fetch("app/controller/get-data/get-pinjaman-stats.php?bulan=" + bulan)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    document.getElementById("totalPinjaman").textContent = data.total || 0;
                    document.getElementById("totalNominal").textContent = "Rp " + (data.nominal || 0);
                    document.getElementById("labelNominal").textContent = data.teks_bulan;
                })
                .catch(error => {
                    console.error('Error updating stats:', error);
                });
        }

        // === IMPORT EXCEL/CSV ===
        formImport.addEventListener("submit", e => {
            e.preventDefault();
            const btn = document.getElementById("btnImportPinjaman");
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';

            let fd = new FormData(formImport);

            fetch("app/controller/csv-control/csv-pinjaman.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => {
                    if (!res.ok) throw new Error("File PHP tidak ditemukan atau Server Error (404/500)");
                    return res.json();
                })
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-upload"></i> Import Data';

                    if (res.status === "success" || res.status === "warning") {
                        showToast(res.message, res.status === "warning" ? "warning" : "success");
                        loadPinjaman(); // Refresh tabel
                        formImport.reset();
                    } else {
                        showToast("Gagal: " + res.message, "error");
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-upload"></i> Import Data';
                    console.error(err);
                    showToast("Terjadi kesalahan: " + err.message, "error");
                });
        });

        // === TAMBAH ===
        formTambah.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formTambah);
            
            // PERBAIKAN: Bersihkan titik pada nominal sebelum dikirim
            fd.set("nominal_pinjaman", fd.get("nominal_pinjaman").replace(/\./g, ""));
            fd.append("action", "add");

            fetch("app/controller/act-pinjaman.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Berhasil menambah pinjaman!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahPinjaman")).hide();
                        formTambah.reset();
                        $('#selectKaryawanTambah').val('').trigger('change');
                        loadPinjaman();
                    } else showToast(res.msg, "error");
                });
        });

        // === EDIT ===
        formEdit.addEventListener("submit", e => {
            e.preventDefault();
            let fd = new FormData(formEdit);
            
            // PERBAIKAN: Bersihkan titik pada nominal sebelum dikirim
            fd.set("nominal_pinjaman", fd.get("nominal_pinjaman").replace(/\./g, ""));
            fd.append("action", "edit");

            fetch("app/controller/act-pinjaman.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast("Berhasil update pinjaman!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditPinjaman")).hide();
                        loadPinjaman();
                    } else showToast(res.msg, "error");
                });
        });

        // === EDIT & DELETE BUTTON ===
        document.addEventListener("click", e => {
            const btnEdit = e.target.closest(".btnEdit");
            const btnDelete = e.target.closest(".btnDelete");

            // EDIT
            if (btnEdit) {
                formEdit.querySelector("input[name=id_pinjaman]").value = btnEdit.dataset.id;
                $('#selectKaryawanEdit').val(btnEdit.dataset.idkaryawan).trigger('change');
                formEdit.querySelector("input[name=tgl_pinjaman]").value = btnEdit.dataset.tglpinjaman;
                formEdit.querySelector("input[name=tgl_bayar_pinjaman]").value = btnEdit.dataset.tglbayar;
                
                // PERBAIKAN: Format nominal dengan titik saat modal edit ditarik datanya
                let rawNominal = btnEdit.dataset.pinjaman.toString();
                let angkaBulat = rawNominal.split(".")[0].replace(/\D/g, "");
                formEdit.querySelector("input[name=nominal_pinjaman]").value = angkaBulat.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                
                formEdit.querySelector("input[name=ket_pinjaman]").value = btnEdit.dataset.ket;

                new bootstrap.Modal(document.getElementById("modalEditPinjaman")).show();
            }

            // DELETE
            if (btnDelete) {
                const id = btnDelete.dataset.id;
                if (confirm("Yakin menghapus pinjaman?")) {
                    fetch("app/controller/act-pinjaman.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=delete&id_pinjaman=" + id
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast("Berhasil menghapus pinjaman!", "success");
                                loadPinjaman();
                            } else showToast(res.msg, "error");
                        });
                }
            }
        });

        window.postingPinjaman = function() {
            const bulan = document.getElementById("filterBulan").value;
            if (!bulan) return showToast("Pilih bulan terlebih dahulu!", "warning");

            if (confirm("Posting pencairan pinjaman bulan ini ke Laporan Keuangan? (Data lama di bulan ini akan direplace otomatis)")) {
                fetch("app/controller/act-posting-pinjaman.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "bulan=" + bulan
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") showToast(res.message, "success");
                        else if (res.status === "warning") showToast(res.message, "warning");
                        else showToast("Gagal: " + res.message, "error");
                    });
            }
        }; 

        initSelect2();
        loadPinjaman();
    });

    const inputPlafon = document.getElementById("inputPlafon");
    const inputTenor = document.getElementById("inputTenor");
    const infoCicilan = document.getElementById("infoCicilan");
    const textCicilan = document.getElementById("textCicilan");
    const labelTglBayar = document.getElementById("labelTglBayar"); 

    function hitungCicilan() {
        // PERBAIKAN: Bersihkan titik saat memproses hitung cicilan
        let plafonStr = inputPlafon.value.replace(/\./g, "");
        let plafon = parseFloat(plafonStr) || 0;
        let tenor = parseInt(inputTenor.value) || 1;

        if (tenor > 1) {
            labelTglBayar.innerText = "Tanggal Mulai Pembayaran";
        } else {
            labelTglBayar.innerText = "Tanggal Pembayaran";
        }

        if (plafon > 0 && tenor > 1) {
            let cicilanDasar = Math.floor(plafon / tenor);
            let sisaBagi = plafon - (cicilanDasar * tenor);
            let cicilanTerakhir = cicilanDasar + sisaBagi;

            if (sisaBagi > 0) {
                textCicilan.innerText = "Rp " + cicilanDasar.toLocaleString('id-ID') + " (Bulan terakhir: Rp " + cicilanTerakhir.toLocaleString('id-ID') + ")";
            } else {
                textCicilan.innerText = "Rp " + cicilanDasar.toLocaleString('id-ID');
            }

            infoCicilan.style.display = "block";
        } else {
            infoCicilan.style.display = "none";
        }
    }

    inputPlafon.addEventListener("input", hitungCicilan);
    inputTenor.addEventListener("input", hitungCicilan);
</script>