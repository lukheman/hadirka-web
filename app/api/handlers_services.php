<?php

declare(strict_types=1);

/**
 * API mobile — Handler layanan: pengajuan (izin/cuti), pinjaman, geofence,
 * slip gaji, pengguna, dan notifikasi.
 *
 * Pengajuan & pinjaman disimpan ke tabel website yang sudah ada
 * (tb_pengajuan_izin, tb_pengajuan_cuti, tb_pengajuan_pinjaman) sehingga
 * proses persetujuan tetap dilakukan dari halaman web HRD.
 */

/* ============================== PENGAJUAN (IZIN/CUTI) ============================== */

function api_leave_list(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));
    $db = api_db();
    $data = [];

    // --- Izin & SKS (satu tabel di website) ---
    $sqlIzin = 'SELECT p.id_pengajuan_izin, p.tgl_izin, p.alasan_izin, p.bukti_izin,
                       p.status_izin, p.waktu_update,
                       k.nik_karyawan, k.nama_lengkap
                FROM tb_pengajuan_izin p
                LEFT JOIN tb_karyawan k
                       ON k.id_karyawan = CAST(p.id_karyawan AS UNSIGNED)
                       OR k.nik_karyawan = p.id_karyawan COLLATE utf8mb4_general_ci';
    $params = [];

    if ($username !== '') {
        $sqlIzin .= ' WHERE LOWER(k.nik_karyawan) = LOWER(?) OR k.id_karyawan = ?';
        $params = [$username, ctype_digit($username) ? (int)$username : 0];
    }

    $sqlIzin .= ' ORDER BY p.tgl_izin DESC';

    $st = $db->prepare($sqlIzin);
    $st->execute($params);

    foreach ($st->fetchAll() as $row) {
        $alasan  = (string)$row['alasan_izin'];
        $isSks   = stripos($alasan, '[SKS]') === 0;
        $bersih  = $isSks ? trim((string)preg_replace('/^\[SKS\]\s*/i', '', $alasan)) : $alasan;

        $data[] = [
            'id'           => (int)$row['id_pengajuan_izin'],
            'sumber'       => 'izin',
            'nik'          => (string)($row['nik_karyawan'] ?? '-'),
            'username'     => (string)($row['nik_karyawan'] ?? '-'),
            'nama'         => (string)($row['nama_lengkap'] ?? '-'),
            'jenis'        => $isSks ? 'SKS (Surat Keterangan Sakit)' : 'Izin',
            'tanggal'      => date('d/m/Y', (int)strtotime((string)$row['tgl_izin'])),
            'alasan'       => $bersih,
            'status'       => api_map_status($row['status_izin']),
            'lampiranPath' => $row['bukti_izin'],
            'createdAt'    => api_iso($row['waktu_update']),
        ];
    }

    // --- Cuti ---
    $sqlCuti = 'SELECT p.id_pengajuan_cuti, p.tgl_awal, p.tgl_akhir, p.alasan_cuti, p.bukti_cuti,
                       p.status_cuti, p.waktu_update,
                       k.nik_karyawan, k.nama_lengkap
                FROM tb_pengajuan_cuti p
                LEFT JOIN tb_karyawan k ON k.id_karyawan = p.id_karyawan';
    $params = [];

    if ($username !== '') {
        $sqlCuti .= ' WHERE LOWER(k.nik_karyawan) = LOWER(?) OR k.id_karyawan = ?';
        $params = [$username, ctype_digit($username) ? (int)$username : 0];
    }

    $sqlCuti .= ' ORDER BY p.tgl_awal DESC';

    $st = $db->prepare($sqlCuti);
    $st->execute($params);

    foreach ($st->fetchAll() as $row) {
        $data[] = [
            'id'           => (int)$row['id_pengajuan_cuti'],
            'sumber'       => 'cuti',
            'nik'          => (string)($row['nik_karyawan'] ?? '-'),
            'username'     => (string)($row['nik_karyawan'] ?? '-'),
            'nama'         => (string)($row['nama_lengkap'] ?? '-'),
            'jenis'        => 'Cuti',
            'tanggal'      => date('d/m/Y', (int)strtotime((string)$row['tgl_awal']))
                . ' - ' . date('d/m/Y', (int)strtotime((string)$row['tgl_akhir'])),
            'alasan'       => (string)$row['alasan_cuti'],
            'status'       => api_map_status($row['status_cuti']),
            'lampiranPath' => $row['bukti_cuti'],
            'createdAt'    => api_iso($row['waktu_update']),
        ];
    }

    usort($data, static fn (array $a, array $b): int => strcmp((string)$b['createdAt'], (string)$a['createdAt']));

    api_out(200, ['success' => true, 'data' => $data]);
}

