    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        .hover-lift {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .blur-loading {
            opacity: 0.6;
            pointer-events: none;
            filter: blur(2px);
            transition: all 0.3s ease;
        }

        .avatar {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-sm {
            width: 2rem;
            height: 2rem;
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

        .bg-label-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .bg-label-primary {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        /* Perbaikan Select2 untuk Bootstrap 5 */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #ced4da !important;
            min-height: 38px;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            padding: 0.375rem 0.75rem;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }
    </style>

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="mb-4">
                <h4 class="fw-bold mb-1">
                    <i class="fa fa-money-bill-wave text-success"></i> Manajemen THR
                </h4>
                <p class="text-muted mb-0">
                    <span class="text-muted fw-light">Master Data /</span> THR Karyawan
                </p>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-success text-white shadow-sm hover-lift">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg">
                                        <div class="avatar-initial bg-white bg-opacity-25 rounded">
                                            <i class="fa fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-white">Total Data THR</h6>
                                    <h4 class="mb-0 fw-bold" id="totalTHR">0</h4>
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
                                            <i class="fa fa-money-bill-wave fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-white" id="labelNominal">Total Keseluruhan</h6>
                                    <h4 class="mb-0 fw-bold" id="totalNominal">Rp 0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3 mb-md-0">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg bg-label-success rounded">
                                        <i class="fa fa-file-upload fa-2x"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1 fw-bold">Import Data THR</h5>
                                    <p class="text-muted mb-0 small">
                                        <i class="fa fa-info-circle"></i> Format Excel: <strong>B</strong>=NIK | <strong>E</strong>=Tgl Mulai | <strong>F</strong>=Tgl Raya | <strong>G-I</strong>=Komponen Gaji | <strong>J</strong>=Masa Kerja | <strong>K</strong>=Nominal
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <form id="formImportTHR" enctype="multipart/form-data">
                                <div class="input-group">
                                    <input type="file" name="file_csv_thr" id="file_csv_thr"
                                        class="form-control" required accept=".csv,.xlsx,.xls">
                                    <button type="submit" class="btn btn-success" id="btnImportTHR">
                                        <i class="fa fa-upload"></i> <span class="d-none d-sm-inline">Import</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <a href="assets/template-csv/Format Impor Data THR.xlsx" style="font-size: 2vh;">Download Template Format .xlsx</a>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTHR">
                        <i class="fa fa-plus-circle"></i> Tambah THR
                    </button>
                    <button class="btn btn-outline-secondary" onclick="loadTHR()">
                        <i class="fa fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <select id="filterPeriode" class="form-select form-select-sm" style="width: 130px;">
                        <option value="">Semua Tahun</option>
                        <?php
                        // Query untuk mengambil tahun unik, diurutkan dari yang terbaru
                        $sqlPeriode = "SELECT DISTINCT periode FROM tb_thr ORDER BY periode DESC";
                        $resultPeriode = $koneksi->query($sqlPeriode);

                        // Cek apakah ada datanya
                        if ($resultPeriode && $resultPeriode->num_rows > 0) {
                            $isFirst = true; // Bikin penanda untuk tahun urutan pertama (terbaru)

                            while ($rowPeriode = $resultPeriode->fetch_assoc()) {
                                $tahun = htmlspecialchars($rowPeriode['periode']);

                                // Jika ini iterasi pertama, tambahkan teks 'selected', jika tidak, kosongkan
                                $selected = $isFirst ? "selected" : "";

                                echo "<option value='$tahun' $selected>$tahun</option>";

                                $isFirst = false; // Ubah jadi false agar iterasi tahun berikutnya tidak ikut 'selected'
                            }
                        } else {
                            // Fallback: Jika tabel kosong, tampilkan tahun ini dan langsung di-select
                            $tahun_sekarang = date('Y');
                            echo "<option value='$tahun_sekarang' selected>$tahun_sekarang</option>";
                        }
                        ?>
                    </select>
                    <button class="btn btn-outline-success btn-sm" onclick="exportToExcel()">
                        <i class="fa fa-file-excel"></i> Export Excel
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="bukaCetakSlip()">
                        <i class="fa fa-print"></i> Cetak Slip
                    </button>
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm bg-label-success rounded">
                            <i class="fa fa-table"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 ms-3 fw-bold">Database THR Karyawan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover MyTable" id="tabelTHR">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="25%">Nama Karyawan</th>
                                    <th class="text-center" width="10%">Periode</th>
                                    <th class="text-center" width="15%">Masa Kerja</th>
                                    <th class="text-end" width="20%">Nominal THR</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTHR">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah -->
        <div class="modal fade" id="modalTambahTHR" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="formTambahTHR" class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Tambah Data THR</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Karyawan</label>
                            <select name="id_karyawan" class="form-select select2-init" required>
                                <option value="">-- Pilih Karyawan --</option>
                                <?php
                                $sql = data_karyawan('nama_lengkap');
                                foreach ($sql as $k) {
                                    echo "<option value='{$k['id_karyawan']}'>{$k['nik_karyawan']} - {$k['nama_lengkap']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tgl Mulai Kerja</label>
                                <input type="date" name="tgl_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tgl Hari Raya</label>
                                <input type="date" name="tgl_hari_raya" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-muted small">Gaji Pokok</label>
                                <input type="number" name="gaji_pokok" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-muted small">Tunj. Lokasi</label>
                                <input type="number" name="tunjangan_lokasi" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-muted small">Tunj. Kebutuhan</label>
                                <input type="number" name="tunjangan_kebutuhan" class="form-control" value="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Masa Kerja (Bulan)</label>
                                <input type="number" name="masa_kerja" class="form-control" placeholder="Masa Kerja" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Nominal THR</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="nominal_thr" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditTHR" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="formEditTHR" class="modal-content">
                    <input type="hidden" name="id_thr">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Data THR</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Karyawan</label>
                            <select name="id_karyawan" class="form-select select2-init" required>
                                <option value="">-- Pilih Karyawan --</option>
                                <?php
                                $sql = data_karyawan('nama_lengkap');
                                foreach ($sql as $k) {
                                    echo "<option value='{$k['id_karyawan']}'>{$k['nik_karyawan']} - {$k['nama_lengkap']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tgl Mulai Kerja</label>
                                <input type="date" name="tgl_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tgl Hari Raya</label>
                                <input type="date" name="tgl_hari_raya" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-muted small">Gaji Pokok</label>
                                <input type="number" name="gaji_pokok" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-muted small">Tunj. Lokasi</label>
                                <input type="number" name="tunjangan_lokasi" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-muted small">Tunj. Kebutuhan</label>
                                <input type="number" name="tunjangan_kebutuhan" class="form-control" value="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Masa Kerja (Bulan)</label>
                                <input type="number" name="masa_kerja" class="form-control" placeholder="Masa Kerja" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Nominal THR</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="nominal_thr" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================
     URUTAN SCRIPT YANG BENAR - JANGAN DIUBAH!
     ============================================ -->

    <!-- 1. jQuery (HARUS PALING ATAS) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- 2. Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 3. DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- 4. Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- 5. SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 6. SCRIPT UTAMA -->
    <script>
        (function() {
            'use strict';

            const container = document.querySelector(".container-xxl");

            // ============================================
            // FUNCTION: Toast Notification
            // ============================================
            window.showToast = function(message, type = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });

                Toast.fire({
                    icon: type === 'success' ? 'success' : 'error',
                    title: message
                });
            };

            // ============================================
            // FUNCTION: Initialize Select2
            // ============================================
            function initSelect2() {
                $('.select2-init').each(function() {
                    // Destroy existing instance
                    if ($(this).hasClass("select2-hidden-accessible")) {
                        $(this).select2('destroy');
                    }

                    // Initialize with config
                    $(this).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : $(document.body),
                        width: '100%',
                        placeholder: $(this).data('placeholder') || '-- Pilih Karyawan --',
                        allowClear: true,
                        language: {
                            noResults: function() {
                                return "Tidak ada hasil ditemukan";
                            },
                            searching: function() {
                                return "Mencari...";
                            }
                        }
                    });
                });
            }

            // ============================================
            // DOCUMENT READY
            // ============================================
            $(document).ready(function() {
                const tbody = document.getElementById("tbodyTHR");
                const formTambah = document.getElementById("formTambahTHR");
                const formEdit = document.getElementById("formEditTHR");

                console.log("Document ready - initializing...");

                // Initialize Select2 on page load
                initSelect2();

                // Re-initialize when modal shown
                $('#modalTambahTHR, #modalEditTHR').on('shown.bs.modal', function() {
                    initSelect2();
                });

                // Cleanup when modal hidden
                $('#modalTambahTHR, #modalEditTHR').on('hidden.bs.modal', function() {
                    $(this).find('.select2-init').select2('destroy');
                });

                // ============================================
                // HANDLER: Filter Periode Change
                // ============================================
                document.getElementById("filterPeriode").addEventListener("change", function() {
                    loadTHR();
                    // opsional: panggil updateStats() juga disini jika kamu ingin 
                    // card "Total THR" & "Total Nominal" ikut difilter berdasarkan tahun
                });

                // ============================================
                // FUNCTION: Load THR Data
                // ============================================
                window.loadTHR = function() {
                    // 1. Ambil nilai filter saat ini
                    const periode = document.getElementById("filterPeriode").value;

                    // 2. Sisipkan parameter periode ke URL fetch
                    fetch("app/controller/get-data/get-thr.php?periode=" + periode)
                        .then(res => {
                            if (!res.ok) throw new Error('Network response was not ok');
                            return res.text();
                        })
                        .then(html => {
                            // Destroy existing DataTable
                            if ($.fn.DataTable.isDataTable("#tabelTHR")) {
                                $("#tabelTHR").DataTable().destroy();
                            }

                            // Update tbody
                            tbody.innerHTML = html;

                            // Initialize DataTable
                            // Initialize DataTable
                            $("#tabelTHR").DataTable({
                                responsive: true,
                                pageLength: 10,
                                order: [
                                    [2, 'desc']
                                ],
                                language: {
                                    "sEmptyTable": "Tidak ada data THR yang tersedia",
                                    "sProcessing": "Sedang memproses...",
                                    "sLengthMenu": "Tampilkan _MENU_ data",
                                    "sZeroRecords": "Tidak ditemukan data yang sesuai",
                                    "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                                    "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                                    "sInfoFiltered": "(disaring dari _MAX_ data keseluruhan)",
                                    "sSearch": "Cari:",
                                    "oPaginate": {
                                        "sFirst": "Pertama",
                                        "sPrevious": "Sebelumnya",
                                        "sNext": "Selanjutnya",
                                        "sLast": "Terakhir"
                                    }
                                }
                            });

                            // Update statistics
                            updateStats();

                            // Initialize tooltips
                            $('[data-bs-toggle="tooltip"]').tooltip();
                        })
                        .catch(error => {
                            console.error('Error loading THR:', error);
                            showToast('Gagal memuat data THR', 'error');
                        });
                };

                // ============================================
                // FUNCTION: Update Statistics
                // ============================================
                function updateStats() {
                    const periode = document.getElementById("filterPeriode").value;

                    fetch("app/controller/get-data/get-thr-stats.php?periode=" + periode)
                        .then(res => {
                            if (!res.ok) throw new Error('Network response was not ok');
                            return res.json();
                        })
                        .then(data => {
                            // Update angka total
                            document.getElementById("totalTHR").textContent = data.total || 0;
                            document.getElementById("totalNominal").textContent = "Rp " + (data.nominal || 0);

                            // Update teks label tahun (Misal: "Total Tahun 2026")
                            document.getElementById("labelNominal").textContent = data.teks_periode;
                        })
                        .catch(error => {
                            console.error('Error updating stats:', error);
                        });
                }

                // ============================================
                // HANDLER: Form Tambah Submit
                // ============================================
                formTambah.addEventListener("submit", function(e) {
                    e.preventDefault();

                    let fd = new FormData(formTambah);
                    fd.append("action", "add");

                    // Disable button
                    const btnSubmit = formTambah.querySelector('button[type="submit"]');
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';

                    fetch("app/controller/act-thr.php", {
                            method: "POST",
                            body: fd
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast(res.msg, "success");

                                // Close modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById("modalTambahTHR"));
                                modal.hide();

                                // Reset form
                                formTambah.reset();
                                $(formTambah).find('.select2-init').val(null).trigger('change');

                                // Reload data
                                loadTHR();
                            } else {
                                showToast(res.msg || 'Terjadi kesalahan', "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Gagal menyimpan data', "error");
                        })
                        .finally(() => {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="fa fa-save"></i> Simpan';
                        });
                });

                // ============================================
                // HANDLER: Click Edit & Delete Buttons
                // ============================================
                document.addEventListener("click", function(e) {
                    const btnEdit = e.target.closest(".btnEdit");
                    const btnDelete = e.target.closest(".btnDelete");

                    if (btnEdit) {
                        const d = btnEdit.dataset;
                        formEdit.querySelector("input[name=id_thr]").value = d.id;
                        formEdit.querySelector("input[name=tgl_mulai]").value = d.mulai;
                        formEdit.querySelector("input[name=tgl_hari_raya]").value = d.raya;
                        formEdit.querySelector("input[name=gaji_pokok]").value = d.gapok;
                        formEdit.querySelector("input[name=tunjangan_lokasi]").value = d.lokasi;
                        formEdit.querySelector("input[name=tunjangan_kebutuhan]").value = d.kebutuhan;
                        formEdit.querySelector("input[name=masa_kerja]").value = d.masa;
                        formEdit.querySelector("input[name=nominal_thr]").value = d.nominal;

                        $(formEdit).find('select[name=id_karyawan]').val(d.karyawan).trigger('change');
                        const modalEdit = new bootstrap.Modal(document.getElementById("modalEditTHR"));
                        modalEdit.show();
                    }

                    if (btnDelete) {
                        const id = btnDelete.dataset.id;

                        Swal.fire({
                            title: 'Hapus data THR?',
                            text: "Data yang dihapus tidak dapat dikembalikan!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                hapusTHR(id);
                            }
                        });
                    }
                });

                // ============================================
                // HANDLER: Form Edit Submit
                // ============================================
                formEdit.addEventListener("submit", function(e) {
                    e.preventDefault();

                    let fd = new FormData(formEdit);
                    fd.append("action", "edit");

                    // Disable button
                    const btnSubmit = formEdit.querySelector('button[type="submit"]');
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mengupdate...';

                    fetch("app/controller/act-thr", {
                            method: "POST",
                            body: fd
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast(res.msg, "success");

                                // Close modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById("modalEditTHR"));
                                modal.hide();

                                // Reload data
                                loadTHR();
                            } else {
                                showToast(res.msg || 'Terjadi kesalahan', "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Gagal mengupdate data', "error");
                        })
                        .finally(() => {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="fa fa-save"></i> Update';
                        });
                });

                // ============================================
                // FUNCTION: Delete THR
                // ============================================
                function hapusTHR(id) {
                    fetch("app/controller/act-thr.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=delete&id_thr=" + id
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                showToast(res.msg, "success");
                                loadTHR();
                            } else {
                                showToast(res.msg || 'Gagal menghapus data', "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Gagal menghapus data', "error");
                        });
                }

                // ============================================
                // HANDLER: Import CSV/Excel
                // ============================================
                document.getElementById("formImportTHR").addEventListener("submit", function(e) {
                    e.preventDefault();

                    const fd = new FormData(this);
                    const btn = document.getElementById("btnImportTHR");
                    const fileInput = document.getElementById("file_csv_thr");

                    // Validate file
                    if (!fileInput.files.length) {
                        showToast('Pilih file terlebih dahulu', 'error');
                        return;
                    }

                    // Disable button & add loading state
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span class="d-none d-sm-inline">Importing...</span>';
                    container.classList.add("blur-loading");

                    fetch("app/controller/csv-control/csv-thr.php", {
                            method: "POST",
                            body: fd
                        })
                        .then(res => res.json())
                        .then(data => {
                            let message = data.message || 'Import selesai';

                            // Add details if available
                            if (data.insert_count || data.update_count || data.error_count) {
                                message += '<br><small>';
                                if (data.insert_count > 0) message += `✓ ${data.insert_count} data baru<br>`;
                                if (data.update_count > 0) message += `⟳ ${data.update_count} data diupdate<br>`;
                                if (data.error_count > 0) message += `✗ ${data.error_count} data error<br>`;
                                message += '</small>';
                            }

                            Swal.fire({
                                icon: data.status === 'success' ? 'success' : data.status === 'warning' ? 'warning' : 'error',
                                title: data.status === 'success' ? 'Berhasil!' : data.status === 'warning' ? 'Selesai dengan Warning' : 'Gagal!',
                                html: message,
                                showConfirmButton: true
                            });

                            if (data.status === "success" || data.status === "warning") {
                                loadTHR();
                                this.reset();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Gagal mengimport file', 'error');
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa fa-upload"></i> <span class="d-none d-sm-inline">Import</span>';
                            container.classList.remove("blur-loading");
                        });
                });

                // ============================================
                // FUNCTION: Export to Excel
                // ============================================
                window.exportToExcel = function() {
                    // Ambil nilai tahun dari dropdown filter
                    const periode = document.getElementById("filterPeriode").value;

                    // Kirim nilai periode via URL (GET) ke file export
                    window.location.href = "app/controller/export/export-thr.php?periode=" + periode;
                };

                // ============================================
                // FUNCTION: Buka Halaman Cetak Slip
                // ============================================
                window.bukaCetakSlip = function() {
                    const periode = document.getElementById("filterPeriode").value;
                    if (!periode) {
                        showToast("Pilih tahun periode terlebih dahulu sebelum mencetak slip!", "error");
                        return;
                    }
                    // Buka tab baru menuju halaman slip THR dengan parameter tahun
                    window.open("slip-thr.php?tahun=" + periode, "_blank");
                };

                // ============================================
                // INITIAL LOAD
                // ============================================
                loadTHR();

                console.log("Initialization complete!");
            });
        })();
    </script>