<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bx bx-file me-2"></i>Verifikasi Bukti SKS</h5>
        </div>
        <div class="">
            <table class="table table-hover border-top">
                <thead>
                    <tr>
                        <th class="py-3">Nama Karyawan</th>
                        <th>Tanggal SKS</th>
                        <th>Tgl Kirim</th>
                        <th>Tgl Diproses Admin</th>
                        <th class="text-center">Lampiran SKS</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="tbody-sks" class="table-border-bottom-0">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewSKS" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                <img src="" id="imgPreviewSKS" class="img-fluid rounded shadow-lg border border-3 border-white">
            </div>
        </div>
    </div>
</div>

<script>
    const api_get_sks = 'app/controller/get-data/get-bukti-sks.php';
    const api_act_sks = 'app/controller/act-bukti-sks.php';

    document.addEventListener('DOMContentLoaded', loadTableSKS);

    async function loadTableSKS() {
        const res = await fetch(api_get_sks + '?_=' + new Date().getTime());
        document.getElementById('tbody-sks').innerHTML = await res.text();
    }

    function previewSKS(src) {
        document.getElementById('imgPreviewSKS').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewSKS')).show();
    }

    async function updateStatusSKS(id, newStatus) {
        try {
            const res = await fetch(`${api_act_sks}?action=update-status&id=${id}&status=${newStatus}`);
            const result = await res.json();
            if (result.status === 'success') loadTableSKS();
        } catch (e) { console.error(e); }
    }

    async function deleteSKS(id) {
        if (!confirm("Hapus data SKS ini selamanya?")) return;
        const res = await fetch(`${api_act_sks}?action=delete&id=${id}`);
        const result = await res.json();
        if (result.status === 'success') loadTableSKS();
    }
</script>