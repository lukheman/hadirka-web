<?php
// app/controller/cetak/cetak-kontrak.php
// Cetak kontrak kerja (.docx): template Word milik jabatan + biodata karyawan.
// Tanpa library tambahan — penggantian placeholder ${...} langsung di XML (ZipArchive),
// sehingga aman dijalankan di shared hosting.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['id_user'])) {
    http_response_code(403);
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

$id_karier = (int)($_GET['id_karier'] ?? 0);
if ($id_karier <= 0) {
    exit('ID karier tidak valid.');
}

// ---------- 1. Data karier + karyawan + identitas + jabatan ----------
$stmt = $koneksi->prepare(
    "SELECT k.*, kr.nik_karyawan, kr.nama_lengkap, kr.jenis_kelamin, kr.tgl_lahir, kr.tempat_lahir, " .
    "j.nama_jabatan, d.nama_departemen, s.nama_site, idn.nik_ktp " .
    "FROM tb_karier k " .
    "JOIN tb_karyawan kr ON kr.id_karyawan = k.id_karyawan " .
    "LEFT JOIN tb_jabatan j ON j.id_jabatan = k.id_jabatan " .
    "LEFT JOIN tb_departemen d ON d.id_departemen = k.id_departemen " .
    "LEFT JOIN tb_site s ON s.id_site = k.id_site " .
    "LEFT JOIN tb_identitas idn ON idn.id_karyawan = k.id_karyawan " .
    "WHERE k.id_karier = ? LIMIT 1"
);
$stmt->bind_param("i", $id_karier);
$stmt->execute();
$kar = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$kar) {
    exit('Data karier tidak ditemukan.');
}

// ---------- 2. Template terbaru milik jabatan ----------
$id_jabatan = (int)($kar['id_jabatan'] ?? 0);
$tpl = null;
if ($id_jabatan > 0) {
    $qt = $koneksi->query(
        "SELECT t.id_template, t.nama_kontrak, t.`file`, t.nama_asli " .
        "FROM tb_template_kontrak_jabatan p " .
        "JOIN tb_template_kontrak t ON t.id_template = p.id_template " .
        "WHERE p.id_jabatan = '$id_jabatan' ORDER BY t.id_template DESC LIMIT 1"
    );
    $tpl = ($qt && $qt->num_rows > 0) ? $qt->fetch_assoc() : null;
}
if (!$tpl) {
    exit('Belum ada template kontrak untuk jabatan ini. Upload dulu di menu Template Kontrak.');
}
$src = __DIR__ . '/../../uploads/kontrak/' . basename((string)$tpl['file']);
if (!is_file($src)) {
    exit('File template tidak ditemukan di server.');
}

// ---------- 3. Siapkan nilai pengganti ----------
$num = function ($v) {
    $s = trim((string)$v);
    if ($s === '') {
        return 0;
    }
    if (strpos($s, ',') !== false) { // format Indonesia: 3.500.000,50
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    }
    return is_numeric($s) ? (float)$s : 0;
};
$rp = function ($v) use ($num) {
    $v = $num($v);
    $dec = (abs($v - round($v)) < 0.005) ? 0 : 2;
    return 'Rp ' . number_format($v, $dec, ',', '.');
};

// Tunjangan milik karier (dinormalkan per nama).
$tunj = [];
$q = $koneksi->query("SELECT nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan WHERE id_karier = '$id_karier'");
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $key = strtolower(trim((string)$r['nama_tunjangan']));
        $val = $num($r['jumlah_tunjangan']);
        $tunj[$key] = ($tunj[$key] ?? 0) + $val;
    }
}
$cariTunj = function (array $names) use ($tunj) {
    foreach ($names as $x) {
        if (isset($tunj[strtolower($x)])) {
            return $tunj[strtolower($x)];
        }
    }
    return 0;
};
$bonus = 0;
foreach ($tunj as $k => $v) {
    if (strpos($k, 'bonus') === 0) {
        $bonus += $v;
    }
}

// Tempat, tanggal lahir Indonesia.
$namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$ttl = '-';
if (!empty($kar['tempat_lahir']) || !empty($kar['tgl_lahir'])) {
    $ttl = trim((string)($kar['tempat_lahir'] ?? ''));
    if (!empty($kar['tgl_lahir']) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string)$kar['tgl_lahir'], $m)) {
        $tglId = ((int)$m[3]) . ' ' . ($namaBulan[(int)$m[2]] ?? $m[2]) . ' ' . $m[1];
        $ttl = $ttl !== '' ? ($ttl . ', ' . $tglId) : $tglId;
    }
    if ($ttl === '') {
        $ttl = '-';
    }
}

$nik = trim((string)($kar['nik_ktp'] ?? ''));

