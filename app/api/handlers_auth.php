<?php

declare(strict_types=1);

/**
 * API mobile — Handler autentikasi & profil karyawan.
 *
 * Kontrak mengikuti backend Node.js lama:
 *   POST /auth/login     { identifier, password }
 *   POST /auth/register  { identifier|nik, nama, password, noHp }
 *                         (di sini: aktivasi akun)
 *   PUT  /user/profile   { username, nama, noHp, fotoProfil }
 *   PUT  /user/password  { username, oldPassword, newPassword }
 */

function api_auth_login(): void
{
    $in = api_input();

    $identifier = api_auth_identifier($in);
    $password   = (string)($in['password'] ?? '');

    if ($identifier === '' || $password === '') {
        api_error(400, 'NIK & Password wajib diisi.');
    }

    $karyawan = api_find_karyawan($identifier);
    if ($karyawan === null) {
        api_error(404, 'NIK tidak ditemukan di database.');
    }

    if (!api_verify_password($karyawan, $password)) {
        api_error(401, 'Password Karyawan yang Anda masukkan salah.');
    }

    if (!api_karyawan_boleh_login($karyawan)) {
        api_error(403, 'Akun tidak aktif (' . (string)($karyawan['status_karyawan'] ?? '-') . '). Hubungi HRD.');
    }

    api_out(200, ['success' => true, 'user' => api_user_payload($karyawan)]);
}

/**
 * Cek kelayakan aktivasi akun sebelum aplikasi meminta password.
 * Hanya karyawan yang sudah dibuat HRD dan belum memiliki password yang boleh lanjut.
 */
function api_auth_check(): void
{
    $in = api_input();
    $identifier = api_auth_identifier($in);

    if ($identifier === '') {
        api_error(400, 'NIK karyawan / NIK KTP wajib diisi.');
    }

    $karyawan = api_find_karyawan($identifier);
    if ($karyawan === null) {
        api_error(404, 'NIK karyawan / NIK KTP belum terdaftar di HRD.');
    }

    if (!api_karyawan_boleh_login($karyawan)) {
        api_error(403, 'Data karyawan tidak aktif. Hubungi HRD.');
    }

    if (trim((string)($karyawan['password'] ?? '')) !== '') {
        api_error(409, 'Akun sudah diaktivasi. Silakan login menggunakan password Anda.');
    }

    api_out(200, [
        'success' => true,
        'data' => [
            'eligible' => true,
            'id' => (int)$karyawan['id_karyawan'],
            'id_karyawan' => (int)$karyawan['id_karyawan'],
            'nik' => (string)($karyawan['nik_karyawan'] ?? ''),
            'nik_karyawan' => (string)($karyawan['nik_karyawan'] ?? ''),
            'nik_ktp' => api_nik_ktp((int)$karyawan['id_karyawan']),
            'nama' => (string)($karyawan['nama_lengkap'] ?? ''),
            'noHp' => (string)($karyawan['no_telp'] ?? ''),
            'jabatan' => (string)($karyawan['jabatan'] ?? '-'),
        ],
    ]);
}

/**
 * Registrasi aplikasi = AKTIVASI akun karyawan yang sudah terdaftar di website.
 *
 * Karyawan tetap dibuat/dikelola HRD lewat menu Data Karyawan; aplikasi hanya
 * mengaktifkan login (set password) agar data tetap satu sumber (website).
 */
