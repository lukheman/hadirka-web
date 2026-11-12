<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold"><span class="text-muted fw-light">Manajemen /</span> Surat Pengalaman Kerja</h4>

        <div class="card mb-3">
            <h5 class="card-header">Template Surat</h5>
            <div class="card-body">
                <div class="alert alert-info py-2 mb-3">
                    <strong>Cara penggunaan:</strong> siapkan template Word dengan placeholder di bawah, lalu upload.
                    Setelah itu pilih template dan karyawan pada bagian cetak. Centang <strong>Input manual</strong> bila karyawan tidak tersedia di sistem.
                    <a href="docs/PANDUAN-SURAT-PENGALAMAN-KERJA.md" target="_blank" class="alert-link">Buka panduan lengkap</a>.
                    <br><strong>Placeholder yang tersedia:</strong>
                    <span class="text-primary fw-semibold">
                        <code class="text-primary">${nomor_surat}</code>
                        <code class="text-primary">${nama}</code>
                        <code class="text-primary">${tanggal_lahir}</code>
                        <code class="text-primary">${nomor_id_karyawan}</code>
                        <code class="text-primary">${jabatan_terakhir}</code>
                        <code class="text-primary">${periode_mulai}</code>
                        <code class="text-primary">${periode_selesai}</code>
                        <code class="text-primary">${alasan_berhenti}</code>
                        <code class="text-primary">${tanggal_tanda_tangan}</code>
                    </span>
                </div>
                <form id="templateForm" class="border rounded p-3" enctype="multipart/form-data">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                            <input class="form-control" name="nama_template" placeholder="Contoh: SPK IPM" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">File Template Word <span class="text-danger">*</span></label>
                            <input class="form-control" type="file" name="file_template" accept=".doc,.docx" required>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit"><i class="bx bx-upload me-1"></i> Upload</button>
                        </div>
                    </div>
                    <div id="templateFileName" class="small text-muted mt-2">Belum ada template yang diupload.</div>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <h5 class="card-header"><i class="bx bx-printer me-1"></i> Cetak Surat Pengalaman Kerja</h5>
            <div class="card-body">
                <form action="app/controller/cetak/cetak-pengalaman.php" method="POST" target="_blank" id="printForm">
                    <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Karyawan tereliminasi</label><select class="form-select" name="id_karyawan" id="printEmployee"><option value="">-- Pilih karyawan --</option></select></div>
                            <div class="col-md-3"><label class="form-label">Tahun Surat <span class="text-danger">*</span></label><input class="form-control" type="number" name="tahun_surat" id="tahunSurat" min="2000" max="2100" required></div>
                            <div class="col-md-3"><label class="form-label">Nomor Surat</label><input class="form-control" name="manual_nomor_surat" id="manualNomorSurat" placeholder="Otomatis"><small class="text-muted">Kosongkan untuk nomor otomatis.</small><small class="d-block text-primary" id="nextNomorSurat">Nomor berikutnya: memuat...</small></div>
                        <div class="col-12"><input type="hidden" name="manual" id="manualValue" value="0"></div>
                    </div>
                    <div id="dataFields" class="row g-3 mt-1 d-none">
                        <div class="col-12"><h6 class="mb-0 text-primary"><i class="bx bx-user me-1"></i> Data Surat <small class="text-muted fw-normal">(dapat diubah sebelum dicetak)</small></h6></div>
                        <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="data_nama"></div>
                        <div class="col-md-6"><label class="form-label">Nomor ID Karyawan</label><input class="form-control" name="data_nomor_id"></div>
                        <div class="col-md-6"><label class="form-label">Tanggal Lahir</label><input class="form-control" name="data_tanggal_lahir" placeholder="Kolaka, 12 Mei 1995"></div>
                        <div class="col-md-6"><label class="form-label">Jabatan Terakhir</label><input class="form-control" name="data_jabatan"></div>
                        <div class="col-md-6"><label class="form-label">Periode Mulai</label><input class="form-control" type="date" name="data_periode_mulai"></div>
                        <div class="col-md-6"><label class="form-label">Periode Selesai</label><input class="form-control" type="date" name="data_periode_selesai"></div>
                        <div class="col-12"><label class="form-label">Alasan Berhenti</label><input class="form-control" name="data_alasan"></div>
                    </div>
                    <div class="text-end mt-3"><button type="button" class="btn btn-outline-secondary me-2" id="manualMode"><i class="bx bx-edit me-1"></i> Input Manual</button><button class="btn btn-success"><i class="bx bx-printer me-1"></i> Cetak Surat</button></div>
                </form>
            </div>
        </div>

        <div class="card"><h5 class="card-header d-flex justify-content-between align-items-center"><span><i class="bx bx-archive me-1"></i> Arsip Surat Pengalaman Kerja</span><button class="btn btn-sm btn-primary" type="button" id="openArchiveUpload"><i class="bx bx-upload me-1"></i> Upload Arsip</button></h5><div class="card-body">
            <div class="table-responsive text-nowrap"><table class="table table-bordered table-sm" id="archiveTable"><thead class="table-info"><tr><th class="text-center">No</th><th>Nama Karyawan</th><th>Nama File</th><th>Keterangan</th><th class="text-center">Tanggal Upload</th><th class="text-center">Aksi</th></tr></thead><tbody id="archiveBody"></tbody></table></div>
        </div></div>
    </div>
