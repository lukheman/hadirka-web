<style>
        /* sedikit styling untuk icon expand */
        td.dt-control {
            cursor: pointer;
        }

        .fade-in {
            animation: fadeIn 300ms ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* pastikan table responsive */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.2rem 0.6rem;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .info-table {
            width: 100%;
            margin-bottom: 0;
        }

        .info-table td {
            padding: 0.2rem 0 !important; /* Merapatkan jarak antar baris */
            vertical-align: top;
            color: #566a7f;
        }

        .info-table td:first-child {
            width: 35%; /* Proporsi ideal agar label tidak terpotong */
            font-weight: 600;
            white-space: nowrap; 
        }

        /* Class baru khusus untuk titik dua (:) */
        .info-table td.separator {
            width: 15px; 
            text-align: center;
            white-space: nowrap;
        }

        .info-table td:last-child {
            width: auto;
            word-break: break-word; /* Aman untuk email panjang tanpa merusak angka */
        }

        /* Untuk detail alamat */
        .col-md-4 p {
            white-space: normal;
            word-break: break-word;
            line-height: 1.6;
        }
    </style>
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y py-4">
            <h4 class="fw-bold mb-3">
                <span class="text-muted fw-light">Master Data /</span> Data Karyawan
            </h4>
            <div class="pb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
                            <i class="fa fa-plus"></i> Tambah Karyawan
                        </button>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#ModalImport" title="Klik untuk mengimpor data karyawan dari file Excel">
                            <i class="fa fa-upload"></i> Import Data
                        </button>
                        <button class="btn btn-sm btn-info" onclick="exportKaryawanCSV()" title="Export data ke Excel">
                            <i class="fa fa-file-export"></i> Export Data
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-2" style="max-width: 520px;">
                        <select id="filterSite" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site">
                            <option value="">-- Semua Site --</option>
                            <?php foreach (data_site() as $d) { ?>
                                <option value="<?= htmlspecialchars($d['id_site']) ?>"><?= htmlspecialchars($d['nama_site']) ?></option>
                            <?php } ?>
                        </select>

                        <select id="filterJabatan" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan jabatan">
                            <option value="">-- Semua Jabatan --</option>
                            <?php foreach (data_jabatan() as $d) {
                                echo "<option value='" . htmlspecialchars($d['id_jabatan']) . "'>" . htmlspecialchars($d['nama_jabatan']) . "</option>";
                            } ?>
                        </select>

                        <select id="filterStatus" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan status">
                            <option value="Semua">-- Semua Status --</option>
                            <option value="Aktif" selected>Karyawan Aktif</option>
                            <option value="Habis Kontrak">Habis Kontrak</option>
                            <option value="Pensiun">Pensiun</option>
                            <option value="PHK">PHK</option>
                            <option value="Resign">Resign</option>
                            <option value="Black List">Black List</option>
                        </select>

                        <select id="filterBulan" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter Bulan Habis Kontrak Tahun Sekarang">
                            <option value="">-- Semua Bulan --</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card position-relative">
                <h5 class="card-header">
                    <i class="fa fa-database"></i> Database Karyawan
                </h5>
                <div class="card-body">
                    <div class="text-center" id="loadingSpinner" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Memuat data...</span>
                        </div>
                        <p class="mt-2">Memuat data karyawan...</p>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table id="tableKaryawan" class="table table-hover table-bordered table-striped">
                            <thead class="table-info">
                                <tr>
                                    <th class="text-center"></th>
                                    <th class="text-center"><i class="fa fa-hashtag"></i> No</th>
                                    <th class="text-center"><i class="fa fa-image"></i> Foto</th>
                                    <th class="text-center"><i class="fa fa-user"></i> Nama Karyawan</th>
                                    <th class="text-center"><i class="fa fa-id-card"></i> NIK</th>
                                    <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                                    <th class="text-center"><i class="fa fa-users"></i> Status</th>
                                    <th class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>
    </div>

    <div class="modal fade" id="ModalImport" tabindex="-1" aria-labelledby="ModalImportLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formImport" class="modal-content" enctype="multipart/form-data">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title text-white" id="ModalImportLabel"><i class="fa fa-upload"></i> Upload Karyawan Excel</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="instruction-text mb-3">
                        Unggah file Excel/CSV sesuai format kolom untuk memperbarui database secara masal.
                    </p>
                    <label for="file_excel" class="form-label"><i class="fa fa-file-excel"></i> Upload File (XLS/XLSX)</label>
                    <input type="file" name="file_excel" id="file_excel" class="form-control" accept=".xls,.xlsx" required data-bs-toggle="tooltip" title="Pilih file Excel untuk import data karyawan">
                    <a href="assets/template-csv/Format_Impor_Database_Karyawan.xlsx" style="font-size: 2vh;">Download Template Format .xlsx</a>
                    <div id="progressWrapper" class="progress mt-2" style="display:none;height:20px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0%;">0%</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" type="submit" id="btnUpload" data-bs-toggle="tooltip" title="Klik untuk mengupload dan memproses file">
                        <i class="fa fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastAlert" class="toast text-bg-primary border-0" role="alert" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">Processing...</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahKaryawan" tabindex="-1">
        <div class="modal-dialog modal-xl ">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Tambah Karyawan</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formTambahKaryawan" enctype="multipart/form-data">
                    <div class="modal-body">
                        <h6>DATA PRIBADI</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label ">Foto Karyawan</label>
                                <input type="file" class="form-control" name="foto" accept="image/*">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                                <label class="form-label mt-3">NIK Karyawan</label>
                                <input type="text" class="form-control" name="nik_karyawan" required>
                                <label class="form-label mt-3">Jenis Kelamin</label>
                                <select class="form-control" name="jenis_kelamin">
                                    <option value="">-- Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <label class="form-label mt-3">Agama</label>
                                <select class="form-control" name="agama">
                                    <option value="">-- Pilih Agama --</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                                <label class="form-label mt-3">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir">
                                <label class="form-label mt-3">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tgl_lahir">
                                <label class="form-label mt-3">No. Telp</label>
                                <input type="text" class="form-control" name="no_telp">
                                <label class="form-label mt-3">Email Pribadi</label>
                                <input type="email" class="form-control" name="email_pribadi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label ">Nationality</label>
                                <input type="text" class="form-control" name="nationality">
                                <label class="form-label mt-3">NIK (KTP)</label>
                                <input type="text" class="form-control" name="nik_ktp">
                                <label class="form-label mt-3">No. KK</label>
                                <input type="text" class="form-control" name="kk_number">
                                <label class="form-label mt-3">NPWP</label>
                                <input type="text" class="form-control" name="npwp">
                                <label class="form-label mt-3">BPJS TK</label>
                                <input type="text" class="form-control" name="bpjs_tk">
                                <label class="form-label mt-3">BPJS KES</label>
                                <input type="text" class="form-control" name="bpjs_kes">
                                <label class="form-label mt-3">Status Pernikahan</label>
                                <select class="form-control" name="status_pernikahan">
                                    <option value="">-- Pilih --</option>
                                    <option value="Menikah">Menikah</option>
                                    <option value="Belum Menikah">Belum Menikah</option>
                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                    <option value="Cerai Mati">Cerai Mati</option>
                                </select>
                                <label class="form-label mt-3">Status PTKP</label>
                                <select class="form-control" name="status_ptkp">
                                    <option value="">-- Pilih --</option>
                                    <option value="TK/0">TK/0</option>
                                    <option value="TK/1">TK/1</option>
                                    <option value="TK/2">TK/2</option>
                                    <option value="TK/3">TK/3</option>
                                    <option value="K/0">K/0</option>
                                    <option value="K/1">K/1</option>
                                    <option value="K/2">K/2</option>
                                    <option value="K/3">K/3</option>
                                </select>
                                <label class="form-label mt-3">Nama Ibu Kandung</label>
                                <input type="text" class="form-control" name="nama_ibu">
                            </div>
                        </div>

                        <hr>
                        <h6>ALAMAT KTP</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat_ktp"></textarea>
                                <label class="form-label mt-3">Provinsi</label>
                                <input type="text" class="form-control" name="provinsi">
                                <label class="form-label mt-3">Kabupaten/Kota</label>
                                <input type="text" class="form-control" name="kabupaten">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" name="kecamatan">
                                <label class="form-label mt-3">Desa</label>
                                <input type="text" class="form-control" name="desa">
                                <label class="form-label mt-3">RT/RW</label>
                                <input type="text" class="form-control" name="rt_rw">
                            </div>
                        </div>

                        <hr>
                        <h6>PENDIDIKAN</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Pendidikan Terakhir</label>
                                <input type="text" class="form-control" name="last_education">
                                <label class="form-label mt-3">Jurusan</label>
                                <input type="text" class="form-control" name="major">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tingkat Pendidikan</label>
                                <input type="text" class="form-control" name="education_level">
                            </div>
                        </div>

                        <hr>
                        <h6>AKUN BANK</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Nama Bank</label>
                                <input type="text" class="form-control" name="nama_bank">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Atas Nama Rekening</label>
                                <input type="text" class="form-control" name="atas_nama_rek">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control" name="no_rek">
                        </div>

                        <hr>
                        <h6>PENEMPATAN KERJA</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site</label>
                                <select class="form-control" name="id_site" id="siteSelectAdd">
                                    <option value="">-- Pilih Site --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Departemen</label>
                                <select class="form-control" name="id_departemen" id="departemenSelectAdd">
                                    <option value="">-- Pilih Departemen --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jabatan</label>
                                <select class="form-control" name="id_jabatan" id="jabatanSelectAdd">
                                    <option value="">-- Pilih Jabatan --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label ">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tgl_mulai">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Tanggal Kontrak Selesai</label>
                            <input type="date" class="form-control" name="tgl_selesai">
                        </div>

                        <hr>
                        <h6>KONTAK DARURAT 1</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">NAMA KONTAK</label>
                                <input type="text" class="form-control" name="nama_kontak1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">HUBUNGAN</label>
                                <input type="text" class="form-control" name="hubungan1">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" name="no_telp_darurat1">
                        </div>

                        <hr>
                        <h6>KONTAK DARURAT 2</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">NAMA KONTAK</label>
                                <input type="text" class="form-control" name="nama_kontak2">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">HUBUNGAN</label>
                                <input type="text" class="form-control" name="hubungan2">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" name="no_telp_darurat2">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalEdit" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form id="formEdit" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Edit Data Karyawan</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_karyawan" id="edit_id_karyawan">
                    <h6>DATA PRIBADI</h6>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label>Foto Karyawan <small>(Kosongkan jika tidak diganti)</small></label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                            <div id="preview_foto_karyawan" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                            <label class="mt-3">NIK Karyawan</label>
                            <input type="text" name="nik_karyawan" id="edit_nik_karyawan" class="form-control" required>
                            <label class="mt-3">Jenis Kelamin</label>
                            <select class="form-control" name="jenis_kelamin" id="edit_jenis_kelamin">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <label class="mt-3">Agama</label>
                            <input type="text" id="edit_agama" name="agama" class="form-control">
                            <label class="mt-3">Tempat Lahir</label>
                            <input type="text" id="edit_tempat_lahir" name="tempat_lahir" class="form-control">
                            <label class="mt-3">Tanggal Lahir</label>
                            <input type="date" id="edit_tgl_lahir" name="tgl_lahir" class="form-control">
                            <label class="mt-3">Status Pernikahan</label>
                            <select id="edit_status_pernikahan" name="status_pernikahan" class="form-control">
                                <option value="">-- Pilih --</option>
                                <option value="Menikah">Menikah</option>
                                <option value="Belum Menikah">Belum Menikah</option>
                                <option value="Cerai Hidup">Cerai Hidup</option>
                                <option value="Cerai Mati">Cerai Mati</option>
                            </select>
                            <label class="mt-3">Status PTKP</label>
                            <select id="edit_status_ptkp" name="status_ptkp" class="form-control">
                                <option value="">-- Pilih --</option>
                                <option value="TK/0">TK/0</option>
                                <option value="TK/1">TK/1</option>
                                <option value="TK/2">TK/2</option>
                                <option value="TK/3">TK/3</option>
                                <option value="K/0">K/0</option>
                                <option value="K/1">K/1</option>
                                <option value="K/2">K/2</option>
                                <option value="K/3">K/3</option>
                            </select>
                            <label class="mt-3">Nama Ibu Kandung</label>
                            <input type="text" id="edit_nama_ibu" name="nama_ibu" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Nationality</label>
                            <input type="text" id="edit_nationality" name="nationality" class="form-control">
                            <label class="mt-3">NIK (KTP)</label>
                            <input type="text" id="edit_nik_ktp" name="nik_ktp" class="form-control">
                            <label class="mt-3">No. KK</label>
                            <input type="text" id="edit_kk_number" name="kk_number" class="form-control">
                            <label class="mt-3">NPWP</label>
                            <input type="text" id="edit_npwp" name="npwp" class="form-control">
                            <label class="mt-3">BPJS TK</label>
                            <input type="text" id="edit_bpjs_tk" name="bpjs_tk" class="form-control">
                            <label class="mt-3">BPJS Kesehatan</label>
                            <input type="text" id="edit_bpjs_kes" name="bpjs_kes" class="form-control">
                            <label class="mt-3">Email Pribadi</label>
                            <input type="email" id="edit_email_pribadi" name="email_pribadi" class="form-control">
                            <label class="mt-3">No Telp</label>
                            <input type="text" id="edit_no_telp" name="no_telp" class="form-control">
                        </div>
                    </div>
                    <hr>

                    <h6>ALAMAT KTP</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Alamat</label>
                            <textarea id="edit_alamat_ktp" name="alamat_ktp" class="form-control"></textarea>
                            <label class="mt-3">Provinsi</label>
                            <input type="text" id="edit_provinsi" name="provinsi" class="form-control">
                            <label class="mt-3">Kabupaten/Kota</label>
                            <input type="text" id="edit_kabupaten" name="kabupaten" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Kecamatan</label>
                            <input type="text" id="edit_kecamatan" name="kecamatan" class="form-control">
                            <label class="mt-3">Desa</label>
                            <input type="text" id="edit_desa" name="desa" class="form-control">
                            <label class="mt-3">RT / RW</label>
                            <input type="text" id="edit_rt_rw" name="rt_rw" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h6>PENDIDIKAN</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="mt-3">Pendidikan Terakhir</label>
                            <input type="text" id="edit_last_education" name="last_education" class="form-control">
                            <label class="mt-3">Jurusan</label>
                            <input type="text" id="edit_major" name="major" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="mt-3">Tingkat Pendidikan</label>
                            <input type="text" id="edit_education_level" name="education_level" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h6>AKUN BANK</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" class="form-control" name="nama_bank" id="edit_nama_bank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Atas Nama Rekening</label>
                            <input type="text" class="form-control" name="atas_nama_rek" id="edit_atas_nama_rek">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Nomor Rekening</label>
                        <input type="text" class="form-control" name="no_rek" id="edit_no_rek">
                    </div>

                    <hr>
                    <h6>Penempatan Kerja</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="mt-3">Site</label>
                            <select id="edit_id_site" name="id_site" class="form-control">
                                <option value="">-- Pilih Site --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="mt-3">Departemen</label>
                            <select id="edit_id_departemen" name="id_departemen" class="form-control">
                                <option value="">-- Pilih Departemen --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="mt-3">Jabatan</label>
                            <select id="edit_id_jabatan" name="id_jabatan" class="form-control">
                                <option value="">-- Pilih Jabatan --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" id="edit_tgl_mulai"
                                name="tgl_mulai" class="form-control">
                        </div>
                        <div class="col-12 mt-3">
                            <label>Tanggal Kontrak Selesai</label>
                            <input type="date" id="edit_tgl_selesai" name="tgl_selesai" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h6>KONTAK DARURAT 1</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nama Kontak</label>
                            <input type="text" class="form-control" name="nama_kontak1" id="edit_nama_kontak1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hubungan</label>
                            <input type="text" class="form-control" name="hubungan1" id="edit_hubungan1">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" name="no_telp_darurat1" id="edit_no_telp_darurat1">
                    </div>

                    <hr>
                    <h6>KONTAK DARURAT 2</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nama Kontak</label>
                            <input type="text" class="form-control" name="nama_kontak2" id="edit_nama_kontak2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hubungan</label>
                            <input type="text" class="form-control" name="hubungan2" id="edit_hubungan2">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" name="no_telp_darurat2" id="edit_no_telp_darurat2">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div class="modal fade" id="ModalEliminasi" tabindex="-1">
        <div class="modal-dialog">
            <form id="formEliminasi" class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Eliminasi Karyawan</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Perhatian:</strong> Anda akan mengeluarkan karyawan ini dari daftar karyawan aktif.
                    </div>
                    <div class="form-group mb-3">
                        <label>NAMA LENGKAP</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="elim_nama_lengkap" required readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label>NIK KARYAWAN</label>
                        <input type="text" name="nik_karyawan" id="elim_nik_karyawan" class="form-control" required readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label>TANGGAL KELUAR </label>
                        <input type="date" name="tgl_keluar" id="elim_tgl_keluar" class="form-control" value="<?= date('Y-m-d')  ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">JENIS KELUAR</label>
                        <select class="form-control" name="id_jenis_keluar" id="elim_jenis_keluar" required>
                            <option value="">-- Pilih Jenis Keluar --</option>
                            <option value="1">Habis Kontrak</option>
                            <option value="2">Pensiun</option>
                            <option value="3">PHK</option>
                            <option value="4">Resign</option>
                            <option value="5">Black List</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="alasan_keluar">KETERANGAN (OPSIONAL)</label>
                        <textarea name="alasan_keluar" id="elim_alasan_keluar" class="form-control" cols="2" placeholder="Contoh: Habis kontrak / Mengundurkan diri"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Simpan & Eliminasi</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalLihatFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 p-0 justify-content-end">
                    <button type="button" class="btn-close btn-close-white fs-4" data-bs-dismiss="modal" aria-label="Close" style="margin-right: -20px; margin-top: -20px;"></button>
                </div>
                <div class="modal-body p-0 text-center">
                    <img id="imgPreviewBesar" src="" alt="Foto Karyawan" class="img-fluid rounded shadow" style="max-height: 80vh; object-fit: contain;">
                    <h5 id="namaFotoKaryawan" class="text-white mt-3 text-shadow"></h5>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Helper Function: Detail Expandable Row
        function formatDetail(detailHtml) {
            return `<div class="p-4 bg-light border-start border-primary border-4 rounded shadow-sm">${detailHtml}</div>`;
        }

        // 2. Helper Function: Toast Notifications
        function showToast(message, type = "success") {
            const toastEl = document.getElementById('toastAlert');
            const toastBody = document.getElementById('toastMessage');

            toastEl.className = `toast text-bg-${type} border-0`;
            toastBody.innerHTML = message;

            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        let tableKaryawan = null;

        $(document).ready(function() {
            // Init Tooltip
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(t => new bootstrap.Tooltip(t));


            // Get data untuk dropdown Site dan Jabatan
            function loadSiteJabatan(callback = null) {
                $.ajax({
                    url: "app/controller/get-data/get-site-departemen-jabatan.php",
                    type: "GET",
                    dataType: "json",
                    success: function(res) {

                        let siteOpt = '<option value="">-- Pilih Site --</option>';
                        let departemenOpt = '<option value="">-- Pilih Departemen --</option>';
                        let jabatanOpt = '<option value="">-- Pilih Jabatan --</option>';

                        res.site.forEach(s => {
                            siteOpt += `<option value="${s.id_site}">${s.nama_site}</option>`;
                        });

                        res.departemen.forEach(j => {
                            departemenOpt += `<option value="${j.id_departemen}">${j.nama_departemen}</option>`;
                        });

                        res.jabatan.forEach(j => {
                            jabatanOpt += `<option value="${j.id_jabatan}">${j.nama_jabatan}</option>`;
                        });

                        $("#siteSelectAdd").html(siteOpt);
                        $("#departemenSelectAdd").html(departemenOpt);
                        $("#jabatanSelectAdd").html(jabatanOpt);
                        $("#edit_id_site").html(siteOpt);
                        $("#edit_id_departemen").html(departemenOpt);
                        $("#edit_id_jabatan").html(jabatanOpt);

                        if (callback) callback();
                    }
                });
            }


            // Pemanggilan Load Site Jabatan
            $('#modalTambahKaryawan').on('shown.bs.modal', function() {
                loadSiteJabatan();
            });


            // 3. Inisialisasi DataTable
            tableKaryawan = $('#tableKaryawan').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "app/controller/get-data/get-karyawan.php",
                    type: "POST",
                    data: function(d) {
                        d.site = $("#filterSite").val();
                        d.jabatan = $("#filterJabatan").val();
                        d.status = $("#filterStatus").val();
                        d.bulan = $("#filterBulan").val();
                    },
                    dataSrc: "data"
                },
                columns: [{
                        className: 'dt-control text-center',
                        orderable: false,
                        data: null,
                        defaultContent: '<i class="fa fa-plus-circle text-primary"></i>'
                    },
                    {
                        data: "no",
                        className: "text-center"
                    },
                    {
                        data: "foto",
                        className: "text-center",
                        orderable: false
                    },
                    {
                        data: "nama"
                    },
                    {
                        data: "nik",
                        className: "text-center"
                    },
                    {
                        data: "jabatan",
                        className: "text-center"
                    },
                    {
                        data: "status_karyawan",
                        className: "text-center"
                    },
                    {
                        data: "action",
                        orderable: false,
                        className: "text-center"
                    }
                ],
                order: [
                    [1, "asc"]
                ],
                language: {
                    // url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });

            // Sembunyikan kolom action jika bukan karyawan aktif
            $('#filterStatus').on('change', function() {
                let status = $(this).val();

                if (status === 'Aktif' || status === 'Semua') {
                    tableKaryawan.column(6).visible(false);
                } else {
                    tableKaryawan.column(6).visible(true);
                }

                tableKaryawan.ajax.reload();
            });


            // 4. Handle Expand/Collapse
            $('#tableKaryawan tbody').on('click', 'td.dt-control', function() {
                let tr = $(this).closest('tr');
                let row = tableKaryawan.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    $(this).html('<i class="fa fa-plus-circle text-primary"></i>');
                } else {
                    row.child(formatDetail(row.data().detail)).show();
                    $(this).html('<i class="fa fa-minus-circle text-danger"></i>');
                }
            });

            // 5. Filter Reload
            $("#filterSite, #filterJabatan, #filterStatus, #filterBulan").on("change", function() {
                tableKaryawan.ajax.reload();
            });

            // 6. ACTION: TAMBAH DATA
            $("#formTambahKaryawan").on("submit", function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append("action", "add");

                $.ajax({
                    url: "app/controller/act-karyawan.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status === "ok") {
                            $("#modalTambahKaryawan").modal("hide");
                            $("#formTambahKaryawan")[0].reset();
                            tableKaryawan.ajax.reload(null, false);
                            showToast("Data karyawan berhasil ditambahkan!");
                        } else {
                            showToast("Gagal: " + (res.msg || "Terjadi kesalahan"), "danger");
                        }
                    }
                });
            });

            // 7. ACTION: GET DATA UNTUK EDIT
            $(document).on("click", ".btnEdit", function() {
                let id = $(this).data("id");
                $.ajax({
                    url: "app/controller/act-karyawan.php",
                    type: "POST",
                    data: {
                        action: "get",
                        id_karyawan: id
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status === "ok") {
                            let d = res.data;
                            loadSiteJabatan(function() {
                                $("#edit_id_karyawan").val(d.id_karyawan);
                                $("#edit_nik_karyawan").val(res.data.nik_karyawan);
                                $("#edit_nama_lengkap").val(d.nama_lengkap);
                                $("#edit_jenis_kelamin").val(d.jenis_kelamin);
                                $("#edit_agama").val(d.agama);
                                $("#edit_tempat_lahir").val(d.tempat_lahir);
                                $("#edit_tgl_lahir").val(d.tgl_lahir);
                                $("#edit_status_pernikahan").val(d.status_pernikahan);
                                $("#edit_status_ptkp").val(d.status_ptkp);
                                $("#edit_nama_ibu").val(d.nama_ibu);
                                if (d.foto && d.foto !== "") {
                                    $("#preview_foto_karyawan").html(`<img src="app/uploads/profil/${d.foto}" width="80" height="80" class="rounded-circle shadow-sm" style="object-fit: cover;">`);
                                } else {
                                    $("#preview_foto_karyawan").html('');
                                }
                                $("#edit_nationality").val(d.nationality);
                                $("#edit_nik_ktp").val(d.nik_ktp);
                                $("#edit_kk_number").val(d.kk_number);
                                $("#edit_npwp").val(d.npwp);
                                $("#edit_bpjs_tk").val(d.bpjs_tk);
                                $("#edit_bpjs_kes").val(d.bpjs_kes);
                                $("#edit_email_pribadi").val(d.email_pribadi);
                                $("#edit_no_telp").val(d.no_telp);
                                $("#edit_alamat_ktp").val(d.alamat_ktp);
                                $("#edit_provinsi").val(d.provinsi);
                                $("#edit_kabupaten").val(d.kabupaten);
                                $("#edit_kecamatan").val(d.kecamatan);
                                $("#edit_desa").val(d.desa);
                                $("#edit_rt_rw").val(d.rt_rw);
                                $("#edit_last_education").val(d.last_education);
                                $("#edit_major").val(d.major);
                                $("#edit_education_level").val(d.education_level);
                                $("#edit_nama_bank").val(d.nama_bank);
                                $("#edit_atas_nama_rek").val(d.atas_nama_rek);
                                $("#edit_no_rek").val(d.no_rek);
                                $("#edit_id_site").val(d.id_site);
                                $("#edit_id_departemen").val(d.id_departemen);
                                $("#edit_id_jabatan").val(d.id_jabatan);
                                $("#edit_tgl_mulai").val(d.tgl_mulai);
                                $("#edit_tgl_selesai").val(d.tgl_selesai);
                                $("#edit_nama_kontak1").val(d.nama_kontak1);
                                $("#edit_hubungan1").val(d.hubungan1);
                                $("#edit_no_telp_darurat1").val(d.no_telp_darurat1);
                                $("#edit_nama_kontak2").val(d.nama_kontak2);
                                $("#edit_hubungan2").val(d.hubungan2);
                                $("#edit_no_telp_darurat2").val(d.no_telp_darurat2);
                                $("#ModalEdit").modal("show");
                            });
                        }
                    }
                });
            });

            // 8. ACTION: SIMPAN PERUBAHAN (EDIT)
            $("#formEdit").on("submit", function(e) {
                e.preventDefault();
                
                // Gunakan FormData agar file gambar ikut terkirim
                let formDataEdit = new FormData(this);
                formDataEdit.append("action", "edit");

                $.ajax({
                    url: "app/controller/act-karyawan.php",
                    type: "POST",
                    data: formDataEdit,
                    dataType: "json",
                    processData: false, // Wajib false untuk file
                    contentType: false, // Wajib false untuk file
                    success: function(res) {
                        if (res.status === "ok") {
                            $("#ModalEdit").modal("hide");
                            tableKaryawan.ajax.reload(null, false);
                            showToast("Data berhasil diperbarui!");
                        } else {
                            console.log(res);
                            showToast("Gagal: " + res.msg, "danger");
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        showToast("Terjadi kesalahan server saat edit data.", "danger");
                    }
                });
            });

            // 9. ACTION: GET DATA UNTUK ELIMINASI
            $(document).on("click", ".btnElimination", function() {
                let id = $(this).data("id");

                $.ajax({
                    url: "app/controller/act-karyawan.php",
                    type: "POST",
                    data: {
                        action: "get",
                        id_karyawan: id
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status === "ok") {
                            let d = res.data;

                            // Isi form eliminasi
                            $("#elim_nik_karyawan").val(d.nik_karyawan);
                            $("#elim_nama_lengkap").val(d.nama_lengkap);
                            $("#elim_tgl_keluar").val("<?= date('Y-m-d') ?>");
                            $("#elim_jenis_keluar").val(""); // Reset dropdown
                            $("#elim_alasan_keluar").val(""); // Reset textarea

                            // Tampilkan modal
                            $("#ModalEliminasi").modal("show");
                        } else {
                            showToast("Gagal mengambil data karyawan", "danger");
                        }
                    },
                    error: function() {
                        showToast("Terjadi kesalahan server", "danger");
                    }
                });
            });

            // 10. ACTION: SIMPAN ELIMINASI
            $("#formEliminasi").on("submit", function(e) {
                e.preventDefault();

                // Validasi form
                let id_jenis_keluar = $("#elim_jenis_keluar").val();
                let tgl_keluar = $("#elim_tgl_keluar").val();

                if (!id_jenis_keluar) {
                    showToast("Jenis keluar harus dipilih!", "warning");
                    return false;
                }

                if (!tgl_keluar) {
                    showToast("Tanggal keluar harus diisi!", "warning");
                    return false;
                }

                // Konfirmasi eliminasi
                let nama = $("#elim_nama_lengkap").val();
                if (!confirm(`Apakah Anda yakin ingin mengeliminasi karyawan:\n${nama}?\n\nProses ini akan mengubah status karyawan menjadi tidak aktif.`)) {
                    return false;
                }

                // Kirim data
                $.ajax({
                    url: "app/controller/act-eliminasi.php",
                    type: "POST",
                    data: $(this).serialize() + "&action=eliminasi",
                    dataType: "json",
                    success: function(res) {
                        if (res.status === "ok") {
                            $("#ModalEliminasi").modal("hide");
                            $("#formEliminasi")[0].reset();
                            tableKaryawan.ajax.reload(null, false);
                            showToast("✅ " + res.msg, "success");
                        } else {
                            showToast("❌ " + res.msg, "danger");
                        }
                    },
                    error: function(xhr) {
                        console.error("Error:", xhr.responseText);
                        showToast("Terjadi kesalahan server. Cek console untuk detail.", "danger");
                    }
                });
            });

            // 11. ACTION: DELETE DATA
            $(document).on("click", ".btnDelete", function() {
                let id = $(this).data("id");
                let nama = $(this).data("nama");

                if (confirm(`Hapus data karyawan: ${nama}?`)) {
                    $.ajax({
                        url: "app/controller/act-karyawan.php",
                        type: "POST",
                        data: {
                            action: "delete",
                            id_karyawan: id
                        },
                        success: function(res) {
                            if (res.status === "ok") {
                                tableKaryawan.ajax.reload(null, false);
                                showToast("Data berhasil dihapus!", "success");
                            } else {
                                showToast("Gagal menghapus data!", "danger");
                            }
                        }
                    });
                }
            });

            // 12. ACTION: IMPORT EXCEL 
            $("#formImport").on("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = $("#btnUpload");

                // Show progress bar
                $("#progressWrapper").show();
                btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: "app/controller/csv-control/csv-karyawan.php",
                    type: "POST",
                    dataType: "json", // ← PENTING! Auto-parse JSON response
                    data: formData,
                    contentType: false,
                    processData: false,

                    // Progress bar handler
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $("#progressBar").css("width", percentComplete + "%").text(percentComplete + "%");
                            }
                        }, false);
                        return xhr;
                    },

                    // Success handler
                    success: function(res) {
                        console.log("=== Import Response ===");
                        console.log("Response:", res);
                        console.log("Success:", res.success);
                        console.log("Berhasil:", res.berhasil);
                        console.log("Gagal:", res.gagal);

                        if (res.success) {
                            // Buat pesan detail
                            let msg = `📊 IMPORT SELESAI!\n\n`;
                            msg += `✅ Berhasil: ${res.berhasil} data\n`;
                            msg += `❌ Gagal: ${res.gagal} data`;

                            // Tampilkan error jika ada (maksimal 5 error pertama)
                            if (res.errors && res.errors.length > 0) {
                                msg += `\n\n━━━━━━━━━━━━━━━━━━━━━━`;
                                msg += `\n📋 ERROR DETAIL:\n`;
                                msg += res.errors.slice(0, 5).join('\n');

                                if (res.errors.length > 5) {
                                    msg += `\n\n... dan ${res.errors.length - 5} error lainnya`;
                                    msg += `\n(Cek console browser untuk detail lengkap)`;
                                }

                                // Log semua error ke console
                                console.log("=== All Errors ===");
                                console.log(res.errors);
                            }

                            // Tampilkan debug info jika ada
                            if (res.debug && res.debug.length > 0) {
                                console.log("=== Debug Info ===");
                                console.log(res.debug);
                            }

                            // Tampilkan alert dengan hasil
                            alert(msg);

                            // Reload table dan tutup modal
                            tableKaryawan.ajax.reload();
                            $("#ModalImport").modal("hide");
                            $("#formImport")[0].reset();

                            // Toast notification
                            if (res.gagal > 0) {
                                showToast(`Import selesai dengan ${res.gagal} error. Cek detail!`, "warning");
                            } else {
                                showToast(`Semua ${res.berhasil} data berhasil diimport!`, "success");
                            }

                        } else {
                            // Jika success = false
                            let errorMsg = "❌ IMPORT GAGAL!\n\n";
                            errorMsg += res.error || "Terjadi kesalahan tidak diketahui";

                            // Tampilkan detail error jika ada
                            if (res.error_detail) {
                                errorMsg += `\n\nDetail:\n`;
                                errorMsg += `File: ${res.error_detail.file}\n`;
                                errorMsg += `Line: ${res.error_detail.line}`;
                            }

                            alert(errorMsg);
                            showToast("Import gagal! Cek console untuk detail.", "danger");

                            console.error("=== Import Error ===");
                            console.error(res);
                        }
                    },

                    // Error handler (untuk AJAX error, bukan logic error)
                    error: function(xhr, status, error) {
                        console.error("=== AJAX Error ===");
                        console.error("Status:", status);
                        console.error("Error:", error);
                        console.error("Response:", xhr.responseText);

                        let errorMsg = "⚠️ TERJADI KESALAHAN SERVER!\n\n";
                        errorMsg += `Status: ${status}\n`;
                        errorMsg += `Error: ${error}\n\n`;
                        errorMsg += "Cek console browser (F12) untuk detail lengkap.";

                        alert(errorMsg);
                        showToast("Terjadi kesalahan server!", "danger");
                    },

                    // Complete handler (selalu dijalankan)
                    complete: function() {
                        // Hide progress bar dan restore button
                        $("#progressWrapper").hide();
                        $("#progressBar").css("width", "0%").text("0%");
                        btn.prop("disabled", false).html('<i class="fa fa-upload"></i> Upload');
                    }
                });
            });

            // 13. ACTION: KLIK FOTO UNTUK ZOOM
            // Kita pakai event delegation (on click) karena gambar di-generate secara dinamis oleh DataTables
            $('#tableKaryawan tbody').on('click', 'img.zoom-foto', function() {
                // Ambil URL gambar dan nama dari atribut data yang diklik
                let imgSrc = $(this).data('src');
                let namaKaryawan = $(this).data('nama');
                
                // Jika fotonya rusak dan kembali ke default (person.png), pastikan modal juga memuat default
                if ($(this).attr('src').includes('person.png')) {
                    imgSrc = 'assets/img/karyawan-pic/person.png';
                }

                // Masukkan src dan teks ke dalam modal
                $('#imgPreviewBesar').attr('src', imgSrc);
                $('#namaFotoKaryawan').text(namaKaryawan);

                // Tampilkan modal
                $('#modalLihatFoto').modal('show');
            });

            // --- ACTION: EXPORT EXCEL KARYAWAN ---
            window.exportKaryawanCSV = function() {
                let site = document.getElementById("filterSite").value;
                let jabatan = document.getElementById("filterJabatan").value;
                let status = document.getElementById("filterStatus").value;
                let bulan = document.getElementById("filterBulan").value;

                // Arahkan ke file export-karyawan.php
                let url = `app/controller/export/export-karyawan.php?site=${site}&jabatan=${jabatan}&status=${status}&bulan=${bulan}`;
                window.location.href = url;
            };
        });
    </script>