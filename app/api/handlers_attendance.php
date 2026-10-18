<?php

declare(strict_types=1);

/**
 * API mobile — Handler absensi GPS + selfie.
 *
 * Data absen aplikasi menulis langsung ke tabel utama `tb_absen` (SATU tabel
 * bersama HRD, rekap, payroll — tidak ada lagi tb_absen_mobile). Baris dari
 * aplikasi ditandai `sumber='mobile'`, `ket_absen='HADIR'`, `jam_kerja='1'`
 * (hari penuh berbayar) sehingga rekap harian & payroll otomatis ikut
 * menghitungnya.
 *
 * Kontrak mengikuti backend Node.js lama:
 *   GET  /attendance?username=      daftar log absensi (sumber='mobile')
 *   POST /attendance/submit         { username, latitude, longitude, tipe,
 *                                     accuracy, fotoPath, fotoBase64 }
 */

function api_attendance_list(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));

    $sql = "SELECT a.*, k.nik_karyawan, k.nama_lengkap
            FROM tb_absen a
            JOIN tb_karyawan k ON k.id_karyawan = a.id_karyawan
            WHERE a.sumber = 'mobile'";
    $params = [];

    if ($username !== '') {
        $sql .= ' AND (LOWER(k.nik_karyawan) = LOWER(?) OR k.id_karyawan = ?)';
        $params = [$username, ctype_digit($username) ? (int)$username : 0];
    }

    $sql .= ' ORDER BY a.dibuat_pada DESC, a.id_absen DESC';

    $st = api_db()->prepare($sql);
    $st->execute($params);

    $data = [];

    foreach ($st->fetchAll() as $row) {
        $data[] = [
            'id'                 => (int)$row['id_absen'],
            'nik'                => (string)$row['nik_karyawan'],
            'username'           => (string)$row['nik_karyawan'],
            'nama'               => (string)$row['nama_lengkap'],
            'tanggal'            => (string)$row['tgl_absen'],
            'waktuIn'            => (string)$row['waktu_masuk'],
            'waktuOut'           => $row['waktu_keluar'],
            'lokasiNama'         => (string)($row['nama_lokasi'] ?? ''),
            'latitude'           => (float)($row['latitude'] ?? 0),
            'longitude'          => (float)($row['longitude'] ?? 0),
            'jarakMeter'         => (float)($row['jarak_meter'] ?? 0),
            'status'             => (string)($row['status_kehadiran'] ?? ''),
            'keterlambatanMenit' => (int)$row['keterlambatan_menit'],
            'durasiKerjaMenit'   => api_durasi_menit($row['waktu_masuk'], $row['waktu_keluar']),
            'isTepat'            => (bool)$row['is_tepat_waktu'],
            'fotoPath'           => $row['foto_selfie_path'],
            'tipe'               => $row['waktu_keluar'] ? 'check_out' : 'check_in',
            'waktu'              => api_iso($row['dibuat_pada']),
        ];
    }

    api_out(200, ['success' => true, 'data' => $data]);
}

