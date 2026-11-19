<?php
require_once "koneksi.php";

$nim = isset($_GET["nim"]) ? trim($_GET["nim"]) : "";
if ($nim === "") {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM mahasiswa WHERE NIM = ?");
mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);
$mhs = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$mhs) {
    die("Data mahasiswa dengan NIM tersebut tidak ditemukan.");
}

function get_one($conn, $sql, $nim) {
    $st = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($st, "s", $nim);
    mysqli_stmt_execute($st);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($st));
}

$alamat_mhs = get_one($conn, "SELECT * FROM alamat_mahasiswa WHERE NIM = ? LIMIT 1", $nim);
$akademik   = get_one($conn, "SELECT * FROM akademik WHERE NIM = ? LIMIT 1", $nim);
$pendidikan = get_one($conn, "SELECT * FROM pendidikan_asal WHERE NIM = ? LIMIT 1", $nim);
$pekerjaan  = get_one($conn, "SELECT * FROM pekerjaan WHERE NIM = ? LIMIT 1", $nim);
$ekonomi    = get_one($conn, "SELECT * FROM ekonomi WHERE NIM = ? LIMIT 1", $nim);
$ortu       = get_one($conn, "SELECT * FROM orang_tua WHERE NIM = ? LIMIT 1", $nim);
$wali       = get_one($conn, "SELECT * FROM wali WHERE NIM = ? LIMIT 1", $nim);

$alm_ortu = null;
if ($ortu && isset($ortu["ID_Orang_Tua"])) {
    $st = mysqli_prepare($conn, "SELECT * FROM alamat_orang_tua WHERE ID_Orang_Tua = ? LIMIT 1");
    mysqli_stmt_bind_param($st, "i", $ortu["ID_Orang_Tua"]);
    mysqli_stmt_execute($st);
    $alm_ortu = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
}

