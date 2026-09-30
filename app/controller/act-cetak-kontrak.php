<?php
// app/controller/act-cetak-kontrak.php
// Cetak massal kontrak KOSONG (blanko, tidak terkait karyawan manapun).
// Karyawan baru belum ada datanya -> dokumen hanya berisi:
//   - nomor kontrak berurutan (nyata, mengonsumsi counter tb_nomor_urut),
//   - nama jabatan (dari jabatan terpilih),
//   - nilai kontrak dari detail template (gaji, lembur, potongan, tunjangan tampil=1).
// Isian personal (nama, NIK, TTL, alamat, tanggal) dikosongkan berupa titik-titik
// untuk diisi tulis tangan / diketik kemudian.
//
// Actions:
// - info     (POST id_jabatan) : kode jabatan, daftar template yg berlaku,
//              nomor terakhir (counter), terbesar (data), berikutnya.
// - generate (POST form langsung, target _blank) :
//              id_jabatan + id_template + jumlah (1-50) + tahun + nomor_mulai opsional
//              -> reservasi N nomor berurutan -> bangun N docx -> unduh ZIP.

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/nomor_surat.php';
require_once __DIR__ . '/../config/kontrak_helper.php';
require_once __DIR__ . '/../../vendor/autoload.php';

function cetak_json($status, $msg, array $extra = [])
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['status' => $status, 'msg' => $msg], $extra));
    exit;
}

function cetak_fail($msg, $code = 500)
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ---------- INFO: jabatan + template + posisi nomor ----------
if ($action === 'info') {
    $id_jabatan = (int)($_POST['id_jabatan'] ?? $_GET['id_jabatan'] ?? 0);
    if ($id_jabatan <= 0) cetak_json('error', 'Jabatan belum dipilih.');

    $qj = $koneksi->query("SELECT id_jabatan, kode_jabatan, nama_jabatan FROM tb_jabatan WHERE id_jabatan = '$id_jabatan' LIMIT 1");
    if (!$qj || $qj->num_rows === 0) cetak_json('error', 'Jabatan tidak ditemukan.');
    $jab = $qj->fetch_assoc();

    $templates = [];
    $qt = $koneksi->query(
        "SELECT t.id_template, t.nama_kontrak, t.nama_asli, t.`file` FROM tb_template_kontrak_jabatan p " .
        "JOIN tb_template_kontrak t ON t.id_template = p.id_template " .
        "WHERE p.id_jabatan = '$id_jabatan' ORDER BY t.id_template DESC"
    );
    if ($qt) {
        while ($tr = $qt->fetch_assoc()) {
            $id_tpl = (int)$tr['id_template'];
            $qd = $koneksi->query(
                "SELECT gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir " .
                "FROM tb_detail_kontrak WHERE id_template = '$id_tpl' LIMIT 1"
            );
            $tr['detail'] = ($qd && $qd->num_rows > 0) ? $qd->fetch_assoc() : null;
            $tr['jml_tunjangan'] = 0;
            $tr['jml_potongan'] = 0;
            $tr['tunjangan'] = [];
            $tr['potongan'] = [];
            if ($tr['detail']) {
                $qd2 = $koneksi->query("SELECT id_detail FROM tb_detail_kontrak WHERE id_template = '$id_tpl' LIMIT 1");
                if ($qd2 && $qd2->num_rows > 0) {
                    $id_det = (int)$qd2->fetch_assoc()['id_detail'];
                    $ct = $koneksi->query("SELECT COUNT(*) n FROM tb_tunjangan_kontrak WHERE id_detail_kontrak = '$id_det'");
                    $cp = $koneksi->query("SELECT COUNT(*) n FROM tb_potongan_kontrak WHERE id_detail_kontrak = '$id_det'");
                    if ($ct) $tr['jml_tunjangan'] = (int)$ct->fetch_assoc()['n'];
                    if ($cp) $tr['jml_potongan'] = (int)$cp->fetch_assoc()['n'];
                    $qt2 = $koneksi->query(
                        "SELECT nama_tunjangan, jenis_tunjangan, jumlah_tunjangan, tampil_template " .
                        "FROM tb_tunjangan_kontrak WHERE id_detail_kontrak = '$id_det' ORDER BY id_tunjangan_kontrak ASC"
                    );
                    if ($qt2) while ($x = $qt2->fetch_assoc()) $tr['tunjangan'][] = $x;
                    $qp2 = $koneksi->query(
                        "SELECT jenis_potongan, jumlah_potongan " .
                        "FROM tb_potongan_kontrak WHERE id_detail_kontrak = '$id_det' ORDER BY id_potongan_kontrak ASC"
                    );
                    if ($qp2) while ($x = $qp2->fetch_assoc()) $tr['potongan'][] = $x;
                }
            }
            $templates[] = $tr;
        }
    }

    // Posisi nomor: counter vs data terbesar (tanpa mengonsumsi).
    $terakhir = 0;
    $qc = $koneksi->query("SELECT terakhir FROM tb_nomor_urut WHERE nama = 'kontrak' LIMIT 1");
    if ($qc && $qc->num_rows > 0) $terakhir = (int)$qc->fetch_assoc()['terakhir'];
    $terbesar = 0;
    $qm = $koneksi->query(
        "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no_surat, '/', 2), '/', -1) AS UNSIGNED)), 0) AS mx " .
        "FROM tb_karier WHERE no_surat REGEXP '^TPJS-KONTRAK-[^/]+/[0-9]+/[0-9]{4}$'"
    );
    if ($qm) $terbesar = (int)$qm->fetch_assoc()['mx'];

    cetak_json('success', 'OK', [
        'id_jabatan' => (int)$jab['id_jabatan'],
        'kode_jabatan' => trim((string)($jab['kode_jabatan'] ?? '')),
        'nama_jabatan' => $jab['nama_jabatan'] ?? '',
        'templates' => $templates,
        'terakhir_counter' => $terakhir,
        'terbesar_data' => $terbesar,
        'berikutnya' => max($terakhir, $terbesar) + 1,
    ]);
}