function api_auth_register(): void
{
    $in = api_input();

    $identifier = api_auth_identifier($in);
    $nik      = strtoupper(trim((string)($in['nik'] ?? '')));
    $nama     = trim((string)($in['nama'] ?? ''));
    $password = (string)($in['password'] ?? '');
    $noHp     = trim((string)($in['noHp'] ?? ''));

    if ($identifier === '' || $nama === '' || $password === '') {
        api_error(400, 'NIK karyawan / NIK KTP, Nama Lengkap, & Password wajib diisi.');
    }

    if (strlen($password) < 6) {
        api_error(400, 'Password minimal 6 karakter.');
    }

    $karyawan = api_find_karyawan($identifier);
    if ($karyawan === null) {
        api_error(404, 'NIK belum terdaftar di HRD. Hubungi HRD untuk mendaftarkan data Anda terlebih dahulu.');
    }

    if (!api_karyawan_boleh_login($karyawan)) {
        api_error(403, 'Akun tidak aktif. Hubungi HRD.');
    }

    if (trim((string)($karyawan['password'] ?? '')) !== '') {
        api_error(400, 'NIK sudah terdaftar. Silakan masuk, atau hubungi HRD bila lupa password.');
    }

    $db = api_db();

    $st = $db->prepare('UPDATE tb_karyawan SET password = ? WHERE id_karyawan = ?');
    $st->execute([password_hash($password, PASSWORD_DEFAULT), (int)$karyawan['id_karyawan']]);

    if ($noHp !== '' && trim((string)($karyawan['no_telp'] ?? '')) === '') {
        $st = $db->prepare('UPDATE tb_karyawan SET no_telp = ? WHERE id_karyawan = ?');
        $st->execute([$noHp, (int)$karyawan['id_karyawan']]);
        $karyawan['no_telp'] = $noHp;
    }

    api_out(200, [
        'success' => true,
        'message' => 'Pendaftaran berhasil! Akun Anda sudah aktif, silakan masuk.',
        'user'    => api_user_payload($karyawan),
    ]);
}

/** Ambil identifier dari kontrak lama maupun field eksplisit baru. */
function api_auth_identifier(array $input): string
{
    foreach (['identifier', 'nik'] as $key) {
        $value = trim((string)($input[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function api_user_profile(): void
{
    $in = api_input();

    $identifier = trim((string)($in['username'] ?? ($in['nik'] ?? '')));
    if ($identifier === '') {
        api_error(400, 'NIK wajib diisi.');
    }

    $karyawan = api_find_karyawan($identifier);
    if ($karyawan === null) {
        api_error(404, 'Pengguna tidak ditemukan.');
    }

    $db = api_db();
    $id = (int)$karyawan['id_karyawan'];

    // --- Foto profil (base64 dari aplikasi -> disimpan sebagai file, konvensi website) ---
    $fotoProfil = $in['fotoProfil'] ?? null;
    if (is_string($fotoProfil) && trim($fotoProfil) !== '') {
        $namaFile = api_safe_name((string)$karyawan['nik_karyawan'])
            . '_' . api_safe_name((string)($karyawan['nama_lengkap'] ?? 'profil')) . '.jpg';

        // Hapus file lama (nama bisa berbeda karena memuat nama karyawan).
        $lama = trim((string)($karyawan['foto'] ?? ''));
        if ($lama !== '' && !str_starts_with($lama, 'data:')) {
            $pathLama = __DIR__ . '/../uploads/profil/' . basename($lama);
            if (is_file($pathLama)) {
                @unlink($pathLama);
            }
        }

        if (api_save_base64_image($fotoProfil, 'profil', $namaFile) !== null) {
            $st = $db->prepare('UPDATE tb_karyawan SET foto = ? WHERE id_karyawan = ?');
            $st->execute([$namaFile, $id]);
        }
    }

    // --- Nama & nomor HP ---
    $nama = trim((string)($in['nama'] ?? ''));
    if ($nama !== '') {
        $st = $db->prepare('UPDATE tb_karyawan SET nama_lengkap = ?, no_telp = ? WHERE id_karyawan = ?');
        $st->execute([$nama, trim((string)($in['noHp'] ?? '')), $id]);
    }

    api_out(200, ['success' => true, 'message' => 'Data profil & foto berhasil disimpan!']);
}

function api_user_password(): void
{
    $in = api_input();

    $identifier  = trim((string)($in['username'] ?? ''));
    $oldPassword = (string)($in['oldPassword'] ?? '');
    $newPassword = (string)($in['newPassword'] ?? '');

    if ($identifier === '' || $oldPassword === '' || $newPassword === '') {
        api_error(400, 'Username, password lama, & password baru wajib diisi.');
    }

    if (strlen($newPassword) < 6) {
        api_error(400, 'Password baru minimal 6 karakter.');
    }

    $karyawan = api_find_karyawan($identifier);
    if ($karyawan === null) {
        api_error(404, 'Pengguna tidak ditemukan.');
    }

    if (!api_verify_password($karyawan, $oldPassword)) {
        api_error(401, 'Password lama tidak sesuai.');
    }

    $st = api_db()->prepare('UPDATE tb_karyawan SET password = ? WHERE id_karyawan = ?');
    $st->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int)$karyawan['id_karyawan']]);

    api_out(200, ['success' => true, 'message' => 'Password berhasil diperbarui.']);
}
