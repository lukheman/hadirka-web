<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Informasi /</span> Notifikasi Aplikasi
    </h4>

    <div class="alert alert-info py-2" role="alert">
        <i class="bx bx-bell me-1"></i>
        Notifikasi ini tampil di <strong>aplikasi absen mobile</strong> milik karyawan (tab Notifikasi).
        Kirim pengumuman, pemberitahuan slip gaji, atau info pengajuan dari sini.
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold">Daftar Notifikasi Terkirim</h5>
            <button class="btn btn-primary shadow" data-bs-toggle="modal" data-bs-target="#modalNotif">
                <i class="bx bx-send me-1"></i> Kirim Notifikasi
            </button>
        </div>
        <div class="card-body mt-4">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Filter Kategori</label>
                    <select id="filterKategoriNotif" class="form-select">
                        <option value="">Semua Kategori</option>
                        <option value="presensi">Presensi / Absensi</option>
                        <option value="penggajian">Penggajian / Slip Gaji</option>
                        <option value="pengajuan">Pengajuan</option>
                        <option value="pengumuman">Pengumuman</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover tabelNotifikasi">
                    <thead>
                        <tr class="align-middle text-nowrap">
                            <th>Waktu</th>
                            <th>Karyawan</th>
                            <th>Judul & Pesan</th>
                            <th>Kategori</th>
                            <th>Status Baca</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyNotifikasi" class="table-border-bottom-0">
                        <tr>
                            <td colspan="6" class="text-center py-4">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNotif" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formNotif" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Kirim Notifikasi Aplikasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul</label>
                    <input type="text" name="judul" class="form-control" maxlength="150" placeholder="Contoh: Slip Gaji Oktober sudah terbit" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="presensi">Presensi / Absensi</option>
                        <option value="penggajian">Penggajian / Slip Gaji</option>
                        <option value="pengajuan">Pengajuan (Izin/Cuti/Pinjaman)</option>
                        <option value="pengumuman" selected>Pengumuman Umum</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pesan</label>
                    <textarea name="pesan" class="form-control" rows="4" placeholder="Tulis isi notifikasi..." required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Target Penerima</label>
                    <select name="target" id="targetTipe" class="form-select">
                        <option value="semua">Semua Karyawan Aktif</option>
                        <option value="site">Per Site</option>
                        <option value="karyawan">Karyawan Tertentu</option>
                    </select>
                </div>

                <div class="mb-3" id="wrapSite" style="display:none;">
                    <label class="form-label fw-bold">Pilih Site</label>
                    <select name="id_site" class="form-select">
                        <option value="">- Pilih Site -</option>
                        <?php
                        $sites = data_site();
                        while ($s = $sites->fetch()) :
                        ?>
                            <option value="<?= $s['id_site'] ?>"><?= htmlspecialchars($s['nama_site']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3" id="wrapKaryawan" style="display:none;">
                    <label class="form-label fw-bold">Pilih Karyawan <small class="text-muted">(bisa lebih dari satu)</small></label>
                    <select name="id_karyawan[]" id="selectKaryawanNotif" class="form-select" multiple style="width:100%;">
                        <?php
                        $karyawans = data_karyawan('nama_lengkap');
                        while ($k = $karyawans->fetch()) :
                        ?>
                            <option value="<?= $k['id_karyawan'] ?>"><?= htmlspecialchars($k['nik_karyawan'] . ' - ' . $k['nama_lengkap']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary shadow">Kirim Notifikasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    const api_act_notif = 'app/controller/act-notifikasi.php';
    const api_get_notif = 'app/controller/get-data/get-notifikasi.php';

    async function loadTabelNotifikasi() {
        const kategori = document.getElementById('filterKategoriNotif').value;
        const params = new URLSearchParams({
            kategori: kategori,
            _: new Date().getTime()
        });

        const res = await fetch(`${api_get_notif}?${params.toString()}`);
        const html = await res.text();

        if ($.fn.DataTable.isDataTable('.tabelNotifikasi')) {
            $('.tabelNotifikasi').DataTable().destroy();
        }

        document.getElementById('tbodyNotifikasi').innerHTML = html;

        $('.tabelNotifikasi').DataTable({
            responsive: true,
            pageLength: 10,
            order: []
        });
    }

    async function hapusNotifikasi(id) {
        if (await confirmDialog("Hapus notifikasi ini dari aplikasi karyawan?")) {
            try {
                const res = await fetch(`${api_act_notif}?action=delete&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    showToast(result.msg || 'Notifikasi dihapus.', 'success');
                    loadTabelNotifikasi();
                } else {
                    showToast(result.msg || 'Gagal menghapus.', 'error');
                }
            } catch (e) {
                showToast('Terjadi kesalahan saat menghapus data.', 'error');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Select2 karyawan di dalam modal butuh dropdownParent agar dropdown tampil di atas modal
        if ($.fn.select2) {
            $('#selectKaryawanNotif').select2({
                dropdownParent: $('#modalNotif'),
                placeholder: 'Cari karyawan...',
                closeOnSelect: false,
                width: '100%'
            });
        }

        // Toggle target penerima
        document.getElementById('targetTipe').addEventListener('change', function() {
            document.getElementById('wrapSite').style.display = (this.value === 'site') ? '' : 'none';
            document.getElementById('wrapKaryawan').style.display = (this.value === 'karyawan') ? '' : 'none';
        });

        // Submit form
        document.getElementById('formNotif').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'save');

            try {
                const res = await fetch(api_act_notif, {
                    method: 'POST',
                    body: fd
                });
                const result = await res.json();

                if (result.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('modalNotif')).hide();
                    this.reset();
                    $('#selectKaryawanNotif').val(null).trigger('change');
                    document.getElementById('wrapSite').style.display = 'none';
                    document.getElementById('wrapKaryawan').style.display = 'none';
                    showToast(result.msg || 'Notifikasi terkirim.', 'success');
                    loadTabelNotifikasi();
                } else {
                    showToast(result.msg || 'Gagal mengirim notifikasi.', 'error');
                }
            } catch (err) {
                showToast('Terjadi kesalahan sistem.', 'error');
            }
        });

        document.getElementById('filterKategoriNotif').addEventListener('change', loadTabelNotifikasi);

        loadTabelNotifikasi();
    });
</script>
