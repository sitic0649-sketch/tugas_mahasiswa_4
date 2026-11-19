<?php
require_once "koneksi.php";

$nim = isset($_GET["nim"]) ? trim($_GET["nim"]) : "";
if ($nim === "") {
    header("Location: index.php");
    exit;
}

$error = "";

function null_if_empty($val) {
    $v = trim((string)$val);
    return $v === "" ? null : $v;
}

function get_one($conn, $sql, $param_type, $param_val) {
    $st = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($st, $param_type, $param_val);
    mysqli_stmt_execute($st);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($st)) ?: [];
}

$mhs = get_one($conn, "SELECT * FROM mahasiswa WHERE NIM = ?", "s", $nim);
if (empty($mhs)) {
    die("Data mahasiswa dengan NIM tersebut tidak ditemukan.");
}

$alm_mhs    = get_one($conn, "SELECT * FROM alamat_mahasiswa WHERE NIM = ? LIMIT 1", "s", $nim);
$akademik   = get_one($conn, "SELECT * FROM akademik WHERE NIM = ? LIMIT 1", "s", $nim);
$pendidikan = get_one($conn, "SELECT * FROM pendidikan_asal WHERE NIM = ? LIMIT 1", "s", $nim);
$pekerjaan  = get_one($conn, "SELECT * FROM pekerjaan WHERE NIM = ? LIMIT 1", "s", $nim);
$ekonomi    = get_one($conn, "SELECT * FROM ekonomi WHERE NIM = ? LIMIT 1", "s", $nim);
$ortu       = get_one($conn, "SELECT * FROM orang_tua WHERE NIM = ? LIMIT 1", "s", $nim);
$wali       = get_one($conn, "SELECT * FROM wali WHERE NIM = ? LIMIT 1", "s", $nim);

$alm_ortu = [];
if (!empty($ortu["ID_Orang_Tua"])) {
    $alm_ortu = get_one($conn, "SELECT * FROM alamat_orang_tua WHERE ID_Orang_Tua = ? LIMIT 1", "i", (int)$ortu["ID_Orang_Tua"]);
}

