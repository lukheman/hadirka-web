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
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-action {
        padding: 4px 10px;
        font-size: 0.8rem;
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
        font-size: 0.95rem;
    }

    table thead th {
        background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
        font-weight: 700;
        color: var(--text-dark);
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 1rem 0.75rem;
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
        vertical-align: middle;
        white-space: nowrap;
    }

    #loanTable td:nth-child(8) {
        white-space: normal;
        min-width: 150px;
    }
</style>

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
                <div class="col-lg-2 col-md-6">
                    <label class="form-label icon-status">Status</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div class="col-lg-12 d-flex justify-content-end gap-2 mt-3">
                    <button id="btnToggleAkses" class="btn btn-secondary shadow-sm px-4">
                        <i class="fa fa-spinner fa-spin me-1"></i> Cek Status...
                    </button>
                    <button id="btnOpenBatchModal" class="btn btn-primary shadow-sm px-4">
                        <i class="fa fa-check-double me-1"></i> Batch ACC Terpilih
                    </button>
                    <button id="btnExportExcel" class="btn btn-success shadow-sm px-4">
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
                                <th class="text-center" style="width: 40px;">
                                    <input type="checkbox" id="checkAllPinjaman">
                                </th>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Karyawan</th>
                                <th>Jabatan</th>
                                <th>Site</th>
                                <th>Tanggal & Waktu</th>
                                <th>Nominal</th>
                                <th>Alasan</th>
                                <th>Bukti</th>
                                <th>Info Rekening</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPengajuan"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     MODAL PROSES SATUAN (ACC / TOLAK)
     ============================================== -->
<div class="modal fade" id="modalProsesPengajuan" tabindex="-1">
    <div class="modal-dialog">
        <form id="formProsesPengajuan" class="modal-content">
            <input type="hidden" name="action" value="proses">
            <input type="hidden" name="id_pengajuan" id="id_pengajuan">
            <input type="hidden" name="status_pinjaman" id="status_pinjaman">

            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title text-white" id="modalTitle">Proses Pengajuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="infoPengajuan"></div>

                <div id="formAcc" style="display: none;">
                    <label class="fw-bold mb-1">Tanggal Rencana Bayar <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_bayar_pinjaman" class="form-control mb-3">
                    <small class="text-success d-block mb-3">*Jika di-ACC, data otomatis masuk ke Menu Data Pinjaman Dana.</small>
                </div>

                <div id="formKetAdmin">
                    <label class="fw-bold mb-1">Pesan / Keterangan untuk Karyawan <span id="starKetAdmin" class="text-danger">*</span></label>
                    <textarea name="keterangan_admin" id="keterangan_admin" class="form-control mb-3" rows="3" placeholder="Masukkan pesan atau alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitModal">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<!-- ==============================================
     MODAL BATCH ACC (BARU)
     ============================================== -->
