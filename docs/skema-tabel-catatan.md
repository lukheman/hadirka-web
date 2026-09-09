A. tb_karier
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
    tgl_selesai

B. tb_karyawan
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
    status_pernikahan
    status_karyawan
    created_at

C. tb_tunjangan
    id_tunjangan
    id_karier
    nama_tunjangan
    jumlah_tunjangan

D. tb_izin
    id_izin
    id_karyawan
    tgl_awal_izin
    tgl_akhir_izin
    foto_izin
    ket_izin
E. tb_sakit
    id_sakit
    id_karyawan
    tgl_awal_sakit
    tgl_akhir_sakit
    foto_sakit
    ket_sakit
F. tb_cuti
    id_cuti
    id_karyawan
    tgl_awal_cuti
    tgl_akhir_cuti
    ket_cuti
G. tb_absen
    id_absen
    id_karyawan
    tgl_absen
    jam_kerja
    ket

