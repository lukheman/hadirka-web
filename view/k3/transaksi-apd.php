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
                    <label class="form-label fw-bold">Cari Karyawan</label>
                    <select name="id_karyawan" id="id_karyawan" class="form-select select2" required>
                        <option value="">-- Ketik NIK / Nama Karyawan --</option>
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">SITE</label>
                        <input type="text" id="detail_site" class="form-control bg-light" placeholder="-" readonly>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">DEPARTEMEN</label>
                        <input type="text" id="detail_departemen" class="form-control bg-light" placeholder="-" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">JABATAN</label>
                        <input type="text" id="detail_jabatan" class="form-control bg-light" placeholder="-" readonly>
                    </div>
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
        loadAllKaryawan(); 
        loadAPDOptions(); 
    });

    async function loadTable() {
        const res = await fetch(api_get_trx);
        const html = await res.text();
        document.getElementById('tbody-transaksi').innerHTML = html;
    }

    // Load semua data karyawan ke dropdown (Sekali saja saat halaman dimuat)
    async function loadAllKaryawan() {
        try {
            const res = await fetch(`${api_trx}?action=get-all-karyawan`);
            const data = await res.json();
            
            let html = '<option value="">-- Ketik NIK / Nama Karyawan --</option>';
            data.forEach(k => {
                let id_tampil = k.nik_karyawan ? k.nik_karyawan : k.id_karyawan; 
                html += `<option value="${k.id_karyawan}">[${id_tampil}] - ${k.nama_lengkap}</option>`;
            });
            
            document.getElementById('id_karyawan').innerHTML = html;

            // Inisialisasi Select2
            if (typeof $ !== 'undefined') {
                $('#id_karyawan').select2({
                    dropdownParent: $('#modalTransaksi'), 
                    placeholder: "-- Ketik NIK / Nama Karyawan --",
                    allowClear: true,
                    width: '100%' // <-- TAMBAHKAN INI
                });

                $('#id_apd').select2({
                    dropdownParent: $('#modalTransaksi'),
                    placeholder: "-- Pilih APD --",
                    width: '100%' // <-- TAMBAHKAN INI
                });

                // Deteksi ketika Karyawan dipilih, langsung fetch detailnya
                $('#id_karyawan').on('select2:select', function (e) {
                    getDetailKaryawan(e.params.data.id);
                });

                // Deteksi ketika Karyawan di-clear (dikosongkan)
                $('#id_karyawan').on('select2:clear', function (e) {
                    getDetailKaryawan('');
                });
            }
        } catch (e) {
            showToast("Gagal memuat data karyawan", "error");
        }
    }

    // Fungsi menarik Site, Departemen, dan Jabatan secara otomatis
    async function getDetailKaryawan(id_karyawan) {
        // Kosongkan field terlebih dahulu
        document.getElementById('detail_site').value = '';
        document.getElementById('detail_departemen').value = '';
        document.getElementById('detail_jabatan').value = '';

        if (!id_karyawan) return; // Berhenti jika tidak ada ID

        try {
            const res = await fetch(`${api_trx}?action=get-karyawan-detail&id=${id_karyawan}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                document.getElementById('detail_site').value = data.site;
                document.getElementById('detail_departemen').value = data.departemen;
                document.getElementById('detail_jabatan').value = data.jabatan;
            }
        } catch (e) {
            console.error("Gagal menarik detail karyawan", e);
        }
    }

    // Memuat daftar APD
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
        // Reset juga tampilan text Select2
        if (typeof $ !== 'undefined') {
            $('#id_karyawan').val(null).trigger('change');
            $('#id_apd').val(null).trigger('change');
        }
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
            } else {
                showToast(result.message || "Gagal menyimpan", "error");
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
                        loadTable();
                        showToast("Catatan berhasil dihapus", "success");
                    } else {
                        showToast("Gagal menghapus data", "error");
                    }
                });
        }
    }

    // Fitur Live Search untuk Tabel Riwayat
    document.getElementById('searchLog').addEventListener('keyup', function() {
        let keyword = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tbody-transaksi tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });
    });
</script>