// ---------- COUNTER global (tanpa jabatan) ----------
if ($action === 'counter') {
    $terakhir = 0;
    $qc = $koneksi->query("SELECT terakhir FROM tb_nomor_urut WHERE nama = 'kontrak' LIMIT 1");
    if ($qc && $qc->num_rows > 0) $terakhir = (int)$qc->fetch_assoc()['terakhir'];
    $terbesar = 0;
    $qm = $koneksi->query(
        "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no_surat, '/', 2), '/', -1) AS UNSIGNED)), 0) AS mx " .
        "FROM tb_karier WHERE no_surat REGEXP '^TPJS-KONTRAK-[^/]+/[0-9]+/[0-9]{4}$'"
    );
    if ($qm) $terbesar = (int)$qm->fetch_assoc()['mx'];
    cetak_json('success', 'OK', [
        'terakhir_counter' => $terakhir,
        'terbesar_data' => $terbesar,
        'berikutnya' => max($terakhir, $terbesar) + 1,
    ]);
}

// ---------- GENERATE: reservasi nomor + bangun ZIP ----------
if ($action === 'generate') {
    try {
        // Repeater: sekali generate bisa beberapa jabatan.
        // Menerima array id_jabatan[] + id_template[] + jumlah[] (tetap kompatibel dgn kiriman tunggal).
        $to_arr = function ($v) {
            if (is_array($v)) return array_values($v);
            return [$v];
        };
        $rows_jab = $to_arr($_POST['id_jabatan'] ?? []);
        $rows_tpl = $to_arr($_POST['id_template'] ?? []);
        $rows_jml = $to_arr($_POST['jumlah'] ?? []);
        $tahun = preg_replace('/\D/', '', (string)($_POST['tahun'] ?? ''));
        if (strlen($tahun) !== 4) $tahun = date('Y');
        $n_rows = max(count($rows_jab), count($rows_tpl), count($rows_jml));
        if ($n_rows < 1) throw new Exception('Tambahkan minimal satu baris jabatan.');
        if ($n_rows > 10) throw new Exception('Maksimal 10 baris jabatan per sekali cetak.');

        // Periode kontrak blanko: Tgl Mulai + Durasi (3/6/12 bulan).
        // Tgl Selesai = mulai + N bulan, mundur 1 hari (sama seperti form karier).
        $tgl_mulai = (string)($_POST['tgl_mulai'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_mulai)) throw new Exception('Tgl Mulai tidak valid.');
        $durasi_bulan = (int)($_POST['durasi_bulan'] ?? 0);
        if (!in_array($durasi_bulan, [3, 6, 12], true)) throw new Exception('Durasi kontrak harus 3, 6, atau 12 bulan.');
        $d_selesai = new DateTime($tgl_mulai);
        $tgl_mulai_bln = (int)$d_selesai->format('j');
        $d_selesai->modify('first day of this month');
        $d_selesai->modify('+' . $durasi_bulan . ' months');
        $maks_bln = (int)$d_selesai->format('t');
        $d_selesai->setDate((int)$d_selesai->format('Y'), (int)$d_selesai->format('n'), min($tgl_mulai_bln - 1, $maks_bln));
        $tgl_selesai = $d_selesai->format('Y-m-d');

        // Validasi + siapkan spesifikasi tiap baris (jabatan, template, nilai).
        $specs = [];
        $total = 0;
        for ($ri = 0; $ri < $n_rows; $ri++) {
            $no_baris = $ri + 1;
            $id_jabatan = (int)($rows_jab[$ri] ?? 0);
            $id_template = (int)($rows_tpl[$ri] ?? 0);
            $jumlah = (int)($rows_jml[$ri] ?? 0);
            if ($id_jabatan <= 0) throw new Exception("Baris $no_baris: pilih jabatan terlebih dahulu.");
            if ($id_template <= 0) throw new Exception("Baris $no_baris: pilih template terlebih dahulu.");
            if ($jumlah < 1 || $jumlah > 50) throw new Exception("Baris $no_baris: jumlah harus 1 s/d 50.");

            // Jabatan + kode.
            $qj = $koneksi->query("SELECT kode_jabatan, nama_jabatan FROM tb_jabatan WHERE id_jabatan = '$id_jabatan' LIMIT 1");
            if (!$qj || $qj->num_rows === 0) throw new Exception("Baris $no_baris: jabatan tidak ditemukan.");
            $jab = $qj->fetch_assoc();
            $kode = trim((string)($jab['kode_jabatan'] ?? ''));
            if ($kode === '') throw new Exception("Baris $no_baris: kode jabatan belum diisi. Lengkapi kode jabatan terlebih dahulu.");
            $nama_jabatan = $jab['nama_jabatan'] ?? '-';

            // Template harus berlaku untuk jabatan ini.
            $qp = $koneksi->query(
                "SELECT t.`file`, t.nama_kontrak FROM tb_template_kontrak_jabatan p " .
                "JOIN tb_template_kontrak t ON t.id_template = p.id_template " .
                "WHERE p.id_jabatan = '$id_jabatan' AND p.id_template = '$id_template' LIMIT 1"
            );
            if (!$qp || $qp->num_rows === 0) throw new Exception("Baris $no_baris: template tidak berlaku untuk jabatan ini.");
            $tpl = $qp->fetch_assoc();

            $tpl_base = basename((string)$tpl['file']);
            $src = __DIR__ . '/../uploads/template-kontrak/' . $tpl_base;
            if (!is_file($src)) {
                $legacy = __DIR__ . '/../uploads/kontrak/' . $tpl_base;
                if (is_file($legacy)) $src = $legacy;
            }
            if (!is_file($src)) throw new Exception("Baris $no_baris: file template tidak ditemukan di server.");

            // Nilai kontrak dari detail template.
            $qd = $koneksi->query(
                "SELECT id_detail, gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir " .
                "FROM tb_detail_kontrak WHERE id_template = '$id_template' LIMIT 1"
            );
            $det = ($qd && $qd->num_rows > 0) ? $qd->fetch_assoc() : ['gaji_pokok' => 0, 'upah_lembur' => 0, 'potongan_izin' => 0, 'potongan_mangkir' => 0];
            $id_det = (int)($det['id_detail'] ?? 0);

            // Tunjangan template yg tampil saja (untuk daftar + placeholder per nama).
            $tun = [];
            $tun_tetap = [];
            $tun_tidak = [];
            if ($id_det > 0) {
                $qtun = $koneksi->query(
                    "SELECT nama_tunjangan, jenis_tunjangan, jumlah_tunjangan FROM tb_tunjangan_kontrak " .
                    "WHERE id_detail_kontrak = '$id_det' AND tampil_template = 1 ORDER BY id_tunjangan_kontrak ASC"
                );
                if ($qtun) {
                    while ($t = $qtun->fetch_assoc()) {
                        $tun[strtolower(trim((string)$t['nama_tunjangan']))] = $t['jumlah_tunjangan'];
                        $rupiah = kontrak_is_makan($t['nama_tunjangan'] ?? '')
                            ? kontrak_rupiah_makan($t['jumlah_tunjangan'])
                            : kontrak_rupiah($t['jumlah_tunjangan']);
                        $baris = [kontrak_title($t['nama_tunjangan']), $rupiah];
                        if (($t['jenis_tunjangan'] ?? 'tetap') === 'tidak_tetap') {
                            $tun_tidak[] = $baris;
                        } else {
                            $tun_tetap[] = $baris;
                        }
                    }
                }
            }
            $specs[] = [
                'kode' => $kode,
                'nama_jabatan' => $nama_jabatan,
                'src' => $src,
                'det' => $det,
                'tun' => $tun,
                'tun_tetap' => $tun_tetap,
                'tun_tidak' => $tun_tidak,
                'jumlah' => $jumlah,
            ];
            $total += $jumlah;
        }
        if ($total > 100) throw new Exception('Total file maksimal 100 per sekali cetak.');
        $tun_nilai = function ($tun, $keys) {
            foreach ((array)$keys as $k) {
                if (isset($tun[strtolower($k)])) return $tun[strtolower($k)];
            }
            return 0;
        };

        // Reservasi TOTAL nomor berurutan sekaligus (satu transaksi counter untuk semua baris).
        // Bila user mengatur Nomor Mulai, pakai itu; selain itu lanjut dari terbesar+1.
        // Counter tidak pernah dimundurkan (GREATEST).
        $override = (int)($_POST['nomor_mulai'] ?? 0);
        if ($override < 0 || $override > 999999) throw new Exception('Nomor Mulai harus 1 s/d 999999.');
        $koneksi->begin_transaction();
        try {
            $koneksi->query("INSERT IGNORE INTO tb_nomor_urut (nama, terakhir) VALUES ('kontrak', 0)");
            $lock = $koneksi->query("SELECT terakhir FROM tb_nomor_urut WHERE nama = 'kontrak' FOR UPDATE");
            if (!$lock || $lock->num_rows === 0) throw new Exception('Counter nomor surat tidak tersedia.');
            $last = (int)$lock->fetch_assoc()['terakhir'];
            $qm = $koneksi->query(
                "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no_surat, '/', 2), '/', -1) AS UNSIGNED)), 0) AS mx " .
                "FROM tb_karier WHERE no_surat REGEXP '^TPJS-KONTRAK-[^/]+/[0-9]+/[0-9]{4}$'"
            );
            if (!$qm) throw new Exception('Gagal membaca nomor terakhir: ' . $koneksi->error);
            $mx = (int)$qm->fetch_assoc()['mx'];
            $mulai = $override > 0 ? $override : (max($last, $mx) + 1);
            $akhir = $mulai + $total - 1;
            if (!$koneksi->query("UPDATE tb_nomor_urut SET terakhir = GREATEST(terakhir, '$akhir') WHERE nama = 'kontrak'")) {
                throw new Exception('Gagal mencatat nomor surat: ' . $koneksi->error);
            }
            $koneksi->commit();
        } catch (Throwable $e) {
            $koneksi->rollback();
            throw $e instanceof Exception ? $e : new Exception($e->getMessage());
        }

        // Isian blanko: titik-titik untuk diisi kemudian, kecuali jabatan,
        // nilai kontrak (dari template tiap baris), dan periode (dari pilihan).
        $kosong_panjang = '........................................................';
        $durasi_teks = $durasi_bulan . ' (' . terbilang_id($durasi_bulan) . ') bulan';
        $tgl_mulai_teks = kontrak_tgl_id($tgl_mulai);
        $tgl_selesai_teks = kontrak_tgl_id($tgl_selesai);
        $vars_bersama = [
            'alamat' => $kosong_panjang,
            'desa ' => '........................',
            'desa' => '........................',
            'kecamatan' => '........................',
            'kabupaten' => '........................',
            'durasi' => $durasi_teks,
            'tgl_mulai' => $tgl_mulai_teks,
            'tgl_selesai' => $tgl_selesai_teks,
            'nama' => $kosong_panjang,
            'nik' => $kosong_panjang,
            'jenis_kelamin' => '........................',
            'tempat_tanggal_lahir' => $kosong_panjang,
        ];

        $tun_tabel = function ($rows) {
            $style = new \PhpOffice\PhpWord\Style\Table();
            $style->setBorderSize(0);
            $style->setBorderColor('FFFFFF');
            $style->setCellMargin(50);
            $style->setIndent(new \PhpOffice\PhpWord\ComplexType\TblWidth(1701));
            $table = new \PhpOffice\PhpWord\Element\Table($style);
            $font = ['name' => 'Times New Roman', 'size' => 11];
            foreach ($rows as $row) {
                $table->addRow();
                $table->addCell(4500)->addText($row[0], $font);
                $table->addCell(4500)->addText(': ' . $row[1], $font);
            }
            return $table;
        };

        $tmpdir = sys_get_temp_dir() . '/kontrak_blanko_' . bin2hex(random_bytes(6));
        if (!mkdir($tmpdir, 0700, true)) throw new Exception('Gagal menyiapkan folder sementara.');
        $files = [];
        try {
            $seq = $mulai;
            foreach ($specs as $sp) {
                $det = $sp['det'];
                $tun = $sp['tun'];
                $vars = $vars_bersama + [
                    'jabatan' => kontrak_title($sp['nama_jabatan']),
                    'gaji_pokok' => kontrak_rupiah($det['gaji_pokok'] ?? 0),
                    'upah_lembur' => kontrak_rupiah($det['upah_lembur'] ?? 0),
                    'izin' => kontrak_rupiah($det['potongan_izin'] ?? 0),
                    'mangkir' => kontrak_rupiah($det['potongan_mangkir'] ?? 0),
                    'bonus' => kontrak_rupiah($tun_nilai($tun, ['bonus kehadiran', 'bonus'])),
                    'tunjangan_daerah' => kontrak_rupiah($tun_nilai($tun, ['tunjangan daerah'])),
                    'tunjangan_hidup' => kontrak_rupiah($tun_nilai($tun, ['tunjangan hidup'])),
                    'tunjangan_skill' => kontrak_rupiah($tun_nilai($tun, ['tunjangan skill'])),
                    'tunjangan_uang_makan' => kontrak_rupiah_makan($tun_nilai($tun, ['tunjangan uang makan', 'tunjangan makan'])),
                ];
                for ($i = 0; $i < $sp['jumlah']; $i++, $seq++) {
                    $no_surat = sprintf('TPJS-KONTRAK-%s/%04d/%s', $sp['kode'], $seq, $tahun);
                    $fname = preg_replace('/[^A-Za-z0-9\-]+/', '-', $no_surat) . '.docx';
                    $dst = $tmpdir . '/' . $fname;
                    kontrak_patch_docx($sp['src'], $dst);
                    $tp = new \PhpOffice\PhpWord\TemplateProcessor($dst);
                    foreach (['tunjangan_tetap' => $sp['tun_tetap'], 'tunjangan_tidak_tetap' => $sp['tun_tidak']] as $macro => $rows) {
                        if ($rows) {
                            $tp->setComplexBlock($macro, $tun_tabel($rows));
                        } else {
                            $tp->setValue($macro, '-');
                        }
                    }
                    $vars['nomor_kontrak'] = $no_surat;
                    $tp->setValues($vars);
                    $tp->saveAs($dst);
                    $files[] = $dst;
                }
            }

            $zip_name = sprintf('Kontrak-Blanko-%04d-sd-%04d-%s.zip', $mulai, $akhir, $tahun);
            $zip_path = $tmpdir . '/' . $zip_name;
            $zip = new ZipArchive();
            if ($zip->open($zip_path, ZipArchive::CREATE) !== true) throw new Exception('Gagal membuat ZIP.');
            foreach ($files as $f) $zip->addFile($f, basename($f));
            $zip->close();

            while (ob_get_level() > 0) ob_end_clean();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zip_name . '"');
            header('Content-Length: ' . filesize($zip_path));
            readfile($zip_path);
        } finally {
            foreach (glob($tmpdir . '/*') ?: [] as $f) @unlink($f);
            @rmdir($tmpdir);
        }
        exit;
    } catch (Throwable $e) {
        cetak_fail('Gagal mencetak kontrak: ' . $e->getMessage());
    }
}

cetak_json('error', 'Action tidak dikenali.');
