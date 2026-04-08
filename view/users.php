<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> User
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser" id="btnOpenAdd">
                <i class="fa fa-plus"></i> Tambah User
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data User</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableUser" width="100%">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Foto</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Site</th>
                                <th>Level</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyUser"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahUser">
    <div class="modal-dialog">
        <form id="formTambahUser" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Username</label>
                <input type="text" name="username" class="form-control mb-2" required>

                <label>Password</label>
                <div class="input-group input-group-merge mb-2">
                    <input type="password" name="password" id="passwordAdd" class="form-control" required placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;">
                    <span class="input-group-text cursor-pointer toggle-password" data-target="#passwordAdd">
                        <i class="bx bx-hide"></i>
                    </span>
                </div>

                <label>Nama Lengkap</label>
                <input type="text" name="nama_user" class="form-control mb-2" required>

                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control mb-2">

                <label>Site</label>
                <select name="site" class="form-control mb-2" required>
                    <option value="">Pilih Site</option>
                    <option value="0">Master</option>
                    <?php
                    if (function_exists('data_site')) {
                        foreach (data_site() as $s) {
                            echo '<option value="' . htmlspecialchars($s['id_site']) . '">' . htmlspecialchars($s['nama_site']) . '</option>';
                        }
                    }
                    ?>
                </select>

                <label>Level</label>
                <select name="level" class="form-select mb-2" required>
                    <option value="0">Admin</option>
                    <option value="1">Direktur</option>
                    <option value="2">HRD</option>
                    <option value="3">Safety</option>
                    <option value="4">User Biasa</option>
                </select>

                <label>Foto Profil</label>
                <input type="file" name="foto" id="fotoAdd" class="form-control">
                <div id="fotoPreviewAdd" class="mt-2"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditUser">
    <div class="modal-dialog">
        <form id="formEditUser" class="modal-content" enctype="multipart/form-data">
            <input type="hidden" name="id_user">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Username</label>
                <input type="text" name="username" class="form-control mb-2" required>

                <label>Password <small>(kosong = tidak diubah)</small></label>
                <input type="password" name="password" class="form-control mb-2">

                <label>Nama Lengkap</label>
                <input type="text" name="nama_user" class="form-control mb-2" required>

                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control mb-2">

                <label>Site</label>
                <select name="site" class="form-control mb-2" required>
                    <option value="" selected>Pilih Site</option>
                    <option value="0">Master</option>
                    <?php
                    if (function_exists('data_site')) {
                        foreach (data_site() as $s) {
                            echo '<option value="' . htmlspecialchars($s['id_site']) . '">' . htmlspecialchars($s['nama_site']) . '</option>';
                        }
                    }
                    ?>
                </select>

                <label>Level</label>
                <select name="level" class="form-select mb-2">
                    <option value="0">Admin</option>
                    <option value="1">Direktur</option>
                    <option value="2">HRD</option>
                    <option value="3">Safety</option>
                    <option value="4">User Biasa</option>
                </select>

                <label>Foto Profil <small>(pilih jika ingin ganti)</small></label>
                <input type="file" name="foto" id="fotoEdit" class="form-control">
                <div id="fotoPreviewEdit" class="mt-2"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tbody = document.getElementById("tbodyUser");
        const formTambah = document.getElementById("formTambahUser");
        const formEdit = document.getElementById("formEditUser");

        // Load users -> get-row HTML
        async function loadUsers() {
            const res = await fetch('app/controller/get-data/get-user.php');
            const html = await res.text();

            // destroy datatable if exists
            if ($.fn.DataTable.isDataTable(".MyTableUser")) {
                $(".MyTableUser").DataTable().destroy();
            }

            tbody.innerHTML = html;

            $(".MyTableUser").DataTable({
                responsive: true,
                pageLength: 10
            });
        }

        loadUsers();

        // Preview Add
        document.getElementById('fotoAdd').addEventListener('change', function(e) {
            const f = e.target.files[0];
            if (!f) {
                document.getElementById('fotoPreviewAdd').innerHTML = '';
                return;
            }
            const r = new FileReader();
            r.onload = ev => document.getElementById('fotoPreviewAdd').innerHTML = `<img src="${ev.target.result}" width="80" class="rounded-circle">`;
            r.readAsDataURL(f);
        });

        // Preview Edit
        document.getElementById('fotoEdit').addEventListener('change', function(e) {
            const f = e.target.files[0];
            if (!f) {
                document.getElementById('fotoPreviewEdit').innerHTML = '';
                return;
            }
            const r = new FileReader();
            r.onload = ev => document.getElementById('fotoPreviewEdit').innerHTML = `<img src="${ev.target.result}" width="80" class="rounded-circle">`;
            r.readAsDataURL(f);
        });

        // Submit Add
        formTambah.addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(formTambah);
            fd.append('action', 'add');

            const res = await fetch('app/controller/act-user.php', {
                method: 'POST',
                body: fd
            });
            const json = await res.json();

            if (json.status === 'success') {
                alert('User berhasil ditambah');
                bootstrap.Modal.getInstance(document.getElementById('modalTambahUser')).hide();
                formTambah.reset();
                document.getElementById('fotoPreviewAdd').innerHTML = '';
                loadUsers();
            } else {
                alert(json.msg || 'Gagal tambah user');
            }
        });

        // Submit Edit
        formEdit.addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(formEdit);
            fd.append('action', 'edit');

            const res = await fetch('app/controller/act-user.php', {
                method: 'POST',
                body: fd
            });
            const json = await res.json();

            if (json.status === 'success') {
                alert('User berhasil diupdate');
                bootstrap.Modal.getInstance(document.getElementById('modalEditUser')).hide();
                loadUsers();
            } else {
                alert(json.msg || 'Gagal update user');
            }
        });

        // delegate edit & delete clicks (rows generated by get-user.php)
        document.addEventListener('click', async e => {
            // Edit
            if (e.target.classList.contains('btnEditUser')) {
                const btn = e.target;
                const id = btn.dataset.id;
                // populate edit form from data attributes
                formEdit.querySelector('input[name=id_user]').value = id;
                formEdit.querySelector('input[name=username]').value = btn.dataset.username || '';
                formEdit.querySelector('input[name=nama_user]').value = btn.dataset.nama || '';
                formEdit.querySelector('input[name=jabatan]').value = btn.dataset.jabatan || '';
                formEdit.querySelector('select[name=site]').value = btn.dataset.site || '';
                formEdit.querySelector('select[name=level]').value = btn.dataset.level || 'user';
                if (btn.dataset.fotoUrl) {
                    document.getElementById('fotoPreviewEdit').innerHTML = `<img src="${btn.dataset.fotoUrl}" width="80" class="rounded-circle">`;
                } else {
                    document.getElementById('fotoPreviewEdit').innerHTML = '';
                }
                new bootstrap.Modal(document.getElementById('modalEditUser')).show();
            }

            // Delete
            if (e.target.classList.contains('btnDeleteUser')) {
                const id = e.target.dataset.id;
                if (!confirm('Yakin hapus user?')) return;

                const fd = new URLSearchParams();
                fd.append('action', 'delete');
                fd.append('id_user', id);

                const res = await fetch('app/controller/act-user.php', {
                    method: 'POST',
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: fd
                });
                const json = await res.json();
                if (json.status === 'success') {
                    alert('User dihapus');
                    loadUsers();
                } else {
                    alert(json.msg || 'Gagal menghapus');
                }
            }
        });

    });

    // Fungsi untuk Toggle Show/Hide Password
    document.querySelectorAll('.toggle-password').forEach(function(icon) {
        icon.addEventListener('click', function() {
            // Ambil ID target input dari atribut data-target
            const targetInputId = this.getAttribute('data-target');
            const inputField = document.querySelector(targetInputId);
            const eyeIcon = this.querySelector('i');

            // Jika tipenya password, ubah ke text (terlihat)
            if (inputField.type === 'password') {
                inputField.type = 'text';
                eyeIcon.classList.remove('bx-hide');
                eyeIcon.classList.add('bx-show');
            } else {
                // Jika tipenya text, kembalikan ke password (tersembunyi)
                inputField.type = 'password';
                eyeIcon.classList.remove('bx-show');
                eyeIcon.classList.add('bx-hide');
            }
        });
    });
</script>