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
        width: 2.5rem;
        height: 2.5rem;
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
        font-weight: bold;
    }

    .bg-label-success {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .bg-label-primary {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .bg-label-info {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }

    .bg-label-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

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
            <h4 class="fw-bold mb-1"><i class="fa fa-money-bill-wave text-success"></i> Manajemen THR</h4>
            <p class="text-muted mb-0"><span class="text-muted fw-light">Master Data /</span> THR Karyawan</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-success text-white shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg">
                                    <div class="avatar-initial bg-white bg-opacity-25 rounded"><i class="fa fa-users fa-2x"></i></div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-white">Total Data THR</h6>
                                <h4 class="mb-0 fw-bold text-white" id="totalTHR">0</h4>
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
                                    <div class="avatar-initial bg-white bg-opacity-25 rounded"><i class="fa fa-money-bill-wave fa-2x"></i></div>
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

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-label-success rounded"><i class="fa fa-file-upload fa-2x"></i></div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1 fw-bold">Import Data THR</h5>
                                <p class="text-muted mb-0 small">
                                    <i class="fa fa-info-circle"></i> Format Excel: <strong>B</strong>=NIK | <strong>E</strong>=Mulai | <strong>F</strong>=Hari Raya | <strong>G-I</strong>=Gaji | <strong>J</strong>=Masa Kerja | <strong>K</strong>=Nominal
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <form id="formImportTHR" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="file" name="file_csv_thr" id="file_csv_thr" class="form-control" required accept=".csv,.xlsx,.xls">
                                <button type="submit" class="btn btn-success" id="btnImportTHR">
                                    <i class="fa fa-upload"></i> <span class="d-none d-sm-inline">Import</span>
                                </button>
                            </div>
                        </form>
                        <a href="assets/template-csv/Format Impor Data THR.xlsx" style="font-size: 1.8vh;" class="mt-1 d-block text-end">Download Template .xlsx</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa fa-filter text-primary"></i> Filter & Aksi</h6>
                    <div>
                        <button class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTHR">
                            <i class="fa fa-plus-circle"></i> Tambah THR
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="loadTHR()">
                            <i class="fa fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small mb-1">Periode Tahun</label>
                        <select id="filterPeriode" class="form-select form-select-sm">
                            <option value="">Semua Tahun</option>
                            <?php
                            $sqlPeriode = "SELECT DISTINCT periode FROM tb_thr ORDER BY periode DESC";
                            $resultPeriode = $koneksi->query($sqlPeriode);
                            if ($resultPeriode && $resultPeriode->num_rows > 0) {
                                $isFirst = true;
                                while ($rowPeriode = $resultPeriode->fetch_assoc()) {
                                    $tahun = htmlspecialchars($rowPeriode['periode']);
                                    $selected = $isFirst ? "selected" : "";
                                    echo "<option value='$tahun' $selected>$tahun</option>";
                                    $isFirst = false;
                                }
                            } else {
                                $tahun_sekarang = date('Y');
                                echo "<option value='$tahun_sekarang' selected>$tahun_sekarang</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label small mb-1">Site</label>
                        <select id="filterSite" class="form-select form-select-sm">
                            <option value="">Semua Site</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label small mb-1">Jabatan</label>
                        <select id="filterJabatan" class="form-select form-select-sm">
                            <option value="">Semua Jabatan</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small mb-1">Nama Bank</label>
                        <select id="filterBank" class="form-select form-select-sm">
                            <option value="">Semua Bank</option>
                            <option value="MANDIRI">MANDIRI</option>
                            <option value="BRI">BRI</option>
                            <option value="BNI">BNI</option>
                            <option value="BCA">BCA</option>
                            <option value="BELUM_PUNYA">BELUM PUNYA REKENING</option>
                            <option value="LAINNYA">LAINNYA</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">
                        <button class="btn btn-outline-success btn-sm w-100" onclick="exportToExcel()">
                            <i class="fa fa-file-excel"></i> Export
                        </button>
                        <button class="btn btn-outline-warning btn-sm w-100" onclick="bukaCetakSlip()">
                            <i class="fa fa-print"></i> Slip
                        </button>
                    </div>
                </div>
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
                    <table class="table table-hover" id="tabelTHR" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Karyawan</th>
                                <th>Jabatan & Site</th>
                                <th>Data Bank</th>
                                <th class="text-center">Periode</th>
                                <th class="text-center">Masa Kerja</th>
                                <th class="text-end">Nominal THR</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTHR">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    (function() {
        'use strict';

        const container = document.querySelector(".container-xxl");
        let allThrData = []; // Variabel global untuk JSON data

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        window.showToast = function(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: message
            });
        };

        function initSelect2() {
            $('.select2-init').each(function() {
                if ($(this).hasClass("select2-hidden-accessible")) $(this).select2('destroy');
                $(this).select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : $(document.body),
                    width: '100%',
                    placeholder: $(this).data('placeholder') || '-- Pilih Karyawan --',
                    allowClear: true
                });
            });
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function normalizeBankName(bankName) {
            if (!bankName) return 'BELUM_PUNYA';
            let bank = bankName.toUpperCase().trim();
            if (bank === 'BELUM PUNYA REKENING' || bank === 'BELUM_PUNYA') return 'BELUM_PUNYA';
            if (bank.includes('MANDIRI')) return 'MANDIRI';
            if (bank.includes('BRI')) return 'BRI';
            if (bank.includes('BNI')) return 'BNI';
            if (bank.includes('BCA')) return 'BCA';
            return 'LAINNYA';
        }

        // ============================================
        // POPULATE FILTERS & RENDER TABLE
        // ============================================
        function populateFilters(data) {
            let sites = [...new Set(data.map(row => row.nama_site).filter(s => s))];
            let siteSelect = document.getElementById("filterSite");
            let currentSite = siteSelect.value;
            siteSelect.innerHTML = '<option value="">Semua Site</option>';
            sites.sort().forEach(site => siteSelect.innerHTML += `<option value="${site}">${site}</option>`);
            siteSelect.value = currentSite;

            let jabatans = [...new Set(data.map(row => row.nama_jabatan).filter(j => j))];
            let jabatanSelect = document.getElementById("filterJabatan");
            let currentJabatan = jabatanSelect.value;
            jabatanSelect.innerHTML = '<option value="">Semua Jabatan</option>';
            jabatans.sort().forEach(jab => jabatanSelect.innerHTML += `<option value="${jab}">${jab}</option>`);
            jabatanSelect.value = currentJabatan;
        }

        function renderTable() {
            let filterSite = document.getElementById("filterSite").value;
            let filterJabatan = document.getElementById("filterJabatan").value;
            let filterBank = document.getElementById("filterBank").value;

            let filteredData = allThrData.filter(row => {
                let siteMatch = !filterSite || row.nama_site === filterSite;
                let jabatanMatch = !filterJabatan || row.nama_jabatan === filterJabatan;
                let bankMatch = true;
                if (filterBank) {
                    let normalizedBank = normalizeBankName(row.nama_bank);
                    bankMatch = normalizedBank === filterBank;
                }
                return siteMatch && jabatanMatch && bankMatch;
            });

            if ($.fn.DataTable.isDataTable("#tabelTHR")) {
                $("#tabelTHR").DataTable().destroy();
            }

            const tbody = document.getElementById("tbodyTHR");
            tbody.innerHTML = "";

            let totalData = 0;
            let totalNominal = 0;

            filteredData.forEach((row, index) => {
                totalData++;
                totalNominal += parseFloat(row.nominal_thr || 0);

                let initial = row.nama_lengkap ? row.nama_lengkap.charAt(0).toUpperCase() : '?';
                let jabatanBadge = row.nama_jabatan ? `<span class="badge bg-label-info mb-1">${row.nama_jabatan}</span>` : '-';
                let siteInfo = row.nama_site ? `<br><small class="text-muted"><i class="fa fa-building"></i> ${row.nama_site}</small>` : '';
                let bankDisplay = row.nama_bank || "BELUM PUNYA REKENING";
                let bankBadge = !row.nama_bank ? '<span class="badge bg-label-warning">Belum Ada Rekening</span>' : `<strong>${bankDisplay}</strong><br><small class="text-muted">${row.no_rek || ''}</small>`;

                let tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class='text-center fw-bold'>${index + 1}</td>
                    <td>
                        <div class='d-flex align-items-center'>
                            <div class='avatar avatar-sm bg-label-success rounded-circle me-2'>
                                <span class='avatar-initial'>${initial}</span>
                            </div>
                            <div>
                                <div class='fw-bold'>${row.nama_lengkap}</div>
                                <small class='text-muted'>NIK: ${row.nik_karyawan}</small>
                            </div>
                        </div>
                    </td>
                    <td>${jabatanBadge}${siteInfo}</td>
                    <td>${bankBadge}</td>
                    <td class='text-center'><span class='badge bg-label-primary'>Tahun ${row.periode}</span></td>
                    <td class='text-center fw-bold'>${row.masa_kerja} Bulan</td>
                    <td class='text-end fw-bold text-success'>Rp ${formatRupiah(row.nominal_thr)}</td>
                    <td class='text-center'>
                        <div class='btn-group btn-group-sm'>
                            <button class='btn btn-outline-primary btnEdit' 
                                data-id='${row.id_thr}' data-karyawan='${row.id_karyawan}'
                                data-mulai='${row.tgl_mulai}' data-raya='${row.tgl_hari_raya}'
                                data-gapok='${row.gaji_pokok}' data-lokasi='${row.tunjangan_lokasi}'
                                data-kebutuhan='${row.tunjangan_kebutuhan}' data-masa='${row.masa_kerja}'
                                data-nominal='${row.nominal_thr}' data-bs-toggle='tooltip' title='Edit'>
                                <i class='fa fa-edit'></i>
                            </button>
                            <button class='btn btn-outline-danger btnDelete' data-id='${row.id_thr}' data-bs-toggle='tooltip' title='Hapus'>
                                <i class='fa fa-trash'></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById("totalTHR").textContent = totalData;
            document.getElementById("totalNominal").textContent = "Rp " + formatRupiah(totalNominal);
            let perTahun = document.getElementById("filterPeriode").value;
            document.getElementById("labelNominal").textContent = perTahun ? `Total Tahun ${perTahun}` : "Total Keseluruhan";

            $("#tabelTHR").DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    "sEmptyTable": "Tidak ada data THR yang tersedia",
                    "sProcessing": "Sedang memproses...",
                    "sLengthMenu": "Tampilkan _MENU_ data",
                    "sZeroRecords": "Tidak ditemukan data yang sesuai",
                    "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "sSearch": "Cari:"
                }
            });
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        // ============================================
        // DOCUMENT READY
        // ============================================
        $(document).ready(function() {
            initSelect2();
            $('#modalTambahTHR, #modalEditTHR').on('shown.bs.modal', initSelect2);
            $('#modalTambahTHR, #modalEditTHR').on('hidden.bs.modal', function() {
                $(this).find('.select2-init').select2('destroy');
            });

            // FETCH DATA
            window.loadTHR = function() {
                const periode = document.getElementById("filterPeriode").value;
                fetch("app/controller/get-data/get-thr.php?periode=" + periode)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            allThrData = res.data;
                            populateFilters(allThrData);
                            renderTable();
                        } else {
                            showToast('Gagal memuat data: ' + res.message, 'error');
                        }
                    }).catch(error => {
                        console.error('Fetch Error:', error);
                        showToast('Gagal menghubungi server', 'error');
                    });
            };

            // FILTERS
            document.getElementById("filterPeriode").addEventListener("change", loadTHR);
            document.getElementById("filterSite").addEventListener("change", renderTable);
            document.getElementById("filterJabatan").addEventListener("change", renderTable);
            document.getElementById("filterBank").addEventListener("change", renderTable);

            // ============================================
            // EXPORT EXCEL (Server-Side)
            // ============================================
            window.exportToExcel = function() {
                let filterSite = document.getElementById("filterSite").value;
                let filterJabatan = document.getElementById("filterJabatan").value;
                let filterBank = document.getElementById("filterBank").value;

                // Filter data sesuai dropdown
                let filteredData = allThrData.filter(row => {
                    let siteMatch = !filterSite || row.nama_site === filterSite;
                    let jabatanMatch = !filterJabatan || row.nama_jabatan === filterJabatan;
                    let bankMatch = true;
                    if (filterBank) {
                        let normalizedBank = normalizeBankName(row.nama_bank);
                        bankMatch = normalizedBank === filterBank;
                    }
                    return siteMatch && jabatanMatch && bankMatch;
                });

                if (filteredData.length === 0) {
                    showToast("Tidak ada data untuk di-export!", "error");
                    return;
                }

                // Mapping data HANYA untuk kolom yang direquest
                let excelData = filteredData.map((row, index) => ({
                    "No": index + 1,
                    "NIK Karyawan": row.nik_karyawan,
                    "Nama Lengkap": row.nama_lengkap,
                    "Site": row.nama_site || "-",
                    "Jabatan": row.nama_jabatan || "-",
                    "Atas Nama Rekening": row.atas_nama_rek || "-",
                    "Nomor Rekening": row.no_rek || "-",
                    "Nama Bank": row.nama_bank || "BELUM PUNYA REKENING",
                    "Nominal THR": parseFloat(row.nominal_thr || 0)
                }));

                // Buat worksheet dan workbook
                let ws = XLSX.utils.json_to_sheet(excelData);
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Data THR");

                // Nama file dinamis
                let periode = document.getElementById("filterPeriode").value || "Semua_Tahun";

                // Hasilnya akan murni .xlsx
                XLSX.writeFile(wb, `Data_THR_${periode}.xlsx`);
            };


            window.bukaCetakSlip = function() {
                const periode = document.getElementById("filterPeriode").value;
                if (!periode) {
                    showToast("Pilih tahun periode terlebih dahulu sebelum mencetak slip!", "error");
                    return;
                }
                window.open("slip-thr.php?tahun=" + periode, "_blank");
            };

            // ============================================
            // CRUD & IMPORT HANDLERS (DIKEMBALIKAN UTUH)
            // ============================================

            // 1. TAMBAH THR
            const formTambah = document.getElementById("formTambahTHR");
            formTambah.addEventListener("submit", function(e) {
                e.preventDefault();
                let fd = new FormData(formTambah);
                fd.append("action", "add");

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
                            bootstrap.Modal.getInstance(document.getElementById("modalTambahTHR")).hide();
                            formTambah.reset();
                            $(formTambah).find('.select2-init').val(null).trigger('change');
                            loadTHR();
                        } else {
                            showToast(res.msg || 'Terjadi kesalahan', "error");
                        }
                    })
                    .catch(error => showToast('Gagal menyimpan data', "error"))
                    .finally(() => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fa fa-save"></i> Simpan';
                    });
            });

            // 2. KLIK EDIT & HAPUS PADA TABEL
            const formEdit = document.getElementById("formEditTHR");
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
                    new bootstrap.Modal(document.getElementById("modalEditTHR")).show();
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
                        if (result.isConfirmed) hapusTHR(id);
                    });
                }
            });

            // 3. EDIT THR
            formEdit.addEventListener("submit", function(e) {
                e.preventDefault();
                let fd = new FormData(formEdit);
                fd.append("action", "edit");

                const btnSubmit = formEdit.querySelector('button[type="submit"]');
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mengupdate...';

                // HARAP PASTIKAN PATH act-thr DI BAWAH INI SESUAI DENGAN FOLDER ANDA
                fetch("app/controller/act-thr.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast(res.msg, "success");
                            bootstrap.Modal.getInstance(document.getElementById("modalEditTHR")).hide();
                            loadTHR();
                        } else {
                            showToast(res.msg || 'Terjadi kesalahan', "error");
                        }
                    })
                    .catch(error => showToast('Gagal mengupdate data', "error"))
                    .finally(() => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fa fa-save"></i> Update';
                    });
            });

            // 4. FUNCTION HAPUS THR
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
                    .catch(error => showToast('Gagal menghapus data', "error"));
            }

            // 5. IMPORT CSV THR
            document.getElementById("formImportTHR").addEventListener("submit", function(e) {
                e.preventDefault();
                const fd = new FormData(this);
                const btn = document.getElementById("btnImportTHR");
                const fileInput = document.getElementById("file_csv_thr");

                if (!fileInput.files.length) {
                    showToast('Pilih file terlebih dahulu', 'error');
                    return;
                }

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

            // INITIAL LOAD PANGGIL
            loadTHR();
        });
    })();
</script>