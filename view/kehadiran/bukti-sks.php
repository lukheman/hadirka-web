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
                        <th>Keterangan</th>
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

<div class="modal fade" id="modalProsesSKS" tabindex="-1">
    <div class="modal-dialog">
        <form id="formProsesSKS" class="modal-content">
            <input type="hidden" id="proses_id" name="id">
            <input type="hidden" id="proses_status" name="status">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalProsesTitle">Proses Pengajuan SKS</h5>
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

    // UPDATE LOGIC: Membuka Modal
    function updateStatusSKS(id, newStatus) {
        document.getElementById('proses_id').value = id;
        document.getElementById('proses_status').value = newStatus;
        document.getElementById('proses_ket_admin').value = '';
        document.getElementById('proses_ket_admin').required = (newStatus.toLowerCase() === 'ditolak');

        document.getElementById('modalProsesTitle').innerText = newStatus === 'Diterima' ? 'Terima Bukti SKS' : (newStatus === 'Ditolak' ? 'Tolak Bukti SKS' : 'Set Pending');
        document.getElementById('proses_teks_info').innerText = `Anda akan mengubah status menjadi: ${newStatus.toUpperCase()}`;

        new bootstrap.Modal(document.getElementById('modalProsesSKS')).show();
    }

    // UPDATE LOGIC: Submit Modal menggunakan POST (FormData)
    document.getElementById('formProsesSKS').addEventListener('submit', async function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append('action', 'update-status');

        try {
            const response = await fetch(api_act_sks, {
                method: 'POST',
                body: fd
            });
            const result = await response.json();
            if (result.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('modalProsesSKS')).hide();
                loadTableSKS();
            } else {
                alert("Gagal: " + result.message);
            }
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan sistem saat memproses data.");
        }
    });

    async function deleteSKS(id) {
        if (!confirm("Hapus data SKS ini selamanya?")) return;
        const res = await fetch(`${api_act_sks}?action=delete&id=${id}`);
        const result = await res.json();
        if (result.status === 'success') loadTableSKS();
    }
</script>