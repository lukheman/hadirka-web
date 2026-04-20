<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">
                    <span class="text-muted fw-light">Approval /</span> Pengajuan Pinjaman
                </h4>
                <p class="text-muted mb-0">Proses pengajuan pinjaman karyawan dari aplikasi mobile.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa fa-bell me-2 text-warning"></i> Daftar Antrean Pengajuan</h5>
            </div>
            <div class="card-body">
                <div class="">
                    <table class="table table-hover table-bordered MyTable w-100">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Karyawan</th>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Alasan</th>
                                <th>Bukti (Wajah & ID)</th>
                                <th>Info Rekening</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPengajuan">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProsesPengajuan">
    <div class="modal-dialog">
        <form id="formProsesPengajuan" class="modal-content">
            <input type="hidden" name="action" value="proses">
            <input type="hidden" name="id_pengajuan" id="id_pengajuan">

            <div class="modal-header">
                <h5 class="modal-title">Proses Pengajuan Pinjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="infoPengajuan"></div>

                <label class="fw-bold">Keputusan <span class="text-danger">*</span></label>
                <select name="status_pinjaman" id="status_pinjaman" class="form-select mb-3" required>
                    <option value="">-- Pilih Keputusan --</option>
                    <option value="disetujui">ACC (Disetujui)</option>
                    <option value="ditolak">Tolak</option>
                </select>

                <div id="formAcc" style="display: none;">
                    <label class="fw-bold">Tanggal Rencana Bayar</label>
                    <input type="date" name="tgl_bayar_pinjaman" class="form-control mb-3">
                    <small class="text-success d-block mb-3">*Jika di-ACC, data otomatis masuk ke Master Data Pinjaman.</small>
                </div>

                <label class="fw-bold">Pesan / Keterangan untuk Karyawan</label>
                <textarea name="keterangan_admin" id="keterangan_admin" class="form-control mb-3" rows="3" placeholder="Wajib diisi jika ditolak..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitProses">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        loadPengajuan();

        // Toggle tampilan form tgl bayar berdasarkan status
        document.getElementById("status_pinjaman").addEventListener("change", function() {
            const formAcc = document.getElementById("formAcc");
            const ketAdmin = document.getElementById("keterangan_admin");

            if (this.value === 'disetujui') {
                formAcc.style.display = 'block';
                ketAdmin.required = false;
            } else if (this.value === 'ditolak') {
                formAcc.style.display = 'none';
                ketAdmin.required = true;
            } else {
                formAcc.style.display = 'none';
                ketAdmin.required = false;
            }
        });

        function loadPengajuan() {
            fetch("app/controller/get-data/get-pengajuan-pinjaman.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable(".MyTable")) {
                        $(".MyTable").DataTable().destroy();
                    }
                    document.getElementById("tbodyPengajuan").innerHTML = html;
                    $(".MyTable").DataTable({
                        responsive: true,
                        language: {
                            emptyTable: "Belum ada data pengajuan."
                        }
                    });
                });
        }

        // --- TAMBAHAN: Fungsi Update Status Cepat ---
        window.updateStatusPengajuan = async function(id, newStatus) {
            let confirmText = "";
            if (newStatus === 'pending') {
                confirmText = "Kembalikan pengajuan ini ke status Pending? (Data akan ditarik dari Master Pinjaman jika sebelumnya sudah di-ACC)";
            } else if (newStatus === 'ditolak') {
                confirmText = "Tolak pengajuan pinjaman ini secara langsung?";
            }

            if (confirm(confirmText)) {
                try {
                    const response = await fetch(`app/controller/act-pengajuan-pinjaman.php?action=update-status&id=${id}&status=${newStatus}`);

                    if (!response.ok) throw new Error(`Server merespon ${response.status}`);

                    const result = await response.json();
                    if (result.status === 'success') {
                        loadPengajuan(); // Refresh tabel
                    } else {
                        alert("Gagal: " + result.msg);
                    }
                } catch (e) {
                    console.error("Update Error:", e);
                    alert("Terjadi kesalahan sistem.");
                }
            }
        }

        // --- TAMBAHAN: Fungsi Hapus Pengajuan ---
        window.deletePengajuan = async function(id) {
            if (confirm("Hapus permanen data pengajuan pinjaman ini beserta buktinya?")) {
                try {
                    const res = await fetch(`app/controller/act-pengajuan-pinjaman.php?action=delete&id=${id}`);
                    const result = await res.json();

                    if (result.status === 'success') {
                        loadPengajuan(); // Refresh tabel
                    } else {
                        alert("Gagal menghapus: " + result.msg);
                    }
                } catch (e) {
                    console.error("Delete Error:", e);
                    alert("Terjadi kesalahan saat menghapus data.");
                }
            }
        }

        // Klik tombol proses di tabel
        document.addEventListener("click", e => {
            const btnProses = e.target.closest(".btnProses");
            if (btnProses) {
                document.getElementById("id_pengajuan").value = btnProses.dataset.id;
                document.getElementById("infoPengajuan").innerHTML =
                    `<strong>Karyawan:</strong> ${btnProses.dataset.nama} <br> 
                 <strong>Nominal:</strong> Rp ${parseInt(btnProses.dataset.nominal).toLocaleString('id-ID')}`;

                document.getElementById("status_pinjaman").value = '';
                document.getElementById("keterangan_admin").value = '';
                document.getElementById("formAcc").style.display = 'none';

                new bootstrap.Modal(document.getElementById("modalProsesPengajuan")).show();
            }
        });

        // Submit Form Proses
        document.getElementById("formProsesPengajuan").addEventListener("submit", e => {
            e.preventDefault();
            const fd = new FormData(e.target);

            fetch("app/controller/act-pengajuan-pinjaman.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        alert(res.msg);
                        bootstrap.Modal.getInstance(document.getElementById("modalProsesPengajuan")).hide();
                        loadPengajuan();
                    } else {
                        alert("Gagal: " + res.msg);
                    }
                });
        });
    });
</script>