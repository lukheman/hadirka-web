<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <div class="card-title mb-0">
                <h5 class="mb-0 text-primary fw-bold">Manajemen Periode</h5>
                <small class="text-muted">Kelola periode absensi dan penggajian karyawan</small>
            </div>
            <button class="btn btn-primary shadow-sm" onclick="addPeriode()">
                <i class="bx bx-plus-circle me-1"></i> Tambah Periode
            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="25%">Tipe Periode</th>
                        <th width="35%">Rentang Tanggal</th>
                        <th width="20%">Status</th>
                        <th width="20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-periode">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPeriode" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formPeriode" class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="modalTitle">Form Periode</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_priode" id="id_priode">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tipe Periode</label>
                    <select name="type_priode" id="type_priode" class="form-select" required>
                        <option value="" disabled selected>Pilih Tipe...</option>
                        <option value="absen">Absensi</option>
                        <option value="slip gaji">Slip Gaji</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    let myModal;
    const api_act = 'app/controller/act-periode.php';
    const api_get = 'app/controller/get-data/get-periode.php';

    // Inisialisasi
    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('modalPeriode');
        if (typeof bootstrap !== 'undefined') {
            myModal = new bootstrap.Modal(modalEl);
            loadTable();
        }
    });

    // 1. Load Tabel
    async function loadTable() {
        try {
            const res = await fetch(api_get);
            const htmlResult = await res.text();
            document.getElementById('tbody-periode').innerHTML = htmlResult;
        } catch (e) {
            document.getElementById('tbody-periode').innerHTML = '<tr><td colspan="4" class="text-center text-danger p-4">Gagal memuat data.</td></tr>';
        }
    }

    // 2. Reset Modal
    function addPeriode() {
        document.getElementById('formPeriode').reset();
        document.getElementById('id_priode').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Periode Baru';
        myModal.show();
    }

    // 3. Edit Data
    async function editData(id) {
        try {
            const res = await fetch(`${api_act}?action=get-single&id=${id}`);
            const data = await res.json();
            if (data.id_priode) {
                document.getElementById('id_priode').value = data.id_priode;
                document.getElementById('tgl_mulai').value = data.tgl_mulai;
                document.getElementById('tgl_selesai').value = data.tgl_selesai;
                document.getElementById('type_priode').value = data.type_priode;
                document.getElementById('modalTitle').innerText = 'Edit Periode';
                myModal.show();
            }
        } catch (e) {
            showToast("Gagal mengambil data", "error");
        }
    }

    // 4. Simpan (Update & Insert)
    document.getElementById('formPeriode').onsubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await fetch(`${api_act}?action=save`, {
                method: 'POST',
                body: new FormData(e.target)
            });
            const result = await res.json();
            if (result.status === 'success') {
                myModal.hide();
                loadTable();
                showToast("Data berhasil disimpan", "success");
            } else {
                showToast(result.message, "error");
            }
        } catch (e) {
            showToast("Terjadi kesalahan sistem", "error");
        }
    };

    // 5. Toggle Status (Open/Closed)
    async function toggleStatus(id, status) {
        try {
            const res = await fetch(`${api_act}?action=toggle-status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            const result = await res.json();
            if (result.status === 'success') {
                loadTable();
                showToast("Status diperbarui ke " + status, "success");
            }
        } catch (e) {
            showToast("Gagal mengubah status", "error");
        }
    }

    // 6. Hapus
    async function deleteData(id) {
        if (await confirmDialog("Hapus periode ini secara permanen?")) {
            fetch(`${api_act}?action=delete&id=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        loadTable();
                        showToast("Data dihapus", "success");
                    }
                });
        }
    }
</script>