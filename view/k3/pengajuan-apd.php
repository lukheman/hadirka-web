<style>
    .preview-thumbnail { transition: all 0.2s; cursor: pointer; background: #fff; }
    .preview-thumbnail:hover { transform: scale(1.1); border-color: #696cff !important; }
    #modalPreviewGambar .modal-content { backdrop-filter: blur(5px); background-color: rgba(0, 0, 0, 0.5) !important; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Manajemen Pengajuan APD</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover border-top">
                <thead>
                    <tr>
                        <th class="py-3">Karyawan</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Barang & Jumlah</th>
                        <th>Alasan</th>
                        <th class="text-center">Bukti (Jika Rusak)</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-pengajuan-apd" class="table-border-bottom-0">
                    <tr><td colspan="7" class="text-center">Memuat data...</td></tr>
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

<script>
    const api_get_apd = 'app/controller/get-data/get-pengajuan-apd.php';
    const api_act_apd = 'app/controller/act-pengajuan-apd-web.php';

    document.addEventListener('DOMContentLoaded', loadTablePengajuanApd);

    async function loadTablePengajuanApd() {
        const res = await fetch(api_get_apd + '?_=' + new Date().getTime());
        document.getElementById('tbody-pengajuan-apd').innerHTML = await res.text();
    }

    function previewBuktiApd(src) {
        document.getElementById('imgPreview').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewGambar')).show();
    }

    async function updateStatusApd(id, newStatus) {
        try {
            const res = await fetch(`${api_act_apd}?action=update-status&id=${id}&status=${newStatus}`);
            const result = await res.json();
            if (result.status === 'success') {
                loadTablePengajuanApd();
                showToast("Status berhasil diperbarui", "success");
            } else {
                alert("Gagal: " + result.message);
            }
        } catch (e) { alert("Terjadi kesalahan sistem."); }
    }

    async function deletePengajuanApd(id) {
        if (confirm("Hapus permanen data pengajuan APD ini?")) {
            try {
                const res = await fetch(`${api_act_apd}?action=delete&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    loadTablePengajuanApd();
                } else {
                    alert("Gagal menghapus: " + result.message);
                }
            } catch(e) { alert("Terjadi kesalahan saat menghapus data."); }
        }
    }
</script>