function api_leave_create(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));
    $jenis    = trim((string)($in['jenis'] ?? 'Izin'));
    $tanggal  = trim((string)($in['tanggal'] ?? ''));
    $alasan   = trim((string)($in['alasan'] ?? ''));
    $lampiran = api_clean_lampiran($in['lampiranPath'] ?? null);

    $karyawan = api_find_karyawan($username);
    if ($karyawan === null) {
        api_error(404, $username !== ''
            ? "Karyawan ($username) tidak ditemukan."
            : 'Username tidak valid.');
    }

    $range = api_parse_rentang($tanggal);
    if ($range === null) {
        api_error(400, 'Format tanggal tidak dikenali. Gunakan format dd/mm/yyyy.');
    }

    $db         = api_db();
    $idKaryawan = (int)$karyawan['id_karyawan'];
    $alasan     = $alasan !== '' ? $alasan : '-';

    if (stripos($jenis, 'cuti') !== false) {
        $lama = (int)round((strtotime($range['end']) - strtotime($range['start'])) / 86400) + 1;

        $st = $db->prepare(
            'INSERT INTO tb_pengajuan_cuti
                (id_karyawan, tgl_awal, tgl_akhir, lama_cuti, alasan_cuti, bukti_cuti, status_cuti)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$idKaryawan, $range['start'], $range['end'], $lama, $alasan, $lampiran, 'pending']);

        api_out(200, ['success' => true, 'message' => 'Pengajuan cuti/izin/sakit berhasil disimpan.']);
    }

    // Izin & SKS memakai tb_pengajuan_izin; SKS ditandai prefix agar HRD mudah membedakan.
    $isSks       = stripos($jenis, 'sks') !== false || stripos($jenis, 'sakit') !== false;
    $alasanFinal = ($isSks ? '[SKS] ' : '') . $alasan;

    if ($range['end'] !== $range['start']) {
        $alasanFinal .= ' (s/d ' . date('d/m/Y', (int)strtotime($range['end'])) . ')';
    }

    $tglIzin = $range['start'] . ' ' . date('H:i:s');

    $st = $db->prepare(
        'INSERT INTO tb_pengajuan_izin (id_karyawan, tgl_izin, alasan_izin, bukti_izin, status_izin)
         VALUES (?, ?, ?, ?, ?)'
    );
    $st->execute([(string)$idKaryawan, $tglIzin, $alasanFinal, $lampiran, 'pending']);

    api_out(200, ['success' => true, 'message' => 'Pengajuan cuti/izin/sakit berhasil disimpan.']);
}

function api_leave_status(array $params): void
{
    $id = (int)($params[0] ?? 0);
    $in = api_input();

    $statusInput = trim((string)($in['status'] ?? ''));
    if ($id <= 0 || $statusInput === '') {
        api_error(400, 'Data tidak lengkap.');
    }

    $statusWeb = api_map_status_web($statusInput);
    $sumber    = strtolower(trim((string)($in['sumber'] ?? ''))); // 'izin' | 'cuti' (opsional)
    $db = api_db();

    // id tabel izin & cuti bisa sama; jika ambigu, utamakan sumber yang
    // diminta lewat body/query "sumber", lalu fallback urutan izin -> cuti.
    $tabel = [
        'izin' => ['tb_pengajuan_izin', 'id_pengajuan_izin', 'status_izin'],
        'cuti' => ['tb_pengajuan_cuti', 'id_pengajuan_cuti', 'status_cuti'],
    ];
    $urutan = $sumber === 'cuti' ? ['cuti', 'izin'] : ['izin', 'cuti'];

    foreach ($urutan as $key) {
        [$namaTabel, $pk, $kolomStatus] = $tabel[$key];

        $st = $db->prepare("SELECT 1 FROM {$namaTabel} WHERE {$pk} = ? LIMIT 1");
        $st->execute([$id]);

        if ($st->fetchColumn()) {
            $db->prepare("UPDATE {$namaTabel} SET {$kolomStatus} = ? WHERE {$pk} = ?")
               ->execute([$statusWeb, $id]);

            api_out(200, [
                'success' => true,
                'message' => "Status pengajuan $key (ID: $id) berhasil diubah menjadi $statusInput.",
            ]);
        }
    }

    api_error(404, 'Pengajuan tidak ditemukan.');
}

/* ================================== PINJAMAN ================================== */