function v($arr, $key) {
    if (!$arr || !isset($arr[$key]) || $arr[$key] === "" || $arr[$key] === null) {
        return "-";
    }
    return htmlspecialchars((string)$arr[$key]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Mahasiswa - <?= htmlspecialchars($mhs["NIM"]) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Detail Mahasiswa: <?= htmlspecialchars($mhs["Nama_Lengkap"]) ?></h1>
            <small>NIM: <?= htmlspecialchars($mhs["NIM"]) ?></small>
        </div>
        <div class="actions">
            <a href="edit.php?nim=<?= urlencode($mhs["NIM"]) ?>" class="btn btn-warning">Edit</a>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <h3>1. Biodata Mahasiswa</h3>
    <table>
        <tr><th style="width: 240px;">NIM</th><td><?= v($mhs, "NIM") ?></td></tr>
        <tr><th>Angkatan</th><td><?= v($mhs, "Angkatan") ?></td></tr>
        <tr><th>Nama Lengkap</th><td><?= v($mhs, "Nama_Lengkap") ?></td></tr>
        <tr><th>Program Studi</th><td><?= v($mhs, "Program_Studi") ?></td></tr>
        <tr><th>NISN</th><td><?= v($mhs, "NISN") ?></td></tr>
        <tr><th>Jenis Kelamin</th><td><?= v($mhs, "Jenis_Kelamin") ?></td></tr>
        <tr><th>Status Perkawinan</th><td><?= v($mhs, "Status_Perkawinan") ?></td></tr>
        <tr><th>Kota / Tanggal Lahir</th><td><?= v($mhs, "Kota_Lahir") ?>, <?= v($mhs, "Tanggal_Lahir") ?></td></tr>
        <tr><th>Agama</th><td><?= v($mhs, "Agama") ?></td></tr>
        <tr><th>Negara / Kewarganegaraan</th><td><?= v($mhs, "Negara") ?> / <?= v($mhs, "Kewarganegaraan") ?></td></tr>
        <tr><th>Email / No. HP</th><td><?= v($mhs, "Email") ?> / <?= v($mhs, "No_HP") ?></td></tr>
        <tr><th>Pekerjaan</th><td><?= v($mhs, "Pekerjaan") ?></td></tr>
    </table>

    <h3>2. Alamat Mahasiswa</h3>
    <table>
        <tr><th style="width: 240px;">Alamat</th><td><?= v($alamat_mhs, "Alamat") ?></td></tr>
        <tr><th>RT / RW / Kelurahan</th><td><?= v($alamat_mhs, "RT") ?> / <?= v($alamat_mhs, "RW") ?> / <?= v($alamat_mhs, "Kelurahan") ?></td></tr>
        <tr><th>Kota / Kode Pos / Negara</th><td><?= v($alamat_mhs, "Kota") ?> / <?= v($alamat_mhs, "Kode_Pos") ?> / <?= v($alamat_mhs, "Negara") ?></td></tr>
    </table>

    <h3>3. Akademik</h3>
    <table>
        <tr><th style="width: 240px;">No. Tes Masuk</th><td><?= v($akademik, "No_Tes_Masuk") ?></td></tr>
        <tr><th>Tanggal Terdaftar</th><td><?= v($akademik, "Tanggal_Terdaftar") ?></td></tr>
        <tr><th>Status Masuk / Jalur Masuk</th><td><?= v($akademik, "Status_Masuk_PT") ?> / <?= v($akademik, "Jalur_Masuk_PT") ?></td></tr>
        <tr><th>Status Mahasiswa</th><td><?= v($akademik, "Status_Mahasiswa") ?></td></tr>
        <tr><th>SKS Wajib / Pilihan / Pindahan</th><td><?= v($akademik, "SKS_Wajib") ?> / <?= v($akademik, "SKS_Pilihan") ?> / <?= v($akademik, "SKS_Pindahan") ?></td></tr>
        <tr><th>PT Asal / Beasiswa</th><td><?= v($akademik, "PT_Asal") ?> / <?= v($akademik, "Beasiswa") ?></td></tr>
    </table>

    <h3>4. Pendidikan Asal</h3>
    <table>
        <tr><th style="width: 240px;">Nama SMTA</th><td><?= v($pendidikan, "Nama_SMTA") ?></td></tr>
        <tr><th>Alamat SMTA</th><td><?= v($pendidikan, "Alamat_SMTA") ?></td></tr>
        <tr><th>Jurusan / Tahun Lulus</th><td><?= v($pendidikan, "Jurusan") ?> / <?= v($pendidikan, "Tahun_Lulus") ?></td></tr>
        <tr><th>No. Ijazah / Tanggal Ijazah</th><td><?= v($pendidikan, "No_Ijazah") ?> / <?= v($pendidikan, "Tanggal_Ijazah") ?></td></tr>
        <tr><th>Jumlah MP Ujian / Nilai Ujian</th><td><?= v($pendidikan, "Jumlah_MP_Ujian") ?> / <?= v($pendidikan, "Nilai_Ujian") ?></td></tr>
    </table>

    <h3>5. Pekerjaan & Ekonomi</h3>
    <table>
        <tr><th style="width: 240px;">Tempat Kerja / No. Telp</th><td><?= v($pekerjaan, "Tempat_Kerja") ?> / <?= v($pekerjaan, "No_Telepon") ?></td></tr>
        <tr><th>No. Akses</th><td><?= v($pekerjaan, "No_Akses") ?></td></tr>
        <tr><th>Jumlah Saudara / Tanggungan</th><td><?= v($ekonomi, "Jumlah_Saudara") ?> / <?= v($ekonomi, "Jumlah_Tanggungan") ?></td></tr>
        <tr><th>KPS / Mampu / NPWP</th><td><?= v($ekonomi, "KPS") ?> / <?= v($ekonomi, "Mampu") ?> / <?= v($ekonomi, "NPWP") ?></td></tr>
    </table>

    <h3>6. Orang Tua & Alamat Orang Tua</h3>
    <table>
        <tr><th style="width: 240px;">Nama Ayah / Tgl Lahir</th><td><?= v($ortu, "Nama_Ayah") ?> / <?= v($ortu, "Tanggal_Lahir_Ayah") ?></td></tr>
        <tr><th>Status / Pendidikan / Pekerjaan Ayah</th><td><?= v($ortu, "Status_Ayah") ?> / <?= v($ortu, "Pendidikan_Ayah") ?> / <?= v($ortu, "Pekerjaan_Ayah") ?></td></tr>
        <tr><th>Penghasilan Ayah</th><td><?= v($ortu, "Penghasilan_Ayah") ?></td></tr>
        <tr><th>Nama Ibu / Tgl Lahir</th><td><?= v($ortu, "Nama_Ibu") ?> / <?= v($ortu, "Tanggal_Lahir_Ibu") ?></td></tr>
        <tr><th>Status / Pendidikan / Pekerjaan Ibu</th><td><?= v($ortu, "Status_Ibu") ?> / <?= v($ortu, "Pendidikan_Ibu") ?> / <?= v($ortu, "Pekerjaan_Ibu") ?></td></tr>
        <tr><th>Penghasilan Ibu</th><td><?= v($ortu, "Penghasilan_Ibu") ?></td></tr>
        <tr><th>Alamat Orang Tua</th><td><?= v($alm_ortu, "Alamat") ?></td></tr>
        <tr><th>Kota / Kode Pos Orang Tua</th><td><?= v($alm_ortu, "Kota") ?> / <?= v($alm_ortu, "Kode_Pos") ?></td></tr>
        <tr><th>No. Telepon / Email Orang Tua</th><td><?= v($alm_ortu, "No_Telepon") ?> / <?= v($alm_ortu, "Email") ?></td></tr>
    </table>

    <h3>7. Wali</h3>
    <table>
        <tr><th style="width: 240px;">Nama Wali / Tgl Lahir</th><td><?= v($wali, "Nama_Wali") ?> / <?= v($wali, "Tanggal_Lahir") ?></td></tr>
        <tr><th>Status Wali / Tgl Meninggal</th><td><?= v($wali, "Status_Wali") ?> / <?= v($wali, "Tanggal_Meninggal") ?></td></tr>
        <tr><th>Alamat / Kota Wali</th><td><?= v($wali, "Alamat") ?> / <?= v($wali, "Kota") ?></td></tr>
        <tr><th>No. Telepon / Email Wali</th><td><?= v($wali, "No_Telepon") ?> / <?= v($wali, "Email") ?></td></tr>
        <tr><th>Pendidikan / Pekerjaan Wali</th><td><?= v($wali, "Pendidikan") ?> / <?= v($wali, "Pekerjaan") ?></td></tr>
    </table>
</div>
</body>
</html>