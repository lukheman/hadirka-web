<?php
require_once 'app/config/koneksi.php';
$nama_list = [
    "ABD RASYID",
    "ABD RAUF",
    "ABD RIJAL",
    "ABD. RAHIM",
    "ABDUL SYAKIR",
    "ADE MUHAJJIRIN S",
    "AGUS FIAN",
    "AHMAD SALEH",
    "AIDIL ADHA",
    "AIMAM WASENDAH",
    "ALDIN",
    "ALFITO DENOPA",
    "ALPIAN",
    "ALWI AKBAR",
    "AMELIA VIRYA ALLO",
    "ANDI HAERUL AKBAR",
    "ANDI NOPSAN ASMA",
    "ANGGA ARI DARMAWAN",
    "ANGGA M. SAPUTRA",
    "ANRI SAPUTRA",
    "ANRIANTO",
    "ARIF PRATAMA",
    "ARIL ANGGARA",
    "ARIL ANSI SABARA",
    "ARMIN OKAYASA",
    "ARMIN OKAYASA",
    "ARUL GUNAWAN",
    "ARWAN",
    "ASBAR",
    "AUNICHE",
    "AWAN HENDRIAWAN",
    "BAYU ARDIANSYAH",
    "BAYU SAMUDRA",
    "BUDI ANSAR",
    "CHAIDIR",
    "CHANDRA ADITYA",
    "CUT NORIS",
    "DANIEL TANGKE LOBO",
    "DARIUS TANAN PAIRUNAN",
    "DARMANTO",
    "DARMIN",
    "DEDI ADI SAPUTRA",
    "DEMIANUS",
    "DENI HIDRIA WANSA",
    "DEWI PURNAMA SARI",
    "DIMAS RILO PRIAMBUDI",
    "DZAR AL-GIFARI",
    "EKO HARYANTO PUTRA",
    "ELIASER FRENKY SALURAPA",
    "FADLAN SYARU SIAM",
    "FAHMI",
    "FAHRIL",
    "FAHRUL",
    "FAISAL",
    "FAJAR RUSTAM",
    "FEBRI FAUZAN",
    "FEBRIANTINI",
    "FERDIANTO",
    "FERONIKA KOMBONG",
    "FHALDI",
    "FITRAH ILHAM",
    "HARUL MAULANA",
    "HASDRIANSYAH",
    "HEKIL LAMUDA VAN HAYON",
    "HENDRA SETIAWAN",
    "HENGKI",
    "HERMAN TATO",
    "HERY SUHARYADI S.T",
    "I GEDE TONJAYA",
    "IAN TRI SAPUTRA",
    "ICHAL",
    "IDA KETUT YUDI DARMAWAN",
    "IGNASIUS TONI KOTEN",
    "IKBAL",
    "INDIRA PUTRI RAMADANI",
    "INDIRA PUTRI RAMADANI",
    "INDRA",
    "IRFAN",
    "IRFAN EFENDI",
    "IRPAN TANDI TANGKE",
    "IRWAN",
    "IRWAN",
    "ISHAR",
    "ISMAIL",
    "IVAN",
    "JEFRIYADI HASYIM",
    "JUAN LANGI' SUPU LAYUK",
    "JUBRI",
    "JUMARDI",
    "JUMARDIN",
    "JUSMAN",
    "JUSWANDI",
    "KASMIR",
    "KELVIN AGSEL DANIEL",
    "KETUT ANGGA ARIS WAHYUDI",
    "KETUT BUDIARSANA",
    "KURNIAWAN",
    "LALU MOH TOHRI",
    "LEONARDUS ANDRE SALURAPA",
    "LIKMAN",
    "LUKAS LA'TE",
    "LUKMAN",
    "M. ADNAN",
    "M. FEBRYOGHA MAHENDRA SYARIF",
    "M. IKSAN",
    "MAHES PELIPUS DEBA",
    "MARCELINA PATINGGI",
    "MUDASSIR",
    "MUH AFDHAL ALQADRI",
    "MUH AIDIL",
    "MUH ALFIANSYAH AHMAR",
    "MUH ARDIANSYAH",
    "MUH DHEAN SUROHMAN",
    "MUH FIRDAN TUNGGARA",
    "MUH KHAESA AIDIL ADHAN A.",
    "MUH SOFYAN",
    "MUH. AFGARIL AKBAR RAMADHAN",
    "MUH. ALBAR",
    "MUH. ARHAM",
    "MUH. FEBRIAN SAPUTRA",
    "MUH. HASRUL",
    "MUH. IKRAM",
    "MUH. IQBAL RAMADHAN",
    "MUH. RULLY MARQIANO",
    "MUH.FARHAN BASIS",
    "MUH.VARIEL CHESAR",
    "MUHAMMAD ARIZAL",
    "MUHAMMAD FAHRI",
    "MUHAMMAD HARDIMAN",
    "MUHAMMAD JABI",
    "MUHAMMAD SAHNUR SAHUDDIN",
    "MUHYIDDIN AL IKHZAN BALHA",
    "MUSAFIR",
    "NONA SEPLI DAKKI",
    "NURCAHAYATI SUARDI",
    "NURDIN",
    "OGAL ROMBESERU",
    "PASIRIO MASE",
    "PONI SAMPE MANGALIK",
    "PUTRI NOLA DJ",
    "PUTRI NOLA DJ",
    "PUTU ASTIANI",
    "PUTU FEBRI SWARIANI",
    "RABIN BIN HADI",
    "RAHMAT HIDAYAT",
    "RAHMAT RAMADHAN",
    "RAHUL",
    "RAMADHAN ABDULLAH",
    "RAMADHAN MUHAMMAD",
    "RAMLI",
    "RAPASINGI",
    "RENDI RINALDI",
    "RESKY FATURRAHMAN",
    "RIANG",
    "RIDWANTO",
    "RIKY WAHYUDI",
    "RISAL",
    "RISAL",
    "RISAL PAISAL",
    "RISWAN",
    "RIZALDI",
    "ROLAN",
    "RUBEN JANUAR BOBIHU",
    "RUSLIN",
    "SADRUDDIN",
    "SAFARUDDIN",
    "SAHID INRAWAN",
    "SAHLAN RIFAI",
    "SAHRUL",
    "SAHRUL",
    "SAHRUL",
    "SAKIR DG MAPUNNA",
    "SELIM RANDANA",
    "SELVI JUNIANTI TATO",
    "SEMUELAN TAMBOLANG",
    "SHEGAL BASO",
    "SIMON KALA LEMBANG",
    "STEVANUS PALAYUKAN",
    "SUDIRMAN KAHAR",
    "SUHARDIN AKBAR",
    "SULHAM",
    "SULTAN WAHID",
    "SUPRIYANTO",
    "SUSANTO",
    "SYAHRUL",
    "TASBILLAH",
    "TRYAS AGUNG OKTAVIAN RAMADHAN",
    "USNI",
    "WAHYUDI MURDAWAN",
    "WEMFRID",
    "YUNISCHA MILENIA",
    "YUNITA SARI",
    "YUSDI ARSALIN GARATA",
    "YUSRI",
    "YUSRI",
    "YUSRIL",
    "ZAINUDDIN, DM",
    "ZULFIKAR"
];