function api_loan_list(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));
    $db = api_db();

    $sql = 'SELECT p.*, k.nik_karyawan, k.nama_lengkap, b.tgl_bayar_pinjaman
            FROM tb_pengajuan_pinjaman p
            JOIN tb_karyawan k ON k.id_karyawan = p.id_karyawan
            LEFT JOIN tb_pinjaman b ON b.id_pengajuan_pinjaman = p.id_pengajuan_pinjaman';
    $params = [];

    if ($username !== '') {
        $sql .= ' WHERE LOWER(k.nik_karyawan) = LOWER(?) OR k.id_karyawan = ?';
        $params = [$username, ctype_digit($username) ? (int)$username : 0];
    }

    $sql .= ' ORDER BY p.tgl_pengajuan DESC, p.id_pengajuan_pinjaman DESC';

    $st = $db->prepare($sql);
    $st->execute($params);

    $data = [];

    foreach ($st->fetchAll() as $row) {
        $data[] = [
            'id'          => (int)$row['id_pengajuan_pinjaman'],
            'nik'         => (string)$row['nik_karyawan'],
            'username'    => (string)$row['nik_karyawan'],
            'nama'        => (string)$row['nama_lengkap'],
            'nominal'     => (float)$row['nominal_pinjaman'],
            'tenor'       => 0, // aplikasi lama selalu 0; pinjaman dipotong sekaligus
            'angsuran'    => 0,
            'alasan'      => (string)$row['alasan_pinjaman'],
            'status'      => api_map_status($row['status_pinjaman']),
            'tglPinjaman' => substr((string)$row['tgl_pengajuan'], 0, 10),
            'tglBayar'    => $row['tgl_bayar_pinjaman'] ?: null,
            'createdAt'   => api_iso($row['created_at']),
        ];
    }

    api_out(200, ['success' => true, 'data' => $data]);
}

function api_loan_create(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));
    $nominal  = (float)($in['nominal'] ?? 0);
    $alasan   = trim((string)($in['alasan'] ?? ''));

    $karyawan = api_find_karyawan($username);
    if ($karyawan === null) {
        api_error(404, $username !== ''
            ? "Karyawan ($username) tidak ditemukan."
            : 'Username tidak valid.');
    }

    if ($nominal <= 0) {
        api_error(400, 'Nominal pinjaman tidak valid.');
    }

    $db = api_db();

    // Ikuti saklar "buka_pinjaman" milik website (menu Pengajuan Pinjaman).
    $nilai = $db->query("SELECT nilai FROM tb_pengaturan WHERE nama_pengaturan = 'buka_pinjaman'")->fetchColumn();
    if ($nilai !== false && (string)$nilai !== '1') {
        api_out(200, [
            'success' => false,
            'message' => 'Pengajuan pinjaman sedang ditutup oleh HRD. Silakan coba lagi lain waktu.',
        ]);
    }

    $st = $db->prepare(
        'INSERT INTO tb_pengajuan_pinjaman
            (id_karyawan, tgl_pengajuan, nominal_pinjaman, alasan_pinjaman, foto_wajah, foto_id_card, status_pinjaman)
         VALUES (?, NOW(), ?, ?, NULL, NULL, ?)'
    );
    $st->execute([
        (int)$karyawan['id_karyawan'],
        (int)round($nominal),
        $alasan !== '' ? $alasan : '-',
        'pending',
    ]);

    api_out(200, ['success' => true, 'message' => 'Pengajuan pinjaman berhasil disimpan.']);
}

function api_loan_status(array $params): void
{
    // Paritas perilaku backend lama: perubahan status pinjaman dilakukan dari
    // website (menu Pengajuan Pinjaman), endpoint ini hanya menjawab sukses.
    $id     = (int)($params[0] ?? 0);
    $in     = api_input();
    $status = trim((string)($in['status'] ?? ''));

    api_out(200, [
        'success' => true,
        'message' => "Status pinjaman (ID: $id) berhasil diubah menjadi $status.",
    ]);
}

/* ================================== GEOFENCE ================================== */

function api_geofence_list(): void
{
    $in = api_input();

    // Hanya site aktif & berkoordinat yang dikirim ke aplikasi (site tanpa
    // koordinat dilewati supaya aplikasi tidak error saat menghitung jarak).
    $activeOnly = true;
    if (array_key_exists('activeOnly', $in)) {
        $activeOnly = filter_var($in['activeOnly'], FILTER_VALIDATE_BOOLEAN);
    }

    $sql = 'SELECT * FROM tb_site WHERE latitude IS NOT NULL AND longitude IS NOT NULL';
    if ($activeOnly) {
        $sql .= ' AND is_aktif = 1';
    }
    $sql .= ' ORDER BY id_site ASC';

    $data = [];

    foreach (api_db()->query($sql)->fetchAll() as $row) {
        $data[] = [
            'id'        => (int)$row['id_site'],
            'kode'      => (string)($row['kode_lokasi'] ?? ''),
            'nama'      => (string)$row['nama_site'],
            'latitude'  => (float)$row['latitude'],
            'longitude' => (float)$row['longitude'],
            'radius'    => (float)$row['radius_meter'],
            'alamat'    => (string)($row['lokasi'] ?? ''),
            'deskripsi' => (string)($row['deskripsi_lokasi'] ?? ''),
            'isAktif'   => (bool)$row['is_aktif'],
            'createdAt' => api_iso($row['created_at']),
        ];
    }

    api_out(200, ['success' => true, 'data' => $data]);
}

