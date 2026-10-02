<style>
    /* Efek Zoom pada Lampiran */
    .preview-thumbnail {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background: #fff;
    }

    .preview-thumbnail:hover {
        transform: scale(1.1);
        border-color: #1D5C96 !important;
        box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4) !important;
    }

    /* Memperhalus Modal Preview */
    #modalPreviewGambar .modal-content {
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.5) !important;
    }

    .w-px-100 {
        width: 100px !important;
    }

    .fs-tiny {
        font-size: 0.7rem;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Site</label>
                    <select id="filterSite" class="form-select">
                        <option value="">Semua Site</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jabatan</label>
                    <select id="filterJabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Manajemen Persetujuan Izin</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover border-top">
                <thead>
                    <tr class="align-middle text-nowrap">
                        <th>Nama Karyawan</th>
                        <th>Tanggal</th>
                        <th>Alasan</th>
                        <th class="text-center">Bukti</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-izin" class="table-border-bottom-0">
                    <tr>
                        <td colspan="6" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewGambar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                <img src="" id="imgPreview" class="img-fluid rounded shadow-lg border border-3 border-white">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProsesAdmin" tabindex="-1">
    <div class="modal-dialog">
        <form id="formProsesAdmin" class="modal-content">
            <input type="hidden" id="proses_id" name="id">
            <input type="hidden" id="proses_status" name="status">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalProsesTitle">Proses Pengajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    <span id="proses_teks_info" class="fw-bold"></span>
                </div>
                <label class="fw-bold mb-1">Pesan / Keterangan Admin</label>
                <textarea name="ket_admin" id="proses_ket_admin" class="form-control" rows="3" placeholder="Opsional (Wajib diisi jika ditolak)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnProsesSubmit">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const api_get = 'app/controller/get-data/get-pengajuan-izin.php';
    const api_act = 'app/controller/act-pengajuan-izin.php';

    // Fungsi set default tanggal (21 bln kemarin - 20 bln ini)
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

        const format = (y, m, d) => `${y}-${(m + 1).toString().padStart(2, "0")}-${d.toString().padStart(2, "0")}`;
        document.getElementById("startDate").value = format(prevYear, prevMonth, 21);
        document.getElementById("endDate").value = format(year, month, 20);
    }

    // Fungsi load Dropdown Site & Jabatan via API yang sudah ada
    async function loadFilterOptions() {
        try {
            const res = await fetch('app/controller/get-data/get-site-departemen-jabatan.php');
            const data = await res.json();

            let siteOpt = '<option value="">Semua Site</option>';
            data.site.forEach(s => siteOpt += `<option value="${s.id_site}">${s.nama_site}</option>`);
            document.getElementById('filterSite').innerHTML = siteOpt;

            let jabatanOpt = '<option value="">Semua Jabatan</option>';
            data.jabatan.forEach(j => jabatanOpt += `<option value="${j.id_jabatan}">${j.nama_jabatan}</option>`);
            document.getElementById('filterJabatan').innerHTML = jabatanOpt;
        } catch (e) {
            console.error("Gagal memuat opsi filter:", e);
        }
    }

    async function loadTableIzin() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const site = document.getElementById('filterSite').value;
        const jabatan = document.getElementById('filterJabatan').value;

        const params = new URLSearchParams({
            start_date: start,
            end_date: end,
            site: site,
            jabatan: jabatan,
            _: new Date().getTime()
        });

        // Ubah api_get sesuai endpoint masing-masing
        const res = await fetch(`${api_get}?${params.toString()}`);
        document.getElementById('tbody-izin').innerHTML = await res.text();
    }

    document.addEventListener('DOMContentLoaded', async () => {
        setDefaultDateRange();
        await loadFilterOptions();

        loadTableIzin(); // Panggil fungsi load tabel terkait

        // Auto reload saat filter diubah
        const filters = ['startDate', 'endDate', 'filterSite', 'filterJabatan'];
        filters.forEach(id => {
            document.getElementById(id).addEventListener('change', loadTableIzin);
        });
    });

    function previewBukti(src) {
        document.getElementById('imgPreview').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewGambar')).show();
    }

    function updateStatus(id, newStatus) {
        document.getElementById('proses_id').value = id;
        document.getElementById('proses_status').value = newStatus;
        document.getElementById('proses_ket_admin').value = '';
        document.getElementById('proses_ket_admin').required = (newStatus === 'ditolak');

        document.getElementById('modalProsesTitle').innerText = newStatus === 'disetujui' ? 'Setujui Izin' : (newStatus === 'ditolak' ? 'Tolak Izin' : 'Set Pending');
        document.getElementById('proses_teks_info').innerText = `Anda akan mengubah status menjadi: ${newStatus.toUpperCase()}`;

        new bootstrap.Modal(document.getElementById('modalProsesAdmin')).show();
    }

    document.getElementById('formProsesAdmin').addEventListener('submit', async function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append('action', 'update-status');

        try {
            const response = await fetch(api_act, {
                method: 'POST',
                body: fd
            });
            const result = await response.json();
            if (result.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('modalProsesAdmin')).hide();
                loadTableIzin();
            } else {
                showToast("Gagal: " + result.message, "error");
            }
        } catch (err) {
            showToast("Terjadi kesalahan sistem.", "error");
        }
    });

    async function deleteIzin(id) {
        if (await confirmDialog("Hapus permanen data pengajuan ini?")) {
            try {
                const res = await fetch(`${api_act}?action=delete&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    loadTableIzin();
                } else {
                    showToast("Gagal menghapus: " + result.message, "error");
                }
            } catch (e) {
                showToast("Terjadi kesalahan saat menghapus data.", "error");
            }
        }
    }
</script>