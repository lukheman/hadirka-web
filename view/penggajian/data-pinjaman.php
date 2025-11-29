<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Pinjaman
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPinjaman">
                <i class="fa fa-plus"></i> Tambah Pinjaman
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Database Pinjaman Karyawan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTable">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Karyawan</th>
                                <th>Tanggal Pinjaman</th>
                                <th>Tanggal Bayar</th>
                                <th>Jumlah Pinjaman</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPinjaman"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahPinjaman">
        <div class="modal-dialog">
            <form id="formTambahPinjaman" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pinjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Karyawan</label>
                    <select name="id_karyawan" class="form-control mb-3" required>
                        <option value="">Pilih Karyawan</option>
                        <?php
                        $sql = data_karyawan('nama_lengkap');
                        foreach ($sql as $data) { ?>
                            <option value="<?= $data['id_karyawan'] ?>"><?= $data['nik_karyawan'].' - '.$data['nama_lengkap'] ?></option>
                        <?php } ?>
                    </select>

                    <label>Tanggal Pinjaman</label>
                    <input type="date" name="tgl_pinjaman" class="form-control mb-3" required>

                    <label>Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar_pinjaman" class="form-control mb-3">

                    <label>Jumlah Pinjaman</label>
                    <input type="number" name="nominal_pinjaman" class="form-control mb-3" required>

                    <label>Keterangan</label>
                    <input type="text" name="ket_pinjaman" class="form-control">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditPinjaman">
        <div class="modal-dialog">
            <form id="formEditPinjaman" class="modal-content">
                <input type="hidden" name="id_pinjaman">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pinjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Karyawan</label>
                    <select name="id_karyawan" class="form-control mb-3" required>
                        <option value="">Pilih Karyawan</option>
                        <?php
                        $sql = data_karyawan('nik_karyawan');
                        foreach ($sql as $data) { ?>
                            <option value="<?= $data['id_karyawan'] ?>"><?= $data['nik_karyawan'].' - '.$data['nama_lengkap'] ?></option>
                        <?php } ?>
                    </select>

                    <label>Tanggal Pinjaman</label>
                    <input type="date" name="tgl_pinjaman" class="form-control mb-3" required>

                    <label>Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar_pinjaman" class="form-control mb-3">

                    <label>Jumlah Pinjaman</label>
                    <input type="number" name="nominal_pinjaman" class="form-control mb-3" required>

                    <label>Keterangan</label>
                    <input type="text" name="ket_pinjaman" class="form-control">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("tbodyPinjaman");
    const formTambah = document.getElementById("formTambahPinjaman");
    const formEdit = document.getElementById("formEditPinjaman");

    // === LOAD DATA ===
    function loadPinjaman() {
        fetch("app/controller/get-data/get-pinjaman.php")
            .then(res => res.text())
            .then(html => {
                if ($.fn.DataTable.isDataTable(".MyTable")) {
                    $(".MyTable").DataTable().destroy();
                }
                tbody.innerHTML = html;
                $(".MyTable").DataTable({ responsive: true, pageLength: 10 });
            });
    }

    // === TAMBAH ===
    formTambah.addEventListener("submit", e => {
        e.preventDefault();
        let fd = new FormData(formTambah);
        fd.append("action", "add");

        fetch("app/controller/act-pinjaman.php", { method: "POST", body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status==="success"){
                    showToast("Berhasil menambah pinjaman!", "success");
                    bootstrap.Modal.getInstance(document.getElementById("modalTambahPinjaman")).hide();
                    formTambah.reset();
                    loadPinjaman();
                }else showToast(res.msg,"error");
            });
    });

    // === EDIT ===
    formEdit.addEventListener("submit", e => {
        e.preventDefault();
        let fd = new FormData(formEdit);
        fd.append("action","edit");

        fetch("app/controller/act-pinjaman.php",{method:"POST",body:fd})
        .then(res=>res.json())
        .then(res=>{
            if(res.status==="success"){
                showToast("Berhasil update pinjaman!","success");
                bootstrap.Modal.getInstance(document.getElementById("modalEditPinjaman")).hide();
                loadPinjaman();
            }else showToast(res.msg,"error");
        });
    });

    // === EDIT & DELETE BUTTON ===
    document.addEventListener("click", e => {
        // EDIT
        if(e.target.classList.contains("btnEdit")){
            const btn = e.target;
            formEdit.querySelector("input[name=id_pinjaman]").value = btn.dataset.id;
            formEdit.querySelector("select[name=id_karyawan]").value = btn.dataset.idkaryawan;
            formEdit.querySelector("input[name=tgl_pinjaman]").value = btn.dataset.tglpinjaman;
            formEdit.querySelector("input[name=tgl_bayar_pinjaman]").value = btn.dataset.tglbayar;
            formEdit.querySelector("input[name=nominal_pinjaman]").value = btn.dataset.pinjaman;
            formEdit.querySelector("input[name=ket_pinjaman]").value = btn.dataset.ket;
            new bootstrap.Modal(document.getElementById("modalEditPinjaman")).show();
        }

        // DELETE
        if(e.target.classList.contains("btnDelete")){
            const id = e.target.dataset.id;
            if(confirm("Yakin menghapus pinjaman?")){
                fetch("app/controller/act-pinjaman.php", {
                    method:"POST",
                    headers:{ "Content-Type":"application/x-www-form-urlencoded" },
                    body:"action=delete&id_pinjaman="+id
                })
                .then(res=>res.json())
                .then(res=>{
                    if(res.status==="success"){
                        showToast("Berhasil menghapus pinjaman!","success");
                        loadPinjaman();
                    }else showToast(res.msg,"error");
                });
            }
        }
    });

    loadPinjaman();
});
</script>