function api_geofence_update(array $params): void
{
    $id = (int)($params[0] ?? 0);
    $in = api_input();

    $db = api_db();

    $st = $db->prepare('SELECT id_site FROM tb_site WHERE id_site = ?');
    $st->execute([$id]);
    if (!$st->fetch()) {
        api_error(404, 'Lokasi tidak ditemukan.');
    }

    $set  = [];
    $vals = [];

    if (array_key_exists('nama', $in) && trim((string)$in['nama']) !== '') {
        $set[] = 'nama_site = ?';
        $vals[] = trim((string)$in['nama']);
    }
    if (array_key_exists('latitude', $in)) {
        $set[] = 'latitude = ?';
        $vals[] = (float)$in['latitude'];
    }
    if (array_key_exists('longitude', $in)) {
        $set[] = 'longitude = ?';
        $vals[] = (float)$in['longitude'];
    }
    if (array_key_exists('radius', $in)) {
        $set[] = 'radius_meter = ?';
        $vals[] = (float)$in['radius'];
    }
    if (array_key_exists('alamat', $in)) {
        $set[] = 'lokasi = ?';
        $vals[] = trim((string)$in['alamat']);
    }
    if (array_key_exists('deskripsi', $in)) {
        $set[] = 'deskripsi_lokasi = ?';
        $vals[] = trim((string)$in['deskripsi']);
    }
    if (array_key_exists('isAktif', $in)) {
        $set[] = 'is_aktif = ?';
        $vals[] = filter_var($in['isAktif'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    if ($set === []) {
        api_error(400, 'Tidak ada perubahan yang dikirim.');
    }

    $vals[] = $id;
    $db->prepare('UPDATE tb_site SET ' . implode(', ', $set) . ' WHERE id_site = ?')->execute($vals);

    api_out(200, ['success' => true, 'message' => 'Lokasi berhasil diperbarui.']);
}

/* ================================== SLIP GAJI ================================== */

function api_payslip_list(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));

    $sql = 'SELECT s.*, k.nik_karyawan, k.nama_lengkap
            FROM tb_slip_gaji s
            JOIN tb_karyawan k ON k.id_karyawan = s.id_karyawan';
    $params = [];

    if ($username !== '') {
        $sql .= ' WHERE LOWER(k.nik_karyawan) = LOWER(?) OR k.id_karyawan = ?';
        $params = [$username, ctype_digit($username) ? (int)$username : 0];
    }

    $sql .= ' ORDER BY s.dibuat_pada DESC, s.id_slip_gaji DESC';

    $st = api_db()->prepare($sql);
    $st->execute($params);

    $data = [];

    foreach ($st->fetchAll() as $row) {
        $data[] = [
            'id'            => (int)$row['id_slip_gaji'],
            'nik'           => (string)$row['nik_karyawan'],
            'username'      => (string)$row['nik_karyawan'],
            'nama'          => (string)$row['nama_lengkap'],
            'periode'       => (string)$row['periode'],
            'gajiPokok'     => (float)$row['gaji_pokok'],
            'tunjangan'     => (float)$row['tunjangan'],
            'potongan'      => (float)$row['potongan'],
            'totalDiterima' => (float)$row['total_diterima'],
            'status'        => (string)$row['status_rilis'],
            'createdAt'     => api_iso($row['dibuat_pada']),
        ];
    }

    api_out(200, ['success' => true, 'data' => $data]);
}

function api_payslip_create(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));
    $periode  = trim((string)($in['periode'] ?? ''));

    if ($periode === '') {
        api_error(400, 'Periode wajib diisi.');
    }

    $karyawan = api_find_karyawan($username);
    if ($karyawan === null) {
        api_error(404, $username !== ''
            ? "Karyawan ($username) tidak ditemukan."
            : 'Username tidak valid.');
    }

    $gajiPokok = (float)($in['gajiPokok'] ?? 0);
    $tunjangan = (float)($in['tunjangan'] ?? 0);
    $potongan  = (float)($in['potongan'] ?? 0);
    $total     = $gajiPokok + $tunjangan - $potongan;

    $st = api_db()->prepare(
        'INSERT INTO tb_slip_gaji
            (id_karyawan, periode, gaji_pokok, tunjangan, potongan, total_diterima, status_rilis, catatan)
         VALUES (?, ?, ?, ?, ?, ?, ?, NULL)'
    );
    $st->execute([
        (int)$karyawan['id_karyawan'],
        $periode,
        $gajiPokok,
        $tunjangan,
        $potongan,
        $total,
        'DITERBITKAN',
    ]);

    api_out(200, ['success' => true, 'message' => 'Slip gaji berhasil diterbitkan.']);
}

