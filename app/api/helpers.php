<?php

declare(strict_types=1);

/**
 * API mobile — Helper bersama.
 *
 * API ini adalah pengganti backend Node.js (db_absen_tjs) untuk aplikasi
 * absensi Flutter. Kontrak request/response sengaja dipertahankan sama
 * dengan backend Node.js supaya aplikasi tidak perlu banyak diubah:
 *   - Sukses : {"success": true, "data": ...} (login memakai key "user")
 *   - Gagal  : {"success": false, "message": "..."}
 *
 * Data disimpan di database website (ipm_hris):
 *   tb_absen (log absen aplikasi, sumber='mobile'), tb_notifikasi,
 *   tb_slip_gaji, tb_site (geofence),
 *   tb_pengajuan_izin/tb_pengajuan_cuti/tb_pengajuan_pinjaman.
 */

/**
 * Koneksi PDO (singleton) ke database website.
 */
function api_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        require_once __DIR__ . '/../config/database.php';
        $pdo = db_connect();
    }

    return $pdo;
}

/**
 * Kirim response JSON lalu hentikan eksekusi.
 */
function api_out(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Response error standar aplikasi.
 */
function api_error(int $code, string $message): void
{
    api_out($code, ['success' => false, 'message' => $message]);
}

/**
 * Body request (JSON; fallback form-encoded / query string).
 */
function api_input(): array
{
    static $data = null;

    if ($data !== null) {
        return $data;
    }

    $raw  = file_get_contents('php://input');
    $json = ($raw !== false && $raw !== '') ? json_decode($raw, true) : null;

    $data = is_array($json) ? $json : array_merge($_GET, $_POST);

    return $data;
}

/**
 * Cari karyawan berdasarkan: id_karyawan (angka), NIK karyawan,
 * atau NIK KTP (tb_identitas.nik_ktp).
 */
function api_find_karyawan(string $identifier): ?array
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return null;
    }

    $db = api_db();

    // 1. NIK karyawan internal (kebiasaan data website: IPM-00001)
    $st = $db->prepare('SELECT * FROM tb_karyawan WHERE LOWER(nik_karyawan) = LOWER(?) LIMIT 1');
    $st->execute([$identifier]);
    $row = $st->fetch();
    if ($row) {
        return $row;
    }

    // 2. id_karyawan numerik
    if (ctype_digit($identifier)) {
        $st = $db->prepare('SELECT * FROM tb_karyawan WHERE id_karyawan = ? LIMIT 1');
        $st->execute([(int)$identifier]);
        $row = $st->fetch();
        if ($row) {
            return $row;
        }
    }

    // 3. NIK KTP pada tb_identitas (alur login aplikasi lama)
    $st = $db->prepare(
        'SELECT k.* FROM tb_identitas i
         JOIN tb_karyawan k ON k.id_karyawan = i.id_karyawan
         WHERE LOWER(i.nik_ktp) = LOWER(?) LIMIT 1'
    );
    $st->execute([$identifier]);

    return $st->fetch() ?: null;
}

/**
 * Verifikasi password karyawan.
 *
 * Mendukung tiga format penyimpanan (masa transisi):
 *   - bcrypt (password_hash)          -> password_verify()
 *   - MD5 (sisa data aplikasi lama)   -> hash_equals(md5($password))
 *   - teks biasa (data seed/demo)     -> perbandingan konstan-waktu
 */
function api_verify_password(array $karyawan, string $password): bool
{
    $stored = (string)($karyawan['password'] ?? '');

    if ($stored === '') {
        return false;
    }

    if (str_starts_with($stored, '$2') || str_starts_with($stored, '$argon')) {
        return password_verify($password, $stored);
    }

    if (strlen($stored) === 32 && ctype_xdigit($stored)) {
        return hash_equals($stored, md5($password));
    }

    return hash_equals($stored, $password);
}

/**
 * Status karyawan yang masih boleh memakai aplikasi.
 * Status terminal (Resign/PHK/Pensiun/Habis Kontrak/Black List) ditolak.
 */
function api_karyawan_boleh_login(array $karyawan): bool
{
    $status = strtolower(trim((string)($karyawan['status_karyawan'] ?? '')));

    if ($status === '') {
        return true; // data lama tanpa status dianggap boleh
    }

    $ditolak = ['resign', 'phk', 'pensiun', 'habis kontrak', 'black list'];

    return !in_array($status, $ditolak, true);
}

/**
 * Payload "user" yang diharapkan aplikasi (kunci sama dengan backend Node.js).
 */
