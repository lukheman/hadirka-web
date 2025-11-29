Saya ingin anda membutkan saya slip gaji dengan model seperti berikut
dan saya akan menjelaskan kepada anda sistem perhitugannya



1. jam kerja di ambil di tb_absen
2. OFF di ambil dari tb_absen ket_absen = OFF
3. SPL diambil jika tb_absen ada jam kerja dan ket_off
4. Izin diambil di tb_izin
5. Sakit Diambil di tb_sakit
6. cuti di ambil di tb_cuti
7. untuk perhitungan potongan izin dan mangkir di ambil dari tb_karier karyawan yg aktif dipriode tersebut
8. untuk perhitungan gaji pokok, tunjangan, dan potongan lainnya di ambil dari tb_karier karyawan yg aktif diperiode tersebut
9. Wl / Wajib Lembur = Setelah jam kerja 7 jam/hari Di hari normal
10. Overtime = 9 - Dan Seterusnya jam kerja di hari normal
11. Untuk SPL / 1-7 jam kerja di hari off nilai harga gaji perjamnya di ambil dari tb_karier spl
12. Untuk SPL 8 jam kerja di hari off nilai harga gaji perjamnya di ambil dari tb_karier overtime
13. Untuk SPL 9 - Dan seterusnya Diambil di tb_karier karyawan yang aktif diperiode tersebut

tb_karier (
id_karier
id_karyawan
id_site
id_departemen
id_jabatan
gaji_pokok
t_hidup
t_daerah
t_makan
t_skil
ot_pertama
ot_kedua
spl
ot1_spl
ot2_spl
pot_izin
pot_mangkir
tgl_mulai
tgl_selesai)

tb_karyawan(
id_karyawan
nik_karyawan
nama_lengkap
jenis_kelamin
tgl_lahir
tempat_lahir
email_pribadi
no_telp
nationality
agama
status_pernikahan)

tb_absen(
id_absen
id_karyawan
tgl_absen
jam_kerja
ket_absen)

tb_izin(
id_izin
id_karyawan
tgl_awal_izin
tgl_akhir_izin
foto_izin
ket_izin)

tb_sakit(
id_sakit
id_karyawan
tgl_awal_sakit
tgl_akhir_sakit
foto_sakit
ket_sakit)

tb_cuti(
id_cuti
id_karyawan
tgl_awal_cuti
tgl_akhir_cuti
ket_cuti)


tb_tunjangan(
id_tunjangan
id_karier
nama_tunjangan
jumlah_tunjangan)

tb_pinjaman(
id_pinjaman	
id_karyawan
nominal_pinjaman
tgl_pinjaman
ket_pinjaman)