<style>
    /* --- MODERN VARIABLES --- */
    :root {
        --primary-color: #4e73df;
        --primary-hover: #3a5dc7;
        --success-color: #1cc88a;
        --success-hover: #17a673;
        --danger-color: #e74a3b;
        --warning-color: #f6c23e;
        --bg-light: #f8f9fc;
        --text-dark: #2c3e50;
        --text-muted: #6c757d;
        --border-radius: 12px;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    /* --- GLOBAL STYLES --- */
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: var(--bg-light);
    }

    /* --- CARD & BUTTON BEAUTIFY --- */
    .card {
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        border: none;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #5a67d8 100%);
        color: white;
        font-weight: 600;
        padding: 1.25rem 1.5rem;
        border: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header::before {
        content: "📊";
        font-size: 1.5rem;
    }

    .filter-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .filter-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .filter-header::before {
        content: "🔍";
        font-size: 1.3rem;
    }

    .btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: var(--shadow-sm);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, #5a67d8 100%);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-hover) 0%, #4a57c7 100%);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color) 0%, #13b879 100%);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, var(--success-hover) 0%, #12a06a 100%);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color) 0%, #f4b619 100%);
        color: #1f2937;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .btn i {
        font-size: 1.1rem;
    }

    /* --- FORM CONTROLS --- */
    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-label.required::after {
        content: "*";
        color: var(--danger-color);
        margin-left: 0.25rem;
    }

    .form-control, .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--danger-color);
    }

    .invalid-feedback {
        display: none;
        color: var(--danger-color);
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    .form-control.is-invalid ~ .invalid-feedback,
    .form-select.is-invalid ~ .invalid-feedback {
        display: block;
    }

    /* --- TABLE STYLE --- */
    /* --- DATATABLES CONTROLS SPACING --- */
    .dataTables_wrapper .row:first-child {
        padding: 1rem 1.5rem 0.5rem 1.5rem; /* Jarak untuk bagian atas (Search & Length) */
        margin: 0;
        align-items: center;
    }
    
    .dataTables_wrapper .row:last-child {
        padding: 0.5rem 1.5rem 1rem 1.5rem; /* Jarak untuk bagian bawah (Pagination & Info) */
        margin: 0;
        align-items: center;
    }

    /* Mengatasi input pencarian agar tidak terlalu menempel ke teks */
    .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: flex-end;
    }
    
    .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-responsive {
        border-radius: 8px;
        overflow-x: auto;
    overflow-y: hidden;
    }

    table {
        margin-bottom: 0;
    }

    table thead th {
        background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
        font-weight: 700;
        color: var(--text-dark);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f1f1;
    }

    tbody tr:hover {
        background: linear-gradient(90deg, #f8f9fc 0%, #eef3ff 100%);
        transform: scale(1.01);
        box-shadow: var(--shadow-sm);
    }

    tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    /* --- BADGES --- */
    .badge-warning {
        background: linear-gradient(135deg, var(--warning-color) 0%, #f4b619 100%);
        color: #1f2937;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-count {
        background: linear-gradient(135deg, var(--success-color) 0%, #13b879 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-left: 0.5rem;
        box-shadow: var(--shadow-sm);
    }

    .salary-badge {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 700;
        display: inline-block;
        box-shadow: var(--shadow-sm);
    }

    /* --- MODAL --- */
    .modal-content {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #5a67d8 100%);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
    }

    .modal-title {
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border: none;
        padding: 1rem 1.5rem;
        background: var(--bg-light);
    }

    .btn-close {
        filter: brightness(0) invert(1);
    }

    /* --- LOADING OVERLAY --- */
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.95);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .spinner-container {
        text-align: center;
    }

    .spinner {
        border: 5px solid #f3f3f3;
        border-top: 5px solid var(--primary-color);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 1rem;
    }

    .loading-text {
        color: var(--text-dark);
        font-weight: 600;
        font-size: 1.1rem;
    }

    @keyframes spin {
        100% { transform: rotate(360deg); }
    }

    tbody tr.fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* --- STATS CARDS --- */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    /* --- ICON STYLES --- */
    .icon-calendar::before { content: "📅"; margin-right: 0.3rem; }
    .icon-building::before { content: "🏢"; margin-right: 0.3rem; }
    .icon-briefcase::before { content: "💼"; margin-right: 0.3rem; }
    .icon-bank::before { content: "🏦"; margin-right: 0.3rem; }

    /* --- EMPTY STATE --- */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state-text {
        font-size: 1.1rem;
        font-weight: 600;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 768px) {
        .filter-card {
            padding: 1rem;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        
        .stats-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- LOADING OVERLAY -->
<div id="loadingOverlay">
    <div class="spinner-container">
        <div class="spinner"></div>
        <div class="loading-text">Memuat data payroll...</div>
    </div>
</div>

<!-- MODAL EDIT REKENING -->
<div class="modal fade" id="modalEditRekening" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title text-white">
                    🏦 Update Data Rekening
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditRekening">
                    <input type="hidden" id="editIdKaryawan">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Karyawan</label>
                        <input type="text" class="form-control" id="editNama" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Bank</label>
                        <select class="form-select" id="editBank" required>
                            <option value="">-- Pilih Bank --</option>
                            <option value="MANDIRI">MANDIRI</option>
                            <option value="BRI">BRI</option>
                            <option value="BNI">BNI</option>
                            <option value="BCA">BCA</option>
                            <option value="CIMB">CIMB NIAGA</option>
                            <option value="PERMATA">PERMATA</option>
                            <option value="DANAMON">DANAMON</option>
                            <option value="BTN">BTN</option>
                            <option value="BELUM_PUNYA">BELUM PUNYA REKENING</option>
                        </select>
                        <div class="invalid-feedback">Silakan pilih nama bank</div>
                    </div>

                    <div id="rekeningFields">
                        <div class="mb-3">
                            <label class="form-label required">Atas Nama Rekening</label>
                            <input type="text" class="form-control" id="editAtasNama" placeholder="Nama sesuai buku tabungan">
                            <div class="invalid-feedback">Atas nama rekening wajib diisi</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Nomor Rekening</label>
                            <input type="text" class="form-control" id="editNoRek" placeholder="Contoh: 1234567890">
                            <div class="invalid-feedback">Nomor rekening wajib diisi</div>
                        </div>
                    </div>

                    <div class="alert alert-info" id="belumPunyaInfo" style="display: none;">
                        <i class="bx bx-info-circle"></i>
                        Karyawan ini belum memiliki rekening bank. Data atas nama dan nomor rekening tidak perlu diisi.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveRekening()">
                    <i class="bx bx-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Master Data /</span> Payroll
            </h4>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-row" id="statsRow" style="display: none;">
            <div class="stat-card">
                <div class="stat-label">Total Karyawan</div>
                <div class="stat-value" id="statTotal">0</div>
            </div>
            <div class="stat-card" style="border-left-color: var(--success-color);">
                <div class="stat-label">Total Gaji</div>
                <div class="stat-value" style="color: var(--success-color);" id="statGaji">Rp 0</div>
            </div>
            <div class="stat-card" style="border-left-color: var(--danger-color);">
                <div class="stat-label">Rata-rata Gaji</div>
                <div class="stat-value" style="color: var(--danger-color);" id="statAvg">Rp 0</div>
            </div>
        </div>

        <!-- FILTER CARD -->
        <div class="filter-card">
            <div class="filter-header">Filter & Pencarian Data</div>
            
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label icon-calendar">Dari Tanggal</label>
                    <input type="date" id="startDate" class="form-control">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label icon-calendar">Sampai Tanggal</label>
                    <input type="date" id="endDate" class="form-control">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label icon-building">Site</label>
                    <select id="filterSite" class="form-select">
                        <option value="">Semua Site</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label icon-briefcase">Jabatan</label>
                    <select id="filterJabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label icon-bank">Nama Bank</label>
                    <select id="filterBank" class="form-select">
                        <option value="">Semua Bank</option>
                        <option value="MANDIRI">MANDIRI</option>
                        <option value="BRI">BRI</option>
                        <option value="BELUM_PUNYA">BELUM PUNYA REKENING</option>
                        <option value="LAINNYA">LAINNYA</option>
                    </select>
                </div>

                <div class="col-lg-6 col-md-6">
                    <label class="form-label" style="visibility: hidden;">Action</label>
                    <div class="d-flex gap-2">
                        <button id="btnExportExcel" class="btn btn-success w-100">
                            <i class="bx bx-download"></i> Export ke Excel
                        </button>
                        <button id="btnPostingKeuangan" class="btn btn-primary w-100" onclick="postingKeuangan()">
                            <i class="bx bx-send"></i> Posting Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATA TABLE -->
        <div class="card">
            <div class="card-header">
                Database Payroll Karyawan
                <span class="badge-count ms-auto" id="recordCount">0 Data</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="payrolTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Karyawan</th>
                                <th>Jabatan</th>
                                <th>Site</th>
                                <th>Atas Nama Rekening</th>
                                <th>No Rekening</th>
                                <th>Nama Bank</th>
                                <th class="text-end" style="width: 180px;">Gaji Bersih</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dataPayrol">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    let allPayrollData = [];
    let dataTablesInstances = {};
    
    function showLoading() {
        document.getElementById("loadingOverlay").style.display = "flex";
    }

    function hideLoading() {
        document.getElementById("loadingOverlay").style.display = "none";
    }

    function setDefaultDateRange() {
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();

        let prevMonth = month - 1;
        let prevYear = year;

        if (prevMonth < 0) {
            prevMonth = 11;
            prevYear = year - 1;
        }

        function f(yyyy, mm, dd) {
            mm = (mm + 1).toString().padStart(2, "0");
            dd = dd.toString().padStart(2, "0");
            return `${yyyy}-${mm}-${dd}`;
        }

        document.getElementById("startDate").value = f(prevYear, prevMonth, 21);
        document.getElementById("endDate").value = f(year, month, 20);
    }

    function populateFilters(data) {
        let sites = [...new Set(data.map(row => row.site).filter(s => s))];
        let siteSelect = document.getElementById("filterSite");
        siteSelect.innerHTML = '<option value="">Semua Site</option>';
        sites.sort().forEach(site => {
            siteSelect.innerHTML += `<option value="${site}">${site}</option>`;
        });

        let jabatans = [...new Set(data.map(row => row.jabatan).filter(j => j && j !== "-"))];
        let jabatanSelect = document.getElementById("filterJabatan");
        jabatanSelect.innerHTML = '<option value="">Semua Jabatan</option>';
        jabatans.sort().forEach(jabatan => {
            jabatanSelect.innerHTML += `<option value="${jabatan}">${jabatan}</option>`;
        });
    }

    function normalizeBankName(bankName) {
        if (!bankName) return 'BELUM_PUNYA';
        
        let bank = bankName.toUpperCase().trim();
        
        if (bank === 'BELUM PUNYA REKENING' || bank === 'BELUM_PUNYA') return 'BELUM_PUNYA';
        if (bank.includes('MANDIRI')) return 'MANDIRI';
        if (bank.includes('BRI')) return 'BRI';
        
        return 'LAINNYA';
    }

    function updateStats(data) {
        let totalGaji = data.reduce((sum, row) => sum + row.gaji_bersih_raw, 0);
        let avgGaji = data.length > 0 ? totalGaji / data.length : 0;

        document.getElementById("statTotal").textContent = data.length;
        document.getElementById("statGaji").textContent = "Rp " + totalGaji.toLocaleString('id-ID');
        document.getElementById("statAvg").textContent = "Rp " + Math.round(avgGaji).toLocaleString('id-ID');
        
        document.getElementById("statsRow").style.display = data.length > 0 ? "grid" : "none";
    }

    function openEditModal(idKaryawan, nama, bank, atasNama, noRek) {
        document.getElementById("editIdKaryawan").value = idKaryawan;
        document.getElementById("editNama").value = nama;
        document.getElementById("editBank").value = bank || "";
        document.getElementById("editAtasNama").value = atasNama || "";
        document.getElementById("editNoRek").value = noRek || "";

        // Reset validation
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        toggleRekeningFields();

        let modal = new bootstrap.Modal(document.getElementById('modalEditRekening'));
        modal.show();
    }

    function toggleRekeningFields() {
        let bank = document.getElementById("editBank").value;
        let rekeningFields = document.getElementById("rekeningFields");
        let belumPunyaInfo = document.getElementById("belumPunyaInfo");
        let atasNamaInput = document.getElementById("editAtasNama");
        let noRekInput = document.getElementById("editNoRek");

        if (bank === "BELUM_PUNYA") {
            rekeningFields.style.display = "none";
            belumPunyaInfo.style.display = "block";
            atasNamaInput.removeAttribute("required");
            noRekInput.removeAttribute("required");
        } else {
            rekeningFields.style.display = "block";
            belumPunyaInfo.style.display = "none";
            if (bank) {
                atasNamaInput.setAttribute("required", "required");
                noRekInput.setAttribute("required", "required");
            }
        }
    }

    function validateRekening() {
        let bank = document.getElementById("editBank").value;
        let atasNama = document.getElementById("editAtasNama").value.trim();
        let noRek = document.getElementById("editNoRek").value.trim();

        let isValid = true;

        // Reset validation
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Validate bank
        if (!bank) {
            document.getElementById("editBank").classList.add("is-invalid");
            isValid = false;
        }

        // Validate rekening only if not "BELUM_PUNYA"
        if (bank && bank !== "BELUM_PUNYA") {
            if (!atasNama) {
                document.getElementById("editAtasNama").classList.add("is-invalid");
                isValid = false;
            }
            if (!noRek) {
                document.getElementById("editNoRek").classList.add("is-invalid");
                isValid = false;
            }
        }

        return isValid;
    }

    function saveRekening() {
        if (!validateRekening()) {
            return;
        }

        let idKaryawan = document.getElementById("editIdKaryawan").value;
        let bank = document.getElementById("editBank").value;
        let atasNama = document.getElementById("editAtasNama").value.trim();
        let noRek = document.getElementById("editNoRek").value.trim();

        // If BELUM_PUNYA, set empty values
        if (bank === "BELUM_PUNYA") {
            atasNama = "";
            noRek = "";
        }

        showLoading();

        $.ajax({
            url: "app/controller/update-rekening.php",
            type: "POST",
            data: {
                id_karyawan: idKaryawan,
                nama_bank: bank,
                atas_nama_rek: atasNama,
                no_rek: noRek
            },
            dataType: "json",
            success: function(res) {
                hideLoading();
                if (res.status === "success") {
                    showToast("Data rekening berhasil diupdate!", "success");
                    bootstrap.Modal.getInstance(document.getElementById('modalEditRekening')).hide();
                    loadPayrol(); // Reload data
                } else {
                    showToast("Error: " + (res.message || "Gagal update data"), "error");
                }
            },
            error: function() {
                hideLoading();
                showToast("Terjadi kesalahan saat menyimpan data!", "error");
            }
        });
    }

    function renderTable(data) {
    const tbody = document.getElementById("dataPayrol");
    
    // 1. Hancurkan instance DataTables sebelumnya (jika ada) agar tidak error saat render ulang
    if ($.fn.DataTable.isDataTable('#payrolTable')) {
        $('#payrolTable').DataTable().destroy();
    }

    tbody.innerHTML = "";

    let filterSite = document.getElementById("filterSite").value;
    let filterJabatan = document.getElementById("filterJabatan").value;
    let filterBank = document.getElementById("filterBank").value;

    let filteredData = data.filter(row => {
        let siteMatch = !filterSite || row.site === filterSite;
        let jabatanMatch = !filterJabatan || row.jabatan === filterJabatan;
        
        let bankMatch = true;
        if (filterBank) {
            let normalizedBank = normalizeBankName(row.bank);
            bankMatch = normalizedBank === filterBank;
        }
        
        return siteMatch && jabatanMatch && bankMatch;
    });

    document.getElementById("recordCount").textContent = `${filteredData.length} Data`;
    updateStats(filteredData);

    if (filteredData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <div class="empty-state-text">Tidak ada data yang sesuai dengan filter</div>
                    </div>
                </td>
            </tr>
        `;
        return; // Jangan inisiasi DataTables jika kosong
    }

    // 2. Kumpulkan baris HTML agar render lebih cepat
    let rowsHtml = "";
    filteredData.forEach((row, index) => {
        let bankDisplay = row.bank || "BELUM PUNYA REKENING";
        let bankBadge = !row.bank ? '<span class="badge-warning">Belum Ada</span>' : `<strong>${bankDisplay}</strong>`;

        rowsHtml += `
            <tr class="fade-in">
                <td class="text-center fw-bold">${index + 1}</td>
                <td><strong>${row.nama}</strong></td>
                <td><span class="badge bg-primary">${row.jabatan}</span></td>
                <td>${row.site || "-"}</td>
                <td>${row.rekening_nama || "-"}</td>
                <td><code>${row.rekening_no || "-"}</code></td>
                <td>${bankBadge}</td>
                <td class="text-end">
                    <span class="salary-badge">Rp ${row.gaji_bersih_formatted}</span>
                </td>
                <td class="text-center">
                    <button class="btn btn-warning btn-sm" onclick="openEditModal('${row.id_karyawan}', '${row.nama}', '${row.bank || ""}', '${row.rekening_nama || ""}', '${row.rekening_no || ""}')">
                        <i class="bx bx-edit"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = rowsHtml;

    // 3. Aktifkan DataTables untuk fitur "slide-slide" (Pagination)
    $('#payrolTable').DataTable({
        "pageLength": 10,             // Jumlah data per slide/halaman
        "lengthMenu": [10, 25, 50],   // Pilihan jumlah data
        "ordering": false,            // Dimatikan agar nomor urut tidak berantakan
        "language": {
            "lengthMenu": "Tampilkan _MENU_ data",
            "search": "Cari Cepat:",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
}

    function loadPayrol() {
        let startDate = document.getElementById("startDate").value;
        let endDate = document.getElementById("endDate").value;

        // --- TAMBAHKAN 3 BARIS INI ---
    if ($.fn.DataTable.isDataTable('#payrolTable')) {
        $('#payrolTable').DataTable().destroy();
    }
    // -----------------------------

        // Tampilkan indikator loading elegan langsung di dalam tabel
        const tbody = document.getElementById("dataPayrol");
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status" style="width: 1.5rem; height: 1.5rem;"></div>
                    <span class="text-muted fw-bold ms-2 align-middle">Memperbarui data tabel...</span>
                </td>
            </tr>
        `;

        // NONAKTIFKAN pemanggilan overlay layar penuh
        // showLoading();

        $.ajax({
            url: "app/controller/get-data/get-payrol.php",
            type: "POST",
            data: { startDate, endDate },
            dataType: "json",

            success: function(res) {
                // NONAKTIFKAN penutupan overlay layar penuh
                // hideLoading();

                if (res.status !== "success") {
                    console.error("Error:", res);
                    showToast("Error: " + (res.message || "Terjadi kesalahan"), "error");
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger fw-bold">Gagal memuat data.</td></tr>`;
                    return;
                }

                allPayrollData = res.data;
                populateFilters(allPayrollData);
                renderTable(allPayrollData);
            },

            error: function(xhr, status, error) {
                // NONAKTIFKAN penutupan overlay layar penuh
                // hideLoading();
                
                console.error("AJAX Error:", xhr.responseText);
                
                try {
                    let errorData = JSON.parse(xhr.responseText);
                    showToast("Error: " + errorData.message, "error");
                } catch(e) {
                    showToast("Gagal mengambil data!", "error");
                }
                
                tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger fw-bold">Koneksi ke server terputus.</td></tr>`;
            }
        });
    }

    function exportExcel() {
        let filterSite = document.getElementById("filterSite").value;
        let filterJabatan = document.getElementById("filterJabatan").value;
        let filterBank = document.getElementById("filterBank").value;

        let filteredData = allPayrollData.filter(row => {
            let siteMatch = !filterSite || row.site === filterSite;
            let jabatanMatch = !filterJabatan || row.jabatan === filterJabatan;
            
            let bankMatch = true;
            if (filterBank) {
                let normalizedBank = normalizeBankName(row.bank);
                bankMatch = normalizedBank === filterBank;
            }
            
            return siteMatch && jabatanMatch && bankMatch;
        });

        if (filteredData.length === 0) {
            showToast("Tidak ada data untuk di-export!", "warning");
            return;
        }

        let excelData = filteredData.map((row, index) => ({
            "No": index + 1,
            "NIK": row.nik_karyawan || "-",
            "Nama": row.nama,
            "Jabatan": row.jabatan,
            "Site": row.site || "-",
            "Atas Nama Rekening": row.rekening_nama || "-",
            "No Rekening": row.rekening_no || "-",
            "Nama Bank": row.bank || "BELUM PUNYA REKENING",
            "Gaji Bersih": row.gaji_bersih_raw
        }));

        let ws = XLSX.utils.json_to_sheet(excelData);
        let wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Payroll");

        let start = document.getElementById("startDate").value || "0000-00-00";
        let end = document.getElementById("endDate").value || "0000-00-00";

        XLSX.writeFile(wb, `Payroll_${start}_sd_${end}.xlsx`);
    }

    document.addEventListener("DOMContentLoaded", () => {
        setDefaultDateRange();
        loadPayrol();

        // Ganti 'blur' menjadi 'change' agar tabel langsung ter-refresh otomatis saat klik kalender
        document.getElementById("startDate").addEventListener("change", loadPayrol);
        document.getElementById("endDate").addEventListener("change", loadPayrol);

        // Untuk filter Select (Dropdown), biarkan tetap 'change'
        document.getElementById("filterSite").addEventListener("change", () => renderTable(allPayrollData));
        document.getElementById("filterJabatan").addEventListener("change", () => renderTable(allPayrollData));
        document.getElementById("filterBank").addEventListener("change", () => renderTable(allPayrollData));

        document.getElementById("btnExportExcel").addEventListener("click", exportExcel);
        document.getElementById("editBank").addEventListener("change", toggleRekeningFields);
    });

    async function postingKeuangan() {
        let startDate = document.getElementById("startDate").value;
        let endDate = document.getElementById("endDate").value;

        if (!(await confirmDialog(`Tutup Buku & Posting Payroll periode ini (${startDate} s/d ${endDate}) ke Laporan Keuangan?\n\nPastikan data absensi dan kasbon sudah final.`))) {
            return;
        }

        const btn = document.getElementById('btnPostingKeuangan');
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
        btn.disabled = true;

        $.ajax({
            url: "app/controller/act-posting-payroll.php",
            type: "POST",
            data: { startDate: startDate, endDate: endDate },
            dataType: "json",
            success: function(res) {
                if (res.status === "success") {
                    showToast(res.message, "success");
                } else {
                    showToast("Gagal: " + res.message, "error");
                }
            },
            error: function() {
                showToast("Terjadi kesalahan server saat memposting data.", "error");
            },
            complete: function() {
                btn.innerHTML = '<i class="bx bx-send"></i> Posting Laporan';
                btn.disabled = false;
            }
        });
    }
</script>