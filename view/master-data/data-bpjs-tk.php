<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    .hover-lift { transition: all 0.3s ease; cursor: pointer; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; }
    .avatar { width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; }
    .avatar-sm { width: 2.5rem; height: 2.5rem; }
    .avatar-lg { width: 3.5rem; height: 3.5rem; }
    .avatar-initial { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-weight: bold; }
    .bg-label-success { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
    .bg-label-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .bg-label-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .bg-label-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="mb-4">
            <h4 class="fw-bold mb-1"><i class="fa fa-id-card text-primary"></i> Data BPJS Ketenagakerjaan</h4>
            <p class="text-muted mb-0"><span class="text-muted fw-light">Master Data /</span> BPJS TK</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card bg-primary text-white shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded"><i class="fa fa-users fa-2x"></i></div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Total Pegawai</h6>
                                <h4 class="mb-0 fw-bold text-white" id="totalPegawai">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card bg-success text-white shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded"><i class="fa fa-check-circle fa-2x"></i></div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Sudah Terdaftar</h6>
                                <h4 class="mb-0 fw-bold text-white" id="totalTerdaftar">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded"><i class="fa fa-times-circle fa-2x"></i></div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Belum Terdaftar</h6>
                                <h4 class="mb-0 fw-bold text-white" id="totalBelum">0</h4>
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
                                <h5 class="mb-1 fw-bold">Import Data BPJS Ketenagakerjaan</h5>
                                <p class="text-muted mb-0 small">
                                    <i class="fa fa-info-circle"></i> Format Excel: <strong>A</strong>=No | <strong>B</strong>=NIK KTP | <strong>C</strong>=Nama Karyawan | <strong>D</strong>=Nomor BPJS
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <form id="formImportBPJS" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="file" name="file_csv_bpjs" id="file_csv_bpjs" class="form-control" required accept=".csv,.xlsx,.xls">
                                <button type="submit" class="btn btn-success" id="btnImportBPJS">
                                    <i class="fa fa-upload"></i> <span class="d-none d-sm-inline">Import</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <a href="assets/template-csv/Format_Impor_Data_BPJS_Ketenagakerjaan.xlsx" style="font-size: 2vh;">Download Template Format .xlsx</a> 
                </div>
            </div>
        </div>
        
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="fa fa-filter text-primary"></i> Filter & Aksi</h6>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm" onclick="loadBPJS()">
                            <i class="fa fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small mb-1">Status Karyawan</label>
                        <select id="filterStatusKar" class="form-select form-select-sm">
                            <option value="AKTIF">Aktif</option>
                            <option value="NONAKTIF">Selain Aktif</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label small mb-1">Status BPJS</label>
                        <select id="filterStatusBPJS" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="SUDAH">Sudah Terdaftar</option>
                            <option value="BELUM">Belum Terdaftar</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
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
                    <div class="col-lg-2 col-md-8 d-flex align-items-end">
                        <button class="btn btn-success btn-sm w-100" onclick="exportToExcel()">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center">
                <div class="avatar avatar-sm bg-label-primary rounded"><i class="fa fa-table"></i></div>
                <h5 class="mb-0 ms-3 fw-bold">Database BPJS Ketenagakerjaan</h5>
            </div>
            <div class="card-body mt-3">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelBPJS" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Karyawan</th>
                                <th>Site & Jabatan</th>
                                <th>No BPJS Ketenagakerjaan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyBPJS">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditBPJS" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEditBPJS" class="modal-content">
                <input type="hidden" name="id_karyawan" id="edit_id_karyawan">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Update No BPJS Ketenagakerjaan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Karyawan</label>
                        <input type="text" id="edit_nama_karyawan" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor BPJS Ketenagakerjaan</label>
                        <input type="text" name="bpjs_tk" id="edit_bpjs_tk" class="form-control" placeholder="Masukkan 11 digit nomor BPJS">
                        <small class="text-muted">Kosongkan jika memang belum terdaftar.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    (function() {
        'use strict';
        let allBPJSData = [];

        function cekTerdaftar(bpjs) {
            // Akan return FALSE jika null, kosong, atau isinya hanya "-"
            return bpjs && bpjs.trim() !== '' && bpjs.trim() !== '-';
        }

        // showToast: pakai definisi global di index.php (P1-JS-4, jangan definisikan ulang).

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
            let filterStatusBPJS = document.getElementById("filterStatusBPJS").value;
            // Filter status karyawan (Aktif/Nonaktif) dikerjakan dari backend (get-bpjs-tk.php),
            // jadi untuk filter array lokal di browser, kita hanya memproses 3 variabel di atas.

            let filteredData = allBPJSData.filter(row => {
                let siteMatch = !filterSite || row.nama_site === filterSite;
                let jabatanMatch = !filterJabatan || row.nama_jabatan === filterJabatan;
                
                let isTerdaftar = cekTerdaftar(row.bpjs_tk);
                let statusMatch = true;
                if (filterStatusBPJS === 'SUDAH') statusMatch = isTerdaftar;
                if (filterStatusBPJS === 'BELUM') statusMatch = !isTerdaftar;

                return siteMatch && jabatanMatch && statusMatch;
            });

            if ($.fn.DataTable.isDataTable("#tabelBPJS")) {
                $("#tabelBPJS").DataTable().destroy();
            }

            const tbody = document.getElementById("tbodyBPJS");
            tbody.innerHTML = "";

            filteredData.forEach((row, index) => {
                let initial = row.nama_lengkap ? row.nama_lengkap.charAt(0).toUpperCase() : '?';
                let jabatanBadge = row.nama_jabatan ? `<span class="badge bg-label-info mb-1">${row.nama_jabatan}</span>` : '-';
                let siteInfo = row.nama_site ? `<br><small class="text-muted"><i class="fa fa-building"></i> ${row.nama_site}</small>` : '';
                
                let isTerdaftar = cekTerdaftar(row.bpjs_tk);
                let bpjsDisplay = isTerdaftar ? 
                    `<strong class="text-success">${row.bpjs_tk}</strong>` : 
                    `<span class="badge bg-label-danger">Belum Terdaftar</span>`;

                let tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class='text-center fw-bold'>${index + 1}</td>
                    <td>
                        <div class='d-flex align-items-center'>
                            <div class='avatar avatar-sm bg-label-primary rounded-circle me-2'>
                                <span class='avatar-initial'>${initial}</span>
                            </div>
                            <div>
                                <div class='fw-bold'>${row.nama_lengkap}</div>
                                <small class='text-muted'>NIK: ${row.nik_karyawan}</small>
                            </div>
                        </div>
                    </td>
                    <td>${jabatanBadge}${siteInfo}</td>
                    <td>${bpjsDisplay}</td>
                    <td class='text-center'>
                        <button class='btn btn-outline-primary btn-sm btnEdit' 
                            data-id='${row.id_karyawan}' 
                            data-nama='${row.nama_lengkap}'
                            data-bpjs='${row.bpjs_tk || ''}' 
                            data-bs-toggle='tooltip' title='Edit BPJS'>
                            <i class='fa fa-edit'></i> Update
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            $("#tabelBPJS").DataTable({ responsive: true, pageLength: 10 });
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        $(document).ready(function() {
            window.loadBPJS = function() {
                let statusKaryawan = document.getElementById("filterStatusKar").value;

                fetch("app/controller/get-data/get-bpjs-tk.php?status_kar=" + statusKaryawan)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            allBPJSData = res.data;
                            
                            // Hitung untuk Banner
                            let total = allBPJSData.length;
                            let terdaftar = allBPJSData.filter(d => cekTerdaftar(d.bpjs_tk)).length;
                            let belum = total - terdaftar;

                            document.getElementById("totalPegawai").innerText = total;
                            document.getElementById("totalTerdaftar").innerText = terdaftar;
                            document.getElementById("totalBelum").innerText = belum;

                            populateFilters(allBPJSData);
                            renderTable();
                        } else {
                            showToast('Gagal memuat data: ' + res.message, 'error');
                        }
                    }).catch(error => {
                        console.error('Fetch Error:', error);
                        showToast('Gagal menghubungi server', 'error');
                    });
            };

            // Event Listeners Filters
            // Khusus Status Karyawan, panggil loadBPJS (mengambil ulang dari DB)
            document.getElementById("filterStatusKar").addEventListener("change", loadBPJS);
            
            // Sisanya cukup merender ulang array lokal
            document.getElementById("filterStatusBPJS").addEventListener("change", renderTable);
            document.getElementById("filterSite").addEventListener("change", renderTable);
            document.getElementById("filterJabatan").addEventListener("change", renderTable);

            // Export Excel
            window.exportToExcel = function() {
                let filterSite = document.getElementById("filterSite").value;
                let filterJabatan = document.getElementById("filterJabatan").value;
                let filterStatusBPJS = document.getElementById("filterStatusBPJS").value;
                let filterStatusKar = document.getElementById("filterStatusKar").value; // Tambahan

                let url = `app/controller/export/export-bpjs-tk.php?site=${encodeURIComponent(filterSite)}&jabatan=${encodeURIComponent(filterJabatan)}&status=${encodeURIComponent(filterStatusBPJS)}&status_kar=${encodeURIComponent(filterStatusKar)}`;
                window.location.href = url;
            };

            // 2. Event Listener untuk Form Import BPJS
            document.getElementById("formImportBPJS").addEventListener("submit", function(e) {
                e.preventDefault();
                const fd = new FormData(this);
                const btn = document.getElementById("btnImportBPJS");
                const fileInput = document.getElementById("file_csv_bpjs");

                if (!fileInput.files.length) {
                    showToast('Pilih file terlebih dahulu', 'error');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Importing...';
                
                // Panggil file csv-bpjs-tk.php 
                fetch("app/controller/csv-control/csv-bpjs-tk.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        let message = data.message || 'Import selesai';

                        if (data.errors && data.errors.length > 0) {
                            // Tampilkan di console untuk debugging
                            console.error("Detail Error Import:", data.errors); 
                            
                            // Tambahkan ke pesan SweetAlert dengan format list HTML
                            message += '<hr><div class="text-start small text-danger" style="max-height: 150px; overflow-y: auto;">';
                            message += '<strong>Detail Error:</strong><ul class="mb-0 ps-3">';
                            data.errors.forEach(err => {
                                message += `<li>${err}</li>`;
                            });
                            message += '</ul></div>';
                        }
                        
                        if (data.status === "success" || data.status === "warning") {
                            loadBPJS(); // Reload tabel dan banner otomatis
                            this.reset();
                        }
                        
                        Swal.fire({
                            icon: data.status === 'success' ? 'success' : data.status === 'warning' ? 'warning' : 'error',
                            title: data.status === 'success' ? 'Berhasil!' : 'Pemberitahuan',
                            html: message,
                            showConfirmButton: true
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Gagal mengimport file', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-upload"></i> <span class="d-none d-sm-inline">Import</span>';
                    });
            });

            // Modal Edit Handler
            document.addEventListener("click", function(e) {
                const btnEdit = e.target.closest(".btnEdit");
                if (btnEdit) {
                    const d = btnEdit.dataset;
                    document.getElementById("edit_id_karyawan").value = d.id;
                    document.getElementById("edit_nama_karyawan").value = d.nama;
                    document.getElementById("edit_bpjs_tk").value = d.bpjs;
                    new bootstrap.Modal(document.getElementById("modalEditBPJS")).show();
                }
            });

            // Submit Edit Form
            document.getElementById("formEditBPJS").addEventListener("submit", function(e) {
                e.preventDefault();
                let fd = new FormData(this);
                fd.append("action", "update_bpjs");

                const btnSubmit = this.querySelector('button[type="submit"]');
                let originalText = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';

                // Sesuaikan path action controller ini dengan struktur folder Anda
                fetch("app/controller/act-bpjs-tk.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        showToast(res.msg, "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditBPJS")).hide();
                        loadBPJS();
                    } else {
                        showToast(res.msg || 'Terjadi kesalahan', "error");
                    }
                })
                .catch(error => showToast('Gagal mengupdate data', "error"))
                .finally(() => {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                });
            });

            loadBPJS();
        });
    })();
</script>