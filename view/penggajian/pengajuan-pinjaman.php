<?php
// Default filter: bulan berjalan
$bulanIni = date('Y-m');
?>
<style>
    .preview-thumbnail {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background: #fff;
    }

    .preview-thumbnail:hover {
        transform: scale(1.1);
        border-color: #1D5C96 !important;
        box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4) !important;
    }

    #modalPreviewPinjaman .modal-content {
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Payroll /</span> Pengajuan Pinjaman
    </h4>

    <!-- Saklar Akses Pengajuan dari Aplikasi -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-1"><i class="bx bx-mobile-alt me-1"></i> Akses Pengajuan dari Aplikasi</h5>
                <small class="text-muted">Saat dibuka, karyawan dapat mengajukan pinjaman dana lewat aplikasi absen mobile.</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span id="labelAkses" class="badge bg-label-secondary">Memuat...</span>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="switchAkses" style="width: 3em; height: 1.5em;" title="Buka/tutup akses pengajuan">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <input type="month" id="filterBulan" class="form-control" value="<?= $bulanIni ?>">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" id="btnCari">
                        <i class="bx bx-search me-1"></i> Tampilkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold">Daftar Pengajuan Pinjaman <span class="badge bg-label-primary" id="jumlahPinjaman">0</span></h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-success" id="btnAccMassal" disabled>
                    <i class="bx bx-check-double me-1"></i> ACC Massal
                </button>
                <button class="btn btn-sm btn-info" id="btnCairMassal" disabled>
                    <i class="bx bx-transfer me-1"></i> Cairkan Massal
                </button>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover border-top" id="tabelPinjaman">
                <thead>
                    <tr class="align-middle text-nowrap">
                        <th class="text-center" style="width: 36px;"><input type="checkbox" id="checkAll" title="Pilih semua baris yang bisa diproses"></th>
                        <th>No</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Nominal</th>
                        <th>Rekening</th>
                        <th>Status</th>
                        <th>Keterangan Admin</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyPinjaman" class="table-border-bottom-0"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetailPinjaman" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Detail Pengajuan Pinjaman</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="detailBodyPinjaman"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Proses (Setujui / Tolak) -->
<div class="modal fade" id="modalProsesPinjaman" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formProsesPinjaman" class="modal-content">
            <input type="hidden" name="id_pengajuan">
            <input type="hidden" name="status_pinjaman">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="prosesTitlePinjaman">Proses Pengajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    <span id="prosesInfoPinjaman" class="fw-bold"></span>
                </div>
                <label class="fw-bold mb-1">Keterangan / Pesan untuk Karyawan</label>
                <textarea name="keterangan_admin" class="form-control" rows="3" placeholder="Opsional (wajib diisi jika ditolak)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pencairan -->
<div class="modal fade" id="modalCairPinjaman" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formCairPinjaman" class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">Tandai Pencairan Dana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    <span id="infoCairPinjaman" class="fw-bold"></span>
                </div>
                <label class="fw-bold mb-1">Tanggal Transfer / Bayar</label>
                <input type="date" name="tgl_bayar_pinjaman" class="form-control" value="<?= date('Y-m-d') ?>" required>
                <small class="text-muted">Data akan masuk ke tabel pinjaman dan langsung terpotong pada penggajian.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info">Proses Pencairan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Preview Foto -->
