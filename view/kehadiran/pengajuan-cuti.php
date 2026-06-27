<style>
    /* Efek Zoom pada Lampiran */
    .preview-thumbnail {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background: #fff;
    }

    .preview-thumbnail:hover {
        transform: scale(1.1);
        border-color: #696cff !important;
        box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4) !important;
    }

    #modalPreviewGambar .modal-content {
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.5) !important;
    }

    .w-px-100 {
        width: 100px !important;
    }

    /* Menyesuaikan style Select2 agar rapi seperti di referensi APD Anda */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 14px !important;
        color: #697a8d !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 5px !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Site</label>
                    <select id="filterSite" class="form-select">
                        <option value="">Semua Site</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jabatan</label>
                    <select id="filterJabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Manajemen Persetujuan Cuti</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahCuti">
                <i class="bx bx-plus me-1"></i> Tambah Pengajuan
            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover border-top">
                <thead>
                    <tr class="align-middle text-nowrap">
                        <th>Nama Karyawan</th>
                        <th>Tanggal Cuti (Awal - Akhir)</th>
                        <th>Lama</th>
                        <th>Alasan</th>
                        <th class="text-center">Lampiran</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-cuti" class="table-border-bottom-0">
                    <tr>
                        <td colspan="7" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahCuti" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Tambah Pengajuan Cuti</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahCuti" onsubmit="tambahCutiAdmin(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Karyawan</label>
                        <select class="form-select select2" id="id_karyawan" name="id_karyawan" required>
                            <option value="">-- Ketik NIK / Nama Karyawan --</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col mb-0">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" id="tgl_awal" name="tgl_awal" required onchange="hitungLamaCuti()">
                        </div>
                        <div class="col mb-0">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tgl_akhir" name="tgl_akhir" required onchange="hitungLamaCuti()">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lama Cuti (Hari)</label>
                        <input type="number" class="form-control" id="lama_cuti" name="lama_cuti" required min="1" readonly>
                        <small class="text-muted">Dihitung otomatis (Termasuk hari libur. Silakan sesuaikan jika perlu).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Cuti</label>
                        <textarea class="form-control" id="alasan_cuti" name="alasan_cuti" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran Bukti (Opsional)</label>
                        <input class="form-control" type="file" id="bukti_cuti" name="bukti_cuti" accept="image/jpeg, image/png, image/webp, application/pdf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Awal</label>
                        <select class="form-select" id="status_cuti" name="status_cuti">
                            <option value="pending">Pending (Butuh Persetujuan)</option>
                            <option value="disetujui">Disetujui (Langsung Potong Jatah)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <small id="form-error" class="text-danger"></small>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-cuti">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewGambar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                <img src="" id="imgPreview" class="img-fluid rounded shadow-lg border border-3 border-white">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProsesAdmin" tabindex="-1">
    <div class="modal-dialog">
        <form id="formProsesAdmin" class="modal-content">
            <input type="hidden" id="proses_id" name="id">
            <input type="hidden" id="proses_status" name="status">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalProsesTitle">Proses Pengajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    <span id="proses_teks_info" class="fw-bold"></span>
                </div>
                <label class="fw-bold mb-1">Pesan / Keterangan Admin</label>
                <textarea name="ket_admin" id="proses_ket_admin" class="form-control" rows="3" placeholder="Opsional (Wajib diisi jika ditolak)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnProsesSubmit">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>

