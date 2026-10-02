<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold">Manajemen Pengumuman</h5>
            <button class="btn btn-primary shadow" onclick="addPengumuman()">
                <i class="bx bx-plus me-1"></i> Buat Pengumuman
            </button>
        </div>
        <div class="card-body mt-4">
            <div class="row" id="container-pengumuman">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPengumuman" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formPengumuman" class="modal-content">
            <input type="hidden" name="id" id="id_pengumuman">

            <input type="hidden" name="warna_kode" id="warna_kode_hex" value="#1D5C96">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalTitle">Form Pengumuman</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul</label>
                    <input type="text" name="judul" id="judul" class="form-control" placeholder="Input judul..." required>
                </div>

                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label class="form-label fw-bold">Pilih Ikon</label>
                        <input type="hidden" name="ikon_nama" id="ikon_nama" value="notifications">
                        <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light" style="max-height: 120px; overflow-y: auto;">
                            <?php
                            $icons = ['notifications', 'check_circle', 'info', 'warning', 'attach_money', 'campaign', 'error', 'verified', 'event', 'engineering'];
                            foreach ($icons as $icon) : ?>
                                <div class="icon-option" onclick="selectIcon('<?= $icon ?>', this)" data-icon="<?= $icon ?>">
                                    <span class="material-symbols-rounded"><?= $icon ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label fw-bold">Tema Warna</label>
                        <select id="select_warna_tampilan" class="form-select" onchange="updateHexColor(this)">
                            <option value="primary" data-hex="#1D5C96">Biru IPM (Primary)</option>
                            <option value="success" data-hex="#71dd37">Hijau (Success)</option>
                            <option value="danger" data-hex="#ff3e1d">Merah (Danger)</option>
                            <option value="warning" data-hex="#F2921D">Oranye (Aksen)</option>
                            <option value="info" data-hex="#2E7BB8">Biru Terang (Info)</option>
                            <option value="dark" data-hex="#123F68">Biru Gelap (Dark)</option>
                        </select>
                        <div class="mt-3 d-flex align-items-center p-2 border rounded">
                            <small class="text-muted me-2">Preview:</small>
                            <div id="color-preview" style="width: 100%; height: 20px; border-radius: 4px; background: #1D5C96; border: 1px solid #ddd;"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Isi Pengumuman</label>
                    <textarea name="detail_konten" id="detail_konten" class="form-control" rows="4" placeholder="Tulis isi pengumuman di sini..." required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary shadow">Simpan Pengumuman</button>
            </div>
        </form>
    </div>
</div>

<style>
    .icon-option {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.2s;
    }

    .icon-option:hover {
        border-color: #1D5C96;
        color: #1D5C96;
    }

    .icon-option.active {
        background: #1D5C96;
        color: white;
        border-color: #1D5C96;
        box-shadow: 0 2px 4px rgba(29, 92, 150, 0.4);
    }

    .material-symbols-rounded {
        font-size: 22px;
    }

    .dropdown-menu {
        margin: 0 !important;
    }
</style>

<script>
    let myModal;
    const api_act = 'app/controller/act-pengumuman.php';
    const api_get = 'app/controller/get-data/get-pengumuman.php';

    document.addEventListener('DOMContentLoaded', () => {
        myModal = new bootstrap.Modal(document.getElementById('modalPengumuman'));
        loadData();
    });

    // Sinkronisasi Dropdown Label ke Kode HEX
    function updateHexColor(el) {
        const hex = el.options[el.selectedIndex].getAttribute('data-hex');
        document.getElementById('warna_kode_hex').value = hex;
        document.getElementById('color-preview').style.background = hex;
    }

    function selectIcon(name, el) {
        document.getElementById('ikon_nama').value = name;
        document.querySelectorAll('.icon-option').forEach(i => i.classList.remove('active'));
        if (el) el.classList.add('active');
    }

    async function loadData() {
        try {
            const res = await fetch(api_get + '?_=' + new Date().getTime());
            document.getElementById('container-pengumuman').innerHTML = await res.text();
        } catch (e) {
            console.error("Load failed");
        }
    }

    function addPengumuman() {
    document.getElementById('formPengumuman').reset();
    document.getElementById('id_pengumuman').value = '';
    
    // Tambahkan baris ini untuk memastikan textarea bersih
    document.getElementById('detail_konten').value = ''; 
    
    document.getElementById('modalTitle').innerText = 'Buat Pengumuman Baru';
    
    // Reset Visuals
    document.getElementById('select_warna_tampilan').selectedIndex = 0;
    updateHexColor(document.getElementById('select_warna_tampilan'));
    selectIcon('notifications', document.querySelector('[data-icon="notifications"]'));
    myModal.show();
}

   async function editPengumuman(id) {
    const res = await fetch(`${api_act}?action=get-single&id=${id}`);
    const data = await res.json();

    document.getElementById('id_pengumuman').value = data.id;
    document.getElementById('judul').value = data.judul;
    
    // Pastikan ini mengisi ID detail_konten yang baru
    // Kita gunakan data.detail_konten (atau data.sub_judul jika salah satunya kosong)
    document.getElementById('detail_konten').value = data.detail_konten || data.sub_judul;
    
    document.getElementById('warna_kode_hex').value = data.warna_kode;
    document.getElementById('color-preview').style.background = data.warna_kode;

    // Cocokkan Dropdown Warna
    const selectWarna = document.getElementById('select_warna_tampilan');
    for (let i = 0; i < selectWarna.options.length; i++) {
        if (selectWarna.options[i].getAttribute('data-hex').toLowerCase() === data.warna_kode.toLowerCase()) {
            selectWarna.selectedIndex = i;
            break;
        }
    }

    // Aktifkan Ikon yang sesuai
    const targetIcon = document.querySelector(`[data-icon="${data.ikon_nama}"]`);
    selectIcon(data.ikon_nama, targetIcon);
    
    document.getElementById('modalTitle').innerText = 'Edit Pengumuman';
    myModal.show();
}


    async function deleteData(id) {
        if (await confirmDialog("Apakah Anda yakin ingin menghapus pengumuman ini?")) {
            fetch(`${api_act}?action=delete&id=${id}`)
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success') {
                        showToast("Pengumuman berhasil dihapus", "warning");
                        loadData(); // Memuat ulang daftar pengumuman
                    } else {
                        showToast("Gagal menghapus data", "error");
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    showToast("Terjadi kesalahan jaringan", "error");
                });
        }
    }

    document.getElementById('formPengumuman').onsubmit = async (e) => {
        e.preventDefault();
        const res = await fetch(`${api_act}?action=save`, {
            method: 'POST',
            body: new FormData(e.target)
        });
        const result = await res.json();
        if (result.status === 'success') {
            myModal.hide();
            loadData();
            showToast("Data berhasil diperbarui", "success");
        }
    };
</script>