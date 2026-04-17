<style>
    /* Efek Zoom pada Lampiran */
    .preview-thumbnail { transition: all 0.2s ease-in-out; cursor: pointer; background: #fff; }
    .preview-thumbnail:hover { transform: scale(1.1); border-color: #696cff !important; box-shadow: 0 0.25rem 0.5rem rgba(105, 108, 255, 0.4) !important; }
    #modalPreviewGambar .modal-content { backdrop-filter: blur(5px); background-color: rgba(0, 0, 0, 0.5) !important; }
    .w-px-100 { width: 100px !important; }

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
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Manajemen Persetujuan Cuti</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahCuti">
                <i class="bx bx-plus me-1"></i> Tambah Pengajuan
            </button>
        </div>
        <div class="">
            <table class="table table-hover border-top" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th class="py-3">Karyawan</th>
                        <th>Tanggal Cuti (Awal - Akhir)</th>
                        <th>Lama</th>
                        <th>Alasan</th>
                        <th class="text-center">Lampiran</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-cuti" class="table-border-bottom-0">
                    <tr><td colspan="7" class="text-center">Memuat data...</td></tr>
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

<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>

<script>
    const api_get_cuti = 'app/controller/get-data/get-pengajuan-cuti.php';
    const api_act_cuti = 'app/controller/act-pengajuan-cuti.php';

    document.addEventListener('DOMContentLoaded', () => {
        loadTableCuti();
        loadAllKaryawan(); // Panggil fungsi dropdown karyawan saat halaman dimuat
    });

    async function loadTableCuti() {
        try {
            const res = await fetch(api_get_cuti + '?_=' + new Date().getTime());
            document.getElementById('tbody-cuti').innerHTML = await res.text();
        } catch (e) {
            document.getElementById('tbody-cuti').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>';
        }
    }

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

    async function updateStatus(id, newStatus) {
        try {
            const response = await fetch(`${api_act_cuti}?action=update-status&id=${id}&status=${newStatus}`);
            if (!response.ok) throw new Error(`Server merespon ${response.status}`);
            
            const result = await response.json();
            if (result.status === 'success') {
                loadTableCuti(); 
            } else {
                alert("Gagal: " + result.message);
            }
        } catch (e) { 
            alert("Terjadi kesalahan sistem.");
        }
    }

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
            } catch(e) {
                alert("Terjadi kesalahan saat menghapus data.");
            }
        }
    }
</script>