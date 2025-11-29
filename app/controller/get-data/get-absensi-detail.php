<?php
require '../../config/koneksi.php';
require '../../config/function.php';

$id_karyawan = $_GET['id_karyawan'] ?? '';
$tgl_a = $_GET['tgl_a'] ?? date('Y-m-01');
$tgl_b = $_GET['tgl_b'] ?? date('Y-m-d');

if (!$id_karyawan) {
    echo "<p class='text-danger'>Karyawan tidak ditemukan</p>";
    exit;
}

// Fungsi range tanggal
function dateRange($start, $end)
{
    $dates = [];
    $current = strtotime($start);
    $endTime = strtotime($end);
    while ($current <= $endTime) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $dates;
}
$allDates = dateRange($tgl_a, $tgl_b);

// Ambil semua absen karyawan ( termasuk id_absen )
$absenAll = [];
$absenQ = $koneksi->query("SELECT id_absen, tgl_absen, jam_kerja, ket_absen 
                           FROM tb_absen 
                           WHERE id_karyawan='$id_karyawan' 
                           AND tgl_absen BETWEEN '$tgl_a' AND '$tgl_b'");
while ($a = $absenQ->fetch_assoc()) {
    $absenAll[$a['tgl_absen']] = $a;
}

// Ambil izin dan sakit
function loadRange($table, $id_karyawan, $tgl_a, $tgl_b, $koneksi)
{
    $arr = [];
    $q = $koneksi->query("SELECT tgl_awal_$table AS awal, tgl_akhir_$table AS akhir 
        FROM tb_$table WHERE id_karyawan='$id_karyawan' 
        AND tgl_awal_$table <= '$tgl_b' 
        AND tgl_akhir_$table >= '$tgl_a'");
    while ($r = $q->fetch_assoc()) $arr[] = $r;
    return $arr;
}
$izinAll  = loadRange('izin',  $id_karyawan, $tgl_a, $tgl_b, $koneksi);
$sakitAll = loadRange('sakit', $id_karyawan, $tgl_a, $tgl_b, $koneksi);

// Ambil data karyawan
$karyawanQ = $koneksi->query("SELECT * FROM tb_karyawan
JOIN tb_karier ON tb_karyawan.id_karyawan=tb_karier.id_karyawan
JOIN tb_jabatan ON tb_karier.id_jabatan=tb_jabatan.id_jabatan
WHERE tb_karyawan.id_karyawan='$id_karyawan'");
$karyawan = $karyawanQ->fetch_assoc();
?>

<div class="table-responsive">
    <table class="table-bordered table-sm w-100 text-center">
        <thead class="bg-primary text-white">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>Jabatan</th>
                <th>Tanggal</th>
                <th>Jam Normal (1-7)</th>
                <th>OT1 (8)</th>
                <th>OT2 (9 ke atas)</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $totalNormal = 0;
            $totalOT1 = 0;
            $totalOT2 = 0;

            foreach ($allDates as $tgl) {

                $absen = $absenAll[$tgl] ?? null;
                $jamNormal = $ot1 = $ot2 = '-';
                $keterangan = 'Mangkir';
                $rowClass = 'bg-danger text-white';

                // ==== CEK IZIN ====
                $isIzin = false;
                foreach ($izinAll as $r) {
                    if ($tgl >= $r['awal'] && $tgl <= $r['akhir']) {
                        $isIzin = true;
                        break;
                    }
                }

                // ==== CEK SAKIT ====
                $isSakit = false;
                foreach ($sakitAll as $r) {
                    if ($tgl >= $r['awal'] && $tgl <= $r['akhir']) {
                        $isSakit = true;
                        break;
                    }
                }

                // ==== CEK ABSEN ====
                if ($absen) {
                    $jk = floatval($absen['jam_kerja']);
                    $ket = strtoupper($absen['ket_absen']);

                    if ($jk > 0) {
                        $jamNormal = min($jk, 7);
                        $ot1 = max(0, min($jk - 7, 1));
                        $ot2 = max(0, $jk - 8);
                        $keterangan = ($ket === "OFF" ? "SPL" : "Masuk");

                        $rowClass = ($ket === "OFF" ? "bg-warning text-dark" : "");
                    } else {
                        if ($ket === "OFF") {
                            $keterangan = "OFF";
                            $rowClass = "bg-warning text-dark";
                        } else {
                            $keterangan = "Mangkir";
                            $rowClass = "bg-danger text-white";
                        }
                    }
                } else {
                    if ($isIzin) {
                        $keterangan = "Izin";
                        $rowClass = "bg-primary text-white";
                    } elseif ($isSakit) {
                        $keterangan = "Sakit";
                        $rowClass = "bg-info text-white";
                    } else {
                        $keterangan = "Mangkir";
                        $rowClass = "bg-danger text-white";
                    }
                }

                // ==== HITUNG TOTAL ====
                $totalNormal += is_numeric($jamNormal) ? $jamNormal : 0;
                $totalOT1 += is_numeric($ot1) ? $ot1 : 0;
                $totalOT2 += is_numeric($ot2) ? $ot2 : 0;
            ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= $no ?></td>
                    <td><?= htmlspecialchars($karyawan['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($karyawan['nik_karyawan']) ?></td>
                    <td><?= htmlspecialchars($karyawan['nama_jabatan']) ?></td>
                    <td><?= tanggal_indo($tgl, true) ?></td>
                    <td><?= $jamNormal ?></td>
                    <td><?= $ot1 ?></td>
                    <td><?= $ot2 ?></td>
                    <td><?= $keterangan ?></td>

                    <td>
                        <?php if ($absen): ?>
                            <!-- EDIT (pakai id_absen) -->
                            <button class="btn btn-sm btn-warning btnEdit"
                                data-id_absen="<?= $absen['id_absen'] ?>"
                                data-tgl="<?= $tgl ?>"
                                data-jk="<?= $absen['jam_kerja'] ?>"
                                data-ket="<?= $absen['ket_absen'] ?>"
                                data-id_karyawan="<?= $id_karyawan ?>">
                                <i class="fa fa-edit"></i>
                            </button>

                            <!-- HAPUS (pakai id_absen) -->
                            <button class="btn btn-sm btn-danger btnHapus"
                                data-id_absen="<?= $absen['id_absen'] ?>"
                                data-tgl="<?= $tgl ?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php else: ?>
                            <!-- TAMBAH (pakai id_karyawan + tgl) -->
                            <button class="btn btn-sm btn-success btnTambah"
                                data-id_karyawan="<?= $id_karyawan ?>"
                                data-tgl="<?= $tgl ?>">
                                <i class="fa fa-plus"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php
                $no++;
            }
            ?>
            <!-- ================= TOTAL ================= -->
            <tr class="bg-secondary text-white fw-bold">
                <td colspan="5">Total</td>
                <td><?= $totalNormal ?></td>
                <td><?= $totalOT1 ?></td>
                <td><?= $totalOT2 ?></td>
                <td colspan="2">-</td>
            </tr>
        </tbody>

    </table>
</div>


<!-- Modal Form Absen -->
<div class="modal fade" id="modalFormAbsen" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAbsen">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Form Absensi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- id_absen untuk edit (kosong saat tambah) -->
                    <input type="hidden" name="id_absen" id="form_id_absen" value="">
                    <input type="hidden" name="id_karyawan" id="form_id_karyawan" value="">
                    <input type="hidden" name="tgl_absen" id="form_tgl" value="">

                    <div class="mb-3">
                        <label class="form-label">Jam Kerja</label>
                        <input type="number" step="0.1" class="form-control" name="jam_kerja" id="form_jk" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <select class="form-select" name="ket_absen" id="form_ket">
                            <option value="Masuk">Masuk</option>
                            <option value="OFF">OFF</option>
                            <option value="SPL">SPL</option>
                            <option value="Mangkir">Mangkir</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- action akan di-set di JS: add / edit -->
                    <input type="hidden" name="action" id="form_action" value="add">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", () => {

        const modal = new bootstrap.Modal(document.getElementById('modalFormAbsen'));

        // === TOMBOL TAMBAH ===
        $(document).on("click", ".btnTambah", function() {
            const id_karyawan = $(this).data("id_karyawan");
            const tgl = $(this).data("tgl");

            $("#form_id_absen").val('');
            $("#form_id_karyawan").val(id_karyawan);
            $("#form_tgl").val(tgl);
            $("#form_jk").val('');
            $("#form_ket").val("Masuk");
            $("#form_action").val("add");
            modal.show();
        });

        // === TOMBOL EDIT ===
        $(document).on("click", ".btnEdit", function() {
            const id_absen = $(this).data("id_absen");
            const id_karyawan = $(this).data("id_karyawan");
            const tgl = $(this).data("tgl");
            const jk = $(this).data("jk");
            const ket = $(this).data("ket");

            $("#form_id_absen").val(id_absen);
            $("#form_id_karyawan").val(id_karyawan);
            $("#form_tgl").val(tgl);
            $("#form_jk").val(jk);
            $("#form_ket").val(ket);
            $("#form_action").val("edit");
            modal.show();
        });

        // === TOMBOL HAPUS ===
        $(document).on("click", ".btnHapus", function() {
            if (!confirm("Hapus absensi tanggal ini?")) return;

            const id_absen = $(this).data("id_absen");

            fetch("app/controller/absensi/act-absen.php", {
                    method: "POST",
                    body: new URLSearchParams({
                        action: "delete",
                        id_absen: id_absen
                    })
                })
                .then(res => res.json())
                .then(data => {
                    showToast(data.message, data.status);
                    if (typeof loadAbsensi === "function") loadAbsensi();
                });
        });

        // === SUBMIT TAMBAH / EDIT ===
        $("#formAbsen").on("submit", function(e) {
            e.preventDefault();

            const fd = new FormData(this);

            fetch("app/controller/absensi/act-absen.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    showToast(data.message, data.status);
                    modal.hide();
                    if (typeof loadAbsensi === "function") loadAbsensi();
                });
        });

    });
</script>