function api_user_payload(array $karyawan): array
{
    $nik = (string)($karyawan['nik_karyawan'] ?? '');
    [$jabatan, $departemen] = api_jabatan_departemen((int)$karyawan['id_karyawan']);

    return [
        'id'         => (int)$karyawan['id_karyawan'],
        'nik'        => $nik,
        'username'   => $nik,
        'nama'       => (string)($karyawan['nama_lengkap'] ?? ''),
        'email'      => $nik . '@ipm.local',
        'noHp'       => (string)($karyawan['no_telp'] ?? ''),
        'role'       => 'karyawan',
        'jabatan'    => $jabatan,
        'departemen' => $departemen,
        'fotoProfil' => api_foto_profil_base64($karyawan),
        'token'      => 'token_karyawan_' . bin2hex(random_bytes(16)),
    ];
}

/**
 * Jabatan & departemen efektif (karier terbaru yang masih berjalan diutamakan).
 *
 * @return array{0:string,1:string}
 */
function api_jabatan_departemen(int $id_karyawan): array
{
    $st = api_db()->prepare(
        "SELECT j.nama_jabatan AS jabatan, d.nama_departemen AS departemen
         FROM tb_karier k
         LEFT JOIN tb_jabatan j ON j.id_jabatan = k.id_jabatan
         LEFT JOIN tb_departemen d ON d.id_departemen = k.id_departemen
         WHERE k.id_karyawan = ?
         ORDER BY (k.tgl_selesai IS NULL OR k.tgl_selesai >= CURDATE()) DESC,
                  k.tgl_mulai DESC, k.id_karier DESC
         LIMIT 1"
    );
    $st->execute([$id_karyawan]);
    $row = $st->fetch() ?: [];

    return [
        trim((string)($row['jabatan'] ?? '')) !== '' ? (string)$row['jabatan'] : '-',
        trim((string)($row['departemen'] ?? '')) !== '' ? (string)$row['departemen'] : '-',
    ];
}

/**
 * Foto profil karyawan sebagai base64 (dibaca dari app/uploads/profil/*).
 * Aplikasi menampilkan fotoProfil dalam bentuk base64.
 */
function api_foto_profil_base64(array $karyawan): ?string
{
    $foto = trim((string)($karyawan['foto'] ?? ''));
    if ($foto === '') {
        return null;
    }

    // Format data lama: langsung base64 / data URI.
    if (str_starts_with($foto, 'data:')) {
        return $foto;
    }

    // Konvensi website: nama file di app/uploads/profil/.
    $path = __DIR__ . '/../uploads/profil/' . basename($foto);
    if (!is_file($path)) {
        return null;
    }

    $bytes = file_get_contents($path);
    if ($bytes === false) {
        return null;
    }

    return base64_encode($bytes);
}

/**
 * Simpan gambar base64 ke folder upload; kembalikan path relatif ala API lama
 * (contoh: "uploads/absensi/absen_20261017_083000_IPM-00001.jpg") atau null.
 */
function api_save_base64_image(string $base64, string $subdir, string $filename, int $maxBytes = 8388608): ?string
{
    $base64 = trim($base64);
    if ($base64 === '') {
        return null;
    }

    // Buang prefix data URI bila ada (mis. "data:image/jpeg;base64,").
    if (preg_match('#^data:image/[a-zA-Z0-9+.-]+;base64,#', $base64)) {
        $base64 = substr($base64, (int)strpos($base64, ',') + 1);
    }

    $bytes = base64_decode($base64, true);
    if ($bytes === false || $bytes === '' || strlen($bytes) > $maxBytes) {
        return null;
    }

    $dir = __DIR__ . '/../uploads/' . trim($subdir, '/');
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return null;
    }

    $target = $dir . '/' . $filename;
    if (file_put_contents($target, $bytes) === false) {
        return null;
    }

    return 'uploads/' . trim($subdir, '/') . '/' . $filename;
}

/**
 * Nama file aman (buang karakter selain huruf/angka/-/_/+spasi jadi _).
 */
function api_safe_name(string $text): string
{
    $text = preg_replace('/[^A-Za-z0-9\-_]+/', '_', $text);
    $text = trim((string)$text, '_');

    return $text !== '' ? $text : 'file';
}

/**
 * Waktu sekarang zona Asia/Makassar (WITA).
 */
function api_wita_now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone('Asia/Makassar'));
}

/**
 * String jam ala aplikasi: "HH:MM WITA".
 */
function api_time_str(DateTimeImmutable $when): string
{
    return $when->format('H:i') . ' WITA';
}

/**
 * Menit keterlambatan terhadap jam masuk 08:00 WITA.
 */
function api_late_minutes(DateTimeImmutable $when): int
{
    $jam = (int)$when->format('G');
    $menit = (int)$when->format('i');

    return ($jam > 8 || ($jam === 8 && $menit > 0)) ? ($jam - 8) * 60 + $menit : 0;
}