<script>
    const api_get_cuti = 'app/controller/get-data/get-pengajuan-cuti.php';
    const api_act_cuti = 'app/controller/act-pengajuan-cuti.php';

    // Fungsi set default tanggal (21 bln kemarin - 20 bln ini)
    function setDefaultDateRange() {
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();
        let prevMonth = month - 1;
        let prevYear = year;

        if (prevMonth < 0) {
            prevMonth = 11;
            prevYear = year - 1;
        }

        const format = (y, m, d) => `${y}-${(m + 1).toString().padStart(2, "0")}-${d.toString().padStart(2, "0")}`;
        document.getElementById("startDate").value = format(prevYear, prevMonth, 21);
        document.getElementById("endDate").value = format(year, month, 20);
    }

    // Fungsi load Dropdown Site & Jabatan via API yang sudah ada
    async function loadFilterOptions() {
        try {
            const res = await fetch('app/controller/get-data/get-site-departemen-jabatan.php');
            const data = await res.json();

            let siteOpt = '<option value="">Semua Site</option>';
            data.site.forEach(s => siteOpt += `<option value="${s.id_site}">${s.nama_site}</option>`);
            document.getElementById('filterSite').innerHTML = siteOpt;

            let jabatanOpt = '<option value="">Semua Jabatan</option>';
            data.jabatan.forEach(j => jabatanOpt += `<option value="${j.id_jabatan}">${j.nama_jabatan}</option>`);
            document.getElementById('filterJabatan').innerHTML = jabatanOpt;
        } catch (e) {
            console.error("Gagal memuat opsi filter:", e);
        }
    }

    // ====================================================
    // CONTOH UNTUK PENGAJUAN IZIN (Sesuaikan nama fungsi untuk Cuti/SKS)
    // ====================================================
    async function loadTableCuti() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const site = document.getElementById('filterSite').value;
        const jabatan = document.getElementById('filterJabatan').value;

        const params = new URLSearchParams({
            start_date: start,
            end_date: end,
            site: site,
            jabatan: jabatan,
            _: new Date().getTime()
        });

        // Ubah api_get sesuai endpoint masing-masing
        const res = await fetch(`${api_get_cuti}?${params.toString()}`);
        document.getElementById('tbody-cuti').innerHTML = await res.text();
    }

    document.addEventListener('DOMContentLoaded', async () => {
        setDefaultDateRange();
        await loadFilterOptions();

        loadTableCuti(); // Panggil fungsi load tabel terkait

        // Auto reload saat filter diubah
        const filters = ['startDate', 'endDate', 'filterSite', 'filterJabatan'];
        filters.forEach(id => {
            document.getElementById(id).addEventListener('change', loadTableCuti);
        });
    });

    // 1. Fungsi Fetch Data Karyawan (Sama seperti referensi APD)
    async function loadAllKaryawan() {
        try {
            const res = await fetch(`${api_act_cuti}?action=get-all-karyawan`);
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
                    dropdownParent: $('#modalTambahCuti'),
                    placeholder: "-- Ketik NIK / Nama Karyawan --",
                    allowClear: true,
                    width: '100%'
                });
            }
        } catch (e) {
            console.error("Gagal memuat data karyawan");
        }
    }

    // 2. Fungsi Hitung Otomatis Lama Cuti
    function hitungLamaCuti() {
        const tglAwal = document.getElementById('tgl_awal').value;
        const tglAkhir = document.getElementById('tgl_akhir').value;

        if (tglAwal && tglAkhir) {
            const awal = new Date(tglAwal);
            const akhir = new Date(tglAkhir);

            // Cek jika tgl akhir lebih kecil dari tgl awal
            if (akhir < awal) {
                alert("Tanggal Akhir tidak boleh lebih awal dari Tanggal Awal!");
                document.getElementById('tgl_akhir').value = '';
                document.getElementById('lama_cuti').value = '';
                return;
            }

            // Hitung selisih waktu
            const diffTime = Math.abs(akhir - awal);
            // Konversi ke hari dan tambah 1 (karena cuti dari tgl 1 s/d 1 = 1 hari)
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

            // --- TAMBAHKAN VALIDASI MAKSIMAL 6 HARI ---
            if (diffDays > 6) {
                alert("Maksimal cuti dalam satu kali pengajuan adalah 6 hari!");
                document.getElementById('tgl_akhir').value = '';
                document.getElementById('lama_cuti').value = '';
                return;
            }

            document.getElementById('lama_cuti').value = diffDays;
        }
    }

    function previewBukti(src) {
        document.getElementById('imgPreview').src = src;
        new bootstrap.Modal(document.getElementById('modalPreviewGambar')).show();
    }

    async function tambahCutiAdmin(event) {
        event.preventDefault();

        const form = document.getElementById('formTambahCuti');
        const formData = new FormData(form);
        formData.append('is_admin_web', 'true');

        const btnSubmit = document.getElementById('btn-submit-cuti');
        const errorText = document.getElementById('form-error');

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Menyimpan...';
        errorText.innerText = '';

        try {
            const response = await fetch(`${api_act_cuti}?action=tambah_admin`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                var myModalEl = document.getElementById('modalTambahCuti');
                var modal = bootstrap.Modal.getInstance(myModalEl);
                modal.hide();

                form.reset();
                if (typeof $ !== 'undefined') {
                    $('#id_karyawan').val(null).trigger('change'); // Reset select2
                }

                loadTableCuti();
                alert(result.message);
            } else {
                errorText.innerText = result.message;
            }
        } catch (error) {
            errorText.innerText = 'Terjadi kesalahan sistem.';
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Simpan';
        }
    }

    function updateStatus(id, newStatus) {
        document.getElementById('proses_id').value = id;
        document.getElementById('proses_status').value = newStatus;
        document.getElementById('proses_ket_admin').value = '';
        document.getElementById('proses_ket_admin').required = (newStatus === 'ditolak');

        document.getElementById('modalProsesTitle').innerText = newStatus === 'disetujui' ? 'Setujui Izin' : (newStatus === 'ditolak' ? 'Tolak Izin' : 'Set Pending');
        document.getElementById('proses_teks_info').innerText = `Anda akan mengubah status menjadi: ${newStatus.toUpperCase()}`;

        new bootstrap.Modal(document.getElementById('modalProsesAdmin')).show();
    }

    document.getElementById('formProsesAdmin').addEventListener('submit', async function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append('action', 'update-status');

        try {
            const response = await fetch(api_act_cuti, {
                method: 'POST',
                body: fd
            });
            const result = await response.json();
            if (result.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('modalProsesAdmin')).hide();
                loadTableCuti();
            } else {
                alert("Gagal: " + result.message);
            }
        } catch (err) {
            alert("Terjadi kesalahan sistem.");
        }
    });

    async function deleteCuti(id) {
        if (confirm("Hapus permanen data pengajuan cuti ini?")) {
            try {
                const res = await fetch(`${api_act_cuti}?action=delete&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    loadTableCuti();
                } else {
                    alert("Gagal menghapus: " + result.message);
                }
            } catch (e) {
                alert("Terjadi kesalahan saat menghapus data.");
            }
        }
    }
</script>