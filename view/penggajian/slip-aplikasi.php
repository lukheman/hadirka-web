<?php
$bulanIni = date('Y-m');
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Payroll /</span> Slip Gaji Aplikasi
    </h4>

    <div class="alert alert-info py-2" role="alert">
        <i class="bx bx-mobile-alt me-1"></i>
        Slip pada halaman ini tampil di <strong>aplikasi absen mobile</strong> milik karyawan (tab Slip Gaji).
        Untuk cetak slip payroll lengkap per periode, gunakan menu <em>Slip Gaji Karyawan</em>.
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Periode (Bulan)</label>
                    <input type="month" id="filterBulanSlip" class="form-control" value="<?= $bulanIni ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Karyawan</label>
                    <select id="filterKaryawanSlip" class="form-select">
                        <option value="">Semua Karyawan</option>
                        <?php
                        $karyawans = data_karyawan('nama_lengkap');
                        while ($k = $karyawans->fetch()) :
                        ?>
                            <option value="<?= $k['id_karyawan'] ?>"><?= htmlspecialchars($k['nik_karyawan'] . ' - ' . $k['nama_lengkap']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSlip">
                        <i class="bx bx-plus me-1"></i> Terbitkan Slip
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Slip Gaji Terbit di Aplikasi</h5>
            <span class="badge bg-label-primary" id="jumlahSlip">0 data</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover border-top tabelSlip">
                <thead>
                    <tr class="align-middle text-nowrap">
                        <th class="text-center">No</th>
                        <th>Dibuat</th>
                        <th>Karyawan</th>
                        <th>Periode</th>
                        <th class="text-end">Gaji Pokok</th>
                        <th class="text-end">Tunjangan</th>
                        <th class="text-end">Potongan</th>
                        <th class="text-end">Total Diterima</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodySlip" class="table-border-bottom-0">
                    <tr>
                        <td colspan="9" class="text-center py-4">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSlip" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formSlip" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Terbitkan Slip Gaji ke Aplikasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Karyawan</label>
                    <select name="id_karyawan" id="slipKaryawan" class="form-select" required style="width: 100%;">
                        <option value="">- Pilih Karyawan -</option>
                        <?php
                        $karyawans2 = data_karyawan('nama_lengkap');
                        while ($k = $karyawans2->fetch()) :
                        ?>
                            <option value="<?= $k['id_karyawan'] ?>"><?= htmlspecialchars($k['nik_karyawan'] . ' - ' . $k['nama_lengkap']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Periode (Bulan)</label>
                    <input type="month" id="slipPeriode" class="form-control" value="<?= $bulanIni ?>" required>
                    <input type="hidden" name="periode" id="slipPeriodeLabel">
                    <small class="text-muted" id="slipPeriodePreview"></small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Gaji Pokok</label>
                        <input type="number" name="gajiPokok" id="slipPokok" class="form-control" min="0" step="1000" value="0" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tunjangan</label>
                        <input type="number" name="tunjangan" id="slipTunjangan" class="form-control" min="0" step="1000" value="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Potongan</label>
                        <input type="number" name="potongan" id="slipPotongan" class="form-control" min="0" step="1000" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Total Diterima</label>
                        <input type="text" id="slipTotal" class="form-control fw-bold text-success bg-light" value="Rp 0" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan <small class="text-muted">(opsional)</small></label>
                    <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: THR / bonus proyek"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary shadow">Terbitkan Slip</button>
            </div>
        </form>
    </div>
</div>

<script>
    const api_act_slip = 'app/controller/act-slip-aplikasi.php';
    const api_get_slip = 'app/controller/get-data/get-slip-aplikasi.php';

    const bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function labelPeriode(ym) {
        if (!ym) return '';
        const [y, m] = ym.split('-');
        const idx = parseInt(m, 10) - 1;
        if (!bulanIndo[idx]) return '';
        return bulanIndo[idx] + ' ' + y;
    }

    function rpSlip(angka) {
        return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
    }

    // ===================== TOTAL OTOMATIS =====================
    function hitungTotalSlip() {
        const pokok = parseFloat(document.getElementById('slipPokok').value) || 0;
        const tunjangan = parseFloat(document.getElementById('slipTunjangan').value) || 0;
        const potongan = parseFloat(document.getElementById('slipPotongan').value) || 0;
        document.getElementById('slipTotal').value = rpSlip(pokok + tunjangan - potongan);
        document.getElementById('slipPeriodeLabel').value = labelPeriode(document.getElementById('slipPeriode').value);
        document.getElementById('slipPeriodePreview').innerText = labelPeriode(document.getElementById('slipPeriode').value)
            ? 'Tersimpan sebagai periode: ' + labelPeriode(document.getElementById('slipPeriode').value)
            : '';
    }

    ['slipPokok', 'slipTunjangan', 'slipPotongan', 'slipPeriode'].forEach(id => {
        document.getElementById(id).addEventListener('input', hitungTotalSlip);
        document.getElementById(id).addEventListener('change', hitungTotalSlip);
    });

    // ===================== TABEL =====================
    async function loadTabelSlip() {
        const params = new URLSearchParams({
            bulan: document.getElementById('filterBulanSlip').value,
            karyawan: document.getElementById('filterKaryawanSlip').value,
            _: new Date().getTime()
        });

        const res = await fetch(`${api_get_slip}?${params.toString()}`);
        const html = await res.text();

        if ($.fn.DataTable.isDataTable('.tabelSlip')) {
            $('.tabelSlip').DataTable().destroy();
        }

        document.getElementById('tbodySlip').innerHTML = html;
        const rows = document.querySelectorAll('#tbodySlip tr').length;
        const kosong = document.getElementById('tbodySlip').querySelector('.text-muted');
        document.getElementById('jumlahSlip').innerText = (rows === 1 && kosong) ? '0 data' : rows + ' data';

        $('.tabelSlip').DataTable({
            responsive: true,
            pageLength: 10,
            order: [],
            columnDefs: [{
                orderable: false,
                targets: [8]
            }]
        });
    }

    async function hapusSlip(id) {
        if (await confirmDialog("Hapus slip gaji ini dari aplikasi karyawan?")) {
            try {
                const res = await fetch(`${api_act_slip}?action=delete&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    showToast(result.msg || 'Slip gaji dihapus.', 'success');
                    loadTabelSlip();
                } else {
                    showToast(result.msg || 'Gagal menghapus.', 'error');
                }
            } catch (e) {
                showToast('Terjadi kesalahan saat menghapus data.', 'error');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if ($.fn.select2) {
            $('#slipKaryawan').select2({
                dropdownParent: $('#modalSlip'),
                placeholder: '- Pilih Karyawan -',
                width: '100%'
            });
        }

        hitungTotalSlip();
        loadTabelSlip();

        document.getElementById('filterBulanSlip').addEventListener('change', loadTabelSlip);
        document.getElementById('filterKaryawanSlip').addEventListener('change', loadTabelSlip);

        document.getElementById('formSlip').addEventListener('submit', async function(e) {
            e.preventDefault();
            hitungTotalSlip();

            if (!document.getElementById('slipPeriodeLabel').value) {
                showToast('Pilih periode bulan terlebih dahulu.', 'error');
                return;
            }

            const fd = new FormData(this);
            fd.append('action', 'save');

            try {
                const res = await fetch(api_act_slip, {
                    method: 'POST',
                    body: fd
                });
                const result = await res.json();

                if (result.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('modalSlip')).hide();
                    this.reset();
                    $('#slipKaryawan').val(null).trigger('change');
                    document.getElementById('slipPeriode').value = '<?= $bulanIni ?>';
                    document.getElementById('slipPokok').value = 0;
                    document.getElementById('slipTunjangan').value = 0;
                    document.getElementById('slipPotongan').value = 0;
                    hitungTotalSlip();
                    showToast(result.msg || 'Slip gaji diterbitkan.', 'success');
                    loadTabelSlip();
                } else {
                    showToast(result.msg || 'Gagal menerbitkan slip.', 'error');
                }
            } catch (err) {
                showToast('Terjadi kesalahan sistem.', 'error');
            }
        });
    });
</script>
