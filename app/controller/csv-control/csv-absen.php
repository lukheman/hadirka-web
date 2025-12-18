<?php
header("Content-Type: application/json");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// ================================
// CEK FILE CSV / EXCEL
// ================================
if (!isset($_FILES['file_csv'])) {
    echo json_encode(['status' => 'error', 'message' => 'File CSV/Excel tidak ditemukan']);
    exit;
}

$fileTmpPath = $_FILES['file_csv']['tmp_name'];
$spreadsheet = IOFactory::load($fileTmpPath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

// ================================
// DEFINISI KOLOM
// ================================
$colNIK = 'C';
$colHariOff = 'D';

// Ambil kolom tanggal mulai E sampai akhir header
$startCol = 'E';
$lastCol = $sheet->getHighestColumn();
$colsTanggal = [];

$col = $startCol;
while (true) {
    $header = trim($sheet->getCell($col . '1')->getValue());
    if ($header !== '') $colsTanggal[] = $col;
    if ($col == $lastCol) break;
    $col++;
}

// Mapping hari ke angka PHP date('w')
$hariMap = [
    'Minggu' => 0,
    'Senin'  => 1,
    'Selasa' => 2,
    'Rabu'   => 3,
    'Kamis'  => 4,
    'Jumat'  => 5,
    'Sabtu'  => 6
];

// ================================
// LOOP PER BARIS KARYAWAN
// ================================
for ($i = 2; $i <= count($data); $i++) {

    $row = $data[$i];

    $nik_karyawan = trim($row[$colNIK]);
    $namaHariOff = trim($row[$colHariOff]);
    $hariOff = $hariMap[$namaHariOff] ?? null;

    // ================================
    // AMBIL ID KARYAWAN
    // ================================
    $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan=?");
    $stmtK->bind_param("s", $nik_karyawan);
    $stmtK->execute();
    $stmtK->bind_result($id_karyawan);
    $stmtK->fetch();
    $stmtK->close();

    // Jika NIK tidak ditemukan → skip
    if (!$id_karyawan) continue;

    // ================================
    // LOOP PER KOLOM TANGGAL
    // ================================
    foreach ($colsTanggal as $col) {

        // Konversi tanggal Excel ke Y-m-d
        $tglExcel = $sheet->getCell($col . '1')->getValue();
        $tgl = is_numeric($tglExcel)
            ? Date::excelToDateTimeObject($tglExcel)->format('Y-m-d')
            : date('Y-m-d', strtotime($tglExcel));

        $nilai = trim($row[$col]);

        // Cek apakah hari ini adalah hari libur OFF
        $isHariOff = $hariOff !== null && (int)date('w', strtotime($tgl)) === $hariOff;

        // ================================
        // 1. JIKA NUMERIC → MASUK TABEL ABSEN
        // ================================
        if ($nilai !== '' && is_numeric(str_replace(',', '.', $nilai))) {

            $jamKerja = str_replace(',', '.', $nilai);
            $ket = $isHariOff ? 'OFF' : '';

            // Cek apakah data absen sudah ada
            $stmtCheck = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
            $stmtCheck->bind_param("is", $id_karyawan, $tgl);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows == 0) {
                // INSERT baru
                $stmtInsert = $koneksi->prepare("
                    INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen)
                    VALUES (?,?,?,?)
                ");
                $stmtInsert->bind_param("isds", $id_karyawan, $tgl, $jamKerja, $ket);
                $stmtInsert->execute();
                $stmtInsert->close();
            } else {
                // UPDATE
                $stmtUpdate = $koneksi->prepare("
                    UPDATE tb_absen SET jam_kerja=?, ket_absen=? 
                    WHERE id_karyawan=? AND tgl_absen=?
                ");
                $stmtUpdate->bind_param("dsis", $jamKerja, $ket, $id_karyawan, $tgl);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }

            $stmtCheck->close();
            continue;
        }

        // ================================
        // 2. NILAI KOSONG + HARI OFF → MASUK ABSEN OFF
        // ================================
        if ($isHariOff) {

            $jamKerja = 0;
            $ket = 'OFF';

            $stmtCheck = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
            $stmtCheck->bind_param("is", $id_karyawan, $tgl);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows == 0) {
                $stmtInsert = $koneksi->prepare("
                    INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen)
                    VALUES (?,?,?,?)
                ");
                $stmtInsert->bind_param("isds", $id_karyawan, $tgl, $jamKerja, $ket);
                $stmtInsert->execute();
                $stmtInsert->close();
            } else {
                $stmtUpdate = $koneksi->prepare("
                    UPDATE tb_absen SET ket_absen=? 
                    WHERE id_karyawan=? AND tgl_absen=?
                ");
                $stmtUpdate->bind_param("sis", $ket, $id_karyawan, $tgl);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }

            $stmtCheck->close();
            continue;
        }

        // ================================
        // 3. JIKA NILAI = "OFF" → MASUK ABSEN OFF
        // ================================
        if (strtoupper($nilai) === 'OFF') {

            $jamKerja = 0;
            $ket = 'OFF';

            if (!empty($list_id_izin)) {
                $idList = implode(",", array_map('intval', $list_id_izin));
                $koneksi->query("DELETE FROM tb_izin WHERE id_izin IN ($idList)");
            }

            if (!empty($list_id_sakit)) {
                $idList = implode(",", array_map('intval', $list_id_sakit));
                $koneksi->query("DELETE FROM tb_sakit WHERE id_sakit IN ($idList)");
            }

            if (!empty($list_id_cuti)) {
                $idList = implode(",", array_map('intval', $list_id_cuti));
                $koneksi->query("DELETE FROM tb_cuti WHERE id_cuti IN ($idList)");
            }


            // Simpan absen
            $stmtCheck = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
            $stmtCheck->bind_param("is", $id_karyawan, $tgl);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows == 0) {
                $stmtInsert = $koneksi->prepare("
                    INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen)
                    VALUES (?,?,?,?)
                ");
                $stmtInsert->bind_param("isds", $id_karyawan, $tgl, $jamKerja, $ket);
                $stmtInsert->execute();
                $stmtInsert->close();
            } else {
                $stmtUpdate = $koneksi->prepare("
                    UPDATE tb_absen SET jam_kerja=?, ket_absen=? 
                    WHERE id_karyawan=? AND tgl_absen=?
                ");
                $stmtUpdate->bind_param("dsis", $jamKerja, $ket, $id_karyawan, $tgl);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }

            $stmtCheck->close();
            continue;
        }

        // ================================
        // 4. JIKA KODE SD / IZIN / CM → MASUK TB_ABSEN (TIDAK KE TABEL IZIN/SAKIT/CUTI)
        // ================================
        if ($nilai !== '') {

            $kode = strtoupper($nilai);

            // Mapping kode → keterangan
            $mapKet = [
                'SD'   => 'SAKIT',
                'IZIN' => 'IZIN',
                'CM'   => 'CUTI'
            ];

            if (isset($mapKet[$kode])) {

                // jam_kerja = ket_absen = keterangan (SAKIT / IZIN / CUTI)
                $jamKerja = $mapKet[$kode];
                $ket = $mapKet[$kode];

                // Hapus absen lama jika ada
                $stmtDel = $koneksi->prepare("DELETE FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                $stmtDel->bind_param("is", $id_karyawan, $tgl);
                $stmtDel->execute();
                $stmtDel->close();

                // Insert absen baru
                $stmtInsert = $koneksi->prepare("
            INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen)
            VALUES (?,?,?,?)
        ");
                $stmtInsert->bind_param("isss", $id_karyawan, $tgl, $jamKerja, $ket);
                $stmtInsert->execute();
                $stmtInsert->close();

                continue;
            }
        }
    }
}

// ================================
// SUCCESS RESPONSE
// ================================
echo json_encode([
    'status' => 'success',
    'message' => 'Import CSV/Excel selesai!'
]);