// Buat placeholder ?,?,?,...
$placeholders = implode(',', array_fill(0, count($nama_list), '?'));

$sql = "SELECT nama_lengkap 
        FROM tb_karyawan 
        WHERE nama_lengkap IN ($placeholders)";

$stmt = $koneksi->prepare($sql);

// Bind semua parameter
$stmt->bind_param(str_repeat('s', count($nama_list)), ...$nama_list);

$stmt->execute();
$result = $stmt->get_result();

// Ambil nama yg ADA di database
$ada = [];
while ($row = $result->fetch_assoc()) {
    $ada[] = $row['nama_lengkap'];
}

// Cari yang TIDAK ADA
$tidak_ada = array_diff($nama_list, $ada);

echo "<pre>";
print_r($tidak_ada);
echo "</pre>";

if(isset($_POST['delete_tb_karyawan'])) {
    // Hapus semua data di tb_karyawan
    $koneksi->query("DELETE FROM tb_karyawan");
    echo "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script><script>Swal.fire({icon:'success',title:'Berhasil',text:'All records from tb_karier have been deleted.'});</script>";
} 

function getId($koneksi, $table, $nama_field, $nama_value, $id_field)
{
    $nama_value = strtolower(trim($nama_value));
    $q = $koneksi->query("SELECT $id_field FROM $table WHERE LOWER(TRIM($nama_field))='$nama_value'");
    return $q->num_rows > 0 ? $q->fetch_assoc()[$id_field] : null;
}


$getIdExample = getId($koneksi, 'tb_site', 'nama_site', 'PT IPIP', 'id_site');

echo ($getIdExample ?? 'Not Found');

?>
<form action="" method="post">
    <button type="submit" name="delete_tb_karyawan" value="1" onclick="return confirm('Are you sure you want to delete all records from tb_karier? This action cannot be undone.');">
        DELETE TB KARYAWAN
    </button>
</form>