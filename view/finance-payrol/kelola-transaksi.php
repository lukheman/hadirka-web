<?php
require_once __DIR__ . '/../../app/config/koneksi.php';

// Ambil data kategori utama untuk modal agar dinamis
$qKategori = mysqli_query($koneksi, "SELECT * FROM tb_kategori_keuangan ORDER BY kelompok ASC, kode_akun ASC");
$kategoriGroups = [];
while ($row = mysqli_fetch_assoc($qKategori)) {
    $kategoriGroups[$row['kelompok']][] = $row;
}

// Ambil KHUSUS data Kas dan Bank untuk Dropdown Lawan Transaksi
$qKasBank = mysqli_query($koneksi, "SELECT * FROM tb_kategori_keuangan WHERE kode_akun IN ('1001', '1002', '1003', '1004', '1005') ORDER BY kode_akun ASC");
$kasBankList = [];
while ($row = mysqli_fetch_assoc($qKasBank)) {
    $kasBankList[] = $row;
}
?>

<!-- Select2/DataTables CSS global via CDN di <head> index.php -->
<style>
    .dataTables_wrapper .row {
        margin-bottom: 0.5rem;
    }

    /* Perbaikan UI Select2 untuk Bootstrap 5 */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #d9dee3 !important;
        min-height: 38px;
    }

    .select2-container--bootstrap-5 .select2-selection--single {
        padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: #696cff !important;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25) !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row align-items-center mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <h4 class="mb-1 fw-bold">Kelola Transaksi Keuangan</h4>
            <p class="text-muted mb-0">Catat dan pantau transaksi berdasarkan kelompok akun</p>
        </div>

        <div class="col-md-6 text-md-end">
            <div class="d-flex flex-column flex-sm-row justify-content-md-end align-items-sm-center gap-2">
                <select id="periodeDropdown" class="form-select form-select-sm fw-bold cursor-pointer bg-white" onchange="loadDataTransaksi()" style="width: auto; min-width: 200px;">
                    <option value="semua">-- Semua Periode --</option>
                    <?php
                    $bulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

                    $bulan_options = [];
                    $tanggal_bulan = new DateTime('first day of this month');

                    for ($i = 0; $i < 12; $i++) {
                        $value = $tanggal_bulan->format('Y-m');

                        // Hindari opsi ganda jika tanggal atau timezone server berubah.
                        if (!isset($bulan_options[$value])) {
                            $bulan_options[$value] = $bulanIndo[(int)$tanggal_bulan->format('m') - 1]
                                . ' ' . $tanggal_bulan->format('Y');
                        }

                        $tanggal_bulan->modify('-1 month');
                    }

                    foreach ($bulan_options as $value => $label) {
                        $selected = array_key_first($bulan_options) === $value ? ' selected' : '';
                        echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>'
                            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                            . '</option>';
                    }
                    ?>
                </select>
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTransaksi">
                    <i class="bx bx-plus-circle me-1"></i> Tambah Transaksi
                </button>
                <a href="?page=data-coa" class="btn btn-sm btn-warning shadow-sm">
                    <i class="bx bx-list-ol me-1"></i> Kelola COA
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom pt-3 pb-2">
            <ul class="nav nav-tabs card-header-tabs" id="transaksiTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active fw-semibold text-primary" data-bs-toggle="tab" href="#tab-pendapatan" role="tab"><i class="bx bx-wallet me-1"></i> Pendapatan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-danger" data-bs-toggle="tab" href="#tab-beban" role="tab"><i class="bx bx-trending-down me-1"></i> Beban & Biaya</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-success" data-bs-toggle="tab" href="#tab-aset" role="tab"><i class="bx bx-building-house me-1"></i> Aset</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-warning" data-bs-toggle="tab" href="#tab-kewajiban" role="tab"><i class="bx bx-credit-card me-1"></i> Kewajiban / Hutang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-info" data-bs-toggle="tab" href="#tab-ekuitas" role="tab"><i class="bx bx-line-chart me-1"></i> Ekuitas / Modal</a>
                </li>
            </ul>
        </div>

        <div class="card-body mt-3">
            <div class="tab-content" id="nav-tabContent">

                <?php
                $tabs = [
                    'pendapatan' => 'Pendapatan',
                    'beban' => 'Beban',
                    'aset' => 'Aset',
                    'kewajiban' => 'Kewajiban',
                    'ekuitas' => 'Ekuitas'
                ];
                $isFirst = true;
                foreach ($tabs as $id => $namaGroup):
                ?>
                    <div class="tab-pane fade <?= $isFirst ? 'show active' : '' ?>" id="tab-<?= $id ?>" role="tabpanel">

                        <div class="mb-3 p-3 bg-light rounded d-flex justify-content-between align-items-center" id="summary-<?= $id ?>" style="display: none !important;">
                            <span class="fw-bold text-muted"><i class="bx bx-bar-chart-alt-2 me-1"></i> Rekapitulasi (Halaman Aktif)</span>
                            <div id="total-<?= $id ?>" class="d-flex gap-4 fw-bold">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100 MyTable" id="table-<?= $id ?>">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="12%">Kode Ref</th> <!-- Tambahan Baru -->
                                        <th width="12%">Tanggal</th>
                                        <th width="16%">Kategori Akun</th>
                                        <th width="15%">Keterangan</th>
                                        <th width="10%">Arus Kas</th>
                                        <th width="10%">Posisi</th>
                                        <th width="12%">Nominal</th>
                                        <th width="8%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-<?= $id ?>">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php
                    $isFirst = false;
                endforeach;
                ?>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white"><i class="bx bx-plus-circle me-2"></i> Input Transaksi (Auto Double-Entry)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahTransaksi" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" name="tanggal_transaksi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori Akun Utama (Beban/Pendapatan/Aset)</label>
                            <select class="form-select" name="id_kategori" id="add_kategori" required>
                                <option value="">-- Pilih Akun Utama --</option>
                                <?php foreach ($kategoriGroups as $kelompok => $kategoris): ?>
                                    <optgroup label="Kelompok: <?= strtoupper($kelompok) ?>">
                                        <?php foreach ($kategoris as $k): ?>
                                            <option value="<?= $k['id_kategori'] ?>"><?= $k['kode_akun'] ?> - <?= $k['nama_akun'] ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Jenis Transaksi</label>
                            <select class="form-select" name="jenis_transaksi" required>
                                <option value="Pengeluaran">Uang Keluar (Beban/Beli Aset/Bayar Hutang)</option>
                                <option value="Pemasukan">Uang Masuk (Pendapatan/Pinjaman Masuk)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dibayar/Diterima Melalui</label>
                            <select class="form-select" name="id_lawan" id="add_lawan" required>
                                <option value="">-- Pilih Kas/Bank --</option>
                                <?php foreach ($kasBankList as $kb): ?>
                                    <option value="<?= $kb['id_kategori'] ?>">[<?= $kb['kode_akun'] ?>] <?= $kb['nama_akun'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="text" class="form-control nominal-mask" name="nominal" placeholder="Contoh: 5.000.000" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Bukti Transaksi <small class="text-muted">(Opsional, Max 1MB/Gambar otomatis dikompres)</small></label>
                        <input type="file" class="form-control file-bukti" id="input_bukti_add" name="bukti_transaksi" accept="image/*,application/pdf">
                        <div id="preview_bukti_add" class="mt-2 text-sm text-muted"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Contoh: Pembayaran Bensin Operasional (Opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="bx bx-save me-1"></i> Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditTransaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-white"><i class="bx bx-edit me-2"></i> Edit Baris Transaksi (Raw Mode)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditTransaksi" enctype="multipart/form-data">
                <input type="hidden" name="id_transaksi" id="edit_id_transaksi">
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="bx bx-info-circle me-1"></i> <strong>Penting:</strong> Mengedit data di sini hanya akan mengubah 1 baris jurnal. Pastikan untuk memperbarui sisi pasangannya secara manual jika ada perubahan nominal.
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" name="tanggal_transaksi" id="edit_tanggal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori Akun</label>
                            <select class="form-select" name="id_kategori" id="edit_kategori" required>
                                <option value="">-- Pilih Kategori Akun --</option>
                                <?php foreach ($kategoriGroups as $kelompok => $kategoris): ?>
                                    <optgroup label="Kelompok: <?= strtoupper($kelompok) ?>">
                                        <?php foreach ($kategoris as $k): ?>
                                            <option value="<?= $k['id_kategori'] ?>"><?= $k['kode_akun'] ?> - <?= $k['nama_akun'] ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipe Arus Kas</label>
                            <select class="form-select" name="tipe_arus" id="edit_arus" required>
                                <option value="Masuk">Masuk</option>
                                <option value="Keluar">Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Posisi Neraca (D/K)</label>
                            <select class="form-select" name="tipe_neraca" id="edit_neraca" required>
                                <option value="debit">Debit</option>
                                <option value="kredit">Kredit</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="text" class="form-control nominal-mask" name="nominal" id="edit_nominal" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Bukti Transaksi <small class="text-muted">(Kosongkan jika tidak diganti)</small></label>
                        <input type="file" class="form-control file-bukti" id="input_bukti_edit" name="bukti_transaksi" accept="image/*,application/pdf">
                        <div id="preview_bukti_edit" class="mt-2 text-sm text-muted"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="2" placeholder="(Opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnUpdate"><i class="bx bx-check me-1"></i> Update Baris Ini</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery/DataTables/Select2 global via CDN di <head> index.php -->

<script>
    $(document).ready(function() {
        // Inisialisasi Select2 di Modal Tambah (Akun Utama)
        $('#add_kategori').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalTambahTransaksi'),
            placeholder: '-- Pilih Akun Utama --',
            width: '100%'
        });

        // Inisialisasi Select2 di Modal Tambah (Akun Lawan/Pembayaran)
        $('#add_lawan').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalTambahTransaksi'),
            placeholder: '-- Pilih Kas/Bank --',
            width: '100%'
        });

        // Inisialisasi Select2 di Modal Edit
        $('#edit_kategori').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalEditTransaksi'),
            placeholder: '-- Pilih Kategori Akun --',
            width: '100%'
        });

        // Reset dropdown saat modal tambah ditutup
        $('#modalTambahTransaksi').on('hidden.bs.modal', function() {
            $('#add_kategori').val('').trigger('change');
            $('#add_lawan').val('').trigger('change');
        });
    });

    // Inisialisasi variabel untuk menyimpan instance DataTables
    let dataTablesInstances = {};

    // Variabel penampung file yang sudah diproses (dikompres/divalidasi)
    let processedFileAdd = null;
    let processedFileEdit = null;

    // Fungsi Kompresi Gambar
    async function compressImage(file, {
        quality = 0.7,
        type = 'image/jpeg'
    }) {
        if (!file.type.startsWith('image/')) return file;
        const imageBitmap = await createImageBitmap(file);
        const canvas = document.createElement('canvas');
        const MAX_WIDTH = 1200; // Disesuaikan agar tidak terlalu besar
        const MAX_HEIGHT = 1200;
        let width = imageBitmap.width;
        let height = imageBitmap.height;

        if (width > height) {
            if (width > MAX_WIDTH) {
                height *= MAX_WIDTH / width;
                width = MAX_WIDTH;
            }
        } else {
            if (height > MAX_HEIGHT) {
                width *= MAX_HEIGHT / height;
                height = MAX_HEIGHT;
            }
        }
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(imageBitmap, 0, 0, width, height);

        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                resolve(new File([blob], file.name, {
                    type: type,
                    lastModified: Date.now()
                }));
            }, type, quality);
        });
    }

    // Handler untuk Input File (Kompresi & Validasi)
    document.querySelectorAll('.file-bukti').forEach(input => {
        input.addEventListener('change', async function() {
            const file = this.files[0];
            const isAddModal = this.id === 'input_bukti_add';
            const previewBox = document.getElementById(isAddModal ? 'preview_bukti_add' : 'preview_bukti_edit');

            // Reset state
            if (isAddModal) processedFileAdd = null;
            else processedFileEdit = null;

            if (file) {
                if (file.type === 'application/pdf') {
                    if (file.size > 1048576) { // 1 MB
                        showToast('Ukuran file PDF maksimal 1MB!', 'warning');
                        this.value = '';
                        previewBox.innerHTML = '<span class="text-danger"><i class="bx bx-x-circle"></i> File PDF Terlalu Besar (>1MB)</span>';
                        return;
                    }
                    if (isAddModal) processedFileAdd = file;
                    else processedFileEdit = file;
                    previewBox.innerHTML = `<span class="text-primary fw-bold"><i class="bx bxs-file-pdf"></i> ${file.name}</span>`;

                } else if (file.type.startsWith('image/')) {
                    previewBox.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Mengompres gambar...';
                    try {
                        const compressedFile = await compressImage(file, {
                            quality: 0.7
                        });

                        if (isAddModal) processedFileAdd = compressedFile;
                        else processedFileEdit = compressedFile;

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewBox.innerHTML = `
                                <img src="${e.target.result}" style="max-height:80px; border-radius:4px; margin-top:5px;"> 
                                <br><small class="text-success fw-bold">Dikompres menjadi: ${(compressedFile.size / 1024).toFixed(0)} KB</small>`;
                        }
                        reader.readAsDataURL(compressedFile);
                    } catch (error) {
                        showToast('Gagal memproses gambar.', 'error');
                        this.value = '';
                        previewBox.innerHTML = '';
                    }
                } else {
                    showToast('Format tidak didukung! Hanya PDF, JPG, PNG.', 'warning');
                    this.value = '';
                    previewBox.innerHTML = '';
                }
            } else {
                previewBox.innerHTML = ''; // Clear jika di-cancel
            }
        });
    });

    async function loadDataTransaksi() {
        const idPriode = document.getElementById("periodeDropdown").value;
        const url = `app/controller/get-data/get-kelola-transaksi.php?id_priode=${idPriode}`;

        try {
            const res = await fetch(url);
            const dataJSON = await res.json();

            if (dataJSON.status === 'success') {
                const mapTabs = {
                    'Pendapatan': 'pendapatan',
                    'Beban': 'beban',
                    'Aset': 'aset',
                    'Kewajiban': 'kewajiban',
                    'Ekuitas': 'ekuitas'
                };

                for (const [kelompok, idTab] of Object.entries(mapTabs)) {
                    const tbody = document.getElementById(`tbody-${idTab}`);
                    const divSummary = document.getElementById(`summary-${idTab}`);
                    const cellTotal = document.getElementById(`total-${idTab}`);
                    const tableId = `#table-${idTab}`;

                    if ($.fn.DataTable.isDataTable(tableId)) {
                        $(tableId).DataTable().destroy();
                    }

                    tbody.innerHTML = '';
                    const rows = dataJSON.data[kelompok] || [];

                    if (rows.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">Belum ada transaksi di kelompok ${kelompok}.</td></tr>`;
                        divSummary.style.setProperty("display", "none", "important");
                    } else {
                        let no = 1;
                        rows.forEach(row => {
                            const badgeColor = row.tipe_arus === 'Masuk' ? 'bg-success' : 'bg-danger';
                            const iconArus = row.tipe_arus === 'Masuk' ? 'bx-down-arrow-circle' : 'bx-up-arrow-circle';
                            const btnBukti = row.bukti_transaksi ?
                                `<button class="btn btn-sm btn-info me-1" onclick="lihatBukti('${row.bukti_transaksi}')" title="Lihat Bukti"><i class="bx bx-file"></i></button>` : '';

                            let badgeNeraca = 'bg-label-secondary text-muted';
                            if (row.tipe_neraca === 'debit') badgeNeraca = 'bg-label-primary';
                            else if (row.tipe_neraca === 'kredit') badgeNeraca = 'bg-label-warning';

                            const txtNeraca = row.tipe_neraca ? row.tipe_neraca.toUpperCase() : '-';

                            tbody.innerHTML += `
                                <tr>
                                    <td>${no++}</td>
                                    <td><code>${row.kode_referensi}</code></td> <!-- Tambahan Baru -->
                                    <td>${row.tanggal}</td>
                                    <td><span class="text-primary fw-semibold">${row.kode_akun}</span> - ${row.nama_akun}</td>
                                    <td>${row.keterangan}</td>
                                    <td><span class="badge ${badgeColor}" data-arus="${row.tipe_arus}"><i class="bx ${iconArus} me-1"></i>${row.tipe_arus}</span></td>
                                    <td><span class="badge ${badgeNeraca} fw-bold">${txtNeraca}</span></td>
                                    <td class="fw-bold"><span class="nominal-val" data-nilai="${row.nominal_raw}">Rp ${row.nominal}</span></td>
                                    <td class="text-center text-nowrap">
                                        ${btnBukti}
                                        <button class="btn btn-sm btn-warning me-1" 
                                            data-id="${row.id_transaksi}"
                                            data-tanggal="${row.tanggal_raw}"
                                            data-kategori="${row.id_kategori}"
                                            data-arus="${row.tipe_arus}"
                                            data-neraca="${row.tipe_neraca}" 
                                            data-nominal="${row.nominal_raw}"
                                            data-ket="${row.keterangan}"
                                            data-bukti="${row.bukti_transaksi}" 
                                            onclick="bukaModalEdit(this)"><i class="bx bx-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" onclick="hapusTransaksi(${row.id_transaksi})"><i class="bx bx-trash"></i></button>
                                    </td>
                                </tr>
                            `;
                        });

                        dataTablesInstances[idTab] = $(tableId).DataTable({
                            pageLength: 10,
                            responsive: true,
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                            },
                            drawCallback: function() {
                                let totalMasuk = 0;
                                let totalKeluar = 0;

                                let api = this.api();
                                api.rows({
                                    page: 'current'
                                }).nodes().each(function(node) {
                                    let badgeArus = node.querySelector('.badge').getAttribute('data-arus');
                                    let spanNominal = node.querySelector('.nominal-val').getAttribute('data-nilai');

                                    let val = parseFloat(spanNominal);
                                    if (!isNaN(val)) {
                                        if (badgeArus === 'Masuk') totalMasuk += val;
                                        if (badgeArus === 'Keluar') totalKeluar += val;
                                    }
                                });

                                const formatRp = (angka) => new Intl.NumberFormat('id-ID', {
                                    minimumFractionDigits: 0
                                }).format(angka);
                                cellTotal.innerHTML = `
                                    <span class="text-success"><i class="bx bx-down-arrow-circle"></i> Masuk: Rp ${formatRp(totalMasuk)}</span>
                                    <span class="text-danger"><i class="bx bx-up-arrow-circle"></i> Keluar: Rp ${formatRp(totalKeluar)}</span>
                                `;
                                divSummary.style.setProperty("display", "flex", "important");
                            }
                        });
                    }
                }
            }
        } catch (error) {
            console.error("Gagal mengambil data:", error);
            showToast("Gagal memuat data tabel transaksi.", "error");
        }
    }

    document.addEventListener("input", e => {
        if (e.target.classList.contains("nominal-mask")) {
            let input = e.target;
            let cursorPos = input.selectionStart;
            let prevLength = input.value.length;

            let val = input.value.replace(/\D/g, "");
            let formatted = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            input.value = formatted;

            let diff = formatted.length - prevLength;
            input.setSelectionRange(cursorPos + diff, cursorPos + diff);
        }
    });

    // --- UPDATE EVENT SUBMIT ADD (AUTO DOUBLE-ENTRY) ---
    document.getElementById('formTambahTransaksi').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpan');
        btn.innerHTML = 'Menyimpan...';
        btn.disabled = true;

        const formData = new FormData(this);
        formData.append('action', 'add');

        let nominalCleanAdd = formData.get("nominal").replace(/\./g, "");
        formData.set("nominal", nominalCleanAdd);

        formData.delete('bukti_transaksi');
        if (processedFileAdd) {
            formData.append('bukti_transaksi', processedFileAdd);
        }

        try {
            const res = await fetch('app/controller/act-kelola-transaksi.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.status === 'success') {
                this.reset();
                $('#add_kategori').val('').trigger('change');
                $('#add_lawan').val('').trigger('change');

                processedFileAdd = null;
                document.getElementById('preview_bukti_add').innerHTML = '';
                bootstrap.Modal.getInstance(document.getElementById('modalTambahTransaksi')).hide();
                showToast(data.msg || 'Transaksi berhasil disimpan.', 'success');
                loadDataTransaksi();
            } else {
                showToast(data.msg, 'error');
            }
        } catch (error) {
            showToast('Terjadi kesalahan sistem.', 'error');
        } finally {
            btn.innerHTML = '<i class="bx bx-save me-1"></i> Simpan Transaksi';
            btn.disabled = false;
        }
    });

    function bukaModalEdit(btn) {
        document.getElementById('input_bukti_edit').value = '';
        processedFileEdit = null;

        document.getElementById('edit_id_transaksi').value = btn.getAttribute('data-id');
        document.getElementById('edit_tanggal').value = btn.getAttribute('data-tanggal');
        $('#edit_kategori').val(btn.getAttribute('data-kategori')).trigger('change');
        document.getElementById('edit_arus').value = btn.getAttribute('data-arus');
        document.getElementById('edit_neraca').value = btn.getAttribute('data-neraca');

        let rawNominal = btn.getAttribute('data-nominal').toString();
        let angkaBulat = rawNominal.split(".")[0].replace(/\D/g, "");
        document.getElementById('edit_nominal').value = angkaBulat.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        document.getElementById('edit_keterangan').value = btn.getAttribute('data-ket');

        const bukti = btn.getAttribute('data-bukti');
        const previewDiv = document.getElementById('preview_bukti_edit');
        if (bukti && bukti !== 'null' && bukti !== '') {
            previewDiv.innerHTML = `
                <div class="p-2 border rounded bg-light mb-2">
                    <a href="app/uploads/bukti_transaksi/${bukti}" target="_blank" class="text-primary fw-bold">
                        <i class="bx bx-link-external"></i> Lihat Bukti Saat Ini
                    </a>
                </div>`;
        } else {
            previewDiv.innerHTML = `<span class="text-muted">Belum ada bukti yang diupload.</span>`;
        }

        const modal = new bootstrap.Modal(document.getElementById('modalEditTransaksi'));
        modal.show();
    }

    function lihatBukti(fileName) {
        if (fileName) window.open(`app/uploads/bukti_transaksi/${fileName}`, '_blank');
    }

    document.getElementById('formEditTransaksi').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnUpdate');
        btn.innerHTML = 'Memperbarui...';
        btn.disabled = true;

        const formData = new FormData(this);
        formData.append('action', 'edit');

        let nominalCleanEdit = formData.get("nominal").replace(/\./g, "");
        formData.set("nominal", nominalCleanEdit);

        formData.delete('bukti_transaksi');
        if (processedFileEdit) {
            formData.append('bukti_transaksi', processedFileEdit);
        }

        try {
            const res = await fetch('app/controller/act-kelola-transaksi.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.status === 'success') {
                this.reset();
                processedFileEdit = null;
                bootstrap.Modal.getInstance(document.getElementById('modalEditTransaksi')).hide();
                showToast(data.msg || 'Transaksi berhasil diperbarui.', 'success');
                loadDataTransaksi();
            } else {
                showToast(data.msg, 'error');
            }
        } catch (error) {
            showToast('Terjadi kesalahan sistem saat memperbarui data.', 'error');
        } finally {
            btn.innerHTML = '<i class="bx bx-check me-1"></i> Update Baris Ini';
            btn.disabled = false;
        }
    });

    async function hapusTransaksi(id) {
        // Pesan peringatan diperbarui
        if (await confirmDialog('Hapus transaksi ini? Sistem akan otomatis menghapus jurnal pasangannya (Kas/Bank) agar neraca tetap seimbang.')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            try {
                const res = await fetch('app/controller/act-kelola-transaksi.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    showToast(data.msg || 'Transaksi berhasil dihapus.', 'success');
                    loadDataTransaksi(); // Refresh tabel
                } else {
                    showToast(data.msg || 'Gagal menghapus transaksi.', 'error');
                }
            } catch (error) {
                showToast('Gagal menghapus transaksi.', 'error');
            }
        }
    }

    document.addEventListener("DOMContentLoaded", loadDataTransaksi);
</script>
