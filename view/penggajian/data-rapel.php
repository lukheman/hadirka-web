<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
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
</head>
<body>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <div class="mb-4">
            <h4 class="fw-bold mb-1">
                <i class="fa fa-money-bill-wave text-success"></i> Manajemen Rapel
            </h4>
            <p class="text-muted mb-0">
                <span class="text-muted fw-light">Master Data /</span> Rapel Karyawan
            </p>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg">
                                    <div class="avatar-initial bg-white bg-opacity-25 rounded">
                                        <i class="fa fa-file-excel fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-white">Total Rapel</h6>
                                <h4 class="mb-0 fw-bold" id="totalRapel">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
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
                                <h6 class="mb-0 text-white">Total Nominal</h6>
                                <h4 class="mb-0 fw-bold" id="totalNominal">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg">
                                    <div class="avatar-initial bg-white bg-opacity-50 rounded">
                                        <i class="fa fa-calendar fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Bulan Ini</h6>
                                <h4 class="mb-0 fw-bold" id="bulanIni">0</h4>
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
                                <h5 class="mb-1 fw-bold">Import Data Rapel</h5>
                                <p class="text-muted mb-0 small">
                                    <i class="fa fa-info-circle"></i> Format: <strong>B</strong>=NIK | <strong>E</strong>=Tanggal | <strong>F</strong>=Nominal | <strong>G</strong>=Keterangan
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <form id="formImportRapel" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="file" name="file_csv_rapel" id="file_csv_rapel" 
                                       class="form-control" required accept=".csv,.xlsx,.xls">
                                <button type="submit" class="btn btn-success" id="btnImportRapel">
                                    <i class="fa fa-upload"></i> <span class="d-none d-sm-inline">Import</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRapel">
                    <i class="fa fa-plus-circle"></i> Tambah Rapel
                </button>
                <button class="btn btn-outline-secondary" onclick="loadRapel()">
                    <i class="fa fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div>
                <button class="btn btn-outline-success btn-sm" onclick="exportToExcel()">
                    <i class="fa fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="avatar avatar-sm bg-label-success rounded">
                        <i class="fa fa-table"></i>
                    </div>
                </div>
                <h5 class="mb-0 ms-3 fw-bold">Database Rapel Karyawan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover MyTable" id="tabelRapel">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th width="25%">Nama Karyawan</th>
                                <th class="text-center" width="15%">Tanggal Rapel</th>
                                <th class="text-end" width="20%">Nominal Rapel</th>
                                <th width="25%">Keterangan</th>
                                <th class="text-center" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRapel">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahRapel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formTambahRapel" class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Tambah Data Rapel</h5>
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
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Rapel</label>
                        <input type="date" name="tgl_rapel" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal Rapel</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="nominal_rapel" class="form-control" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea name="ket_rapel" class="form-control" rows="3" placeholder="Masukkan keterangan (opsional)"></textarea>
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
    <div class="modal fade" id="modalEditRapel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEditRapel" class="modal-content">
                <input type="hidden" name="id_rapel">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Data Rapel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Karyawan</label>
                        <select name="id_karyawan" class="form-select select2-init" required>
                            <option value="">-- Pilih Karyawan --</option>
                            <?php
                            $sql = data_karyawan('nik_karyawan');
                            foreach ($sql as $k) {
                                echo "<option value='{$k['id_karyawan']}'>{$k['nik_karyawan']} - {$k['nama_lengkap']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Rapel</label>
                        <input type="date" name="tgl_rapel" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal Rapel</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="nominal_rapel" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea name="ket_rapel" class="form-control" rows="3"></textarea>
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
        const tbody = document.getElementById("tbodyRapel");
        const formTambah = document.getElementById("formTambahRapel");
        const formEdit = document.getElementById("formEditRapel");
        
        console.log("Document ready - initializing...");
        
        // Initialize Select2 on page load
        initSelect2();
        
        // Re-initialize when modal shown
        $('#modalTambahRapel, #modalEditRapel').on('shown.bs.modal', function() {
            initSelect2();
        });
        
        // Cleanup when modal hidden
        $('#modalTambahRapel, #modalEditRapel').on('hidden.bs.modal', function() {
            $(this).find('.select2-init').select2('destroy');
        });
        
        // ============================================
        // FUNCTION: Load Rapel Data
        // ============================================
        window.loadRapel = function() {
            fetch("app/controller/get-data/get-rapel.php")
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.text();
                })
                .then(html => {
                    // Destroy existing DataTable
                    if ($.fn.DataTable.isDataTable("#tabelRapel")) {
                        $("#tabelRapel").DataTable().destroy();
                    }
                    
                    // Update tbody
                    tbody.innerHTML = html;
                    
                    // Initialize DataTable
                    $("#tabelRapel").DataTable({
                        responsive: true,
                        pageLength: 10,
                        order: [[2, 'desc']], // Sort by date
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                        }
                    });
                    
                    // Update statistics
                    updateStats();
                    
                    // Initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                })
                .catch(error => {
                    console.error('Error loading rapel:', error);
                    showToast('Gagal memuat data rapel', 'error');
                });
        };
        
        // ============================================
        // FUNCTION: Update Statistics
        // ============================================
        function updateStats() {
            fetch("app/controller/get-data/get-rapel-stats.php")
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    document.getElementById("totalRapel").textContent = data.total || 0;
                    document.getElementById("totalNominal").textContent = "Rp " + (data.nominal || 0).toLocaleString('id-ID');
                    document.getElementById("bulanIni").textContent = data.bulan_ini || 0;
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
            
            fetch("app/controller/act-rapel.php", { 
                method: "POST", 
                body: fd 
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    showToast(res.msg, "success");
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById("modalTambahRapel"));
                    modal.hide();
                    
                    // Reset form
                    formTambah.reset();
                    $(formTambah).find('.select2-init').val(null).trigger('change');
                    
                    // Reload data
                    loadRapel();
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
                
                formEdit.querySelector("input[name=id_rapel]").value = d.id;
                formEdit.querySelector("input[name=tgl_rapel]").value = d.tgl;
                formEdit.querySelector("input[name=nominal_rapel]").value = d.nominal;
                formEdit.querySelector("textarea[name=ket_rapel]").value = d.ket || '';
                
                // Set select2 value
                $(formEdit).find('select[name=id_karyawan]').val(d.karyawan).trigger('change');
                
                // Show modal
                const modalEdit = new bootstrap.Modal(document.getElementById("modalEditRapel"));
                modalEdit.show();
            }
            
            if (btnDelete) {
                const id = btnDelete.dataset.id;
                
                Swal.fire({
                    title: 'Hapus data rapel?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        hapusRapel(id);
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
            
            fetch("app/controller/act-rapel.php", { 
                method: "POST", 
                body: fd 
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    showToast(res.msg, "success");
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById("modalEditRapel"));
                    modal.hide();
                    
                    // Reload data
                    loadRapel();
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
        // FUNCTION: Delete Rapel
        // ============================================
        function hapusRapel(id) {
            fetch("app/controller/act-rapel.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=delete&id_rapel=" + id
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    showToast(res.msg, "success");
                    loadRapel();
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
        document.getElementById("formImportRapel").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const fd = new FormData(this);
            const btn = document.getElementById("btnImportRapel");
            const fileInput = document.getElementById("file_csv_rapel");
            
            // Validate file
            if (!fileInput.files.length) {
                showToast('Pilih file terlebih dahulu', 'error');
                return;
            }
            
            // Disable button & add loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span class="d-none d-sm-inline">Importing...</span>';
            container.classList.add("blur-loading");
            
            fetch("app/controller/csv-control/csv-rapel.php", { 
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
                
                if(data.status === "success" || data.status === "warning") {
                    loadRapel();
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
            window.location.href = "app/controller/export/export-rapel.php";
        };
        
        // ============================================
        // INITIAL LOAD
        // ============================================
        loadRapel();
        
        console.log("Initialization complete!");
    });
})();
</script>

</body>
</html>