function api_attendance_submit(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));
    $lat      = (float)($in['latitude'] ?? 0);
    $lng      = (float)($in['longitude'] ?? 0);
    $accuracy = (float)($in['accuracy'] ?? 0);
    $tipe     = (string)($in['tipe'] ?? 'check_in');
    $fotoPath = trim((string)($in['fotoPath'] ?? ''));
    $fotoB64  = trim((string)($in['fotoBase64'] ?? ''));

    $karyawan = api_find_karyawan($username);
    if ($karyawan === null) {
        api_error(404, $username !== ''
            ? "Karyawan ($username) tidak ditemukan."
            : 'Username tidak valid.');
    }

    if ($accuracy <= 0 || $accuracy > 100) {
        api_error(422, 'Akurasi GPS tidak cukup. Aktifkan lokasi presisi dan coba lagi.');
    }

    $db         = api_db();
    $idKaryawan = (int)$karyawan['id_karyawan'];
    $site       = api_nearest_site($lat, $lng, $idKaryawan);
    if ($site['id'] === null) {
        api_error(422, 'Site penempatan karyawan belum memiliki koordinat GPS. Hubungi HRD.');
    }
    $now        = api_wita_now();
    $tanggal    = $now->format('Y-m-d');
    $jamStr     = api_time_str($now);

    // Simpan foto selfie bila aplikasi mengirim base64 (fitur baru).
    // Bila tidak, simpan string dari aplikasi apa adanya (perilaku lama).
    $fotoTersimpan = $fotoPath !== '' ? $fotoPath : null;
    if ($fotoB64 !== '') {
        $namaFile = 'absen_' . $now->format('Ymd_His') . '_' . api_safe_name((string)$karyawan['nik_karyawan']) . '.jpg';
        $saved = api_save_base64_image($fotoB64, 'absensi', $namaFile);
        if ($saved !== null) {
            $fotoTersimpan = $saved;
        }
    }

    // SATU baris per karyawan per hari (logikanya sama dengan import CSV & rekap).
    $st = $db->prepare('SELECT * FROM tb_absen WHERE id_karyawan = ? AND tgl_absen = ? LIMIT 1');
    $st->execute([$idKaryawan, $tanggal]);
    $log = $st->fetch();

    $perintahHrd = function (array $row): string {
        return strtoupper(trim((string)($row['ket_absen'] ?? '')));
    };

    if ($tipe === 'check_out') {
        if ($log) {
            if (($log['sumber'] ?? 'web') === 'mobile') {
                $st = $db->prepare(
                    'UPDATE tb_absen
                     SET waktu_keluar = ?, status_kehadiran = ?,
                         foto_selfie_path = COALESCE(?, foto_selfie_path)
                     WHERE id_absen = ?'
                );
                $st->execute([$jamStr, 'Sudah Selesai', $fotoTersimpan, (int)$log['id_absen']]);
            } elseif ($perintahHrd($log) !== 'HADIR') {
                api_error(409, 'Hari ini sudah diabsen HRD (' . trim((string)$log['ket_absen']) . '). Hubungi HRD untuk koreksi.');
            } else {
                // Perkaya baris HADIR dari HRD dengan jam keluar dari aplikasi.
                $st = $db->prepare(
                    "UPDATE tb_absen
                     SET sumber = 'mobile',
                         id_lokasi = ?, nama_lokasi = ?, latitude = ?, longitude = ?,
                         jarak_meter = ?, waktu_keluar = ?, status_kehadiran = ?,
                         foto_selfie_path = COALESCE(?, foto_selfie_path),
                         waktu_masuk = COALESCE(waktu_masuk, ?)
                     WHERE id_absen = ?"
                );
                $st->execute([
                    $site['id'], $site['nama'], $lat, $lng, $site['jarak'],
                    $jamStr, 'Sudah Selesai', $fotoTersimpan, '08:00 WITA',
                    (int)$log['id_absen'],
                ]);
            }

            // Muat ulang baris agar respons memuat data terbaru.
            $st = $db->prepare('SELECT * FROM tb_absen WHERE id_absen = ?');
            $st->execute([(int)$log['id_absen']]);
            $log = $st->fetch();
        } else {
            // Absen keluar tanpa absen masuk: catat apa adanya (perilaku lama).
            $st = $db->prepare(
                "INSERT INTO tb_absen
                    (id_karyawan, tgl_absen, jam_kerja, ket_absen, sumber, id_lokasi, nama_lokasi,
                     latitude, longitude, jarak_meter, foto_selfie_path, waktu_masuk, waktu_keluar,
                     status_kehadiran, keterlambatan_menit, is_tepat_waktu)
                 VALUES (?, ?, '1', 'HADIR', 'mobile', ?, ?, ?, ?, ?, ?, '08:00 WITA', ?,
                         'Sudah Selesai', 0, 1)"
            );
            $st->execute([
                $idKaryawan, $tanggal, $site['id'], $site['nama'], $lat, $lng,
                $site['jarak'], $fotoTersimpan, $jamStr,
            ]);

            $st = $db->prepare('SELECT * FROM tb_absen WHERE id_absen = ?');
            $st->execute([(int)$db->lastInsertId()]);
            $log = $st->fetch();
        }
    } else {
        if ($log) {
            if (($log['sumber'] ?? 'web') === 'mobile') {
                // Idempoten: sudah absen masuk hari ini, kembalikan log yang ada.
            } elseif ($perintahHrd($log) !== 'HADIR') {
                api_error(409, 'Hari ini sudah diabsen HRD (' . trim((string)$log['ket_absen']) . '). Hubungi HRD untuk koreksi.');
            } else {
                // Perkaya baris HADIR dari HRD dengan data absen aplikasi.
                $late   = api_late_minutes($now);
                $status = $late > 0 ? "Terlambat {$late}m" : 'Tepat Waktu';

                $st = $db->prepare(
                    "UPDATE tb_absen
                     SET sumber = 'mobile',
                         id_lokasi = ?, nama_lokasi = ?, latitude = ?, longitude = ?,
                         jarak_meter = ?, waktu_masuk = ?, status_kehadiran = ?,
                         keterlambatan_menit = ?, is_tepat_waktu = ?,
                         foto_selfie_path = COALESCE(?, foto_selfie_path)
                     WHERE id_absen = ?"
                );
                $st->execute([
                    $site['id'], $site['nama'], $lat, $lng, $site['jarak'],
                    $jamStr, $status, $late, $late === 0 ? 1 : 0, $fotoTersimpan,
                    (int)$log['id_absen'],
                ]);

                // Muat ulang baris agar respons memuat data terbaru.
                $st = $db->prepare('SELECT * FROM tb_absen WHERE id_absen = ?');
                $st->execute([(int)$log['id_absen']]);
                $log = $st->fetch();
            }
        } else {
            $late   = api_late_minutes($now);
            $status = $late > 0 ? "Terlambat {$late}m" : 'Tepat Waktu';

            $st = $db->prepare(
                "INSERT INTO tb_absen
                    (id_karyawan, tgl_absen, jam_kerja, ket_absen, sumber, id_lokasi, nama_lokasi,
                     latitude, longitude, jarak_meter, foto_selfie_path, waktu_masuk, waktu_keluar,
                     status_kehadiran, keterlambatan_menit, is_tepat_waktu)
                 VALUES (?, ?, '1', 'HADIR', 'mobile', ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)"
            );
            $st->execute([
                $idKaryawan, $tanggal, $site['id'], $site['nama'], $lat, $lng,
                $site['jarak'], $fotoTersimpan, $jamStr, $status, $late, $late === 0 ? 1 : 0,
            ]);

            $st = $db->prepare('SELECT * FROM tb_absen WHERE id_absen = ?');
            $st->execute([(int)$db->lastInsertId()]);
            $log = $st->fetch();
        }
    }

    api_out(200, [
        'success' => true,
        'message' => 'Absen berhasil disimpan.',
        'data'    => [
            'id'               => (int)$log['id_absen'],
            'tipe'             => $log['waktu_keluar'] ? 'check_out' : 'check_in',
            'lokasiId'         => $log['id_lokasi'] !== null ? (int)$log['id_lokasi'] : null,
            'namaLokasi'       => (string)($log['nama_lokasi'] ?? ''),
            'latitude'         => (float)($log['latitude'] ?? 0),
            'longitude'        => (float)($log['longitude'] ?? 0),
            'jarakMeter'       => (float)($log['jarak_meter'] ?? 0),
            'waktu'            => $now->format('c'),
            'waktuIn'          => (string)($log['waktu_masuk'] ?? ''),
            'waktuOut'         => $log['waktu_keluar'],
            'status'           => (string)($log['status_kehadiran'] ?? ''),
            'durasiKerjaMenit' => api_durasi_menit($log['waktu_masuk'], $log['waktu_keluar']),
        ],
    ]);
}
