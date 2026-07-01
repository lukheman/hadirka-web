<style>
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

    .card {
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        border: none;
        /* PENTING: hapus overflow:hidden agar tidak clip dropdown */
        /* overflow: hidden; <-- DIHAPUS */
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    .card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .card-header-custom {
        background: linear-gradient(135deg, var(--primary-color) 0%, #5a67d8 100%);
        color: white;
        font-weight: 600;
        padding: 1.25rem 1.5rem;
        border: none;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
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

    .btn-success {
        background: linear-gradient(135deg, var(--success-color) 0%, #13b879 100%);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, var(--success-hover) 0%, #12a06a 100%);
    }

    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-control,
    .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    table thead th {
        background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
        font-weight: 700;
        color: var(--text-dark);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
    }

    tbody tr {
        transition: all 0.2s ease;
    }

    tbody tr:hover {
        background: linear-gradient(90deg, #f8f9fc 0%, #eef3ff 100%);
    }

    .table-responsive {
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_scroll .dataTables_scrollBody {
        overflow: auto !important;
    }

    .badge-count {
        background: linear-gradient(135deg, var(--success-color) 0%, #13b879 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: var(--shadow-sm);
    }

    .loan-nominal-badge {
        background: linear-gradient(135deg, #ff9f43 0%, #ff5252 100%);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-weight: 700;
        display: inline-block;
        box-shadow: var(--shadow-sm);
    }

    .icon-calendar::before {
        content: "📅";
        margin-right: 0.3rem;
    }

    .icon-building::before {
        content: "🏢";
        margin-right: 0.3rem;
    }

    .icon-briefcase::before {
        content: "💼";
        margin-right: 0.3rem;
    }

    .icon-bank::before {
        content: "🏦";
        margin-right: 0.3rem;
    }

    .icon-status::before {
        content: "📊";
        margin-right: 0.3rem;
    }

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

    #loanTable th,
    #loanTable td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #loanTable td:nth-child(7) {
        white-space: normal;
        min-width: 150px;
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 8px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* ======================================================
       CUSTOM DROPDOWN — Di-render ke <body>, bukan ke tabel
       Gunakan position:fixed dengan !important agar tidak
       diblokir oleh transform/will-change dari template
    ====================================================== */
    #loan-dropdown-portal .custom-dropdown-menu {
        display: none;
        position: fixed !important;
        z-index: 99999 !important;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 10px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
        min-width: 230px;
        padding: 0.4rem 0;
        /* Paksa browser tidak memakai stacking context baru */
        transform: none !important;
        will-change: auto !important;
        filter: none !important;
    }

    #loan-dropdown-portal .custom-dropdown-menu.show {
        display: block;
    }

    #loan-dropdown-portal .dropdown-item {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.15s;
    }

    #loan-dropdown-portal .dropdown-item:hover {
        background-color: #f0f4ff;
    }

    #loan-dropdown-portal .dropdown-header {
        font-size: 0.72rem;
        padding: 0.4rem 1rem 0.2rem;
        color: #aaa;
        letter-spacing: 0.5px;
    }
</style>

<!-- Portal khusus untuk dropdown — langsung child dari content wrapper,
     terpisah dari tabel dan card agar tidak kena clip/overflow -->
<div id="loan-dropdown-portal"></div>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">
                    <span class="text-muted fw-light">Approval /</span> Pengajuan Pinjaman
                </h4>
                <p class="text-muted mb-0">Proses pengajuan pinjaman karyawan dari aplikasi mobile.</p>
            </div>
        </div>

        <div class="filter-card">
            <div class="filter-header">Filter & Pencarian Data Pengajuan</div>
            <div class="row g-3">
                <div class="col-lg-2 col-md-6">
                    <label class="form-label icon-calendar">Dari Tgl</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label icon-calendar">Sampai Tgl</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label icon-building">Site</label>
                    <select id="filterSite" class="form-select">
                        <option value="">Semua Site</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label icon-briefcase">Jabatan</label>
                    <select id="filterJabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label icon-bank">Nama Bank</label>
                    <select id="filterBank" class="form-select">
                        <option value="">Semua Bank</option>
                        <option value="MANDIRI">MANDIRI</option>
                        <option value="BRI">BRI</option>
                        <option value="BNI">BNI</option>
                        <option value="BCA">BCA</option>
                        <option value="BELUM_PUNYA">BELUM PUNYA REKENING</option>
                        <option value="LAINNYA">LAINNYA</option>
                    </select>
                </div>
                <!-- TAMBAHAN: Filter Status -->
                <div class="col-lg-2 col-md-6">
                    <label class="form-label icon-status">Status</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                <div class="col-lg-12 text-end mt-3">
                    <button id="btnExportExcel" class="btn btn-success shadow-sm">
                        <i class="fa fa-file-excel me-1"></i> Export ke Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span>Daftar Antrean Pengajuan Pinjaman</span>
                <span class="badge-count ms-auto" id="recordCount">0 Data</span>
            </div>
            <div class="card-body mt-3">
                <div class="table-responsive">
                    <table id="loanTable" class="table table-hover table-bordered w-100">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Karyawan</th>
                                <th>Jabatan</th>
                                <th>Site</th>
                                <th>Tanggal & Waktu</th>
                                <th>Nominal</th>
                                <th>Alasan</th>
                                <th>Bukti (Wajah & ID)</th>
                                <th>Info Rekening</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPengajuan"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Proses Pengajuan -->
<div class="modal fade" id="modalProsesPengajuan" tabindex="-1">
    <div class="modal-dialog">
        <form id="formProsesPengajuan" class="modal-content">
            <input type="hidden" name="action" value="proses">
            <input type="hidden" name="id_pengajuan" id="id_pengajuan">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Proses Pengajuan Pinjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="infoPengajuan"></div>
                <label class="fw-bold mb-1">Keputusan <span class="text-danger">*</span></label>
                <select name="status_pinjaman" id="status_pinjaman" class="form-select mb-3" required>
                    <option value="">-- Pilih Keputusan --</option>
                    <option value="disetujui">ACC (Disetujui)</option>
                    <option value="ditolak">Tolak</option>
                </select>
                <div id="formAcc" style="display: none;">
                    <label class="fw-bold mb-1">Tanggal Rencana Bayar</label>
                    <input type="date" name="tgl_bayar_pinjaman" class="form-control mb-3">
                    <small class="text-success d-block mb-3">*Jika di-ACC, data otomatis masuk ke Master Data Pinjaman.</small>
                </div>
                <label class="fw-bold mb-1">Pesan / Keterangan untuk Karyawan</label>
                <textarea name="keterangan_admin" id="keterangan_admin" class="form-control mb-3" rows="3" placeholder="Wajib diisi jika ditolak..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    let allLoanData = [];

    // Referensi portal dropdown — elemen ini langsung child body/wrapper,
    // bebas dari transform/overflow apapun yang ada di dalam card/tabel
    const dropdownPortal = document.getElementById('loan-dropdown-portal');

    // ============================================================
    // HELPER: Bersihkan semua menu lama di portal
    // ============================================================
    function clearDropdownPortal() {
        dropdownPortal.innerHTML = '';
    }

    // ============================================================
    // HELPER: Tutup semua dropdown yang sedang terbuka
    // ============================================================
    window.closeAllDropdowns = function() {
        dropdownPortal.querySelectorAll('.custom-dropdown-menu.show').forEach(m => {
            m.classList.remove('show');
        });
    };

    // ============================================================
    // EVENT: Klik tombol titik tiga & item menu
    // (satu listener terpusat, event delegation)
    // ============================================================
    document.addEventListener('click', function(e) {

        // 1. Klik tombol titik tiga (btnDropdownAksi)
        const btn = e.target.closest('.btnDropdownAksi');
        if (btn) {
            e.stopPropagation();
            const idx = btn.dataset.index;
            const menu = document.getElementById('dropdownMenu_' + idx);
            if (!menu) return;

            const isOpen = menu.classList.contains('show');
            closeAllDropdowns();

            if (!isOpen) {
                // Ukur dimensi menu sebelum ditampilkan
                menu.style.visibility = 'hidden';
                menu.style.display = 'block';
                const mW = menu.offsetWidth;
                const mH = menu.offsetHeight;
                menu.style.display = '';
                menu.style.visibility = '';

                const r = btn.getBoundingClientRect();
                let top = r.bottom + 4;
                let left = r.right - mW;

                // Jangan keluar dari tepi layar
                if (left < 8) left = 8;
                if (left + mW > window.innerWidth - 8) left = window.innerWidth - mW - 8;
                if (top + mH > window.innerHeight - 8) top = r.top - mH - 4;
                if (top < 8) top = 8;

                menu.style.top = top + 'px';
                menu.style.left = left + 'px';
                menu.classList.add('show');
            }
            return;
        }

        // 2. Klik item "Proses (ACC/Tolak)" di dalam menu
        const btnProses = e.target.closest('.btnProses');
        if (btnProses) {
            closeAllDropdowns();
            document.getElementById('id_pengajuan').value = btnProses.dataset.id;
            document.getElementById('infoPengajuan').innerHTML =
                `<strong>Karyawan:</strong> ${btnProses.dataset.nama}<br>
             <strong>Nominal:</strong> Rp ${parseInt(btnProses.dataset.nominal).toLocaleString('id-ID')}`;
            document.getElementById('status_pinjaman').value = '';
            document.getElementById('keterangan_admin').value = '';
            document.getElementById('formAcc').style.display = 'none';
            new bootstrap.Modal(document.getElementById('modalProsesPengajuan')).show();
            return;
        }

        // 3. Klik di luar menu → tutup semua
        if (!e.target.closest('#loan-dropdown-portal .custom-dropdown-menu')) {
            closeAllDropdowns();
        }
    });

    // Tutup saat scroll (capture phase agar menangkap scroll di dalam tabel juga)
    window.addEventListener('scroll', closeAllDropdowns, true);

    // ============================================================
    // FUNGSI: Render baris tabel + inject menu ke portal
    // ============================================================
    function renderTable(data) {
        const tbody = document.getElementById('tbodyPengajuan');

        if ($.fn.DataTable.isDataTable('#loanTable')) {
            $('#loanTable').DataTable().destroy();
        }

        tbody.innerHTML = '';
        clearDropdownPortal(); // <-- Bersihkan menu lama di portal

        const filterSite = document.getElementById('filterSite').value;
        const filterJabatan = document.getElementById('filterJabatan').value;
        const filterBank = document.getElementById('filterBank').value;
        const filterStatus = document.getElementById('filterStatus').value; // Tambahan

        const filteredData = data.filter(row => {
            const siteMatch = !filterSite || row.site === filterSite;
            const jabatanMatch = !filterJabatan || row.jabatan === filterJabatan;
            const statusMatch = !filterStatus || row.status_pinjaman === filterStatus; // Tambahan
            let bankMatch = true;
            if (filterBank) {
                bankMatch = normalizeBankName(row.nama_bank) === filterBank;
            }
            // Tambahkan statusMatch ke return
            return siteMatch && jabatanMatch && bankMatch && statusMatch;
        });

        document.getElementById('recordCount').textContent = `${filteredData.length} Data`;

        if (filteredData.length === 0) {
            tbody.innerHTML = `
            <tr><td colspan="11">
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-text">Tidak ada data pengajuan yang sesuai dengan filter</div>
                </div>
            </td></tr>`;
            return;
        }

        let rowsHtml = '';
        let menusHtml = '';

        filteredData.forEach((row, index) => {
            // Badge status
            let statusBadge = '';
            if (row.status_pinjaman === 'pending') statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
            else if (row.status_pinjaman === 'disetujui') statusBadge = '<span class="badge bg-success">Disetujui</span>';
            else if (row.status_pinjaman === 'ditolak') statusBadge = '<span class="badge bg-danger">Ditolak</span>';

            // Foto
            const baseUrl = 'http://api-hris.triputrajayasultra.com/';
            const fotoWajah = row.foto_wajah ? `<a href='${baseUrl}${row.foto_wajah}'    target='_blank' class='btn btn-xs btn-outline-info    mb-1 py-1 px-2' style='font-size:0.75rem'>Cek Wajah</a><br>` : '';
            const fotoId = row.foto_id_card ? `<a href='${baseUrl}${row.foto_id_card}'  target='_blank' class='btn btn-xs btn-outline-primary       py-1 px-2' style='font-size:0.75rem'>Cek ID</a>` : '';

            // Info rekening
            let infoRekening = `<span class='text-muted'><small>Belum disetting</small></span>`;
            if (row.nama_bank && row.no_rek) {
                infoRekening = `<b>${row.nama_bank}</b><br>${row.no_rek}<br><small class='text-muted'>a.n ${row.atas_nama_rek || '-'}</small>`;
            }

            // Tombol aksi (hanya tombol, menu ada di portal)
            const btnAksi = `
            <button type='button'
                class='btn btn-sm btn-outline-secondary rounded-pill btnDropdownAksi'
                data-index='${index}'
                title='Aksi'>
                <i class='fa fa-ellipsis-v'></i>
            </button>`;

            // Menu dropdown untuk portal
            let menuHtml = `
            <div class='custom-dropdown-menu' id='dropdownMenu_${index}'>
                <div class='dropdown-header'>AKSI PENGAJUAN</div>`;

            if (row.status_pinjaman !== 'disetujui') {
                menuHtml += `
                <a class='dropdown-item d-flex align-items-center btnProses'
                    href='javascript:void(0)'
                    data-id='${row.id_pengajuan_pinjaman}'
                    data-nama='${(row.nama_lengkap || '').replace(/'/g, "&#39;")}'
                    data-nominal='${row.nominal_pinjaman}'>
                    <i class='fa fa-check-square me-2 text-primary'></i> Proses (ACC/Tolak + Ket)
                </a>`;
            }

            if (row.status_pinjaman !== 'pending') {
                menuHtml += `
                <a class='dropdown-item d-flex align-items-center' href='javascript:void(0)'
                    onclick='updateStatusPengajuan(${row.id_pengajuan_pinjaman}, "pending"); closeAllDropdowns();'>
                    <i class='fa fa-clock me-2 text-warning'></i> Set Pending
                </a>`;
            }

            if (row.status_pinjaman !== 'ditolak') {
                menuHtml += `
                <a class='dropdown-item d-flex align-items-center' href='javascript:void(0)'
                    onclick='updateStatusPengajuan(${row.id_pengajuan_pinjaman}, "ditolak"); closeAllDropdowns();'>
                    <i class='fa fa-times-circle me-2 text-danger'></i> Set Ditolak Cepat
                </a>`;
            }

            menuHtml += `
                <div class='dropdown-divider'></div>
                <a class='dropdown-item d-flex align-items-center text-danger' href='javascript:void(0)'
                    onclick='deletePengajuan(${row.id_pengajuan_pinjaman}); closeAllDropdowns();'>
                    <i class='fa fa-trash me-2'></i> Hapus Permanen
                </a>
            </div>`;

            menusHtml += menuHtml;

            // Format tanggal dan jam
            const dateObj = new Date(row.tgl_pengajuan);
            let formattedDate = row.tgl_pengajuan;

            if (!isNaN(dateObj)) {
                // Ambil format tanggal
                const tanggal = dateObj.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });

                // Ambil format jam (HH:MM)
                const jam = dateObj.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                }).replace(/\./g, ':'); // JS id-ID kadang memakai titik (misal 14.30), kita standarkan jadi 14:30

                // Gabungkan dengan HTML agar jam tampil rapi di bawah tanggal
                formattedDate = `${tanggal}<br><small class="text-muted fw-bold"><i class="fa fa-clock me-1"></i>${jam}</small>`;
            }

            rowsHtml += `
            <tr>
                <td class='text-center fw-bold'>${index + 1}</td>
                <td>${row.nik_karyawan || ''}<br><b>${row.nama_lengkap || ''}</b></td>
                <td><span class="badge bg-primary">${row.jabatan || ''}</span></td>
                <td>${row.site || '-'}</td>
                <td>${formattedDate}</td>
                <td><span class="loan-nominal-badge">Rp ${Number(row.nominal_pinjaman).toLocaleString('id-ID')}</span></td>
                <td>${row.alasan_pinjaman || ''}</td>
                <td>${fotoWajah}${fotoId}</td>
                <td>${infoRekening}</td>
                <td class='text-center'>${statusBadge}</td>
                <td class='text-center'>${btnAksi}</td>
            </tr>`;
        });

        tbody.innerHTML = rowsHtml;

        // Inject semua menu ke portal (bukan ke tabel)
        dropdownPortal.innerHTML = menusHtml;

        // Init DataTables
        $('#loanTable').DataTable({
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            ordering: false,
            language: {
                lengthMenu: 'Tampilkan _MENU_ data',
                search: 'Cari Cepat:',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Selanjutnya',
                    previous: 'Sebelumnya'
                }
            }
        });
    }

    // ============================================================
    // FUNGSI: Filter helper
    // ============================================================
    function normalizeBankName(bankName) {
        if (!bankName) return 'BELUM_PUNYA';
        const bank = bankName.toUpperCase().trim();
        if (bank === 'BELUM PUNYA REKENING' || bank === 'BELUM_PUNYA' || bank === '') return 'BELUM_PUNYA';
        if (bank.includes('MANDIRI')) return 'MANDIRI';
        if (bank.includes('BRI')) return 'BRI';
        if (bank.includes('BNI')) return 'BNI';
        if (bank.includes('BCA')) return 'BCA';
        return 'LAINNYA';
    }

    function populateFilters(data) {
        const sites = [...new Set(data.map(r => r.site).filter(Boolean))].sort();
        const siteEl = document.getElementById('filterSite');
        siteEl.innerHTML = '<option value="">Semua Site</option>';
        sites.forEach(s => siteEl.innerHTML += `<option value="${s}">${s}</option>`);

        const jabatans = [...new Set(data.map(r => r.jabatan).filter(j => j && j !== '-'))].sort();
        const jabEl = document.getElementById('filterJabatan');
        jabEl.innerHTML = '<option value="">Semua Jabatan</option>';
        jabatans.forEach(j => jabEl.innerHTML += `<option value="${j}">${j}</option>`);
    }

    // ============================================================
    // FUNGSI: Load data dari server
    // ============================================================
    function loadPengajuan() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if ($.fn.DataTable.isDataTable('#loanTable')) {
            $('#loanTable').DataTable().destroy();
        }

        const tbody = document.getElementById('tbodyPengajuan');
        tbody.innerHTML = `
        <tr><td colspan="11" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="text-muted fw-bold ms-2">Memperbarui data tabel pengajuan...</span>
        </td></tr>`;

        $.ajax({
            url: 'app/controller/get-data/get-pengajuan-pinjaman.php',
            type: 'POST',
            data: {
                startDate,
                endDate
            },
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    tbody.innerHTML = `<tr><td colspan="11" class="text-center text-danger fw-bold">Gagal memuat data: ${res.message}</td></tr>`;
                    return;
                }
                allLoanData = res.data;
                populateFilters(allLoanData);
                renderTable(allLoanData);
            },
            error: function() {
                tbody.innerHTML = `<tr><td colspan="11" class="text-center text-danger fw-bold">Koneksi ke server terputus.</td></tr>`;
            }
        });
    }

    // ============================================================
    // FUNGSI: Export Excel
    // ============================================================
    function exportExcel() {
        const filterSite = document.getElementById('filterSite').value;
        const filterJabatan = document.getElementById('filterJabatan').value;
        const filterBank = document.getElementById('filterBank').value;
        const filterStatus = document.getElementById('filterStatus').value; // Tambahan

        const filteredData = allLoanData.filter(row => {
            const siteMatch = !filterSite || row.site === filterSite;
            const jabatanMatch = !filterJabatan || row.jabatan === filterJabatan;
            const statusMatch = !filterStatus || row.status_pinjaman === filterStatus; // Tambahan
            let bankMatch = true;
            if (filterBank) bankMatch = normalizeBankName(row.nama_bank) === filterBank;

            return siteMatch && jabatanMatch && bankMatch && statusMatch; // Tambahan
        });

        if (filteredData.length === 0) {
            alert('Tidak ada data pengajuan pinjaman untuk di-export!');
            return;
        }

        const excelData = filteredData.map((row, i) => ({
            'No': i + 1,
            'NIK Karyawan': row.nik_karyawan || '-',
            'Nama Lengkap': row.nama_lengkap,
            'Jabatan': row.jabatan,
            'Site': row.site || '-',
            'Tanggal Pengajuan': row.tgl_pengajuan,
            'Nominal Pinjaman (Rp)': row.nominal_pinjaman,
            'Alasan Pinjaman': row.alasan_pinjaman || '-',
            'Atas Nama Rekening': row.atas_nama_rek || '-',
            'Nomor Rekening': row.no_rek ? String(row.no_rek) : '-',
            'Nama Bank': row.nama_bank || 'BELUM PUNYA REKENING',
            'Status': row.status_pinjaman.toUpperCase()
        }));

        const ws = XLSX.utils.json_to_sheet(excelData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Pengajuan Pinjaman');

        const start = document.getElementById('startDate').value || '0000-00-00';
        const end = document.getElementById('endDate').value || '0000-00-00';
        XLSX.writeFile(wb, `Data_Pengajuan_Pinjaman_${start}_sd_${end}.xlsx`);
    }

    // ============================================================
    // FUNGSI: Update status & hapus
    // ============================================================
    window.updateStatusPengajuan = async function(id, newStatus) {
        const confirmText = newStatus === 'pending' ?
            'Kembalikan pengajuan ini ke status Pending? (Data akan ditarik dari Master Pinjaman jika sebelumnya sudah di-ACC)' :
            'Tolak pengajuan pinjaman ini secara langsung?';

        if (!confirm(confirmText)) return;

        try {
            const res = await fetch(`app/controller/act-pengajuan-pinjaman.php?action=update-status&id=${id}&status=${newStatus}`);
            const result = await res.json();
            if (result.status === 'success') loadPengajuan();
            else alert('Gagal: ' + result.msg);
        } catch (e) {
            alert('Terjadi kesalahan sistem.');
        }
    };

    window.deletePengajuan = async function(id) {
        if (!confirm('Hapus permanen data pengajuan pinjaman ini beserta buktinya?')) return;

        try {
            const res = await fetch(`app/controller/act-pengajuan-pinjaman.php?action=delete&id=${id}`);
            const result = await res.json();
            if (result.status === 'success') loadPengajuan();
            else alert('Gagal menghapus: ' + result.msg);
        } catch (e) {
            alert('Terjadi kesalahan saat menghapus data.');
        }
    };

    // ============================================================
    // INIT
    // ============================================================
    function setDefaultDateRange() {
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth();
        let prevMonth = month - 1;
        let prevYear = year;
        if (prevMonth < 0) {
            prevMonth = 11;
            prevYear = year - 1;
        }

        const f = (y, m, d) =>
            `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

        document.getElementById('startDate').value = f(prevYear, prevMonth, 21);
        document.getElementById('endDate').value = f(year, month, 20);
    }

    document.addEventListener('DOMContentLoaded', () => {
        setDefaultDateRange();
        loadPengajuan();

        document.getElementById('startDate').addEventListener('change', loadPengajuan);
        document.getElementById('endDate').addEventListener('change', loadPengajuan);

        document.getElementById('filterSite').addEventListener('change', () => renderTable(allLoanData));
        document.getElementById('filterJabatan').addEventListener('change', () => renderTable(allLoanData));
        document.getElementById('filterBank').addEventListener('change', () => renderTable(allLoanData));
        document.getElementById('filterStatus').addEventListener('change', () => renderTable(allLoanData));

        document.getElementById('btnExportExcel').addEventListener('click', exportExcel);

        // Toggle form tanggal bayar di modal
        document.getElementById('status_pinjaman').addEventListener('change', function() {
            const formAcc = document.getElementById('formAcc');
            const ketAdmin = document.getElementById('keterangan_admin');
            if (this.value === 'disetujui') {
                formAcc.style.display = 'block';
                ketAdmin.required = false;
            } else if (this.value === 'ditolak') {
                formAcc.style.display = 'none';
                ketAdmin.required = true;
            } else {
                formAcc.style.display = 'none';
                ketAdmin.required = false;
            }
        });

        // Submit modal proses
        document.getElementById('formProsesPengajuan').addEventListener('submit', e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            fetch('app/controller/act-pengajuan-pinjaman.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        alert(res.msg);
                        bootstrap.Modal.getInstance(document.getElementById('modalProsesPengajuan')).hide();
                        loadPengajuan();
                    } else {
                        alert('Gagal: ' + res.msg);
                    }
                });
        });
    });
</script>