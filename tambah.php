<?php
require_once "koneksi.php";

$error = "";

function null_if_empty($val) {
    $v = trim((string)$val);
    return $v === "" ? null : $v;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nim = trim($_POST["NIM"] ?? "");
    $angkatan = (int)($_POST["Angkatan"] ?? date("Y"));
    $nama_lengkap = trim($_POST["Nama_Lengkap"] ?? "");
    $program_studi = trim($_POST["Program_Studi"] ?? "");
    $nisn = null_if_empty($_POST["NISN"] ?? "");
    $jenis_kelamin = null_if_empty($_POST["Jenis_Kelamin"] ?? "");
    $status_perkawinan = null_if_empty($_POST["Status_Perkawinan"] ?? "");
    $kota_lahir = null_if_empty($_POST["Kota_Lahir"] ?? "");
    $tanggal_lahir = null_if_empty($_POST["Tanggal_Lahir"] ?? "");
    $agama = null_if_empty($_POST["Agama"] ?? "");
    $negara = null_if_empty($_POST["Negara"] ?? "Indonesia");
    $kewarganegaraan = null_if_empty($_POST["Kewarganegaraan"] ?? "WNI");
    $email = null_if_empty($_POST["Email"] ?? "");
    $no_hp = null_if_empty($_POST["No_HP"] ?? "");
    $pekerjaan = null_if_empty($_POST["Pekerjaan"] ?? "");

    // Alamat mahasiswa
    $alm_mhs = null_if_empty($_POST["alm_mhs_alamat"] ?? "");
    $alm_mhs_kota = null_if_empty($_POST["alm_mhs_kota"] ?? "");
    $alm_mhs_rt = null_if_empty($_POST["alm_mhs_rt"] ?? "");
    $alm_mhs_rw = null_if_empty($_POST["alm_mhs_rw"] ?? "");
    $alm_mhs_kel = null_if_empty($_POST["alm_mhs_kelurahan"] ?? "");
    $alm_mhs_pos = null_if_empty($_POST["alm_mhs_kode_pos"] ?? "");
    $alm_mhs_neg = null_if_empty($_POST["alm_mhs_negara"] ?? "Indonesia");

    // Akademik
    $no_tes = null_if_empty($_POST["No_Tes_Masuk"] ?? "");
    $tgl_daftar = null_if_empty($_POST["Tanggal_Terdaftar"] ?? "");
    $status_masuk = null_if_empty($_POST["Status_Masuk_PT"] ?? "");
    $jalur_masuk = null_if_empty($_POST["Jalur_Masuk_PT"] ?? "");
    $status_mhs = null_if_empty($_POST["Status_Mahasiswa"] ?? "Aktif");
    $sks_wajib = isset($_POST["SKS_Wajib"]) && $_POST["SKS_Wajib"] !== "" ? (int)$_POST["SKS_Wajib"] : null;
    $sks_pilihan = isset($_POST["SKS_Pilihan"]) && $_POST["SKS_Pilihan"] !== "" ? (int)$_POST["SKS_Pilihan"] : null;
    $sks_pindahan = isset($_POST["SKS_Pindahan"]) && $_POST["SKS_Pindahan"] !== "" ? (int)$_POST["SKS_Pindahan"] : null;
    $pt_asal = null_if_empty($_POST["PT_Asal"] ?? "");
    $beasiswa = null_if_empty($_POST["Beasiswa"] ?? "");

    // Pendidikan asal
    $nama_smta = null_if_empty($_POST["Nama_SMTA"] ?? "");
    $alamat_smta = null_if_empty($_POST["Alamat_SMTA"] ?? "");
    $jurusan_smta = null_if_empty($_POST["Jurusan"] ?? "");
    $tahun_lulus = isset($_POST["Tahun_Lulus"]) && $_POST["Tahun_Lulus"] !== "" ? (int)$_POST["Tahun_Lulus"] : null;
    $no_ijazah = null_if_empty($_POST["No_Ijazah"] ?? "");
    $tgl_ijazah = null_if_empty($_POST["Tanggal_Ijazah"] ?? "");
    $jml_mp = isset($_POST["Jumlah_MP_Ujian"]) && $_POST["Jumlah_MP_Ujian"] !== "" ? (int)$_POST["Jumlah_MP_Ujian"] : null;
    $nilai_ujian = isset($_POST["Nilai_Ujian"]) && $_POST["Nilai_Ujian"] !== "" ? (float)$_POST["Nilai_Ujian"] : null;

    // Pekerjaan & ekonomi
    $tempat_kerja = null_if_empty($_POST["Tempat_Kerja"] ?? "");
    $no_telp_kerja = null_if_empty($_POST["No_Telepon"] ?? "");
    $no_akses = null_if_empty($_POST["No_Akses"] ?? "");
    $jml_saudara = isset($_POST["Jumlah_Saudara"]) && $_POST["Jumlah_Saudara"] !== "" ? (int)$_POST["Jumlah_Saudara"] : null;
    $jml_tanggungan = isset($_POST["Jumlah_Tanggungan"]) && $_POST["Jumlah_Tanggungan"] !== "" ? (int)$_POST["Jumlah_Tanggungan"] : null;
    $kps = null_if_empty($_POST["KPS"] ?? "");
    $mampu = isset($_POST["Mampu"]) && $_POST["Mampu"] !== "" ? (int)$_POST["Mampu"] : null;
    $npwp = null_if_empty($_POST["NPWP"] ?? "");

    // Orang tua & alamat orang tua
    $nama_ayah = null_if_empty($_POST["Nama_Ayah"] ?? "");
    $tgl_lahir_ayah = null_if_empty($_POST["Tanggal_Lahir_Ayah"] ?? "");
    $status_ayah = null_if_empty($_POST["Status_Ayah"] ?? "");
    $pend_ayah = null_if_empty($_POST["Pendidikan_Ayah"] ?? "");
    $pek_ayah = null_if_empty($_POST["Pekerjaan_Ayah"] ?? "");
    $pengh_ayah = isset($_POST["Penghasilan_Ayah"]) && $_POST["Penghasilan_Ayah"] !== "" ? (float)$_POST["Penghasilan_Ayah"] : null;

    $nama_ibu = null_if_empty($_POST["Nama_Ibu"] ?? "");
    $tgl_lahir_ibu = null_if_empty($_POST["Tanggal_Lahir_Ibu"] ?? "");
    $status_ibu = null_if_empty($_POST["Status_Ibu"] ?? "");
    $pend_ibu = null_if_empty($_POST["Pendidikan_Ibu"] ?? "");
    $pek_ibu = null_if_empty($_POST["Pekerjaan_Ibu"] ?? "");
    $pengh_ibu = isset($_POST["Penghasilan_Ibu"]) && $_POST["Penghasilan_Ibu"] !== "" ? (float)$_POST["Penghasilan_Ibu"] : null;

    $alm_ortu = null_if_empty($_POST["alm_ortu_alamat"] ?? "");
    $alm_ortu_kota = null_if_empty($_POST["alm_ortu_kota"] ?? "");
    $alm_ortu_pos = null_if_empty($_POST["alm_ortu_kode_pos"] ?? "");
    $alm_ortu_telp = null_if_empty($_POST["alm_ortu_no_telepon"] ?? "");
    $alm_ortu_email = null_if_empty($_POST["alm_ortu_email"] ?? "");

    // Wali
    $nama_wali = null_if_empty($_POST["Nama_Wali"] ?? "");
    $tgl_lahir_wali = null_if_empty($_POST["Tanggal_Lahir_Wali"] ?? "");
    $status_wali = null_if_empty($_POST["Status_Wali"] ?? "");
    $tgl_meninggal_wali = null_if_empty($_POST["Tanggal_Meninggal"] ?? "");
    $alm_wali = null_if_empty($_POST["Alamat_Wali"] ?? "");
    $kota_wali = null_if_empty($_POST["Kota_Wali"] ?? "");
    $telp_wali = null_if_empty($_POST["No_Telepon_Wali"] ?? "");
    $email_wali = null_if_empty($_POST["Email_Wali"] ?? "");
    $pend_wali = null_if_empty($_POST["Pendidikan_Wali"] ?? "");
    $pek_wali = null_if_empty($_POST["Pekerjaan_Wali"] ?? "");

    if ($nim === "" || $nama_lengkap === "" || $program_studi === "") {
        $error = "NIM, Nama Lengkap, dan Program Studi wajib diisi.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO mahasiswa (
                    NIM, Angkatan, Nama_Lengkap, Program_Studi, NISN, Jenis_Kelamin,
                    Status_Perkawinan, Kota_Lahir, Tanggal_Lahir, Agama, Negara,
                    Kewarganegaraan, Email, No_HP, Pekerjaan
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt, "sisssssssssssss",
                $nim, $angkatan, $nama_lengkap, $program_studi, $nisn, $jenis_kelamin,
                $status_perkawinan, $kota_lahir, $tanggal_lahir, $agama, $negara,
                $kewarganegaraan, $email, $no_hp, $pekerjaan
            );
            mysqli_stmt_execute($stmt);

            if ($alm_mhs !== null || $alm_mhs_kota !== null || $alm_mhs_kel !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO alamat_mahasiswa (NIM, Alamat, Kota, RT, RW, Kelurahan, Kode_Pos, Negara)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt, "ssssssss",
                    $nim, $alm_mhs, $alm_mhs_kota, $alm_mhs_rt, $alm_mhs_rw, $alm_mhs_kel, $alm_mhs_pos, $alm_mhs_neg
                );
                mysqli_stmt_execute($stmt);
            }

            if ($no_tes !== null || $tgl_daftar !== null || $jalur_masuk !== null || $sks_wajib !== null || $beasiswa !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO akademik (
                        NIM, No_Tes_Masuk, Tanggal_Terdaftar, Status_Masuk_PT, Jalur_Masuk,
                        Status_Mahasiswa, SKS_Wajib, SKS_Pilihan, SKS_Pindahan, PT_Asal, Beasiswa
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt, "ssssssiiiss",
                    $nim, $no_tes, $tgl_daftar, $status_masuk, $jalur_masuk,
                    $status_mhs, $sks_wajib, $sks_pilihan, $sks_pindahan, $pt_asal, $beasiswa
                );
                mysqli_stmt_execute($stmt);
            }

            if ($nama_smta !== null || $jurusan_smta !== null || $no_ijazah !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO pendidikan_asal (
                        NIM, Nama_SMTA, Alamat_SMTA, Jurusan, Tahun_Lulus,
                        No_Ijazah, Tanggal_Ijazah, Jumlah_MP_Ujian, Nilai_Ujian
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt, "ssssissid",
                    $nim, $nama_smta, $alamat_smta, $jurusan_smta, $tahun_lulus,
                    $no_ijazah, $tgl_ijazah, $jml_mp, $nilai_ujian
                );
                mysqli_stmt_execute($stmt);
            }

            if ($tempat_kerja !== null || $no_telp_kerja !== null || $no_akses !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO pekerjaan (NIM, Tempat_Kerja, No_Telepon, No_Akses) VALUES (?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, "ssss", $nim, $tempat_kerja, $no_telp_kerja, $no_akses);
                mysqli_stmt_execute($stmt);
            }

            if ($jml_saudara !== null || $jml_tanggungan !== null || $kps !== null || $mampu !== null || $npwp !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO ekonomi (NIM, Jumlah_Saudara, Jumlah_Tanggungan, KPS, Mampu, NPWP)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, "siisis", $nim, $jml_saudara, $jml_tanggungan, $kps, $mampu, $npwp);
                mysqli_stmt_execute($stmt);
            }

            $id_ortu = null;
            if ($nama_ayah !== null || $nama_ibu !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO orang_tua (
                        NIM, Nama_Ayah, Tanggal_Lahir_Ayah, Status_Ayah, Pendidikan_Ayah, Pekerjaan_Ayah, Penghasilan_Ayah,
                        Nama_Ibu, Tanggal_Lahir_Ibu, Status_Ibu, Pendidikan_Ibu, Pekerjaan_Ibu, Penghasilan_Ibu
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt, "ssssssdsssssd",
                    $nim, $nama_ayah, $tgl_lahir_ayah, $status_ayah, $pend_ayah, $pek_ayah, $pengh_ayah,
                    $nama_ibu, $tgl_lahir_ibu, $status_ibu, $pend_ibu, $pek_ibu, $pengh_ibu
                );
                mysqli_stmt_execute($stmt);
                $id_ortu = mysqli_insert_id($conn);
            }

            if ($alm_ortu !== null || $alm_ortu_kota !== null || $alm_ortu_telp !== null || $alm_ortu_email !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO alamat_orang_tua (ID_Orang_Tua, Alamat, Kota, Kode_Pos, No_Telepon, Email)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, "isssss", $id_ortu, $alm_ortu, $alm_ortu_kota, $alm_ortu_pos, $alm_ortu_telp, $alm_ortu_email);
                mysqli_stmt_execute($stmt);
            }

            if ($nama_wali !== null || $alm_wali !== null || $telp_wali !== null) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO wali (
                        NIM, Nama_Wali, Tanggal_Lahir, Status_Wali, Tanggal_Meninggal,
                        Alamat, Kota, No_Telepon, Email, Pendidikan, Pekerjaan
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt, "sssssssssss",
                    $nim, $nama_wali, $tgl_lahir_wali, $status_wali, $tgl_meninggal_wali,
                    $alm_wali, $kota_wali, $telp_wali, $email_wali, $pend_wali, $pek_wali
                );
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($conn);
            header("Location: index.php?pesan=sukses_tambah");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mahasiswa - Tugas 4</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Tambah Data Mahasiswa</h1>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="tambah.php">
        <div class="form-section">
            <h3>1. Biodata Utama (mahasiswa)</h3>
            <div class="form-grid">
                <div class="form-group"><label>NIM *</label><input type="text" name="NIM" required></div>
                <div class="form-group"><label>Angkatan *</label><input type="number" name="Angkatan" value="<?= date("Y") ?>" required></div>
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="Nama_Lengkap" required></div>
                <div class="form-group"><label>Program Studi *</label><input type="text" name="Program_Studi" required></div>
                <div class="form-group"><label>NISN</label><input type="text" name="NISN"></div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="Jenis_Kelamin">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="form-group"><label>Status Perkawinan</label><input type="text" name="Status_Perkawinan" placeholder="Belum Menikah / Menikah"></div>
                <div class="form-group"><label>Kota Lahir</label><input type="text" name="Kota_Lahir"></div>
                <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="Tanggal_Lahir"></div>
                <div class="form-group"><label>Agama</label><input type="text" name="Agama"></div>
                <div class="form-group"><label>Negara</label><input type="text" name="Negara" value="Indonesia"></div>
                <div class="form-group"><label>Kewarganegaraan</label><input type="text" name="Kewarganegaraan" value="WNI"></div>
                <div class="form-group"><label>Email</label><input type="email" name="Email"></div>
                <div class="form-group"><label>No. HP</label><input type="text" name="No_HP"></div>
                <div class="form-group"><label>Pekerjaan</label><input type="text" name="Pekerjaan"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>2. Alamat Mahasiswa (alamat_mahasiswa)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Alamat</label><textarea name="alm_mhs_alamat"></textarea></div>
                <div class="form-group"><label>Kota</label><input type="text" name="alm_mhs_kota"></div>
                <div class="form-group"><label>RT</label><input type="text" name="alm_mhs_rt"></div>
                <div class="form-group"><label>RW</label><input type="text" name="alm_mhs_rw"></div>
                <div class="form-group"><label>Kelurahan</label><input type="text" name="alm_mhs_kelurahan"></div>
                <div class="form-group"><label>Kode Pos</label><input type="text" name="alm_mhs_kode_pos"></div>
                <div class="form-group"><label>Negara</label><input type="text" name="alm_mhs_negara" value="Indonesia"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>3. Data Akademik (akademik)</h3>
            <div class="form-grid">
                <div class="form-group"><label>No. Tes Masuk</label><input type="text" name="No_Tes_Masuk"></div>
                <div class="form-group"><label>Tanggal Terdaftar</label><input type="date" name="Tanggal_Terdaftar"></div>
                <div class="form-group"><label>Status Masuk PT</label><input type="text" name="Status_Masuk_PT" placeholder="Baru / Pindahan"></div>
                <div class="form-group"><label>Jalur Masuk PT</label><input type="text" name="Jalur_Masuk_PT"></div>
                <div class="form-group"><label>Status Mahasiswa</label><input type="text" name="Status_Mahasiswa" value="Aktif"></div>
                <div class="form-group"><label>SKS Wajib</label><input type="number" name="SKS_Wajib"></div>
                <div class="form-group"><label>SKS Pilihan</label><input type="number" name="SKS_Pilihan"></div>
                <div class="form-group"><label>SKS Pindahan</label><input type="number" name="SKS_Pindahan"></div>
                <div class="form-group"><label>PT Asal</label><input type="text" name="PT_Asal"></div>
                <div class="form-group"><label>Beasiswa</label><input type="text" name="Beasiswa"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>4. Pendidikan Asal (pendidikan_asal)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Nama SMTA</label><input type="text" name="Nama_SMTA"></div>
                <div class="form-group"><label>Alamat SMTA</label><textarea name="Alamat_SMTA"></textarea></div>
                <div class="form-group"><label>Jurusan</label><input type="text" name="Jurusan"></div>
                <div class="form-group"><label>Tahun Lulus</label><input type="number" name="Tahun_Lulus"></div>
                <div class="form-group"><label>No. Ijazah</label><input type="text" name="No_Ijazah"></div>
                <div class="form-group"><label>Tanggal Ijazah</label><input type="date" name="Tanggal_Ijazah"></div>
                <div class="form-group"><label>Jumlah MP Ujian</label><input type="number" name="Jumlah_MP_Ujian"></div>
                <div class="form-group"><label>Nilai Ujian</label><input type="number" step="0.01" name="Nilai_Ujian"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>5. Pekerjaan & Ekonomi (pekerjaan & ekonomi)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Tempat Kerja</label><input type="text" name="Tempat_Kerja"></div>
                <div class="form-group"><label>No. Telepon</label><input type="text" name="No_Telepon"></div>
                <div class="form-group"><label>No. Akses</label><input type="text" name="No_Akses"></div>
                <div class="form-group"><label>Jumlah Saudara</label><input type="number" name="Jumlah_Saudara"></div>
                <div class="form-group"><label>Jumlah Tanggungan</label><input type="number" name="Jumlah_Tanggungan"></div>
                <div class="form-group"><label>KPS</label><input type="text" name="KPS"></div>
                <div class="form-group">
                    <label>Mampu</label>
                    <select name="Mampu">
                        <option value="">-- Pilih --</option>
                        <option value="1">1 (Ya / Mampu)</option>
                        <option value="0">0 (Tidak)</option>
                    </select>
                </div>
                <div class="form-group"><label>NPWP</label><input type="text" name="NPWP"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>6. Data Orang Tua & Alamat Orang Tua (orang_tua & alamat_orang_tua)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Nama Ayah</label><input type="text" name="Nama_Ayah"></div>
                <div class="form-group"><label>Tanggal Lahir Ayah</label><input type="date" name="Tanggal_Lahir_Ayah"></div>
                <div class="form-group"><label>Status Ayah</label><input type="text" name="Status_Ayah"></div>
                <div class="form-group"><label>Pendidikan Ayah</label><input type="text" name="Pendidikan_Ayah"></div>
                <div class="form-group"><label>Pekerjaan Ayah</label><input type="text" name="Pekerjaan_Ayah"></div>
                <div class="form-group"><label>Penghasilan Ayah</label><input type="number" step="0.01" name="Penghasilan_Ayah"></div>

                <div class="form-group"><label>Nama Ibu</label><input type="text" name="Nama_Ibu"></div>
                <div class="form-group"><label>Tanggal Lahir Ibu</label><input type="date" name="Tanggal_Lahir_Ibu"></div>
                <div class="form-group"><label>Status Ibu</label><input type="text" name="Status_Ibu"></div>
                <div class="form-group"><label>Pendidikan Ibu</label><input type="text" name="Pendidikan_Ibu"></div>
                <div class="form-group"><label>Pekerjaan Ibu</label><input type="text" name="Pekerjaan_Ibu"></div>
                <div class="form-group"><label>Penghasilan Ibu</label><input type="number" step="0.01" name="Penghasilan_Ibu"></div>

                <div class="form-group"><label>Alamat Orang Tua</label><textarea name="alm_ortu_alamat"></textarea></div>
                <div class="form-group"><label>Kota Orang Tua</label><input type="text" name="alm_ortu_kota"></div>
                <div class="form-group"><label>Kode Pos Orang Tua</label><input type="text" name="alm_ortu_kode_pos"></div>
                <div class="form-group"><label>No. Telepon Orang Tua</label><input type="text" name="alm_ortu_no_telepon"></div>
                <div class="form-group"><label>Email Orang Tua</label><input type="email" name="alm_ortu_email"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>7. Data Wali (wali)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Nama Wali</label><input type="text" name="Nama_Wali"></div>
                <div class="form-group"><label>Tanggal Lahir Wali</label><input type="date" name="Tanggal_Lahir_Wali"></div>
                <div class="form-group"><label>Status Wali</label><input type="text" name="Status_Wali"></div>
                <div class="form-group"><label>Tanggal Meninggal</label><input type="date" name="Tanggal_Meninggal"></div>
                <div class="form-group"><label>Alamat Wali</label><textarea name="Alamat_Wali"></textarea></div>
                <div class="form-group"><label>Kota Wali</label><input type="text" name="Kota_Wali"></div>
                <div class="form-group"><label>No. Telepon Wali</label><input type="text" name="No_Telepon_Wali"></div>
                <div class="form-group"><label>Email Wali</label><input type="email" name="Email_Wali"></div>
                <div class="form-group"><label>Pendidikan Wali</label><input type="text" name="Pendidikan_Wali"></div>
                <div class="form-group"><label>Pekerjaan Wali</label><input type="text" name="Pekerjaan_Wali"></div>
            </div>
        </div>

        <button type="submit" class="btn">Simpan Data</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>