<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1">Total Jenis APD</span>
                            <div class="d-flex align-items-baseline">
                                <h3 class="mb-0 me-2" id="stat-total">0</h3>
                                <small class="text-success fw-medium">Items</small>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-package fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1">Status Aktif</span>
                            <div class="d-flex align-items-baseline">
                                <h3 class="mb-0 me-2" id="stat-aktif">0</h3>
                                <small class="text-info fw-medium">Ready</small>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-shield fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-3">
            <div class="card-title mb-0">
                <h5 class="mb-0">Daftar Inventaris APD</h5>
                <small class="text-muted">Kelola aset alat pelindung diri perusahaan</small>
            </div>
            <button class="btn btn-primary shadow-sm" onclick="addAPD()">
                <i class="bx bx-plus me-1"></i> Tambah APD
            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>APD & Kategori</th>
                        <th>Deskripsi</th>
                        <th>Masa Pakai</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-apd" class="table-border-bottom-0">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAPD" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formAPD" class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="modalTitle">Tambah Item APD</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_apd" id="id_apd">
                <div class="mb-3">
                    <label class="form-label">Nama APD</label>
                    <input type="text" name="nama_apd" id="nama_apd" class="form-control" placeholder="Contoh: Masker N95" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori_apd" id="kategori_apd" class="form-select" required>
                        <option value="Proteksi Kepala">Proteksi Kepala</option>
                        <option value="Pernapasan">Pernapasan</option>
                        <option value="Proteksi Tangan">Proteksi Tangan</option>
                        <option value="Proteksi Telinga">Proteksi Telinga</option>
                        <option value="Harness">Harness</option>
                        <option value="Pakaian Kerja">Pakaian Kerja</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Masa Pakai (Hari)</label>
                        <input type="number" name="masa_pakai_hari" id="masa_pakai_hari" class="form-control" value="365">
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">Stok Awal</label>
                        <input type="number" name="stok" id="stok" class="form-control" value="0" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Status Aktif</label>
                        <select name="status_aktif" id="status_aktif" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Item</button>
            </div>
        </form>
    </div>
</div>

<script>
    let myModal;
    const api_act = 'app/controller/act-apd.php';
    const api_get = 'app/controller/get-data/get-apd.php';

    document.addEventListener('DOMContentLoaded', function() {
        myModal = new bootstrap.Modal(document.getElementById('modalAPD'));
        loadTable();
    });

    async function loadTable() {
        try {
            const res = await fetch(api_get);
            const data = await res.json();
            document.getElementById('tbody-apd').innerHTML = data.html;
            document.getElementById('stat-total').innerText = data.total;
            document.getElementById('stat-aktif').innerText = data.aktif;
        } catch (e) {
            showToast("Gagal memuat data", "error");
        }
    }

    function addAPD() {
        document.getElementById('formAPD').reset();
        document.getElementById('id_apd').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Item APD';
        myModal.show();
    }

    async function editAPD(id) {
        try {
            const res = await fetch(`${api_act}?action=get-single&id=${id}`);
            const data = await res.json();
            document.getElementById('id_apd').value = data.id_apd;
            document.getElementById('nama_apd').value = data.nama_apd;
            document.getElementById('kategori_apd').value = data.kategori_apd;
            document.getElementById('deskripsi').value = data.deskripsi;
            document.getElementById('masa_pakai_hari').value = data.masa_pakai_hari;
            document.getElementById('stok').value = data.stok;
            document.getElementById('status_aktif').value = data.status_aktif;
            document.getElementById('modalTitle').innerText = 'Edit Item APD';
            myModal.show();
        } catch (e) { showToast("Gagal ambil data", "error"); }
    }

    document.getElementById('formAPD').onsubmit = async (e) => {
        e.preventDefault();
        const res = await fetch(`${api_act}?action=save`, { method: 'POST', body: new FormData(e.target) });
        const result = await res.json();
        if(result.status === 'success') {
            myModal.hide(); loadTable(); showToast("Berhasil disimpan", "success");
        }
    };

    async function deleteAPD(id) {
        if(await confirmDialog("Hapus data APD ini?")) {
            fetch(`${api_act}?action=delete&id=${id}`).then(() => { loadTable(); showToast("Dihapus", "success"); });
        }
    }
</script>