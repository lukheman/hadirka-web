<?php
function nama_pt($call)
{
    if($call == 'singkat'){
        return 'PT. TJS';
    }else{
        return 'PT. Triputra Jayasultra';
    }
}

function cek_sesi()
{
    $id_user    = $_SESSION['id_user'];
    $username   = $_SESSION['username'];
    $level      = $_SESSION['level'];
    if (empty($username)&& empty($id_user) && empty($level)) {
        return '<script></script>';
    }
}


function tanggal_indo($tanggal, $cetak_hari = false)
{
    $hari = array(
        1 =>    'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu'
    );

    $bulan = array(
        1 =>   'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $split       = explode('-', $tanggal);
    $tgl_indo = $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];

    if ($cetak_hari) {
        $num = date('N', strtotime($tanggal));
        return $hari[$num] . ', ' . $tgl_indo;
    }
    return $tgl_indo;
}


function tgl_waktu($tgl)
{
    return date('d-m-Y', strtotime($tgl));
}

function jk($jk)
{
    if ($jk == 'L') {
        return 'Laki-laki';
    } else if ($jk == 'P') {
        return 'Perempuan';
    }
}


function bapak_ibu($j)
{
    if ($j == 'L') {
        return 'Bapak';
    } else {
        return 'Ibu';
    }
}

function kembali()
{
    $link = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $data = 'window.location="' . $link . '"';
    return $data;
}

function rupiah($angka)
{
    $angka = (float)$angka; // ubah string kosong atau null jadi 0
    $rupiah = number_format($angka, 0, ',', '.');
    return 'Rp ' . $rupiah;
}


function terbilangRupiah($angka)
{
    $angka = abs((int)$angka);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");

    $terbilang = "";
    if ($angka < 12) {
        $terbilang = $huruf[$angka];
    } elseif ($angka < 20) {
        $terbilang = terbilangRupiah($angka - 10) . " belas";
    } elseif ($angka < 100) {
        $terbilang = terbilangRupiah($angka / 10) . " puluh " . terbilangRupiah($angka % 10);
    } elseif ($angka < 200) {
        $terbilang = "seratus " . terbilangRupiah($angka - 100);
    } elseif ($angka < 1000) {
        $terbilang = terbilangRupiah($angka / 100) . " ratus " . terbilangRupiah($angka % 100);
    } elseif ($angka < 2000) {
        $terbilang = "seribu " . terbilangRupiah($angka - 1000);
    } elseif ($angka < 1000000) {
        $terbilang = terbilangRupiah($angka / 1000) . " ribu " . terbilangRupiah($angka % 1000);
    } elseif ($angka < 1000000000) {
        $terbilang = terbilangRupiah($angka / 1000000) . " juta " . terbilangRupiah($angka % 1000000);
    } else {
        $terbilang = "Angka terlalu besar untuk diubah menjadi terbilang";
    }

    return ucfirst($terbilang); // Mengubah huruf pertama menjadi huruf besar
}

function rupiah_to_int($uang)
{
    return str_replace(".", "", str_replace("Rp", "", $uang));
}

function bacawaktu()
{
    $time = date('H:i:s');
    $hour = (int) date('H', strtotime($time));
    if ($hour >= 0 && $hour < 6) {
        return "malam";
    } elseif ($hour >= 6 && $hour < 12) {
        return "pagi";
    } elseif ($hour >= 12 && $hour < 15) {
        return "siang";
    } elseif ($hour >= 15 && $hour < 18) {
        return "sore";
    } else {
        return "malam";
    }
}

function code()
{
    return "HALO NAMA SAYA ABDUL JABAL";
}

function encrypt($id)
{
    $data = base64_encode($id . code());
    return $data;
}

function decrypt($id)
{
    $kode = base64_decode($id);
    $data = str_replace(code(), "", $kode);
    return $data;
}

function no_telp($no)
{
    $phoneNumber = preg_replace('/[^0-9]/', '', $no);
    // Jika dimulai dengan '0', ganti dengan '62'
    if (substr($phoneNumber, 0, 1) === '0') {
        $phoneNumber = '62' . substr($phoneNumber, 1);
    }
    return $phoneNumber;
}

function hitungUmur($tanggal_lahir)
{
    $tgl_lahir = new DateTime($tanggal_lahir);
    $sekarang  = new DateTime();

    return $sekarang->diff($tgl_lahir)->y;
}


function active_link($acuan, $target)
{
    $targets = explode(' ', $target); // Ubah string target menjadi array
    if (in_array($acuan, $targets)) {
        return "text-white active bg-info";
    }
    return ""; // Jika tidak cocok, kembalikan string kosong agar tidak menyebabkan error
}

function getTanggalAwalAkhir($bulan, $tahun)
{
    // Format tanggal awal (YYYY-MM-01)
    $tanggal_awal = date("Y-m-01", strtotime("$tahun-$bulan-01"));

    // Format tanggal akhir (YYYY-MM-31 atau sesuai jumlah hari di bulan tersebut)
    $tanggal_akhir = date("Y-m-t", strtotime("$tahun-$bulan-01"));

    return [
        'awal' => $tanggal_awal,
        'akhir' => $tanggal_akhir
    ];
}

function total_hm($hmawal, $hmakhir)
{
    $hmakhir = is_numeric($hmakhir) ? $hmakhir : 0;
    $hmawal = is_numeric($hmawal) ? $hmawal : 0;
    return $hmakhir - $hmawal;
}

function jumlahHari($tglAwal, $tglAkhir)
{
    $awal = new DateTime($tglAwal);
    $akhir = new DateTime($tglAkhir);
    $diff = $akhir->diff($awal)->days + 1;
    return $diff;
}