<div class="modal fade" id="modalPreviewPinjaman" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                <img src="" id="imgPreviewPinjaman" class="img-fluid rounded shadow-lg border border-3 border-white" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<script>
    const api_get_pinjaman = 'app/controller/get-data/get-pengajuan-pinjaman.php';
    const api_act_pinjaman = 'app/controller/act-pengajuan-pinjaman.php';

    let dataPinjaman = [];
    let modeCair = 'single'; // single | batch
    let idCair = null;

    const badgePinjaman = {
        pending: ['warning', 'Pending'],
        disetujui: ['primary', 'Disetujui (Antre TF)'],
        dicairkan: ['success', 'Dicairkan'],
        ditolak: ['danger', 'Ditolak'],
        lunas: ['info', 'Lunas']
    };

    function rp(angka) {
        return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
    }

    function esc(t) {
        return String(t == null ? '' : t)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // ===================== SAKLAR AKSES =====================
    async function loadAkses() {
        try {
            const res = await fetch(api_act_pinjaman + '?action=get-status-akses');
            const json = await res.json();
            if (json.status === 'success') {
                const buka = json.nilai === '1';
                document.getElementById('switchAkses').checked = buka;
                document.getElementById('labelAkses').className = 'badge ' + (buka ? 'bg-label-success' : 'bg-label-danger');
                document.getElementById('labelAkses').innerText = buka ? 'TERBUKA untuk karyawan' : 'DITUTUP';
            }
        } catch (e) {
            console.error('Gagal memuat status akses', e);
        }
    }

    document.getElementById('switchAkses').addEventListener('change', async function() {
        const nilai = this.checked ? '1' : '0';
        try {
            const res = await fetch(api_act_pinjaman, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=toggle-akses&nilai=' + nilai
            });
            const json = await res.json();
            if (json.status === 'success') {
                showToast(json.msg, 'success');
                loadAkses();
            } else {
                showToast(json.msg || 'Gagal mengubah akses.', 'error');
                loadAkses();
            }
        } catch (e) {
            showToast('Terjadi kesalahan sistem.', 'error');
            loadAkses();
        }
    });

    // ===================== TABEL =====================
    async function loadTablePinjaman() {
        const periode = getPeriodeBulan('#filterBulan');
        if (!periode.awal) {
            showToast('Pilih bulan terlebih dahulu.', 'info');
            return;
        }

        const body = new URLSearchParams({
            startDate: periode.awal,
            endDate: periode.akhir
        });

        try {
            const res = await fetch(api_get_pinjaman, {
                method: 'POST',
                body: body
            });
            const json = await res.json();
            dataPinjaman = json.data || [];
            renderTablePinjaman();
        } catch (e) {
            showToast('Gagal memuat data pengajuan.', 'error');
        }
    }

    function renderTablePinjaman() {
        const tbody = document.getElementById('tbodyPinjaman');
        document.getElementById('jumlahPinjaman').innerText = dataPinjaman.length + ' data';

        // PENTING: Jangan taruh baris colspan di tbody sebelum init DataTable.
        // Baris <td colspan="9"> menyebabkan baris itu terhitung 1 kolom vs 9 kolom
        // thead -> Warning "Incorrect column count" (datatables tn/18).
        // Saat kosong, biarkan tbody kosong (DataTables menampilkan "No data" bawaan).
        if (dataPinjaman.length === 0) {
            tbody.innerHTML = '';
        } else {
            tbody.innerHTML = dataPinjaman.map(d => {
                const badge = badgePinjaman[d.status_pinjaman] || ['secondary', d.status_pinjaman];
                const bisaPilih = (d.status_pinjaman === 'pending' || d.status_pinjaman === 'disetujui');

                // Kolom rekening
                let rekening = '<span class="text-muted">-</span>';
                if (d.no_rek || d.nama_bank) {
                    rekening = `<div class="d-flex flex-column">
                        <span class="fw-semibold">${esc(d.nama_bank || '-')}</span>
                        <small class="text-muted">${esc(d.no_rek || '-')}<br>${esc(d.atas_nama_rek || '')}</small>
                    </div>`;
                }

                // Tombol aksi
                let aksi = `<button class="btn btn-sm btn-outline-primary" onclick="openDetailPinjaman(${d.id_pengajuan_pinjaman})" title="Detail"><i class="bx bx-show"></i></button>`;
                if (d.status_pinjaman === 'pending') {
                    aksi += ` <button class="btn btn-sm btn-success" onclick="openProsesPinjaman(${d.id_pengajuan_pinjaman}, 'disetujui')" title="Setujui"><i class="bx bx-check"></i></button>`;
                    aksi += ` <button class="btn btn-sm btn-warning" onclick="openProsesPinjaman(${d.id_pengajuan_pinjaman}, 'ditolak')" title="Tolak"><i class="bx bx-x"></i></button>`;
                }
                if (d.status_pinjaman === 'disetujui') {
                    aksi += ` <button class="btn btn-sm btn-info" onclick="openCairPinjaman(${d.id_pengajuan_pinjaman})" title="Cairkan / TF"><i class="bx bx-transfer"></i></button>`;
                }
                if (d.status_pinjaman === 'ditolak') {
                    aksi += ` <button class="btn btn-sm btn-secondary" onclick="setPendingPinjaman(${d.id_pengajuan_pinjaman})" title="Kembalikan ke Pending"><i class="bx bx-time"></i></button>`;
                }
                aksi += ` <button class="btn btn-sm btn-danger" onclick="hapusPinjaman(${d.id_pengajuan_pinjaman})" title="Hapus"><i class="bx bx-trash"></i></button>`;

                return `<tr>
                    <td class="text-center">${bisaPilih ? `<input type="checkbox" class="check-pinjaman" data-id="${d.id_pengajuan_pinjaman}" data-status="${d.status_pinjaman}">` : ''}</td>
                    <td>${d.no}</td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">${esc(d.nama_lengkap)}</span>
                            <small class="text-muted">${esc(d.nik_karyawan)} &bull; ${esc(d.jabatan || '-')} &bull; ${esc(d.site || '-')}</small>
                        </div>
                    </td>
                    <td>${d.tgl_pengajuan ? new Date(d.tgl_pengajuan).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'}</td>
                    <td class="fw-semibold text-dark">${rp(d.nominal_pinjaman)}</td>
                    <td>${rekening}</td>
                    <td><span class="badge bg-label-${badge[0]}">${badge[1]}</span></td>
                    <td><small class="text-muted text-wrap d-inline-block" style="max-width: 220px; white-space: normal;">${esc(d.keterangan_admin || '-')}</small></td>
                    <td class="text-center text-nowrap">${aksi}</td>
                </tr>`;
            }).join('');
        }

        if ($.fn.DataTable.isDataTable('#tabelPinjaman')) {
            $('#tabelPinjaman').DataTable().destroy();
        }

        $('#tabelPinjaman').DataTable({
            responsive: true,
            pageLength: 10,
            order: [],
            columnDefs: [{
                orderable: false,
                targets: [0, 8]
            }]
        });

        updateTombolBatch();
    }

    // ===================== PILIH BATCH =====================
    function getCheckedPinjaman(status) {
        return Array.from(document.querySelectorAll('.check-pinjaman'))
            .filter(c => c.checked && (!status || c.dataset.status === status))
            .map(c => c.dataset.id)
            .filter((v, i, a) => a.indexOf(v) === i);
    }

    function updateTombolBatch() {
        const pending = getCheckedPinjaman('pending').length;
        const disetujui = getCheckedPinjaman('disetujui').length;
        document.getElementById('btnAccMassal').disabled = pending === 0;
        document.getElementById('btnCairMassal').disabled = disetujui === 0;
        document.getElementById('btnAccMassal').innerHTML = `<i class="bx bx-check-double me-1"></i> ACC Massal${pending ? ' (' + pending + ')' : ''}`;
        document.getElementById('btnCairMassal').innerHTML = `<i class="bx bx-transfer me-1"></i> Cairkan Massal${disetujui ? ' (' + disetujui + ')' : ''}`;
    }

    document.getElementById('checkAll').addEventListener('change', function() {
        document.querySelectorAll('.check-pinjaman').forEach(c => c.checked = this.checked);
        updateTombolBatch();
    });

    document.addEventListener('change', e => {
        if (e.target.classList.contains('check-pinjaman')) {
            updateTombolBatch();
        }
    });

    document.getElementById('btnAccMassal').addEventListener('click', async () => {
        const ids = getCheckedPinjaman('pending');
        if (ids.length === 0) return;

        if (!await confirmDialog(`Setujui (ACC) ${ids.length} pengajuan pinjaman terpilih?`)) return;

        const fd = new FormData();
        fd.append('action', 'acc_batch');
        ids.forEach(id => fd.append('id_pengajuan[]', id));

        try {
            const res = await fetch(api_act_pinjaman, {
                method: 'POST',
                body: fd
            });
            const json = await res.json();
            showToast(json.msg || '-', json.status === 'success' ? 'success' : 'error');
            loadTablePinjaman();
        } catch (e) {
            showToast('Terjadi kesalahan sistem.', 'error');
        }
    });

    document.getElementById('btnCairMassal').addEventListener('click', () => {
        const ids = getCheckedPinjaman('disetujui');
        if (ids.length === 0) return;

        modeCair = 'batch';
        window.__idsCair = ids;
        document.getElementById('infoCairPinjaman').innerText = `Akan mencairkan ${ids.length} pengajuan terpilih.`;
        new bootstrap.Modal(document.getElementById('modalCairPinjaman')).show();
    });

    // ===================== DETAIL =====================
    function openDetailPinjaman(id) {
        const d = dataPinjaman.find(x => String(x.id_pengajuan_pinjaman) === String(id));
        if (!d) return;

        const badge = badgePinjaman[d.status_pinjaman] || ['secondary', d.status_pinjaman];
        const fotoWajah = d.foto_wajah
            ? `<div class="preview-thumbnail shadow-sm border rounded p-1 d-inline-block" onclick="previewFotoPinjaman('app/${esc(d.foto_wajah)}')"><img src="app/${esc(d.foto_wajah)}" style="width:90px;height:90px;object-fit:cover;" class="rounded"></div>`
            : '<span class="badge bg-label-secondary">Tidak ada (pengajuan aplikasi)</span>';
        const fotoKtp = d.foto_id_card
            ? `<div class="preview-thumbnail shadow-sm border rounded p-1 d-inline-block" onclick="previewFotoPinjaman('app/${esc(d.foto_id_card)}')"><img src="app/${esc(d.foto_id_card)}" style="width:90px;height:90px;object-fit:cover;" class="rounded"></div>`
            : '<span class="badge bg-label-secondary">Tidak ada (pengajuan aplikasi)</span>';

        document.getElementById('detailBodyPinjaman').innerHTML = `
            <div class="col-md-6">
                <small class="text-muted d-block">Karyawan</small>
                <span class="fw-bold">${esc(d.nama_lengkap)}</span>
                <small class="text-muted d-block">${esc(d.nik_karyawan)} &bull; ${esc(d.jabatan || '-')} &bull; ${esc(d.site || '-')}</small>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Tanggal Pengajuan</small>
                <span class="fw-semibold">${d.tgl_pengajuan ? new Date(d.tgl_pengajuan).toLocaleString('id-ID') : '-'}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Nominal</small>
                <span class="fw-bold fs-5 text-success">${rp(d.nominal_pinjaman)}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Status</small>
                <span class="badge bg-label-${badge[0]}">${badge[1]}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Rekening Tujuan</small>
                <span class="fw-semibold">${esc(d.nama_bank || '-')}</span>
                <small class="text-muted d-block">${esc(d.no_rek || '-')} a.n. ${esc(d.atas_nama_rek || '-')}</small>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Alasan</small>
                <span class="text-wrap">${esc(d.alasan_pinjaman || '-')}</span>
            </div>
            <div class="col-12">
                <small class="text-muted d-block">Keterangan Admin</small>
                <span class="text-wrap d-block bg-light border rounded p-2" style="white-space: pre-wrap;">${esc(d.keterangan_admin || '-')}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block mb-1">Foto Wajah</small>
                ${fotoWajah}
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block mb-1">Foto ID Card</small>
                ${fotoKtp}
            </div>`;

        new bootstrap.Modal(document.getElementById('modalDetailPinjaman')).show();
    }

    function previewFotoPinjaman(src) {
        document.getElementById('imgPreviewPinjaman').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewPinjaman')).show();
    }

    // ===================== PROSES (SETUJUI / TOLAK) =====================
    function openProsesPinjaman(id, status) {
        const form = document.getElementById('formProsesPinjaman');
        form.querySelector('input[name=id_pengajuan]').value = id;
        form.querySelector('input[name=status_pinjaman]').value = status;
        form.querySelector('textarea[name=keterangan_admin]').value = '';
        form.querySelector('textarea[name=keterangan_admin]').required = (status === 'ditolak');

        document.getElementById('prosesTitlePinjaman').innerText = status === 'disetujui' ? 'Setujui Pengajuan' : 'Tolak Pengajuan';
        document.getElementById('prosesInfoPinjaman').innerText = status === 'disetujui'
            ? 'Pengajuan akan disetujui dan masuk antrean transfer/pencairan.'
            : 'Pengajuan akan ditolak. Mohon isi alasan penolakan.';

        new bootstrap.Modal(document.getElementById('modalProsesPinjaman')).show();
    }

    document.getElementById('formProsesPinjaman').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', 'proses');

        try {
            const res = await fetch(api_act_pinjaman, {
                method: 'POST',
                body: fd
            });
            const json = await res.json();
            bootstrap.Modal.getInstance(document.getElementById('modalProsesPinjaman')).hide();
            showToast(json.msg || '-', json.status === 'success' ? 'success' : 'error');
            loadTablePinjaman();
        } catch (err) {
            showToast('Terjadi kesalahan sistem.', 'error');
        }
    });

    // ===================== PENCAIRAN =====================
    function openCairPinjaman(id) {
        modeCair = 'single';
        idCair = id;
        window.__idsCair = null;
        document.getElementById('infoCairPinjaman').innerText = 'Tandai pengajuan ini sebagai sudah ditransfer.';
        new bootstrap.Modal(document.getElementById('modalCairPinjaman')).show();
    }

    document.getElementById('formCairPinjaman').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', modeCair === 'single' ? 'cair_single' : 'cair_batch');
        fd.append('tgl_bayar_pinjaman', this.querySelector('input[name=tgl_bayar_pinjaman]').value);

        if (modeCair === 'single') {
            fd.append('id', idCair);
        } else {
            (window.__idsCair || []).forEach(id => fd.append('id_pengajuan[]', id));
        }

        try {
            const res = await fetch(api_act_pinjaman, {
                method: 'POST',
                body: fd
            });
            const json = await res.json();
            bootstrap.Modal.getInstance(document.getElementById('modalCairPinjaman')).hide();
            showToast(json.msg || '-', json.status === 'success' ? 'success' : 'error');
            loadTablePinjaman();
        } catch (err) {
            showToast('Terjadi kesalahan sistem.', 'error');
        }
    });

    // ===================== AKSI CEPAT =====================
    async function setPendingPinjaman(id) {
        if (!await confirmDialog('Kembalikan pengajuan ini ke status Pending?')) return;

        try {
            const res = await fetch(`${api_act_pinjaman}?action=update-status&id=${id}&status=pending`);
            const json = await res.json();
            showToast(json.msg || '-', json.status === 'success' ? 'success' : 'error');
            loadTablePinjaman();
        } catch (e) {
            showToast('Terjadi kesalahan sistem.', 'error');
        }
    }

    async function hapusPinjaman(id) {
        if (!await confirmDialog('Hapus permanen pengajuan pinjaman ini?')) return;

        try {
            const res = await fetch(`${api_act_pinjaman}?action=delete&id=${id}`);
            const json = await res.json();
            showToast(json.msg || '-', json.status === 'success' ? 'success' : 'error');
            loadTablePinjaman();
        } catch (e) {
            showToast('Terjadi kesalahan sistem.', 'error');
        }
    }

    // ===================== INIT =====================
    document.getElementById('btnCari').addEventListener('click', loadTablePinjaman);
    document.addEventListener('DOMContentLoaded', () => {
        loadAkses();
        loadTablePinjaman();
    });
</script>