$vars = [
    'nama'                 => trim((string)($kar['nama_lengkap'] ?? '')) !== '' ? $kar['nama_lengkap'] : '-',
    'jabatan'              => trim((string)($kar['nama_jabatan'] ?? '')) !== '' ? $kar['nama_jabatan'] : '-',
    'jenis_kelamin'        => jk($kar['jenis_kelamin'] ?? ''),
    'tempat_tanggal_lahir' => $ttl,
    'nik'                  => $nik,
    'gaji_pokok'           => $rp($kar['gaji_pokok'] ?? 0),
    'tunjangan_daerah'     => $rp($cariTunj(['tunjangan daerah'])),
    'tunjangan_hidup'      => $rp($cariTunj(['tunjangan hidup'])),
    'tunjangan_uang_makan' => $rp($cariTunj(['tunjangan uang makan', 'tunjangan makan'])),
    'tunjangan_skill'      => $rp($cariTunj(['tunjangan skill'])),
    'bonus'                => $rp($bonus),
    'upah_lembur'          => $rp($kar['lembur'] ?? 0),
    'izin'                 => $rp($kar['pot_izin'] ?? 0),
    'mangkir'              => $rp($kar['pot_mangkir'] ?? 0),
];
if (trim((string)$vars['jenis_kelamin']) === '') {
    $vars['jenis_kelamin'] = '-';
}

// ---------- 4. Penggantian placeholder di XML (tahan terhadap run terpecah) ----------
function kontrak_ganti_vars($xml, array $vars)
{
    if (!preg_match_all('/<w:t((?:\s[^>]*)?)>(.*?)<\/w:t>/s', $xml, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        return $xml;
    }
    $n = count($m);
    $texts = [];
    $starts = [];
    $pos = 0;
    for ($i = 0; $i < $n; $i++) {
        $t = html_entity_decode($m[$i][2][0], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $texts[$i] = $t;
        $starts[$i] = $pos;
        $pos += strlen($t);
        $mapPad = strlen($t);
        for ($k = 0; $k < $mapPad; $k++) {
            $map[] = $i;
        }
    }
    $full = implode('', $texts);
    if (!preg_match_all('/\$\{([A-Za-z0-9_]+)\}/', $full, $mm, PREG_OFFSET_CAPTURE)) {
        return $xml;
    }
    foreach ($mm[0] as $idx => $mt) {
        $name = $mm[1][$idx][0];
        if (!array_key_exists($name, $vars)) {
            continue;
        }
        $val = (string)$vars[$name];
        $p = $mt[1];
        $len = strlen($mt[0]);
        $f = $map[$p];
        $l = $map[$p + $len - 1];
        $lf = $p - $starts[$f];
        $ll = $p + $len - $starts[$l];
        if ($f === $l) {
            $texts[$f] = substr($texts[$f], 0, $lf) . $val . substr($texts[$f], $ll);
        } else {
            $texts[$f] = substr($texts[$f], 0, $lf) . $val;
            for ($i = $f + 1; $i < $l; $i++) {
                $texts[$i] = '';
            }
            $texts[$l] = substr($texts[$l], $ll);
        }
    }
    $out = '';
    $last = 0;
    for ($i = 0; $i < $n; $i++) {
        $off = $m[$i][0][1];
        $attrs = $m[$i][1][0];
        $new = htmlspecialchars($texts[$i], ENT_XML1 | ENT_COMPAT, 'UTF-8');
        if ($new !== '' && ($new[0] === ' ' || substr($new, -1) === ' ') && stripos($attrs, 'xml:space') === false) {
            $attrs .= ' xml:space="preserve"';
        }
        $out .= substr($xml, $last, $off - $last) . '<w:t' . $attrs . '>' . $new . '</w:t>';
        $last = $off + strlen($m[$i][0][0]);
    }
    return $out . substr($xml, $last);
}

// ---------- 5. Salin template, ganti, unduh ----------
$tmp = tempnam(sys_get_temp_dir(), 'kontrak_') . '.docx';
if (!copy($src, $tmp)) {
    exit('Gagal menyiapkan file kontrak.');
}
$zip = new ZipArchive();
if ($zip->open($tmp) !== true) {
    @unlink($tmp);
    exit('Gagal membuka template kontrak.');
}
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', (string)$name)) {
        $content = $zip->getFromIndex($i);
        if ($content !== false) {
            $zip->addFromString($name, kontrak_ganti_vars($content, $vars));
        }
    }
}
$zip->close();

$namaFile = 'Kontrak - ' . ($kar['nama_lengkap'] ?? 'karyawan');
if (!empty($kar['nama_jabatan'])) {
    $namaFile .= ' (' . $kar['nama_jabatan'] . ')';
}
$namaFile = preg_replace('/[\\\\\/:*?"<>|]/', '-', $namaFile) . '.docx';

while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $namaFile . '"');
header('Content-Length: ' . filesize($tmp));
readfile($tmp);
@unlink($tmp);
exit;
