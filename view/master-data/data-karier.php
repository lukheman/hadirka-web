<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="fw-bold">
            <span class="text-muted fw-light">Data Karyawan /</span> Karier
        </h4>

        <div class="pb-3">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKarier">
                <i class="fa fa-plus"></i> Tambah Karier
            </button>
        </div>

        <div class="card">
            <h5 class="card-header">Data Karier</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered MyTableKarier">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Site</th>
                                <th class="text-center">Departemen</th>
                                <th class="text-center">Jabatan</th>
                                <th class="text-center">Gaji Pokok</th>
                                <th class="text-center">Tunjangan Hidup</th>
                                <th class="text-center">Tunjangan Daerah</th>
                                <th class="text-center">Tunjangan Makan</th>
                                <th class="text-center">Tunjangan Skill</th>
                                <th class="text-center">OT Pertama</th>
                                <th class="text-center">OT Kedua</th>
                                <th class="text-center">SPL</th>
                                <th class="text-center">OT1 SPL</th>
                                <th class="text-center">OT2 SPL</th>
                                <th class="text-center">Pot Izin</th>
                                <th class="text-center">Pot Mangkir</th>
                                <th class="text-center">Tgl Mulai</th>
                                <th class="text-center">Tgl Selesai</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKarier"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambahKarier">
        <div class="modal-dialog modal-xl"> <!-- modal lebih lebar -->
            <form id="formTambahKarier" class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Tambah Karier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Site -->
                        <div class="col-md-3">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <option value="">-- Pilih Site --</option>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch_assoc()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Departemen -->
                        <div class="col-md-3">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-3">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Gaji Pokok -->
                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="number" name="gaji_pokok" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>


                        <!-- Data Tunjagan  -->
                        <div class="col-md-3">
                            <label>Tunjangan Daerah</label>
                            <input type="number" name="t_daerah" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Tunjangan Hidup</label>
                            <input type="number" name="t_hidup" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Tunjangan Skill</label>
                            <input type="number" name="t_skil" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Tunjangan Makan</label>
                            <input type="number" name="t_makan" class="form-control" required>
                        </div>

                        <!-- OT Pertama -->
                        <div class="col-md-3">
                            <label>OT Pertama (8 Jam)</label>
                            <input type="number" name="ot_pertama" class="form-control">
                        </div>

                        <!-- OT Kedua -->
                        <div class="col-md-3">
                            <label>OT Kedua (9 - Dst)</label>
                            <input type="number" name="ot_kedua" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>SPL</label>
                            <input type="number" name="spl" class="form-control">
                        </div>

                        <!-- OT1 SPL -->
                        <div class="col-md-3">
                            <label>OT1 SPL</label>
                            <input type="number" name="ot1_spl" class="form-control">
                        </div>

                        <!-- OT2 SPL -->
                        <div class="col-md-4">
                            <label>OT2 SPL</label>
                            <input type="number" name="ot2_spl" class="form-control">
                        </div>

                        <!-- Pot Izin -->
                        <div class="col-md-4">
                            <label>Potongan Izin</label>
                            <input type="number" name="pot_izin" class="form-control">
                        </div>

                        <!-- Pot Mangkir -->
                        <div class="col-md-4">
                            <label>Potongan Mangkir</label>
                            <input type="number" name="pot_mangkir" class="form-control">
                        </div>

                        <!-- Tgl Mulai -->
                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>

                        <!-- Tgl Selesai -->
                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditKarier">
        <div class="modal-dialog modal-xl"> <!-- ukuran sama seperti modal tambah -->
            <form id="formEditKarier" class="modal-content">
                <input type="hidden" name="id_karier">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Edit Karier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Site -->
                        <div class="col-md-3">
                            <label>Site</label>
                            <select name="id_site" class="form-control" required>
                                <?php
                                $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                                while ($s = $sites->fetch_assoc()) {
                                    echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Departemen -->
                        <div class="col-md-3">
                            <label>Departemen</label>
                            <select name="id_departemen" class="form-control" required>
                                <?php
                                $departemens = $koneksi->query("SELECT * FROM tb_departemen ORDER BY nama_departemen ASC");
                                while ($d = $departemens->fetch_assoc()) {
                                    echo "<option value='{$d['id_departemen']}'>{$d['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-3">
                            <label>Jabatan</label>
                            <select name="id_jabatan" class="form-control" required>
                                <?php
                                $jabatans = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY nama_jabatan ASC");
                                while ($j = $jabatans->fetch_assoc()) {
                                    echo "<option value='{$j['id_jabatan']}'>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Gaji Basic -->
                        <div class="col-md-3">
                            <label>Gaji Basic</label>
                            <input type="number" name="gaji_pokok" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <b class="text-secondary">Gaji Pokok Dan Tunjangan Tetap</b>
                            <hr>
                        </div>

                        <!-- Tunjangan -->
                        <div class="col-md-3">
                            <label>Tunjangan Daerah</label>
                            <input type="number" name="t_daerah" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Tunjangan Hidup</label>
                            <input type="number" name="t_hidup" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Tunjangan Skill</label>
                            <input type="number" name="t_skil" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Tunjangan Makan</label>
                            <input type="number" name="t_makan" class="form-control" required>
                        </div>

                        <!-- OT -->
                        <div class="col-md-3">
                            <label>OT Pertama (8 Jam)</label>
                            <input type="number" name="ot_pertama" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>OT Kedua (9 - Dst)</label>
                            <input type="number" name="ot_kedua" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>SPL</label>
                            <input type="number" name="spl" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>OT1 SPL</label>
                            <input type="number" name="ot1_spl" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>OT2 SPL</label>
                            <input type="number" name="ot2_spl" class="form-control">
                        </div>

                        <!-- Potongan -->
                        <div class="col-md-4">
                            <label>Potongan Izin</label>
                            <input type="number" name="pot_izin" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Potongan Mangkir</label>
                            <input type="number" name="pot_mangkir" class="form-control">
                        </div>

                        <!-- Tanggal -->
                        <div class="col-md-6">
                            <label>Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label>Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>



</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const karyawanId = "<?= $_GET['id'] ?>";
        const tbody = document.getElementById("tbodyKarier");

        // --- LOAD DATA ---
        function loadKarier() {
            fetch("app/controller/get-data/get-karier.php?id=" + karyawanId)
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTableKarier")) {
                        $(".MyTableKarier").DataTable().destroy();
                    }
                    tbody.innerHTML = html;
                    $(".MyTableKarier").DataTable({
                        responsive: true,
                        pageLength: 10
                    });
                });
        }

        // --- TAMBAH ---
        document.getElementById("formTambahKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "add");
            fd.append("id_karyawan", karyawanId);

            fetch("app/controller/act-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        loadKarier();
                        showToast("Karier berhasil ditambah!", "success");
                        // toastr.success("Berhasil menghapus!");
                        bootstrap.Modal.getInstance(document.getElementById("modalTambahKarier")).hide();
                        this.reset();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // --- EDIT ---
        document.getElementById("formEditKarier").addEventListener("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.append("action", "edit");
            fetch("app/controller/act-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status == "success") {
                        showToast("Karier berhasil diupdate!", "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalEditKarier")).hide();
                        loadKarier();
                        console.log("Response:", text); // lihat apa yang dikirim PHP
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // --- EDIT BUTTON HANDLER ---
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnEditKarier");
            if (!btn) return;

            const form = document.getElementById("formEditKarier");
            form.querySelector("input[name=id_karier]").value = btn.dataset.id;
            form.querySelector("select[name=id_site]").value = btn.dataset.id_site;
            form.querySelector("select[name=id_departemen]").value = btn.dataset.id_departemen;
            form.querySelector("select[name=id_jabatan]").value = btn.dataset.id_jabatan;
            form.querySelector("input[name=gaji_pokok]").value = btn.dataset.gaji_pokok;
            form.querySelector("input[name=t_hidup]").value = btn.dataset.t_hidup;
            form.querySelector("input[name=t_daerah]").value = btn.dataset.t_daerah;
            form.querySelector("input[name=t_makan]").value = btn.dataset.t_makan;
            form.querySelector("input[name=t_skil]").value = btn.dataset.t_skil;

            form.querySelector("input[name=ot_pertama]").value = btn.dataset.ot_pertama;
            form.querySelector("input[name=ot_kedua]").value = btn.dataset.ot_kedua;
            form.querySelector("input[name=spl]").value = btn.dataset.spl;
            form.querySelector("input[name=ot1_spl]").value = btn.dataset.ot1_spl;
            form.querySelector("input[name=ot2_spl]").value = btn.dataset.ot2_spl;
            form.querySelector("input[name=pot_izin]").value = btn.dataset.pot_izin;
            form.querySelector("input[name=pot_mangkir]").value = btn.dataset.pot_mangkir;
            form.querySelector("input[name=tgl_mulai]").value = btn.dataset.tgl_mulai;
            form.querySelector("input[name=tgl_selesai]").value = btn.dataset.tgl_selesai;

            new bootstrap.Modal(document.getElementById("modalEditKarier")).show();
        });

        // --- DELETE (opsional) ---
        document.addEventListener("click", e => {
            let btn = e.target.closest(".btnDeleteKarier");
            if (!btn) return;

            if (confirm("Yakin hapus karier?")) {
                fetch("app/controller/act-karier.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "action=delete&id_karier=" + btn.dataset.id
                    })
                    .then(res => res.json()) // <--- gunakan json, bukan text
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Berhasil menghapus!", "success");
                            loadKarier(); // reload table
                        } else {
                            showToast(res.msg, "error");
                        }
                    })
                    .catch(err => {
                        showToast("Terjadi kesalahan: " + err, "error");
                    });
            }
        });


        // --- Load awal ---
        loadKarier();

    });
</script>