/**
 * Hitung durasi kerja (menit) dari string jam "HH:MM ...".
 */
function api_durasi_menit(?string $waktuIn, ?string $waktuOut): int
{
    $parse = static function (?string $t): ?int {
        if ($t === null || !preg_match('/(\d{1,2}):(\d{2})/', $t, $m)) {
            return null;
        }

        return ((int)$m[1]) * 60 + (int)$m[2];
    };

    $in = $parse($waktuIn);
    $out = $parse($waktuOut);

    if ($in === null || $out === null || $out < $in) {
        return 0;
    }

    return $out - $in;
}

/**
 * Jarak dua titik (meter) — Haversine.
 */
function api_haversine_m(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earth = 6371000.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

    return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

/**
 * Site (geofence) terdekat dari titik absen.
 * Fallback: site aktif pertama; terakhir: tanpa site.
 *
 * @return array{id:?int,nama:string,jarak:float}
 */
function api_nearest_site(float $lat, float $lng): array
{
    $db = api_db();

    $sites = $db->query(
        'SELECT id_site, nama_site, latitude, longitude FROM tb_site
         WHERE is_aktif = 1 AND latitude IS NOT NULL AND longitude IS NOT NULL'
    )->fetchAll();

    $best = null;
    $bestJarak = null;

    foreach ($sites as $s) {
        $jarak = api_haversine_m($lat, $lng, (float)$s['latitude'], (float)$s['longitude']);
        if ($bestJarak === null || $jarak < $bestJarak) {
            $best = $s;
            $bestJarak = $jarak;
        }
    }

    if ($best !== null) {
        return [
            'id'    => (int)$best['id_site'],
            'nama'  => (string)$best['nama_site'],
            'jarak' => round((float)$bestJarak, 2),
        ];
    }

    $fallback = $db->query('SELECT id_site, nama_site FROM tb_site WHERE is_aktif = 1 ORDER BY id_site ASC LIMIT 1')->fetch();

    if ($fallback) {
        return ['id' => (int)$fallback['id_site'], 'nama' => (string)$fallback['nama_site'], 'jarak' => 0.0];
    }

    return ['id' => null, 'nama' => 'Kantor', 'jarak' => 0.0];
}

/**
 * Mapping status tabel web (lowercase) -> status yang dikenal aplikasi.
 */
function api_map_status(?string $status): string
{
    $s = strtolower(trim((string)$status));

    return match ($s) {
        'pending', ''        => 'MENUNGGU',
        'disetujui', 'dicairkan', 'lunas' => 'DISETUJUI',
        'ditolak'            => 'DITOLAK',
        default              => strtoupper($s),
    };
}

/**
 * Mapping status aplikasi -> status tabel web.
 */
function api_map_status_web(?string $status): string
{
    $s = strtoupper(trim((string)$status));

    return match ($s) {
        'MENUNGGU', 'PENDING'      => 'pending',
        'DISETUJUI', 'DITERIMA'    => 'disetujui',
        'DITOLAK', 'DIBATALKAN'    => 'ditolak',
        'DICAIRKAN'                => 'dicairkan',
        default                     => 'pending',
    };
}

/**
 * Format tanggal/waktu dari DB -> gaya Node ("2026-08-01T10:15:00").
 */
function api_iso(?string $dbDate): ?string
{
    if ($dbDate === null || $dbDate === '') {
        return null;
    }

    return str_replace(' ', 'T', $dbDate);
}

/**
 * Parse rentang tanggal aplikasi "17/10/2026 - 19/10/2026".
 *
 * @return array{start:string,end:string}|null format Y-m-d
 */
function api_parse_rentang(string $tanggal): ?array
{
    if (!preg_match(
        '#(\d{1,2})/(\d{1,2})/(\d{4})(?:\s*-\s*(\d{1,2})/(\d{1,2})/(\d{4}))?#',
        $tanggal,
        $m
    )) {
        return null;
    }

    $start = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);

    if (empty($m[4])) {
        return ['start' => $start, 'end' => $start];
    }

    $end = sprintf('%04d-%02d-%02d', (int)$m[6], (int)$m[5], (int)$m[4]);

    if ($end < $start) {
        $end = $start;
    }

    return ['start' => $start, 'end' => $end];
}

/**
 * Lampiran dari aplikasi: hanya simpan path yang masuk akal di server.
 * Path lokal device (mis. /data/user/0/...) diabaikan -> null.
 */
function api_clean_lampiran($path): ?string
{
    if (!is_string($path)) {
        return null;
    }

    $path = trim($path);
    if ($path === '' || str_starts_with($path, '/') || str_contains($path, ':\\')) {
        return null;
    }

    if (str_contains($path, '://') || strlen($path) > 255) {
        return null;
    }

    return $path;
}
