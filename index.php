<?php
require_once "koneksi.php";

$cari = isset($_GET["cari"]) ? trim($_GET["cari"]) : "";
$pesan = isset($_GET["pesan"]) ? $_GET["pesan"] : "";

if ($cari !== "") {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT NIM, Nama_Lengkap, Program_Studi, Angkatan, No_HP, Email
         FROM mahasiswa
         WHERE NIM LIKE ? OR Nama_Lengkap LIKE ? OR Program_Studi LIKE ?
         ORDER BY NIM ASC"
    );
    $like = "%" . $cari . "%";
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query(
        $conn,
        "SELECT NIM, Nama_Lengkap, Program_Studi, Angkatan, No_HP, Email
         FROM mahasiswa
         ORDER BY NIM ASC"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - Tugas 4</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Sistem Pendataan Mahasiswa</h1>
            <small>Terhubung dengan database: <strong>tugas</strong></small>
        </div>
        <a href="tambah.php" class="btn">+ Tambah Mahasiswa</a>
    </div>

    <?php if ($pesan === "sukses_tambah"): ?>
        <div class="alert alert-success">Data mahasiswa berhasil ditambahkan ke database.</div>
    <?php elseif ($pesan === "sukses_edit"): ?>
        <div class="alert alert-success">Data mahasiswa berhasil diperbarui.</div>
    <?php elseif ($pesan === "sukses_hapus"): ?>
        <div class="alert alert-success">Data mahasiswa berhasil dihapus.</div>
    <?php endif; ?>

    <form method="GET" action="index.php" class="search-box">
        <input type="text" name="cari" placeholder="Cari berdasarkan NIM, Nama Lengkap, atau Program Studi..." value="<?= htmlspecialchars($cari) ?>">
        <button type="submit" class="btn">Cari</button>
        <?php if ($cari !== ""): ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>NIM</th>
                <th>Nama Lengkap</th>
                <th>Program Studi</th>
                <th>Angkatan</th>
                <th>No. HP</th>
                <th>Email</th>
                <th style="width: 210px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row["NIM"]) ?></td>
                    <td><?= htmlspecialchars($row["Nama_Lengkap"]) ?></td>
                    <td><?= htmlspecialchars($row["Program_Studi"]) ?></td>
                    <td><?= htmlspecialchars($row["Angkatan"]) ?></td>
                    <td><?= htmlspecialchars($row["No_HP"]) ?></td>
                    <td><?= htmlspecialchars($row["Email"]) ?></td>
                    <td>
                        <div class="actions">
                            <a href="detail.php?nim=<?= urlencode($row["NIM"]) ?>" class="btn btn-secondary btn-sm">Detail</a>
                            <a href="edit.php?nim=<?= urlencode($row["NIM"]) ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus.php?nim=<?= urlencode($row["NIM"]) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data NIM <?= htmlspecialchars($row["NIM"]) ?>?')">Hapus</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="text-align: center;">Belum ada data mahasiswa di database.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>