function api_payslip_delete(array $params): void
{
    $id = (int)($params[0] ?? 0);

    $st = api_db()->prepare('DELETE FROM tb_slip_gaji WHERE id_slip_gaji = ?');
    $st->execute([$id]);

    if ($st->rowCount() === 0) {
        api_error(404, 'Slip gaji tidak ditemukan.');
    }

    api_out(200, ['success' => true, 'message' => 'Slip gaji berhasil dihapus.']);
}

/* ================================ MANAJEMEN USER ================================ */

function api_users_list(): void
{
    $sql = 'SELECT k.id_karyawan AS id, k.nik_karyawan AS nik, k.nik_karyawan AS username,
                   k.nama_lengkap AS nama, CONCAT(k.nik_karyawan, \'@ipm.local\') AS email,
                   COALESCE(k.no_telp, \'\') AS noHp, \'karyawan\' AS role,
                   COALESCE(j.nama_jabatan, \'-\') AS jabatan,
                   k.created_at AS createdAt
            FROM tb_karyawan k
            LEFT JOIN tb_karier kr ON kr.id_karier =
                (SELECT MAX(kr2.id_karier) FROM tb_karier kr2 WHERE kr2.id_karyawan = k.id_karyawan)
            LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
            ORDER BY k.id_karyawan ASC';

    $data = [];

    foreach (api_db()->query($sql)->fetchAll() as $row) {
        $data[] = [
            'id'        => (int)$row['id'],
            'nik'       => (string)$row['nik'],
            'username'  => (string)$row['username'],
            'nama'      => (string)$row['nama'],
            'email'     => (string)$row['email'],
            'noHp'      => (string)$row['noHp'],
            'role'      => (string)$row['role'],
            'jabatan'   => (string)$row['jabatan'],
            'createdAt' => api_iso($row['createdAt']),
        ];
    }

    api_out(200, ['success' => true, 'data' => $data]);
}

function api_users_create(): void
{
    // Pembuatan akun karyawan dikelola dari website (menu Data Karyawan)
    // supaya data karier/gaji tetap lengkap satu sumber.
    api_out(200, [
        'success' => false,
        'message' => 'Pembuatan akun dikelola dari website HRD (menu Data Karyawan).',
    ]);
}

function api_users_delete(array $params): void
{
    api_out(200, [
        'success' => false,
        'message' => 'Penghapusan akun dikelola dari website HRD (menu Data Karyawan).',
    ]);
}

/* ================================= NOTIFIKASI ================================= */

function api_notifications_list(): void
{
    $in = api_input();

    $username = trim((string)($in['username'] ?? ''));

    $sql = 'SELECT n.*, k.nik_karyawan, k.nama_lengkap
            FROM tb_notifikasi n
            JOIN tb_karyawan k ON k.id_karyawan = n.id_karyawan';
    $params = [];

    if ($username !== '') {
        $sql .= ' WHERE LOWER(k.nik_karyawan) = LOWER(?) OR k.id_karyawan = ?';
        $params = [$username, ctype_digit($username) ? (int)$username : 0];
    }

    $sql .= ' ORDER BY n.dibuat_pada DESC, n.id_notifikasi DESC LIMIT 500';

    $st = api_db()->prepare($sql);
    $st->execute($params);

    $data = [];

    foreach ($st->fetchAll() as $row) {
        $data[] = [
            'id'        => (int)$row['id_notifikasi'],
            'nik'       => (string)$row['nik_karyawan'],
            'username'  => (string)$row['nik_karyawan'],
            'nama'      => (string)$row['nama_lengkap'],
            'judul'     => (string)$row['judul'],
            'pesan'     => (string)$row['pesan'],
            'kategori'  => (string)$row['kategori'],
            'isDibaca'  => (bool)$row['is_dibaca'],
            'createdAt' => api_iso($row['dibuat_pada']),
        ];
    }

    api_out(200, ['success' => true, 'data' => $data]);
}
