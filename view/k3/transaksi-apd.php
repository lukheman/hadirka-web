<style>
    /* Menyesuaikan tinggi Select2 agar sama dengan input teks Bootstrap */
    .select2-container .select2-selection--single {
        height: 38px !important;
        /* Tinggi standar input form-control */
        border: 1px solid #d9dee3 !important;
        /* Menyesuaikan warna border bawaan template */
        border-radius: 0.375rem !important;
        /* Lengkungan sudut standar Bootstrap */
    }

    /* Memposisikan teks pilihan agar berada tepat di tengah (vertically centered) */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 14px !important;
        color: #697a8d !important;
        /* Warna teks */
    }

    /* Menyesuaikan tinggi panah dropdown (arrow) di sebelah kanan */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 5px !important;
    }

    /* ==========================================================
       Khusus mengecilkan Select2 HANYA di bagian Filter Karyawan 
       ========================================================== */
    #wrapper-filter-karyawan .select2-container .select2-selection--single {
        height: 33px !important; /* Rata-rata tinggi form-select-sm Bootstrap */
        border: 1px solid #d9dee3 !important;
        border-radius: 0.25rem !important;
        display: flex !important;
        align-items: center !important; /* Memaksa teks rata tengah vertikal */
    }

    #wrapper-filter-karyawan .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 0.5rem !important; /* Menyamakan jarak kiri dengan filter lain */
        font-size: 0.875rem !important;
        color: #697a8d !important;
        width: 100%;
    }

    #wrapper-filter-karyawan .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 31px !important;
        top: 0 !important;
        right: 5px !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card bg-label-primary">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 text-primary">Log Pengambilan APD</h4>
                        <p class="mb-0">Pantau distribusi Alat Pelindung Diri kepada karyawan secara real-time.</p>
                    </div>
                    <button class="btn btn-warning shadow me-2" data-bs-toggle="modal" data-bs-target="#ModalImport">
                        <i class="bx bx-upload me-1"></i> Import Excel
                    </button>
                    <button class="btn btn-primary shadow" onclick="tambahTransaksi()">
                        <i class="bx bx-plus-circle me-1"></i> Catat Pengambilan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER TRANSAKSI APD -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filter Site</label>
                    <?php if ($_SESSION['site'] == 0) { ?>
                        <select id="filterSite" class="form-select form-select-sm" onchange="loadTable()">
                            <option value="">-- Semua Site --</option>
                            <?php
                            $q_site = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                            while ($s = $q_site->fetch_assoc()) {
                                echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                            }
                            ?>
                        </select>
                    <?php } else { ?>
                        <input id="filterSite" type="hidden" value="<?= $_SESSION['site'] ?>">
                        <input type="text" class="form-control form-control-sm bg-light" value="Site Anda (Otomatis)" readonly>
                    <?php } ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filter Departemen</label>
                    <select id="filterDepartemen" class="form-select form-select-sm" onchange="loadTable()">
                        <option value="">-- Semua Departemen --</option>
                        <?php
                        $q_dept = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                        while ($d = $q_dept->fetch_assoc()) {
                            echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filter Jabatan</label>
                    <select id="filterJabatan" class="form-select form-select-sm" onchange="loadTable()">
                        <option value="">-- Semua Jabatan --</option>
                        <?php
                        $q_jab = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                        while ($j = $q_jab->fetch_assoc()) {
                            echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- TAMBAHAN FILTER KARYAWAN -->
                <div class="col-md-3" id="wrapper-filter-karyawan">
                    <label class="form-label small fw-bold text-muted">Filter Karyawan</label>
                    <select id="filterKaryawan" class="form-select form-select-sm" onchange="loadTable()">
                        <option value="">-- Semua Karyawan --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- /FILTER TRANSAKSI APD -->

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
                        <th>Nama APD</th>
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
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal_pengambilan" id="tanggal_pengambilan" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <label class="form-label fw-bold mb-0">Daftar APD & Jumlah</label>
                    <button type="button" class="btn btn-sm btn-success" onclick="addApdRow()">
                        <i class="bx bx-plus"></i> Tambah APD
                    </button>
                </div>

                <div id="apd-container">
                    <div class="row apd-row mb-2">
                        <div class="col-7">
                            <select name="id_apd[]" class="form-select select2-apd" required>
                                <option value="">-- Pilih APD --</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <input type="number" name="jumlah[]" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-2 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-label-danger btn-remove-apd" style="visibility: hidden;">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
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

<div class="modal fade" id="modalEditTransaksi" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditTransaksi" class="modal-content">
            <input type="hidden" name="id_pengambilan" id="edit_id_pengambilan">

            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">Edit Transaksi APD</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Karyawan</label>
                    <select name="id_karyawan" id="edit_id_karyawan" class="form-select select2" required>
                        <option value="">-- Ketik NIK / Nama Karyawan --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal_pengambilan" id="edit_tanggal_pengambilan" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-8 mb-3">
                        <label class="form-label fw-bold">Pilih APD</label>
                        <select name="id_apd" id="edit_id_apd" class="form-select select2" required>
                            <option value="">-- Pilih APD --</option>
                        </select>
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label fw-bold">Jumlah</label>
                        <input type="number" name="jumlah" id="edit_jumlah" class="form-control" min="1" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Update Transaksi</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ModalImport" tabindex="-1" aria-labelledby="ModalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formImport" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white" id="ModalImportLabel"><i class="bx bx-upload"></i> Upload Transaksi APD</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="instruction-text mb-3">
                    Unggah file Excel (.xls / .xlsx) sesuai format kolom untuk mencatat distribusi APD secara masal.
                </p>
                <label for="file_excel" class="form-label fw-bold">File Excel</label>
                <input type="file" name="file_excel" id="file_excel" class="form-control" accept=".xls,.xlsx" required>
                <a href="assets/template-csv/Format_Impor_Data_Peminjaman_APD.xlsx" class="text-primary small mt-1 d-inline-block">Download Template Format .xlsx</a>

                <div id="progressWrapper" class="progress mt-3" style="display:none; height:20px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0%;">0%</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-warning" type="submit" id="btnUpload">
                    <i class="bx bx-upload"></i> Upload Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery global via CDN di <head> index.php -->

<script>
    let myModal;
    let myModalEdit;
    const api_trx = 'app/controller/act-transaksi-apd.php';
    const api_get_trx = 'app/controller/get-data/get-transaksi-apd.php';

    document.addEventListener('DOMContentLoaded', function() {
        myModal = new bootstrap.Modal(document.getElementById('modalTransaksi'));
        myModalEdit = new bootstrap.Modal(document.getElementById('modalEditTransaksi')); // Inisialisasi
        loadTable();
        loadAllKaryawan();
        loadAPDOptions();
    });

    async function loadTable() {
        // Ambil nilai dari dropdown filter
        let site = document.getElementById("filterSite") ? document.getElementById("filterSite").value : '';
        let departemen = document.getElementById("filterDepartemen") ? document.getElementById("filterDepartemen").value : '';
        let jabatan = document.getElementById("filterJabatan") ? document.getElementById("filterJabatan").value : '';
        let karyawan = document.getElementById("filterKaryawan") ? document.getElementById("filterKaryawan").value : '';

        // Sisipkan parameter ke URL
        let url = `${api_get_trx}?site=${site}&departemen=${departemen}&jabatan=${jabatan}&karyawan=${karyawan}`;

        try {
            const res = await fetch(url);
            const html = await res.text();
            document.getElementById('tbody-transaksi').innerHTML = html;
        } catch (e) {
            console.error("Gagal memuat tabel: ", e);
        }
    }

    // Load semua data karyawan ke dropdown (Sekali saja saat halaman dimuat)
    async function loadAllKaryawan() {
        try {
            const res = await fetch(`${api_trx}?action=get-all-karyawan`);
            const data = await res.json();

            let html = '<option value="">-- Ketik NIK / Nama Karyawan --</option>';
            let filterHtml = '<option value="">-- Semua Karyawan --</option>';

            data.forEach(k => {
                let id_tampil = k.nik_karyawan ? k.nik_karyawan : k.id_karyawan;
                html += `<option value="${k.id_karyawan}">[${id_tampil}] - ${k.nama_lengkap}</option>`;
                filterHtml += `<option value="${k.id_karyawan}">[${id_tampil}] - ${k.nama_lengkap}</option>`;
            });

            document.getElementById('id_karyawan').innerHTML = html;
            document.getElementById('edit_id_karyawan').innerHTML = html;
            document.getElementById('filterKaryawan').innerHTML = filterHtml; // Isi dropdown filter

            // Inisialisasi Select2
            if (typeof $ !== 'undefined') {
                $('#id_karyawan').select2({
                    dropdownParent: $('#modalTransaksi'),
                    placeholder: "-- Ketik NIK / Nama Karyawan --",
                    allowClear: true,
                    width: '100%'
                });

                $('#edit_id_karyawan').select2({
                    dropdownParent: $('#modalEditTransaksi'),
                    placeholder: "-- Ketik NIK / Nama Karyawan --",
                    allowClear: true,
                    width: '100%'
                });

                // Inisialisasi Select2 untuk Filter Karyawan (Tanpa Parent Modal)
                $('#filterKaryawan').select2({
                    placeholder: "-- Semua Karyawan --",
                    allowClear: true,
                    width: '100%'
                });

                // Deteksi ketika Filter Karyawan diubah, panggil loadTable()
                $('#filterKaryawan').on('change', function() {
                    loadTable();
                });

                $('#id_apd').select2({
                    dropdownParent: $('#modalTransaksi'),
                    placeholder: "-- Pilih APD --",
                    width: '100%'
                });

                $('#id_karyawan').on('select2:select', function(e) {
                    getDetailKaryawan(e.params.data.id);
                });

                $('#id_karyawan').on('select2:clear', function(e) {
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

    // Tambahkan variabel global ini di awal script
    let apdOptionsHtml = '<option value="">-- Pilih APD --</option>';

    // Memuat daftar APD
    async function loadAPDOptions() {
        try {
            const res = await fetch(`${api_trx}?action=get-options-apd`);
            const data = await res.json();

            apdOptionsHtml = '<option value="">-- Pilih APD --</option>';
            data.forEach(a => {
                apdOptionsHtml += `<option value="${a.id_apd}">${a.nama_apd}</option>`;
            });

            // Masukkan ke select pertama
            document.querySelector('.select2-apd').innerHTML = apdOptionsHtml;
            initSelect2Apd();

            document.getElementById('edit_id_apd').innerHTML = apdOptionsHtml;
            if (typeof $ !== 'undefined') {
                $('#edit_id_apd').select2({
                    dropdownParent: $('#modalEditTransaksi'),
                    placeholder: "-- Pilih APD --",
                    width: '100%'
                });
            }

        } catch (e) {
            console.error("Gagal load APD", e);
        }
    }

    // Buat fungsi untuk inisialisasi Select2 khusus APD
    function initSelect2Apd(element = $('.select2-apd')) {
        if (typeof $ !== 'undefined') {
            element.select2({
                dropdownParent: $('#modalTransaksi'),
                placeholder: "-- Pilih APD --",
                width: '100%'
            });
        }
    }

    // Fungsi menambah baris input APD
    function addApdRow() {
        const container = document.getElementById('apd-container');
        const newRow = document.createElement('div');
        newRow.className = 'row apd-row mb-2';
        newRow.innerHTML = `
        <div class="col-7">
            <select name="id_apd[]" class="form-select select2-apd-dynamic" required>
                ${apdOptionsHtml}
            </select>
        </div>
        <div class="col-3">
            <input type="number" name="jumlah[]" class="form-control" min="1" value="1" required>
        </div>
        <div class="col-2 d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove-apd">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
        container.appendChild(newRow);

        // Inisialisasi Select2 pada elemen yang baru dibuat
        initSelect2Apd($(newRow).find('.select2-apd-dynamic'));
    }

    // Event Delegate untuk tombol hapus baris APD
    document.getElementById('apd-container').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-apd');
        if (btn) {
            btn.closest('.apd-row').remove();
        }
    });

    function tambahTransaksi() {
        document.getElementById('formTransaksi').reset();
        // Kembalikan ke 1 baris saja
        const container = document.getElementById('apd-container');
        const rows = container.querySelectorAll('.apd-row');
        rows.forEach((row, index) => {
            if (index > 0) row.remove(); // Hapus baris tambahan
        });

        if (typeof $ !== 'undefined') {
            $('#id_karyawan').val(null).trigger('change');
            $('.select2-apd').val(null).trigger('change');

            // Reset detail karyawan
            document.getElementById('detail_site').value = '';
            document.getElementById('detail_departemen').value = '';
            document.getElementById('detail_jabatan').value = '';
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

    async function editTransaksi(id) {
        try {
            // Ambil data spesifik dari backend
            const res = await fetch(`${api_trx}?action=get-single&id=${id}`);
            const result = await res.json();

            if (result.status === 'success') {
                const data = result.data;

                // Set value ke dalam inputan Modal Edit
                document.getElementById('edit_id_pengambilan').value = data.id_pengambilan;
                document.getElementById('edit_tanggal_pengambilan').value = data.tanggal_pengambilan;
                document.getElementById('edit_jumlah').value = data.jumlah;

                // Trigger perubahan untuk Select2
                if (typeof $ !== 'undefined') {
                    $('#edit_id_karyawan').val(data.id_karyawan).trigger('change');
                    $('#edit_id_apd').val(data.id_apd).trigger('change');
                }

                // Tampilkan Modal
                myModalEdit.show();
            } else {
                showToast(result.message || "Data tidak ditemukan", "error");
            }
        } catch (e) {
            showToast("Gagal mengambil data transaksi", "error");
        }
    }

    document.getElementById('formEditTransaksi').onsubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await fetch(`${api_trx}?action=update`, {
                method: 'POST',
                body: new FormData(e.target)
            });
            const result = await res.json();
            if (result.status === 'success') {
                myModalEdit.hide();
                loadTable();
                showToast("Transaksi berhasil diupdate", "success");
            } else {
                showToast(result.message || "Gagal update", "error");
            }
        } catch (e) {
            showToast("Gagal update transaksi", "error");
        }
    };

    async function deleteTransaksi(id) {
        if (await confirmDialog("Apakah Anda yakin ingin menghapus catatan transaksi ini?")) {
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

    // ==========================================
    // BLOK FUNGSI IMPORT EXCEL
    // ==========================================
    $("#formImport").on("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = $("#btnUpload");

        // Tampilkan progress bar
        $("#progressWrapper").show();
        btn.prop("disabled", true).html('<i class="bx bx-loader bx-spin"></i> Processing...');

        $.ajax({
            // Arahkan ke file prosesor baru
            url: "app/controller/csv-control/csv-transaksi-apd.php",
            type: "POST",
            dataType: "json",
            data: formData,
            contentType: false,
            processData: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $("#progressBar").css("width", percentComplete + "%").text(percentComplete + "%");
                    }
                }, false);
                return xhr;
            },
            success: function(res) {
                if (res.success) {
                    let msg = `📊 IMPORT SELESAI!\n\n✅ Berhasil: ${res.berhasil} data\n❌ Gagal: ${res.gagal} data`;

                    if (res.errors && res.errors.length > 0) {
                        msg += `\n\n📋 ERROR DETAIL:\n` + res.errors.slice(0, 5).join('\n');
                        if (res.errors.length > 5) {
                            msg += `\n... dan ${res.errors.length - 5} error lainnya`;
                        }
                    }

                    Swal.fire({ icon: 'success', title: 'Import Selesai', html: '<pre style="text-align:left;white-space:pre-wrap;">' + msg.replace(/</g, '&lt;') + '</pre>', confirmButtonText: 'OK' });
                    loadTable(); // Refresh tabel setelah import
                    $("#ModalImport").modal("hide");
                    $("#formImport")[0].reset();
                    showToast(`Import Selesai. Berhasil: ${res.berhasil}`, "success");
                } else {
                    Swal.fire({ icon: 'error', title: 'Import Gagal', text: (res.error || "Terjadi kesalahan pada file Excel.") });
                    showToast("Import Gagal", "error");
                }
            },
            error: function(xhr, status, error) {
                showToast("TERJADI KESALAHAN SERVER! Cek console browser untuk detail lengkap.", "error");
                console.error("Response:", xhr.responseText);
            },
            complete: function() {
                // Reset UI form
                $("#progressWrapper").hide();
                $("#progressBar").css("width", "0%").text("0%");
                btn.prop("disabled", false).html('<i class="bx bx-upload"></i> Upload Data');
            }
        });
    });
</script>