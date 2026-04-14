<style>
    /* Efek Zoom pada Lampiran */
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
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Manajemen Persetujuan Izin</h5>
        </div>
        <div class="">
            <table class="table table-hover border-top">
                <thead>
                    <tr>
                        <th class="py-3">Karyawan</th>
                        <th>Tanggal</th>
                        <th>Alasan</th>
                        <th class="text-center">Bukti</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-izin" class="table-border-bottom-0">
                    <tr><td colspan="6" class="text-center">Memuat data...</td></tr>
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
    const api_get = 'app/controller/get-data/get-pengajuan-izin.php';
    const api_act = 'app/controller/act-pengajuan-izin.php';

    document.addEventListener('DOMContentLoaded', loadTableIzin);

    async function loadTableIzin() {
        const res = await fetch(api_get + '?_=' + new Date().getTime());
        document.getElementById('tbody-izin').innerHTML = await res.text();
    }

    function previewBukti(src) {
        document.getElementById('imgPreview').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewGambar')).show();
    }

    async function updateStatus(id, newStatus) {
        try {
            const response = await fetch(`${api_act}?action=update-status&id=${id}&status=${newStatus}`);
            
            // Cek jika response sukses (200 OK)
            if (!response.ok) throw new Error(`Server merespon ${response.status}`);
            
            const result = await response.json();
            if (result.status === 'success') {
                loadTableIzin(); 
            } else {
                alert("Gagal: " + result.message);
            }
        } catch (e) { 
            console.error("Update Error:", e);
            alert("Terjadi kesalahan sistem.");
        }
    }

    async function deleteIzin(id) {
        if (confirm("Hapus permanen data pengajuan ini?")) {
            try {
                const res = await fetch(`${api_act}?action=delete&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    loadTableIzin();
                } else {
                    alert("Gagal menghapus: " + result.message);
                }
            } catch(e) {
                alert("Terjadi kesalahan saat menghapus data.");
            }
        }
    }
</script>