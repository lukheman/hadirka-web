<style>
    .preview-thumbnail {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background: #fff;
    }

    .preview-thumbnail:hover {
        transform: scale(1.1);
        border-color: #696cff !important;
        box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4) !important;
    }

    #modalPreviewGambar .modal-content {
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.5) !important;
    }

    .w-px-100 {
        width: 100px !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-check-shield me-2"></i>Validasi Pengajuan APD</h5>
                <small class="text-muted">Kelola permintaan Alat Pelindung Diri dari karyawan</small>
            </div>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadTablePengajuanApd()">
                <i class="bx bx-refresh"></i> Refresh
            </button>
        </div>
        <div class="">
            <table class="table table-hover border-top">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Karyawan</th>
                        <th>Tanggal Request</th>
                        <th>Barang & Jumlah</th>
                        <th>Alasan</th>
                        <th>Bukti</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-pengajuan-apd" class="table-border-bottom-0">
                    <tr>
                        <td colspan="6" class="text-center py-4">Memuat data...</td>
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
    const api_get_apd = 'app/controller/get-data/get-pengajuan-apd.php';
    const api_act_apd = 'app/controller/act-pengajuan-apd.php';

    document.addEventListener('DOMContentLoaded', loadTablePengajuanApd);

    async function loadTablePengajuanApd() {
        try {
            const res = await fetch(api_get_apd + '?_=' + new Date().getTime());
            document.getElementById('tbody-pengajuan-apd').innerHTML = await res.text();
        } catch (e) {
            document.getElementById('tbody-pengajuan-apd').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>';
        }
    }

    function previewBuktiApd(src) {
        document.getElementById('imgPreview').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewGambar')).show();
    }


    function updateStatusApd(id, newStatus) {
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
            const response = await fetch(api_act_apd, {
                method: 'POST',
                body: fd
            });
            const result = await response.json();
            if (result.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('modalProsesAdmin')).hide();
                loadTablePengajuanApd();
            } else {
                showToast("Gagal: " + result.message, "error");
            }
        } catch (err) {
            showToast("Terjadi kesalahan sistem.", "error");
        }
    });

    async function deletePengajuanApd(id) {
        if (await confirmDialog("Hapus permanen data pengajuan APD ini? Data tidak bisa dikembalikan.")) {
            try {
                const res = await fetch(`${api_act_apd}?action=delete&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    loadTablePengajuanApd();
                } else {
                    showToast("Gagal menghapus: " + result.message, "error");
                }
            } catch (e) {
                showToast("Terjadi kesalahan saat menghapus data.", "error");
            }
        }
    }
</script>