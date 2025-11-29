<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Rapel
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRapel">
                <i class="fa fa-plus"></i> Tambah Rapel
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Database Rapel</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTable">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Karyawan</th>
                                <th>Tanggal Rapel</th>
                                <th>Nominal Rapel</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRapel"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahRapel">
        <div class="modal-dialog">
            <form id="formTambahRapel" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Rapel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Karyawan</label>
                    <select name="id_karyawan" class="form-control mb-3" required>
                        <option value="">Pilih Karyawan</option>
                        <?php
                        $sql = data_karyawan('nama_lengkap');
                        foreach($sql as $k){
                            echo "<option value='{$k['id_karyawan']}'>{$k['nik_karyawan']} - {$k['nama_lengkap']}</option>";
                        }
                        ?>
                    </select>

                    <label>Tanggal Rapel</label>
                    <input type="date" name="tgl_rapel" class="form-control mb-3" required>

                    <label>Nominal Rapel</label>
                    <input type="number" name="nominal_rapel" class="form-control mb-3" required>

                    <label>Keterangan</label>
                    <input type="text" name="ket_rapel" class="form-control">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditRapel">
        <div class="modal-dialog">
            <form id="formEditRapel" class="modal-content">
                <input type="hidden" name="id_rapel">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Rapel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Nama Karyawan</label>
                    <select name="id_karyawan" class="form-control mb-3" required>
                        <option value="">Pilih Karyawan</option>
                        <?php
                        $sql = data_karyawan('nik_karyawan');
                        foreach($sql as $k){
                            echo "<option value='{$k['id_karyawan']}'>{$k['nik_karyawan']} - {$k['nama_lengkap']}</option>";
                        }
                        ?>
                    </select>

                    <label>Tanggal Rapel</label>
                    <input type="date" name="tgl_rapel" class="form-control mb-3" required>

                    <label>Nominal Rapel</label>
                    <input type="number" name="nominal_rapel" class="form-control mb-3" required>

                    <label>Keterangan</label>
                    <input type="text" name="ket_rapel" class="form-control">
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
    const tbody = document.getElementById("tbodyRapel");
    const formTambah = document.getElementById("formTambahRapel");
    const formEdit = document.getElementById("formEditRapel");

    // Load Data
    function loadRapel(){
        fetch("app/controller/get-data/get-rapel.php")
            .then(res=>res.text())
            .then(html=>{
                if($.fn.DataTable.isDataTable(".MyTable")){
                    $(".MyTable").DataTable().destroy();
                }
                tbody.innerHTML = html;
                $(".MyTable").DataTable({ responsive:true, pageLength:10 });
            });
    }

    // Tambah
    formTambah.addEventListener("submit", e=>{
        e.preventDefault();
        let fd = new FormData(formTambah);
        fd.append("action","add");
        fetch("app/controller/act-rapel.php",{ method:"POST", body:fd })
            .then(res=>res.json())
            .then(res=>{
                if(res.status==="success"){
                    showToast("Berhasil menambah rapel!","success");
                    bootstrap.Modal.getInstance(document.getElementById("modalTambahRapel")).hide();
                    formTambah.reset();
                    loadRapel();
                }else showToast(res.msg,"error");
            });
    });

    // Edit
    formEdit.addEventListener("submit", e=>{
        e.preventDefault();
        let fd = new FormData(formEdit);
        fd.append("action","edit");
        fetch("app/controller/act-rapel.php",{ method:"POST", body:fd })
            .then(res=>res.json())
            .then(res=>{
                if(res.status==="success"){
                    showToast("Berhasil update rapel!","success");
                    bootstrap.Modal.getInstance(document.getElementById("modalEditRapel")).hide();
                    loadRapel();
                }else showToast(res.msg,"error");
            });
    });

    // Tombol Edit & Delete
    document.addEventListener("click", e=>{
        if(e.target.classList.contains("btnEdit")){
            const btn = e.target;
            formEdit.querySelector("input[name=id_rapel]").value = btn.dataset.id;
            formEdit.querySelector("input[name=tgl_rapel]").value = btn.dataset.tgl;
            formEdit.querySelector("input[name=nominal_rapel]").value = btn.dataset.nominal;
            formEdit.querySelector("input[name=ket_rapel]").value = btn.dataset.ket;
            new bootstrap.Modal(document.getElementById("modalEditRapel")).show();
        }

        if(e.target.classList.contains("btnDelete")){
            const id = e.target.dataset.id;
            if(confirm("Yakin menghapus rapel?")){
                fetch("app/controller/act-rapel.php",{
                    method:"POST",
                    headers:{ "Content-Type":"application/x-www-form-urlencoded" },
                    body:"action=delete&id_rapel="+id
                })
                .then(res=>res.json())
                .then(res=>{
                    if(res.status==="success"){
                        showToast("Berhasil menghapus rapel!","success");
                        loadRapel();
                    }else showToast(res.msg,"error");
                });
            }
        }
    });

    loadRapel();
});
</script>
