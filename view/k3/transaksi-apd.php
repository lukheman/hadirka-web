<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card bg-label-primary">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 text-primary">Log Pengambilan APD</h4>
                        <p class="mb-0">Pantau distribusi Alat Pelindung Diri kepada karyawan secara real-time.</p>
                    </div>
                    <button class="btn btn-primary shadow" onclick="tambahTransaksi()">
                        <i class="bx bx-plus-circle me-1"></i> Catat Pengambilan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0">Riwayat Pengambilan</h5>
            <div class="d-flex">
                <input type="text" id="searchLog" class="form-control form-control-sm me-2" placeholder="Cari karyawan...">
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Barang APD</th>
                        <th>Jumlah</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-transaksi">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTransaksi" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formTransaksi" class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Catat Transaksi APD</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Site / Lokasi</label>
                    <select id="select_site" class="form-select" onchange="loadJabatan(this.value)" required>
                        <option value="">-- Pilih Site --</option>
                        <?php
                        $s = mysqli_query($koneksi, "SELECT * FROM tb_site");
                        while ($rs = mysqli_fetch_assoc($s)) echo "<option value='$rs[id_site]'>$rs[nama_site]</option>";
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Jabatan</label>
                    <select id="select_jabatan" class="form-select" onchange="loadKaryawan()" disabled required>
                        <option value="">-- Pilih Jabatan --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Karyawan</label>
                    <select name="id_karyawan" id="id_karyawan" class="form-select" disabled required>
                        <option value="">-- Pilih Karyawan --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih APD</label>
                    <select name="id_apd" id="id_apd" class="form-select select2" required>
                        <option value="">-- Pilih APD --</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" name="tanggal_pengambilan" id="tanggal_pengambilan" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" value="1" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

<script>
    let myModal;
    const api_trx = 'app/controller/act-transaksi-apd.php';
    const api_get_trx = 'app/controller/get-data/get-transaksi-apd.php';

    document.addEventListener('DOMContentLoaded', function() {
        myModal = new bootstrap.Modal(document.getElementById('modalTransaksi'));
        loadTable();
        loadAPDOptions(); // Hanya memuat daftar APD saat awal
    });

    async function loadTable() {
        const res = await fetch(api_get_trx);
        const html = await res.text();
        document.getElementById('tbody-transaksi').innerHTML = html;
    }

    // 1. Load Jabatan berdasarkan Site
    async function loadJabatan(siteId) {
        const selectJabatan = document.getElementById('select_jabatan');
        const selectKaryawan = document.getElementById('id_karyawan');

        if (!siteId) {
            selectJabatan.disabled = true;
            selectKaryawan.disabled = true;
            selectJabatan.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
            return;
        }

        try {
            const formData = new FormData();
            formData.append('site', siteId);

            const res = await fetch('app/controller/get-data/get-jabatan-by-site.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            let html = '<option value="">-- Pilih Jabatan --</option>';
            data.forEach(j => {
                html += `<option value="${j.id_jabatan}">${j.nama_jabatan}</option>`;
            });

            selectJabatan.innerHTML = html;
            selectJabatan.disabled = false;
            selectKaryawan.disabled = true;
            selectKaryawan.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
        } catch (e) {
            showToast("Gagal memuat data jabatan", "error");
        }
    }

    // 2. Load Karyawan berdasarkan Site & Jabatan
    async function loadKaryawan() {
        const siteId = document.getElementById('select_site').value;
        const jabatanId = document.getElementById('select_jabatan').value;
        const selectKaryawan = document.getElementById('id_karyawan');

        if (!siteId || !jabatanId) return;

        try {
            const formData = new FormData();
            formData.append('site', siteId);
            formData.append('jabatan', jabatanId);

            const res = await fetch('app/controller/get-data/get-karyawan-by-site-jabatan.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            let html = '<option value="">-- Pilih Karyawan --</option>';
            data.forEach(k => {
                html += `<option value="${k.id_karyawan}">${k.nama_lengkap}</option>`;
            });

            selectKaryawan.innerHTML = html;
            selectKaryawan.disabled = false;
        } catch (e) {
            showToast("Gagal memuat data karyawan", "error");
        }
    }

    // 3. Memuat daftar APD saja ke dropdown
    async function loadAPDOptions() {
        try {
            const res = await fetch(`${api_trx}?action=get-options-apd`);
            const data = await res.json();
            let apdHtml = '<option value="">-- Pilih APD --</option>';
            data.forEach(a => apdHtml += `<option value="${a.id_apd}">${a.nama_apd}</option>`);
            document.getElementById('id_apd').innerHTML = apdHtml;
        } catch (e) {
            console.error("Gagal load APD");
        }
    }

    function tambahTransaksi() {
        document.getElementById('formTransaksi').reset();
        document.getElementById('select_jabatan').disabled = true;
        document.getElementById('id_karyawan').disabled = true;
        myModal.show();
    }

    document.getElementById('formTransaksi').onsubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await fetch(`${api_trx}?action=save`, {
                method: 'POST',
                body: new FormData(e.target)
            });
            const result = await res.json();
            if (result.status === 'success') {
                myModal.hide();
                loadTable();
                showToast("Transaksi berhasil dicatat", "success");
            }
        } catch (e) {
            showToast("Gagal menyimpan transaksi", "error");
        }
    };

    function deleteTransaksi(id) {
        if (confirm("Apakah Anda yakin ingin menghapus catatan transaksi ini?")) {
            fetch(`${api_trx}?action=delete&id=${id}`)
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success') {
                        loadTable(); // Memuat ulang isi tabel
                        showToast("Catatan berhasil dihapus", "success");
                    } else {
                        showToast("Gagal menghapus data", "error");
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    showToast("Terjadi kesalahan jaringan", "error");
                });
        }
    }
</script>