<div class="modal fade" id="modalBatchAcc" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form id="formBatchAcc" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fa fa-check-double"></i> Batch ACC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    Menyetujui <b id="batchCountInfo" class="fs-5">0</b> pengajuan sekaligus.
                </div>
                <label class="fw-bold mb-1">Tanggal Rencana Bayar <span class="text-danger">*</span></label>
                <input type="date" name="tgl_bayar_pinjaman_batch" id="tgl_bayar_pinjaman_batch" class="form-control mb-1" required>
                <small class="text-success d-block mb-3">*Data akan masuk ke Menu Data Pinjaman Dana.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitBatch">Proses ACC Massal</button>
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
    let selectedBatchIds = []; // Simpan ID massal secara global

    // Wrapper pintar untuk showToast agar kebal error (jika function belum terload di halaman)
    function notify(msg, type = "success") {
        if (typeof showToast === "function") {
            showToast(msg, type);
        } else {
            alert((type.toUpperCase()) + ": " + msg);
        }
    }

    // ============================================================
    // FUNGSI: Render baris tabel
    // ============================================================
    function renderTable(data) {
        const tbody = document.getElementById('tbodyPengajuan');

        if ($.fn.DataTable.isDataTable('#loanTable')) {
            $('#loanTable').DataTable().destroy();
        }

        tbody.innerHTML = '';
        document.getElementById('checkAllPinjaman').checked = false;

        const filterSite = document.getElementById('filterSite').value;
        const filterJabatan = document.getElementById('filterJabatan').value;
        const filterBank = document.getElementById('filterBank').value;
        const filterStatus = document.getElementById('filterStatus').value;

        const filteredData = data.filter(row => {
            const siteMatch = !filterSite || row.site === filterSite;
            const jabatanMatch = !filterJabatan || row.jabatan === filterJabatan;
            const statusMatch = !filterStatus || row.status_pinjaman === filterStatus;
            let bankMatch = true;
            if (filterBank) bankMatch = normalizeBankName(row.nama_bank) === filterBank;
            return siteMatch && jabatanMatch && bankMatch && statusMatch;
        });

        document.getElementById('recordCount').textContent = `${filteredData.length} Data`;

        if (filteredData.length === 0) {
            tbody.innerHTML = `
            <tr><td colspan="12">
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-text">Tidak ada data pengajuan yang sesuai dengan filter</div>
                </div>
            </td></tr>`;
            return;
        }

        let rowsHtml = '';

        filteredData.forEach((row, index) => {
            let statusBadge = '';
            if (row.status_pinjaman === 'pending') statusBadge = '<span class="badge bg-warning">Pending</span>';
            else if (row.status_pinjaman === 'disetujui') statusBadge = '<span class="badge bg-success">Disetujui</span>';
            else if (row.status_pinjaman === 'ditolak') statusBadge = '<span class="badge bg-danger">Ditolak</span>';

            const baseUrl = 'http://api-hris.triputrajayasultra.com/';
            const fotoWajah = row.foto_wajah ? `<a href='${baseUrl}${row.foto_wajah}' target='_blank' class='btn btn-xs btn-outline-info mb-1 py-1 px-2' style='font-size:0.75rem'>Cek Wajah</a><br>` : '';
            const fotoId = row.foto_id_card ? `<a href='${baseUrl}${row.foto_id_card}' target='_blank' class='btn btn-xs btn-outline-primary py-1 px-2' style='font-size:0.75rem'>Cek ID</a>` : '';

            let infoRekening = `<span class='text-muted'><small>Belum disetting</small></span>`;
            if (row.nama_bank && row.no_rek) {
                infoRekening = `<b>${row.nama_bank}</b><br>${row.no_rek}<br><small class='text-muted'>a.n ${row.atas_nama_rek || '-'}</small>`;
            }

            const checkbox = row.status_pinjaman === 'pending' ?
                `<input type="checkbox" class="chk-pinjaman" value="${row.id_pengajuan_pinjaman}">` :
                `<input type="checkbox" disabled>`;

            let btnAcc = '';
            let btnTolak = '';
            let btnPending = '';
            let btnHapus = `<button class="btn btn-danger btn-action btnHapusPinjaman" data-id="${row.id_pengajuan_pinjaman}">Hapus</button>`;

            if (row.status_pinjaman === 'pending') {
                btnAcc = `<button class="btn btn-success btn-action btnProsesAcc" data-id="${row.id_pengajuan_pinjaman}" data-nama="${(row.nama_lengkap || '').replace(/'/g, "&#39;")}" data-nominal="${row.nominal_pinjaman}">ACC</button>`;
                btnTolak = `<button class="btn btn-warning btn-action text-white btnProsesTolak" data-id="${row.id_pengajuan_pinjaman}" data-nama="${(row.nama_lengkap || '').replace(/'/g, "&#39;")}" data-nominal="${row.nominal_pinjaman}">Tolak</button>`;
            } else {
                btnPending = `<button class="btn btn-secondary btn-action text-white btnSetPending" data-id="${row.id_pengajuan_pinjaman}" title="Kembalikan ke Pending">Batal Proses</button>`;
            }

            const aksiButtons = `<div class="d-flex flex-wrap gap-1 justify-content-center">${btnAcc}${btnTolak}${btnPending}${btnHapus}</div>`;

            const dateObj = new Date(row.tgl_pengajuan);
            let formattedDate = row.tgl_pengajuan;
            if (!isNaN(dateObj)) {
                const tanggal = dateObj.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
                const jam = dateObj.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                }).replace(/\./g, ':');
                formattedDate = `${tanggal}<br><small class="text-muted fw-bold"><i class="fa fa-clock me-1"></i>${jam}</small>`;
            }

            rowsHtml += `
            <tr>
                <td class='text-center'>${checkbox}</td>
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
                <td class='text-center'>${aksiButtons}</td>
            </tr>`;
        });

        tbody.innerHTML = rowsHtml;

        $('#loanTable').DataTable({
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            ordering: false,
            columnDefs: [{
                orderable: false,
                targets: 0
            }],
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
    // FUNGSI: Helper Buka Modal Satuan
    // ============================================================
    function openProsesModal(btn, status) {
        document.getElementById('id_pengajuan').value = btn.dataset.id;
        document.getElementById('infoPengajuan').innerHTML =
            `<strong>Karyawan:</strong> ${btn.dataset.nama}<br>
             <strong>Nominal:</strong> Rp ${parseInt(btn.dataset.nominal).toLocaleString('id-ID')}`;

        document.getElementById('status_pinjaman').value = status;

        const modalHeader = document.getElementById('modalHeader');
        const modalTitle = document.getElementById('modalTitle');
        const formAcc = document.getElementById('formAcc');
        const ketAdmin = document.getElementById('keterangan_admin');
        const tglBayarInput = document.querySelector('input[name="tgl_bayar_pinjaman"]');
        const starKetAdmin = document.getElementById('starKetAdmin');
        const btnSubmitModal = document.getElementById('btnSubmitModal');

        // Reset form agar bersih setiap modal dibuka
        ketAdmin.value = '';
        tglBayarInput.value = '';

        if (status === 'disetujui') {
            modalHeader.className = "modal-header bg-success text-white";
            modalTitle.innerHTML = '<i class="fa fa-check-circle"></i> Setujui Pengajuan Pinjaman';
            formAcc.style.display = 'block';
            tglBayarInput.required = true;
            ketAdmin.required = false;
            starKetAdmin.style.display = 'none';
            btnSubmitModal.className = "btn btn-success";
            btnSubmitModal.innerText = "ACC Pinjaman Sekarang";
        } else if (status === 'ditolak') {
            modalHeader.className = "modal-header bg-warning text-dark";
            modalTitle.innerHTML = '<i class="fa fa-times-circle"></i> Tolak Pengajuan Pinjaman';
            formAcc.style.display = 'none';
            tglBayarInput.required = false;
            ketAdmin.required = true;
            starKetAdmin.style.display = 'inline';
            btnSubmitModal.className = "btn btn-warning text-white";
            btnSubmitModal.innerText = "Tolak Pinjaman";
        }

        new bootstrap.Modal(document.getElementById('modalProsesPengajuan')).show();
    }

    // ============================================================
    // FUNGSI: Buka Modal BATCH ACC
    // ============================================================
    document.getElementById("btnOpenBatchModal").addEventListener("click", () => {
        let table = $("#loanTable").DataTable();
        selectedBatchIds = []; // Reset Array Global

        table.$(".chk-pinjaman:checked").each(function() {
            selectedBatchIds.push($(this).val());
        });

        if (selectedBatchIds.length === 0) {
            notify("Pilih minimal satu pengajuan pinjaman (berstatus Pending) untuk di-ACC massal!", "error");
            return;
        }

        // Tampilkan info jumlah di modal
        document.getElementById('batchCountInfo').innerText = selectedBatchIds.length;

        // Reset & Set Default Tanggal Hari Ini
        let d = new Date();
        let defaultDate = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
        document.getElementById('tgl_bayar_pinjaman_batch').value = defaultDate;

        new bootstrap.Modal(document.getElementById('modalBatchAcc')).show();
    });

    // ============================================================
    // FUNGSI: Eksekusi Submit Form BATCH ACC
    // ============================================================
    document.getElementById("formBatchAcc").addEventListener("submit", function(e) {
        e.preventDefault();

        let tglBayar = document.getElementById('tgl_bayar_pinjaman_batch').value;
        let fd = new FormData();
        fd.append("action", "acc_batch");
        fd.append("tgl_bayar_pinjaman", tglBayar);

        selectedBatchIds.forEach(id => fd.append("id_pengajuan[]", id));

        // Animasi loading pada tombol
        let btnSubmit = document.getElementById('btnSubmitBatch');
        let originalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sedang Memproses...';

        fetch("app/controller/act-pengajuan-pinjaman.php", {
                method: "POST",
                body: fd
            })
            .then(res => res.json())
            .then(res => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;

                if (res.status === "success") {
                    notify(res.msg, "success"); // Notifikasi ala menu karier
                    document.getElementById("checkAllPinjaman").checked = false;
                    bootstrap.Modal.getInstance(document.getElementById('modalBatchAcc')).hide();
                    loadPengajuan();
                } else {
                    notify(res.msg, "error");
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                notify("Terjadi kesalahan sistem saat memproses.", "error");
            });
    });

    // ============================================================
    // FUNGSI: Export Excel
    // ============================================================
    function exportExcel() {
        const filterSite = document.getElementById('filterSite').value;
        const filterJabatan = document.getElementById('filterJabatan').value;
        const filterBank = document.getElementById('filterBank').value;
        const filterStatus = document.getElementById('filterStatus').value;

        const filteredData = allLoanData.filter(row => {
            const siteMatch = !filterSite || row.site === filterSite;
            const jabatanMatch = !filterJabatan || row.jabatan === filterJabatan;
            const statusMatch = !filterStatus || row.status_pinjaman === filterStatus;
            let bankMatch = true;
            if (filterBank) bankMatch = normalizeBankName(row.nama_bank) === filterBank;
            return siteMatch && jabatanMatch && bankMatch && statusMatch;
        });

        if (filteredData.length === 0) {
            notify('Tidak ada data pengajuan pinjaman untuk di-export!', 'warning');
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
    // FUNGSI: Update Status Cepat & Hapus
    // ============================================================
    window.updateStatusPengajuan = async function(id, newStatus) {
        const confirmText = newStatus === 'pending' ?
            'Batalkan proses ini dan kembalikan ke status Pending?\n(Data akan ditarik dari Master Pinjaman)' :
            'Tolak pengajuan pinjaman ini secara langsung?';

        if (!confirm(confirmText)) return;

        try {
            const res = await fetch(`app/controller/act-pengajuan-pinjaman.php?action=update-status&id=${id}&status=${newStatus}`);
            const result = await res.json();
            if (result.status === 'success') {
                notify(result.msg, "success");
                loadPengajuan();
            } else {
                notify(result.msg, "error");
            }
        } catch (e) {
            notify('Terjadi kesalahan sistem saat update status.', 'error');
        }
    };

    window.deletePengajuan = async function(id) {
        if (!confirm('Hapus permanen data pengajuan pinjaman ini beserta buktinya?')) return;
        try {
            const res = await fetch(`app/controller/act-pengajuan-pinjaman.php?action=delete&id=${id}`);
            const result = await res.json();
            if (result.status === 'success') {
                notify(result.msg, "success");
                loadPengajuan();
            } else {
                notify(result.msg, "error");
            }
        } catch (e) {
            notify('Terjadi kesalahan sistem saat menghapus data.', 'error');
        }
    };

    // ============================================================
    // EVENT LISTENER: Tombol Aksi & Checkbox All
    // ============================================================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btnProsesAcc')) {
            openProsesModal(e.target.closest('.btnProsesAcc'), 'disetujui');
        }
        if (e.target.closest('.btnProsesTolak')) {
            openProsesModal(e.target.closest('.btnProsesTolak'), 'ditolak');
        }
        if (e.target.closest('.btnSetPending')) {
            updateStatusPengajuan(e.target.closest('.btnSetPending').dataset.id, 'pending');
        }
        if (e.target.closest('.btnHapusPinjaman')) {
            deletePengajuan(e.target.closest('.btnHapusPinjaman').dataset.id);
        }
    });

    document.addEventListener("change", e => {
        if (e.target.id === "checkAllPinjaman") {
            let isChecked = e.target.checked;
            let table = $("#loanTable").DataTable();
            table.$(".chk-pinjaman").prop("checked", isChecked);
        }
    });

    // ============================================================
    // FUNGSI: Form Submit (Satuan)
    // ============================================================
    document.getElementById('formProsesPengajuan').addEventListener('submit', e => {
        e.preventDefault();

        let btnSubmit = document.getElementById('btnSubmitModal');
        let originalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';

        fetch('app/controller/act-pengajuan-pinjaman.php', {
                method: 'POST',
                body: new FormData(e.target)
            })
            .then(r => r.json())
            .then(res => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;

                if (res.status === 'success') {
                    notify(res.msg, "success");
                    bootstrap.Modal.getInstance(document.getElementById('modalProsesPengajuan')).hide();
                    loadPengajuan();
                } else {
                    notify(res.msg, "error");
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                notify("Koneksi bermasalah. Gagal menyimpan.", "error");
            });
    });

    // ============================================================
    // FUNGSI: Filter Helper & Load API
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

    function loadPengajuan() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        const tbody = document.getElementById('tbodyPengajuan');
        tbody.innerHTML = `<tr><td colspan="12" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;

        $.ajax({
            url: 'app/controller/get-data/get-pengajuan-pinjaman.php',
            type: 'POST',
            data: {
                startDate,
                endDate
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    allLoanData = res.data;
                    populateFilters(allLoanData);
                    renderTable(allLoanData);
                }
            }
        });
    }

    // FUNGSI: Buka / Tutup Akses Pengajuan Pinjaman
    let statusBukaPinjaman = '1'; // Default

    function loadStatusAkses() {
        fetch('app/controller/act-pengajuan-pinjaman.php?action=get-status-akses')
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    statusBukaPinjaman = res.nilai;
                    updateBtnAksesUI();
                }
            }).catch(e => console.error("Gagal load status akses", e));
    }

    function updateBtnAksesUI() {
        const btn = document.getElementById('btnToggleAkses');
        if (statusBukaPinjaman === '1') {
            btn.className = 'btn btn-danger shadow-sm px-4';
            btn.innerHTML = '<i class="fa fa-lock me-1"></i> Tutup Akses Pengajuan';
        } else {
            btn.className = 'btn btn-info text-white shadow-sm px-4';
            btn.innerHTML = '<i class="fa fa-unlock me-1"></i> Buka Akses Pengajuan';
        }
    }

    document.getElementById('btnToggleAkses').addEventListener('click', () => {
        const actionText = statusBukaPinjaman === '1' ? 'MENUTUP' : 'MEMBUKA';
        if (!confirm(`Anda yakin ingin ${actionText} akses pengajuan pinjaman untuk seluruh karyawan?`)) return;

        const newStatus = statusBukaPinjaman === '1' ? '0' : '1';
        let fd = new FormData();
        fd.append('action', 'toggle-akses');
        fd.append('nilai', newStatus);

        const btn = document.getElementById('btnToggleAkses');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Memproses...';

        fetch('app/controller/act-pengajuan-pinjaman.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                if (res.status === 'success') {
                    notify(res.msg, 'success');
                    statusBukaPinjaman = newStatus;
                    updateBtnAksesUI();
                } else {
                    notify(res.msg, 'error');
                    updateBtnAksesUI();
                }
            }).catch(() => {
                btn.disabled = false;
                notify('Terjadi kesalahan koneksi.', 'error');
                updateBtnAksesUI();
            });
    });

    
    // INIT DOM
    document.addEventListener('DOMContentLoaded', () => {
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth();
        let prevMonth = month - 1,
            prevYear = year;
        if (prevMonth < 0) {
            prevMonth = 11;
            prevYear = year - 1;
        }

        const f = (y, m, d) => `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        document.getElementById('startDate').value = f(prevYear, prevMonth, 21);
        document.getElementById('endDate').value = f(year, month, 20);

        loadPengajuan();
        loadStatusAkses();

        document.getElementById('startDate').addEventListener('change', loadPengajuan);
        document.getElementById('endDate').addEventListener('change', loadPengajuan);
        ['filterSite', 'filterJabatan', 'filterBank', 'filterStatus'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => renderTable(allLoanData));
        });

        document.getElementById('btnExportExcel').addEventListener('click', exportExcel);
    });
</script>