</div>

<div class="modal fade" id="modalArsip" tabindex="-1"><div class="modal-dialog"><form id="arsipForm" class="modal-content" enctype="multipart/form-data"><div class="modal-header bg-primary text-white"><h5 class="modal-title text-white">Upload Arsip</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Nama Karyawan <span class="text-danger">*</span></label><input class="form-control mb-3" name="nama_karyawan" required><label class="form-label">File PDF/Word <span class="text-danger">*</span></label><input class="form-control mb-3" type="file" name="file_arsip" accept=".pdf,.doc,.docx" required><label class="form-label">Keterangan</label><input class="form-control" name="keterangan" maxlength="255"></div><div class="modal-footer"><button class="btn btn-primary">Upload</button></div></form></div></div>
<div class="modal fade" id="modalEditArsip" tabindex="-1"><div class="modal-dialog"><form id="editArsipForm" class="modal-content" enctype="multipart/form-data"><div class="modal-header bg-warning"><h5 class="modal-title"><i class="bx bx-edit me-1"></i> Edit Arsip</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="id_arsip" id="editArsipId"><label class="form-label">Nama Karyawan <span class="text-danger">*</span></label><input class="form-control mb-3" name="nama_karyawan" id="editArsipNama" required><label class="form-label">File Pengganti <small class="text-muted">(opsional)</small></label><input class="form-control mb-3" type="file" name="file_arsip" accept=".pdf,.doc,.docx"><label class="form-label">Keterangan</label><input class="form-control" name="keterangan" id="editArsipKeterangan" maxlength="255"></div><div class="modal-footer"><button class="btn btn-warning">Simpan Perubahan</button></div></form></div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $id = id => document.getElementById(id);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[character]));
    let templates = [], employees = [];

    function loadTemplates() {
        fetch('app/controller/act-template-pengalaman.php?action=list')
            .then(response => response.json())
            .then(result => {
            templates = result.data || [];
            $id('templateFileName').innerHTML = templates[0]
                ? `<i class="bx bx-file text-primary me-1"></i> File terupload: <strong>${esc(templates[0].nama_asli)}</strong>`
                : 'Belum ada template yang diupload.';
            });
    }

    function loadEmployees() {
        fetch('app/controller/get-data/get-pengalaman.php')
            .then(response => response.json())
            .then(result => {
            employees = result.data || [];
            const options = '<option value="">-- Pilih karyawan --</option>'
                + employees.map(employee => `<option value="${employee.id_karyawan}">${esc(employee.nama_lengkap)} - ${esc(employee.nik_karyawan)}</option>`).join('');
            $id('printEmployee').innerHTML = options;
            });
    }

    function loadNextLetterNumber() {
        const year = $id('tahunSurat').value || new Date().getFullYear();
        fetch(`app/controller/get-data/get-next-nomor-pengalaman.php?tahun=${encodeURIComponent(year)}`)
            .then(response => response.json())
            .then(result => {
                $id('nextNomorSurat').innerHTML = result.status === 'success'
                    ? `<i class="bx bx-right-arrow-alt me-1"></i>Nomor berikutnya: <strong>${esc(result.nomor)}</strong>`
                    : '<i class="bx bx-error me-1"></i>Nomor berikutnya tidak tersedia';
            })
            .catch(() => {
                $id('nextNomorSurat').textContent = 'Nomor berikutnya tidak tersedia';
            });
    }

    function fillEmployeeData() {
        const employee = employees.find(item => String(item.id_karyawan) === $id('printEmployee').value);
        const fields = {
            data_nama: employee?.nama_lengkap || '',
            data_nomor_id: employee?.nik_karyawan || '',
            data_tanggal_lahir: employee?.tgl_lahir || '',
            data_jabatan: employee?.nama_jabatan || '',
            data_periode_mulai: employee?.tgl_mulai || '',
            data_periode_selesai: employee?.tgl_selesai || employee?.tgl_keluar || '',
            data_alasan: employee?.alasan_keluar || employee?.status_karyawan || ''
        };
        Object.entries(fields).forEach(([name, value]) => {
            const input = $id('printForm').querySelector(`[name="${name}"]`);
            if (input) input.value = value;
        });
    }

    $id('printEmployee').addEventListener('change', function () {
        if (this.value) {
            fillEmployeeData();
            $id('dataFields').classList.remove('d-none');
        }
    });

    $id('manualMode').addEventListener('click', function () {
        const manual = $id('manualValue').value === '0';
        $id('manualValue').value = manual ? '1' : '0';
        this.classList.toggle('active', manual);
        this.classList.toggle('btn-secondary', manual);
        this.classList.toggle('btn-outline-secondary', !manual);
        $id('printEmployee').disabled = manual;
        if (manual) {
            $id('printEmployee').value = '';
            $id('dataFields').classList.remove('d-none');
            $id('dataFields').querySelectorAll('input').forEach(input => input.value = '');
        }
    });

    $id('templateForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const form = new FormData(this);
        form.append('action', 'upload');

        fetch('app/controller/act-template-pengalaman.php', { method: 'POST', body: form })
            .then(response => response.json())
            .then(result => {
                showToast(result.message, result.status === 'success' ? 'success' : 'error');
                if (result.status === 'success') {
                    this.reset();
                    loadTemplates();
                }
            });
    });

    $id('tahunSurat').addEventListener('change', loadNextLetterNumber);

    $id('openArchiveUpload').addEventListener('click', function () {
        $id('arsipForm').reset();
        new bootstrap.Modal($id('modalArsip')).show();
    });

    function loadArchives() {
        fetch('app/controller/act-arsip-pengalaman.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'list_all' })
        })
            .then(response => response.json())
            .then(result => {
                const rows = result.data || [];
                $id('archiveBody').innerHTML = rows.map((archive, index) => `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${esc(archive.nama_karyawan || '-')}</td>
                        <td><a target="_blank" href="app/uploads/arsip-pengalaman/${encodeURIComponent(archive.file)}">${esc(archive.file)}</a></td>
                        <td>${esc(archive.keterangan || '-')}</td>
                        <td class="text-center">${esc(archive.created_at)}</td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-success" target="_blank" href="app/uploads/arsip-pengalaman/${encodeURIComponent(archive.file)}"><i class="bx bx-show"></i></a>
                            <button class="btn btn-sm btn-warning edit-archive" data-id="${archive.id_arsip}" data-nama="${esc(archive.nama_karyawan || '')}" data-keterangan="${esc(archive.keterangan || '')}"><i class="bx bx-edit"></i></button>
                            <button class="btn btn-sm btn-danger delete-archive" data-id="${archive.id_arsip}"><i class="bx bx-trash"></i></button>
                        </td>
                    </tr>
                `).join('');

                if ($.fn.DataTable.isDataTable('#archiveTable')) {
                    $('#archiveTable').DataTable().destroy();
                }

                $('#archiveTable').DataTable({
                    responsive: true,
                    pageLength: 10
                });
            });
    }

    $id('arsipForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const form = new FormData(this);
        form.append('action', 'upload');

        fetch('app/controller/act-arsip-pengalaman.php', { method: 'POST', body: form })
            .then(response => response.json())
            .then(result => {
                showToast(result.message, result.status === 'success' ? 'success' : 'error');
                if (result.status === 'success') {
                    bootstrap.Modal.getInstance($id('modalArsip')).hide();
                    this.reset();
                    loadArchives();
                }
            });
    });

    $id('editArsipForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const form = new FormData(this);
        form.append('action', 'update');

        fetch('app/controller/act-arsip-pengalaman.php', { method: 'POST', body: form })
            .then(response => response.json())
            .then(result => {
                showToast(result.message, result.status === 'success' ? 'success' : 'error');
                if (result.status === 'success') {
                    bootstrap.Modal.getInstance($id('modalEditArsip')).hide();
                    loadArchives();
                }
            });
    });
    document.addEventListener('click', async function (event) {
        const edit = event.target.closest('.edit-archive');
        if (edit) {
            $id('editArsipId').value = edit.dataset.id;
            $id('editArsipNama').value = edit.dataset.nama || '';
            $id('editArsipKeterangan').value = edit.dataset.keterangan || '';
            $id('editArsipForm').querySelector('[name="file_arsip"]').value = '';
            new bootstrap.Modal($id('modalEditArsip')).show();
        }
        const archive = event.target.closest('.delete-archive');
        if (archive && await confirmDialog('Yakin hapus arsip ini? File akan ikut terhapus.')) {
            fetch('app/controller/act-arsip-pengalaman.php', {method:'POST', body:new URLSearchParams({action:'delete', id_arsip:archive.dataset.id})}).then(loadArchives);
        }
    });

    $id('tahunSurat').value = new Date().getFullYear();
    loadTemplates();
    loadEmployees();
    loadArchives();
    loadNextLetterNumber();
});
</script>