function upsert_by_nim($conn, $table, $nim, array $data) {
    $existing = get_one($conn, "SELECT NIM FROM `$table` WHERE NIM = ? LIMIT 1", "s", $nim);
    $cols = array_keys($data);
    $vals = array_values($data);

    if (!empty($existing)) {
        $set_clause = implode(", ", array_map(fn($c) => "`$c` = ?", $cols));
        $sql = "UPDATE `$table` SET $set_clause WHERE NIM = ?";
        $types = str_repeat("s", count($vals)) . "s";
        $params = array_merge($vals, [$nim]);
    } else {
        $col_clause = implode(", ", array_map(fn($c) => "`$c`", array_merge(["NIM"], $cols)));
        $ph_clause = implode(", ", array_fill(0, count($vals) + 1, "?"));
        $sql = "INSERT INTO `$table` ($col_clause) VALUES ($ph_clause)";
        $types = "s" . str_repeat("s", count($vals));
        $params = array_merge([$nim], $vals);
    }

    $st = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($st, $types, ...$params);
    mysqli_stmt_execute($st);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $angkatan = (int)($_POST["Angkatan"] ?? $mhs["Angkatan"]);
    $nama_lengkap = trim($_POST["Nama_Lengkap"] ?? "");
    $program_studi = trim($_POST["Program_Studi"] ?? "");
    $nisn = null_if_empty($_POST["NISN"] ?? "");
    $jenis_kelamin = null_if_empty($_POST["Jenis_Kelamin"] ?? "");
    $status_perkawinan = null_if_empty($_POST["Status_Perkawinan"] ?? "");
    $kota_lahir = null_if_empty($_POST["Kota_Lahir"] ?? "");
    $tanggal_lahir = null_if_empty($_POST["Tanggal_Lahir"] ?? "");
    $agama = null_if_empty($_POST["Agama"] ?? "");
    $negara = null_if_empty($_POST["Negara"] ?? "");
    $kewarganegaraan = null_if_empty($_POST["Kewarganegaraan"] ?? "");
    $email = null_if_empty($_POST["Email"] ?? "");
    $no_hp = null_if_empty($_POST["No_HP"] ?? "");
    $pekerjaan_mhs = null_if_empty($_POST["Pekerjaan"] ?? "");

    if ($nama_lengkap === "" || $program_studi === "") {
        $error = "Nama Lengkap dan Program Studi wajib diisi.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // 1. Update tabel mahasiswa
            $upd = mysqli_prepare(
                $conn,
                "UPDATE mahasiswa SET
                    Angkatan = ?, Nama_Lengkap = ?, Program_Studi = ?, NISN = ?,
                    Jenis_Kelamin = ?, Status_Perkawinan = ?, Kota_Lahir = ?, Tanggal_Lahir = ?,
                    Agama = ?, Negara = ?, Kewarganegaraan = ?, Email = ?, No_HP = ?, Pekerjaan = ?
                 WHERE NIM = ?"
            );
            mysqli_stmt_bind_param(
                $upd, "issssssssssssss",
                $angkatan, $nama_lengkap, $program_studi, $nisn,
                $jenis_kelamin, $status_perkawinan, $kota_lahir, $tanggal_lahir,
                $agama, $negara, $kewarganegaraan, $email, $no_hp, $pekerjaan_mhs, $nim
            );
            mysqli_stmt_execute($upd);

            // 2. Alamat mahasiswa
            upsert_by_nim($conn, "alamat_mahasiswa", $nim, [
                "Alamat"    => null_if_empty($_POST["alm_mhs_alamat"] ?? ""),
                "Kota"      => null_if_empty($_POST["alm_mhs_kota"] ?? ""),
                "RT"        => null_if_empty($_POST["alm_mhs_rt"] ?? ""),
                "RW"        => null_if_empty($_POST["alm_mhs_rw"] ?? ""),
                "Kelurahan" => null_if_empty($_POST["alm_mhs_kelurahan"] ?? ""),
                "Kode_Pos"  => null_if_empty($_POST["alm_mhs_kode_pos"] ?? ""),
                "Negara"    => null_if_empty($_POST["alm_mhs_negara"] ?? ""),
            ]);

            // 3. Akademik
            upsert_by_nim($conn, "akademik", $nim, [
                "No_Tes_Masuk"      => null_if_empty($_POST["No_Tes_Masuk"] ?? ""),
                "Tanggal_Terdaftar" => null_if_empty($_POST["Tanggal_Terdaftar"] ?? ""),
                "Status_Masuk_PT"   => null_if_empty($_POST["Status_Masuk_PT"] ?? ""),
                "Jalur_Masuk_PT"    => null_if_empty($_POST["Jalur_Masuk_PT"] ?? ""),
                "Status_Mahasiswa"  => null_if_empty($_POST["Status_Mahasiswa"] ?? ""),
                "SKS_Wajib"         => (isset($_POST["SKS_Wajib"]) && $_POST["SKS_Wajib"] !== "") ? (string)(int)$_POST["SKS_Wajib"] : null,
                "SKS_Pilihan"       => (isset($_POST["SKS_Pilihan"]) && $_POST["SKS_Pilihan"] !== "") ? (string)(int)$_POST["SKS_Pilihan"] : null,
                "SKS_Pindahan"      => (isset($_POST["SKS_Pindahan"]) && $_POST["SKS_Pindahan"] !== "") ? (string)(int)$_POST["SKS_Pindahan"] : null,
                "PT_Asal"           => null_if_empty($_POST["PT_Asal"] ?? ""),
                "Beasiswa"          => null_if_empty($_POST["Beasiswa"] ?? ""),
            ]);

            // 4. Pendidikan asal
            upsert_by_nim($conn, "pendidikan_asal", $nim, [
                "Nama_SMTA"        => null_if_empty($_POST["Nama_SMTA"] ?? ""),
                "Alamat_SMTA"      => null_if_empty($_POST["Alamat_SMTA"] ?? ""),
                "Jurusan"          => null_if_empty($_POST["Jurusan"] ?? ""),
                "Tahun_Lulus"      => (isset($_POST["Tahun_Lulus"]) && $_POST["Tahun_Lulus"] !== "") ? (string)(int)$_POST["Tahun_Lulus"] : null,
                "No_Ijazah"        => null_if_empty($_POST["No_Ijazah"] ?? ""),
                "Tanggal_Ijazah"   => null_if_empty($_POST["Tanggal_Ijazah"] ?? ""),
                "Jumlah_MP_Ujian"  => (isset($_POST["Jumlah_MP_Ujian"]) && $_POST["Jumlah_MP_Ujian"] !== "") ? (string)(int)$_POST["Jumlah_MP_Ujian"] : null,
                "Nilai_Ujian"      => (isset($_POST["Nilai_Ujian"]) && $_POST["Nilai_Ujian"] !== "") ? (string)(float)$_POST["Nilai_Ujian"] : null,
            ]);

            // 5. Pekerjaan & ekonomi
            upsert_by_nim($conn, "pekerjaan", $nim, [
                "Tempat_Kerja" => null_if_empty($_POST["Tempat_Kerja"] ?? ""),
                "No_Telepon"   => null_if_empty($_POST["No_Telepon"] ?? ""),
                "No_Akses"     => null_if_empty($_POST["No_Akses"] ?? ""),
            ]);

            upsert_by_nim($conn, "ekonomi", $nim, [
                "Jumlah_Saudara"    => (isset($_POST["Jumlah_Saudara"]) && $_POST["Jumlah_Saudara"] !== "") ? (string)(int)$_POST["Jumlah_Saudara"] : null,
                "Jumlah_Tanggungan" => (isset($_POST["Jumlah_Tanggungan"]) && $_POST["Jumlah_Tanggungan"] !== "") ? (string)(int)$_POST["Jumlah_Tanggungan"] : null,
                "KPS"               => null_if_empty($_POST["KPS"] ?? ""),
                "Mampu"             => (isset($_POST["Mampu"]) && $_POST["Mampu"] !== "") ? (string)(int)$_POST["Mampu"] : null,
                "NPWP"              => null_if_empty($_POST["NPWP"] ?? ""),
            ]);

            // 6. Orang tua & alamat orang tua
            $ortu_data = [
                "Nama_Ayah"          => null_if_empty($_POST["Nama_Ayah"] ?? ""),
                "Tanggal_Lahir_Ayah" => null_if_empty($_POST["Tanggal_Lahir_Ayah"] ?? ""),
                "Status_Ayah"        => null_if_empty($_POST["Status_Ayah"] ?? ""),
                "Pendidikan_Ayah"    => null_if_empty($_POST["Pendidikan_Ayah"] ?? ""),
                "Pekerjaan_Ayah"     => null_if_empty($_POST["Pekerjaan_Ayah"] ?? ""),
                "Penghasilan_Ayah"   => (isset($_POST["Penghasilan_Ayah"]) && $_POST["Penghasilan_Ayah"] !== "") ? (string)(float)$_POST["Penghasilan_Ayah"] : null,
                "Nama_Ibu"           => null_if_empty($_POST["Nama_Ibu"] ?? ""),
                "Tanggal_Lahir_Ibu"  => null_if_empty($_POST["Tanggal_Lahir_Ibu"] ?? ""),
                "Status_Ibu"         => null_if_empty($_POST["Status_Ibu"] ?? ""),
                "Pendidikan_Ibu"     => null_if_empty($_POST["Pendidikan_Ibu"] ?? ""),
                "Pekerjaan_Ibu"      => null_if_empty($_POST["Pekerjaan_Ibu"] ?? ""),
                "Penghasilan_Ibu"    => (isset($_POST["Penghasilan_Ibu"]) && $_POST["Penghasilan_Ibu"] !== "") ? (string)(float)$_POST["Penghasilan_Ibu"] : null,
            ];
            upsert_by_nim($conn, "orang_tua", $nim, $ortu_data);

            $ortu_baru = get_one($conn, "SELECT ID_Orang_Tua FROM orang_tua WHERE NIM = ? LIMIT 1", "s", $nim);
            if (!empty($ortu_baru["ID_Orang_Tua"])) {
                $id_ortu = (int)$ortu_baru["ID_Orang_Tua"];
                $alm_ortu_data = [
                    "Alamat"     => null_if_empty($_POST["alm_ortu_alamat"] ?? ""),
                    "Kota"       => null_if_empty($_POST["alm_ortu_kota"] ?? ""),
                    "Kode_Pos"   => null_if_empty($_POST["alm_ortu_kode_pos"] ?? ""),
                    "No_Telepon" => null_if_empty($_POST["alm_ortu_no_telepon"] ?? ""),
                    "Email"      => null_if_empty($_POST["alm_ortu_email"] ?? ""),
                ];
                $cek_alm = get_one($conn, "SELECT ID_Orang_Tua FROM alamat_orang_tua WHERE ID_Orang_Tua = ? LIMIT 1", "i", $id_ortu);
                if (!empty($cek_alm)) {
                    $st_ao = mysqli_prepare(
                        $conn,
                        "UPDATE alamat_orang_tua SET Alamat = ?, Kota = ?, Kode_Pos = ?, No_Telepon = ?, Email = ? WHERE ID_Orang_Tua = ?"
                    );
                    mysqli_stmt_bind_param(
                        $st_ao, "sssssi",
                        $alm_ortu_data["Alamat"], $alm_ortu_data["Kota"], $alm_ortu_data["Kode_Pos"],
                        $alm_ortu_data["No_Telepon"], $alm_ortu_data["Email"], $id_ortu
                    );
                    mysqli_stmt_execute($st_ao);
                } else {
                    $st_ao = mysqli_prepare(
                        $conn,
                        "INSERT INTO alamat_orang_tua (ID_Orang_Tua, Alamat, Kota, Kode_Pos, No_Telepon, Email) VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    mysqli_stmt_bind_param(
                        $st_ao, "isssss",
                        $id_ortu, $alm_ortu_data["Alamat"], $alm_ortu_data["Kota"],
                        $alm_ortu_data["Kode_Pos"], $alm_ortu_data["No_Telepon"], $alm_ortu_data["Email"]
                    );
                    mysqli_stmt_execute($st_ao);
                }
            }

            // 7. Wali
            upsert_by_nim($conn, "wali", $nim, [
                "Nama_Wali"         => null_if_empty($_POST["Nama_Wali"] ?? ""),
                "Tanggal_Lahir"     => null_if_empty($_POST["Tanggal_Lahir_Wali"] ?? ""),
                "Status_Wali"       => null_if_empty($_POST["Status_Wali"] ?? ""),
                "Tanggal_Meninggal" => null_if_empty($_POST["Tanggal_Meninggal"] ?? ""),
                "Alamat"            => null_if_empty($_POST["Alamat_Wali"] ?? ""),
                "Kota"              => null_if_empty($_POST["Kota_Wali"] ?? ""),
                "No_Telepon"        => null_if_empty($_POST["No_Telepon_Wali"] ?? ""),
                "Email"             => null_if_empty($_POST["Email_Wali"] ?? ""),
                "Pendidikan"        => null_if_empty($_POST["Pendidikan_Wali"] ?? ""),
                "Pekerjaan"         => null_if_empty($_POST["Pekerjaan_Wali"] ?? ""),
            ]);

            mysqli_commit($conn);
            header("Location: index.php?pesan=sukses_edit");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}

function fval($arr, $key, $default = "") {
    if (!is_array($arr) || !isset($arr[$key]) || $arr[$key] === null) {
        return htmlspecialchars($default);
    }
    return htmlspecialchars((string)$arr[$key]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Mahasiswa - <?= htmlspecialchars($mhs["NIM"]) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Edit Data Mahasiswa (<?= htmlspecialchars($mhs["NIM"]) ?>)</h1>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit.php?nim=<?= urlencode($mhs["NIM"]) ?>">
        <div class="form-section">
            <h3>1. Biodata Utama (mahasiswa)</h3>
            <div class="form-grid">
                <div class="form-group"><label>NIM</label><input type="text" value="<?= fval($mhs, "NIM") ?>" disabled></div>
                <div class="form-group"><label>Angkatan *</label><input type="number" name="Angkatan" value="<?= fval($mhs, "Angkatan") ?>" required></div>
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="Nama_Lengkap" value="<?= fval($mhs, "Nama_Lengkap") ?>" required></div>
                <div class="form-group"><label>Program Studi *</label><input type="text" name="Program_Studi" value="<?= fval($mhs, "Program_Studi") ?>" required></div>
                <div class="form-group"><label>NISN</label><input type="text" name="NISN" value="<?= fval($mhs, "NISN") ?>"></div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="Jenis_Kelamin">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" <?= (($mhs["Jenis_Kelamin"] ?? "") === "Laki-laki") ? "selected" : "" ?>>Laki-laki</option>
                        <option value="Perempuan" <?= (($mhs["Jenis_Kelamin"] ?? "") === "Perempuan") ? "selected" : "" ?>>Perempuan</option>
                    </select>
                </div>
                <div class="form-group"><label>Status Perkawinan</label><input type="text" name="Status_Perkawinan" value="<?= fval($mhs, "Status_Perkawinan") ?>"></div>
                <div class="form-group"><label>Kota Lahir</label><input type="text" name="Kota_Lahir" value="<?= fval($mhs, "Kota_Lahir") ?>"></div>
                <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="Tanggal_Lahir" value="<?= fval($mhs, "Tanggal_Lahir") ?>"></div>
                <div class="form-group"><label>Agama</label><input type="text" name="Agama" value="<?= fval($mhs, "Agama") ?>"></div>
                <div class="form-group"><label>Negara</label><input type="text" name="Negara" value="<?= fval($mhs, "Negara") ?>"></div>
                <div class="form-group"><label>Kewarganegaraan</label><input type="text" name="Kewarganegaraan" value="<?= fval($mhs, "Kewarganegaraan") ?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="Email" value="<?= fval($mhs, "Email") ?>"></div>
                <div class="form-group"><label>No. HP</label><input type="text" name="No_HP" value="<?= fval($mhs, "No_HP") ?>"></div>
                <div class="form-group"><label>Pekerjaan</label><input type="text" name="Pekerjaan" value="<?= fval($mhs, "Pekerjaan") ?>"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>2. Alamat Mahasiswa (alamat_mahasiswa)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Alamat</label><textarea name="alm_mhs_alamat"><?= fval($alm_mhs, "Alamat") ?></textarea></div>
                <div class="form-group"><label>Kota</label><input type="text" name="alm_mhs_kota" value="<?= fval($alm_mhs, "Kota") ?>"></div>
                <div class="form-group"><label>RT</label><input type="text" name="alm_mhs_rt" value="<?= fval($alm_mhs, "RT") ?>"></div>
                <div class="form-group"><label>RW</label><input type="text" name="alm_mhs_rw" value="<?= fval($alm_mhs, "RW") ?>"></div>
                <div class="form-group"><label>Kelurahan</label><input type="text" name="alm_mhs_kelurahan" value="<?= fval($alm_mhs, "Kelurahan") ?>"></div>
                <div class="form-group"><label>Kode Pos</label><input type="text" name="alm_mhs_kode_pos" value="<?= fval($alm_mhs, "Kode_Pos") ?>"></div>
                <div class="form-group"><label>Negara</label><input type="text" name="alm_mhs_negara" value="<?= fval($alm_mhs, "Negara") ?>"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>3. Data Akademik (akademik)</h3>
            <div class="form-grid">
                <div class="form-group"><label>No. Tes Masuk</label><input type="text" name="No_Tes_Masuk" value="<?= fval($akademik, "No_Tes_Masuk") ?>"></div>
                <div class="form-group"><label>Tanggal Terdaftar</label><input type="date" name="Tanggal_Terdaftar" value="<?= fval($akademik, "Tanggal_Terdaftar") ?>"></div>
                <div class="form-group"><label>Status Masuk PT</label><input type="text" name="Status_Masuk_PT" value="<?= fval($akademik, "Status_Masuk_PT") ?>"></div>
                <div class="form-group"><label>Jalur Masuk PT</label><input type="text" name="Jalur_Masuk_PT" value="<?= fval($akademik, "Jalur_Masuk_PT") ?>"></div>
                <div class="form-group"><label>Status Mahasiswa</label><input type="text" name="Status_Mahasiswa" value="<?= fval($akademik, "Status_Mahasiswa") ?>"></div>
                <div class="form-group"><label>SKS Wajib</label><input type="number" name="SKS_Wajib" value="<?= fval($akademik, "SKS_Wajib") ?>"></div>
                <div class="form-group"><label>SKS Pilihan</label><input type="number" name="SKS_Pilihan" value="<?= fval($akademik, "SKS_Pilihan") ?>"></div>
                <div class="form-group"><label>SKS Pindahan</label><input type="number" name="SKS_Pindahan" value="<?= fval($akademik, "SKS_Pindahan") ?>"></div>
                <div class="form-group"><label>PT Asal</label><input type="text" name="PT_Asal" value="<?= fval($akademik, "PT_Asal") ?>"></div>
                <div class="form-group"><label>Beasiswa</label><input type="text" name="Beasiswa" value="<?= fval($akademik, "Beasiswa") ?>"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>4. Pendidikan Asal (pendidikan_asal)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Nama SMTA</label><input type="text" name="Nama_SMTA" value="<?= fval($pendidikan, "Nama_SMTA") ?>"></div>
                <div class="form-group"><label>Alamat SMTA</label><textarea name="Alamat_SMTA"><?= fval($pendidikan, "Alamat_SMTA") ?></textarea></div>
                <div class="form-group"><label>Jurusan</label><input type="text" name="Jurusan" value="<?= fval($pendidikan, "Jurusan") ?>"></div>
                <div class="form-group"><label>Tahun Lulus</label><input type="number" name="Tahun_Lulus" value="<?= fval($pendidikan, "Tahun_Lulus") ?>"></div>
                <div class="form-group"><label>No. Ijazah</label><input type="text" name="No_Ijazah" value="<?= fval($pendidikan, "No_Ijazah") ?>"></div>
                <div class="form-group"><label>Tanggal Ijazah</label><input type="date" name="Tanggal_Ijazah" value="<?= fval($pendidikan, "Tanggal_Ijazah") ?>"></div>
                <div class="form-group"><label>Jumlah MP Ujian</label><input type="number" name="Jumlah_MP_Ujian" value="<?= fval($pendidikan, "Jumlah_MP_Ujian") ?>"></div>
                <div class="form-group"><label>Nilai Ujian</label><input type="number" step="0.01" name="Nilai_Ujian" value="<?= fval($pendidikan, "Nilai_Ujian") ?>"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>5. Pekerjaan & Ekonomi (pekerjaan & ekonomi)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Tempat Kerja</label><input type="text" name="Tempat_Kerja" value="<?= fval($pekerjaan, "Tempat_Kerja") ?>"></div>
                <div class="form-group"><label>No. Telepon</label><input type="text" name="No_Telepon" value="<?= fval($pekerjaan, "No_Telepon") ?>"></div>
                <div class="form-group"><label>No. Akses</label><input type="text" name="No_Akses" value="<?= fval($pekerjaan, "No_Akses") ?>"></div>
                <div class="form-group"><label>Jumlah Saudara</label><input type="number" name="Jumlah_Saudara" value="<?= fval($ekonomi, "Jumlah_Saudara") ?>"></div>
                <div class="form-group"><label>Jumlah Tanggungan</label><input type="number" name="Jumlah_Tanggungan" value="<?= fval($ekonomi, "Jumlah_Tanggungan") ?>"></div>
                <div class="form-group"><label>KPS</label><input type="text" name="KPS" value="<?= fval($ekonomi, "KPS") ?>"></div>
                <div class="form-group">
                    <label>Mampu</label>
                    <select name="Mampu">
                        <option value="">-- Pilih --</option>
                        <option value="1" <?= ((string)($ekonomi["Mampu"] ?? "") === "1") ? "selected" : "" ?>>1 (Ya / Mampu)</option>
                        <option value="0" <?= ((string)($ekonomi["Mampu"] ?? "") === "0") ? "selected" : "" ?>>0 (Tidak)</option>
                    </select>
                </div>
                <div class="form-group"><label>NPWP</label><input type="text" name="NPWP" value="<?= fval($ekonomi, "NPWP") ?>"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>6. Data Orang Tua & Alamat Orang Tua (orang_tua & alamat_orang_tua)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Nama Ayah</label><input type="text" name="Nama_Ayah" value="<?= fval($ortu, "Nama_Ayah") ?>"></div>
                <div class="form-group"><label>Tanggal Lahir Ayah</label><input type="date" name="Tanggal_Lahir_Ayah" value="<?= fval($ortu, "Tanggal_Lahir_Ayah") ?>"></div>
                <div class="form-group"><label>Status Ayah</label><input type="text" name="Status_Ayah" value="<?= fval($ortu, "Status_Ayah") ?>"></div>
                <div class="form-group"><label>Pendidikan Ayah</label><input type="text" name="Pendidikan_Ayah" value="<?= fval($ortu, "Pendidikan_Ayah") ?>"></div>
                <div class="form-group"><label>Pekerjaan Ayah</label><input type="text" name="Pekerjaan_Ayah" value="<?= fval($ortu, "Pekerjaan_Ayah") ?>"></div>
                <div class="form-group"><label>Penghasilan Ayah</label><input type="number" step="0.01" name="Penghasilan_Ayah" value="<?= fval($ortu, "Penghasilan_Ayah") ?>"></div>

                <div class="form-group"><label>Nama Ibu</label><input type="text" name="Nama_Ibu" value="<?= fval($ortu, "Nama_Ibu") ?>"></div>
                <div class="form-group"><label>Tanggal Lahir Ibu</label><input type="date" name="Tanggal_Lahir_Ibu" value="<?= fval($ortu, "Tanggal_Lahir_Ibu") ?>"></div>
                <div class="form-group"><label>Status Ibu</label><input type="text" name="Status_Ibu" value="<?= fval($ortu, "Status_Ibu") ?>"></div>
                <div class="form-group"><label>Pendidikan Ibu</label><input type="text" name="Pendidikan_Ibu" value="<?= fval($ortu, "Pendidikan_Ibu") ?>"></div>
                <div class="form-group"><label>Pekerjaan Ibu</label><input type="text" name="Pekerjaan_Ibu" value="<?= fval($ortu, "Pekerjaan_Ibu") ?>"></div>
                <div class="form-group"><label>Penghasilan Ibu</label><input type="number" step="0.01" name="Penghasilan_Ibu" value="<?= fval($ortu, "Penghasilan_Ibu") ?>"></div>

                <div class="form-group"><label>Alamat Orang Tua</label><textarea name="alm_ortu_alamat"><?= fval($alm_ortu, "Alamat") ?></textarea></div>
                <div class="form-group"><label>Kota Orang Tua</label><input type="text" name="alm_ortu_kota" value="<?= fval($alm_ortu, "Kota") ?>"></div>
                <div class="form-group"><label>Kode Pos Orang Tua</label><input type="text" name="alm_ortu_kode_pos" value="<?= fval($alm_ortu, "Kode_Pos") ?>"></div>
                <div class="form-group"><label>No. Telepon Orang Tua</label><input type="text" name="alm_ortu_no_telepon" value="<?= fval($alm_ortu, "No_Telepon") ?>"></div>
                <div class="form-group"><label>Email Orang Tua</label><input type="email" name="alm_ortu_email" value="<?= fval($alm_ortu, "Email") ?>"></div>
            </div>
        </div>

        <div class="form-section">
            <h3>7. Data Wali (wali)</h3>
            <div class="form-grid">
                <div class="form-group"><label>Nama Wali</label><input type="text" name="Nama_Wali" value="<?= fval($wali, "Nama_Wali") ?>"></div>
                <div class="form-group"><label>Tanggal Lahir Wali</label><input type="date" name="Tanggal_Lahir_Wali" value="<?= fval($wali, "Tanggal_Lahir") ?>"></div>
                <div class="form-group"><label>Status Wali</label><input type="text" name="Status_Wali" value="<?= fval($wali, "Status_Wali") ?>"></div>
                <div class="form-group"><label>Tanggal Meninggal</label><input type="date" name="Tanggal_Meninggal" value="<?= fval($wali, "Tanggal_Meninggal") ?>"></div>
                <div class="form-group"><label>Alamat Wali</label><textarea name="Alamat_Wali"><?= fval($wali, "Alamat") ?></textarea></div>
                <div class="form-group"><label>Kota Wali</label><input type="text" name="Kota_Wali" value="<?= fval($wali, "Kota") ?>"></div>
                <div class="form-group"><label>No. Telepon Wali</label><input type="text" name="No_Telepon_Wali" value="<?= fval($wali, "No_Telepon") ?>"></div>
                <div class="form-group"><label>Email Wali</label><input type="email" name="Email_Wali" value="<?= fval($wali, "Email") ?>"></div>
                <div class="form-group"><label>Pendidikan Wali</label><input type="text" name="Pendidikan_Wali" value="<?= fval($wali, "Pendidikan") ?>"></div>
                <div class="form-group"><label>Pekerjaan Wali</label><input type="text" name="Pekerjaan_Wali" value="<?= fval($wali, "Pekerjaan") ?>"></div>
            </div>
        </div>

        <button type="submit" class="btn">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>