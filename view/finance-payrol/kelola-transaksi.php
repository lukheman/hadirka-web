<?php
require_once __DIR__ . '/../../app/config/koneksi.php';

// Ambil data kategori untuk modal agar dinamis
$qKategori = mysqli_query($koneksi, "SELECT * FROM tb_kategori_keuangan ORDER BY kelompok ASC, kode_akun ASC");
$kategoriGroups = [];
while($row = mysqli_fetch_assoc($qKategori)){
    $kategoriGroups[$row['kelompok']][] = $row;
}
?>

<!-- Tambahkan CSS DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .row {
        margin-bottom: 0.5rem;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header & Action Buttons -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <h4 class="mb-1 fw-bold">Kelola Transaksi Keuangan</h4>
            <p class="text-muted mb-0">Catat dan pantau transaksi berdasarkan kelompok akun</p>
        </div>
        
        <div class="col-md-6 text-md-end">
            <div class="d-flex flex-column flex-sm-row justify-content-md-end align-items-sm-center gap-2">
                <!-- Filter Dropdown AJAX -->
                <select id="periodeDropdown" class="form-select form-select-sm fw-bold cursor-pointer bg-white" onchange="loadDataTransaksi()" style="width: auto; min-width: 200px;">
                    <option value="semua">-- Semua Periode --</option>
                    <?php
                    $qPeriode = mysqli_query($koneksi, "SELECT * FROM tb_priode WHERE type_priode = 'slip gaji' ORDER BY tgl_mulai DESC");
                    if (mysqli_num_rows($qPeriode) > 0) {
                        $isFirst = true; // Tambahkan penanda ini
                        while ($rowP = mysqli_fetch_assoc($qPeriode)) {
                            $tgl_str = date('d/m/Y', strtotime($rowP['tgl_mulai'])) . " - " . date('d/m/Y', strtotime($rowP['tgl_selesai']));
                            
                            // Jika iterasi pertama (terbaru), beri atribut selected
                            $selected = $isFirst ? "selected" : "";
                            
                            echo "<option value='{$rowP['id_priode']}' $selected>{$tgl_str}</option>";
                            $isFirst = false; // Matikan penanda setelah looping pertama
                        }
                    }
                    ?>
                </select>
                <!-- Tombol Tambah -->
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
            <!-- Nav Tabs -->
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
                
                <!-- Tab Template (Diulang 5 kali oleh PHP) -->
                <?php 
                $tabs = [
                    'pendapatan' => 'Pendapatan', 
                    'beban' => 'Beban', 
                    'aset' => 'Aset', 
                    'kewajiban' => 'Kewajiban', 
                    'ekuitas' => 'Ekuitas'
                ];
                $isFirst = true;
                foreach($tabs as $id => $namaGroup): 
                ?>
                <div class="tab-pane fade <?= $isFirst ? 'show active' : '' ?>" id="tab-<?= $id ?>" role="tabpanel">
                    
                    <!-- REKAP TOTAL DI ATAS TABEL -->
                    <div class="mb-3 p-3 bg-light rounded d-flex justify-content-between align-items-center" id="summary-<?= $id ?>" style="display: none !important;">
                        <span class="fw-bold text-muted"><i class="bx bx-bar-chart-alt-2 me-1"></i> Rekapitulasi (Halaman Aktif)</span>
                        <div id="total-<?= $id ?>" class="d-flex gap-4 fw-bold">
                            <!-- Rekap dimasukkan oleh JS -->
                        </div>
                    </div>

                    <div class="table-responsive">
                        <!-- Tambahkan Class MyTable agar terdeteksi oleh DataTables -->
                        <table class="table table-hover align-middle w-100 MyTable" id="table-<?= $id ?>">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="25%">Kategori Akun</th>
                                    <th width="25%">Keterangan</th>
                                    <th width="10%">Arus Kas</th>
                                    <th width="15%">Nominal</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-<?= $id ?>">
                                <tr><td colspan="7" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>
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

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bx bx-plus-circle text-primary me-2"></i> Input Transaksi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahTransaksi">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" name="tanggal_transaksi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori Akun</label>
                            <select class="form-select" name="id_kategori" required>
                                <option value="">-- Pilih Kategori Akun --</option>
                                <?php foreach($kategoriGroups as $kelompok => $kategoris): ?>
                                    <optgroup label="Kelompok: <?= strtoupper($kelompok) ?>">
                                        <?php foreach($kategoris as $k): ?>
                                            <option value="<?= $k['id_kategori'] ?>"><?= $k['kode_akun'] ?> - <?= $k['nama_akun'] ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Arus Kas</label>
                            <select class="form-select" name="tipe_arus" required>
                                <option value="Masuk">Uang Masuk (Kas Bertambah)</option>
                                <option value="Keluar">Uang Keluar (Kas Berkurang)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="number" class="form-control" name="nominal" placeholder="Contoh: 5000000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Contoh: Pembayaran Invoice INV001 (Opsional)"></textarea>
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

<!-- Modal Edit Transaksi -->
<div class="modal fade" id="modalEditTransaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bx bx-edit text-warning me-2"></i> Edit Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditTransaksi">
                <input type="hidden" name="id_transaksi" id="edit_id_transaksi">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" name="tanggal_transaksi" id="edit_tanggal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori Akun</label>
                            <select class="form-select" name="id_kategori" id="edit_kategori" required>
                                <option value="">-- Pilih Kategori Akun --</option>
                                <?php foreach($kategoriGroups as $kelompok => $kategoris): ?>
                                    <optgroup label="Kelompok: <?= strtoupper($kelompok) ?>">
                                        <?php foreach($kategoris as $k): ?>
                                            <option value="<?= $k['id_kategori'] ?>"><?= $k['kode_akun'] ?> - <?= $k['nama_akun'] ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Arus Kas</label>
                            <select class="form-select" name="tipe_arus" id="edit_arus" required>
                                <option value="Masuk">Uang Masuk (Kas Bertambah)</option>
                                <option value="Keluar">Uang Keluar (Kas Berkurang)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="number" class="form-control" name="nominal" id="edit_nominal" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="2" placeholder="(Opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnUpdate"><i class="bx bx-check me-1"></i> Update Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sertakan jQuery dan DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Inisialisasi variabel untuk menyimpan instance DataTables
    let dataTablesInstances = {};

    async function loadDataTransaksi() {
        const idPriode = document.getElementById("periodeDropdown").value;
        const url = `app/controller/get-data/get-kelola-transaksi.php?id_priode=${idPriode}`;

        try {
            const res = await fetch(url);
            const dataJSON = await res.json();
            
            if(dataJSON.status === 'success') {
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
                    
                    // Destroy DataTable jika sudah pernah diinisialisasi
                    if ($.fn.DataTable.isDataTable(tableId)) {
                        $(tableId).DataTable().destroy();
                    }

                    tbody.innerHTML = '';
                    const rows = dataJSON.data[kelompok] || [];
                    
                    if(rows.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi di kelompok ${kelompok}.</td></tr>`;
                        divSummary.style.setProperty("display", "none", "important"); 
                    } else {
                        let no = 1;
                        rows.forEach(row => {
                            const badgeColor = row.tipe_arus === 'Masuk' ? 'bg-success' : 'bg-danger';
                            const iconArus = row.tipe_arus === 'Masuk' ? 'bx-down-arrow-circle' : 'bx-up-arrow-circle';
                            
                            // Penting: Gunakan tipe_arus dan nominal_raw dari Javascript objek langsung, 
                            // jangan sembunyikan di attribute HTML yang rawan error
                            tbody.innerHTML += `
                                <tr>
                                    <td>${no++}</td>
                                    <td>${row.tanggal}</td>
                                    <td><span class="text-primary fw-semibold">${row.kode_akun}</span> - ${row.nama_akun}</td>
                                    <td>${row.keterangan}</td>
                                    <td><span class="badge ${badgeColor}" data-arus="${row.tipe_arus}"><i class="bx ${iconArus} me-1"></i>${row.tipe_arus}</span></td>
                                    <td class="fw-bold"><span class="nominal-val" data-nilai="${row.nominal_raw}">Rp ${row.nominal}</span></td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-sm btn-warning me-1" 
                                            data-id="${row.id_transaksi}"
                                            data-tanggal="${row.tanggal_raw}"
                                            data-kategori="${row.id_kategori}"
                                            data-arus="${row.tipe_arus}"
                                            data-nominal="${row.nominal_raw}"
                                            data-ket="${row.keterangan}"
                                            onclick="bukaModalEdit(this)"><i class="bx bx-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" onclick="hapusTransaksi(${row.id_transaksi})"><i class="bx bx-trash"></i></button>
                                    </td>
                                </tr>
                            `;
                        });

                        // Re-initialize DataTable dengan Callback Aman
                        dataTablesInstances[idTab] = $(tableId).DataTable({
                            pageLength: 10,
                            responsive: true,
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                            },
                            drawCallback: function () {
                                let totalMasuk = 0;
                                let totalKeluar = 0;
                                
                                // Metode paling aman: Cari DOM span dengan class khusus
                                let api = this.api();
                                api.rows({ page: 'current' }).nodes().each(function(node) {
                                    let badgeArus = node.querySelector('.badge').getAttribute('data-arus');
                                    let spanNominal = node.querySelector('.nominal-val').getAttribute('data-nilai');
                                    
                                    let val = parseFloat(spanNominal);
                                    if(!isNaN(val)) {
                                        if(badgeArus === 'Masuk') totalMasuk += val;
                                        if(badgeArus === 'Keluar') totalKeluar += val;
                                    }
                                });

                                const formatRp = (angka) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(angka);
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
            alert("Gagal memuat data tabel transaksi.");
        }
    }

    // Aksi Tambah
    document.getElementById('formTambahTransaksi').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpan');
        btn.innerHTML = 'Menyimpan...'; btn.disabled = true;

        const formData = new FormData(this);
        formData.append('action', 'add');

        try {
            const res = await fetch('app/controller/act-kelola-transaksi.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if(data.status === 'success') {
                this.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalTambahTransaksi')).hide();
                loadDataTransaksi(); // Refresh tabel
            } else {
                alert(data.msg);
            }
        } catch (error) {
            alert('Terjadi kesalahan sistem.');
        } finally {
            btn.innerHTML = '<i class="bx bx-save me-1"></i> Simpan Transaksi'; btn.disabled = false;
        }
    });

    // Menampilkan Modal Edit
    function bukaModalEdit(btn) {
        document.getElementById('edit_id_transaksi').value = btn.getAttribute('data-id');
        document.getElementById('edit_tanggal').value = btn.getAttribute('data-tanggal');
        document.getElementById('edit_kategori').value = btn.getAttribute('data-kategori');
        document.getElementById('edit_arus').value = btn.getAttribute('data-arus');
        document.getElementById('edit_nominal').value = btn.getAttribute('data-nominal');
        document.getElementById('edit_keterangan').value = btn.getAttribute('data-ket');

        const modal = new bootstrap.Modal(document.getElementById('modalEditTransaksi'));
        modal.show();
    }

    // Aksi Edit
    document.getElementById('formEditTransaksi').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnUpdate');
        btn.innerHTML = 'Memperbarui...'; btn.disabled = true;

        const formData = new FormData(this);
        formData.append('action', 'edit');

        try {
            const res = await fetch('app/controller/act-kelola-transaksi.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if(data.status === 'success') {
                this.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalEditTransaksi')).hide();
                loadDataTransaksi(); // Refresh tabel
            } else {
                alert(data.msg);
            }
        } catch (error) {
            alert('Terjadi kesalahan sistem saat memperbarui data.');
        } finally {
            btn.innerHTML = '<i class="bx bx-check me-1"></i> Update Transaksi'; btn.disabled = false;
        }
    });

    // Aksi Hapus
    async function hapusTransaksi(id) {
        if(confirm('Yakin ingin menghapus transaksi ini?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            try {
                const res = await fetch('app/controller/act-kelola-transaksi.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if(data.status === 'success') {
                    loadDataTransaksi(); // Refresh tabel otomatis
                } else {
                    alert(data.msg);
                }
            } catch (error) {
                alert('Gagal menghapus transaksi.');
            }
        }
    }

    // Auto Load
    document.addEventListener("DOMContentLoaded", loadDataTransaksi);
</script>