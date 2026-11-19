<?php
require_once "koneksi.php";

$nim = isset($_GET["nim"]) ? trim($_GET["nim"]) : "";
if ($nim === "") {
    header("Location: index.php");
    exit;
}

mysqli_begin_transaction($conn);
try {
    // Hapus alamat_orang_tua berdasarkan ID_Orang_Tua milik NIM tersebut (jika ada)
    $st_ortu = mysqli_prepare($conn, "SELECT ID_Orang_Tua FROM orang_tua WHERE NIM = ?");
    mysqli_stmt_bind_param($st_ortu, "s", $nim);
    mysqli_stmt_execute($st_ortu);
    $res_ortu = mysqli_stmt_get_result($st_ortu);
    while ($row_ortu = mysqli_fetch_assoc($res_ortu)) {
        if (!empty($row_ortu["ID_Orang_Tua"])) {
            $id_ortu = (int)$row_ortu["ID_Orang_Tua"];
            $st_del_ao = mysqli_prepare($conn, "DELETE FROM alamat_orang_tua WHERE ID_Orang_Tua = ?");
            mysqli_stmt_bind_param($st_del_ao, "i", $id_ortu);
            mysqli_stmt_execute($st_del_ao);
        }
    }

    // Hapus data dari tabel-tabel relasi ber-NIM
    $child_tables = [
        "alamat_mahasiswa",
        "akademik",
        "pendidikan_asal",
        "pekerjaan",
        "ekonomi",
        "orang_tua",
        "wali",
    ];

    foreach ($child_tables as $tbl) {
        $st = mysqli_prepare($conn, "DELETE FROM `$tbl` WHERE NIM = ?");
        mysqli_stmt_bind_param($st, "s", $nim);
        mysqli_stmt_execute($st);
    }

    // Hapus data utama dari tabel mahasiswa
    $st_mhs = mysqli_prepare($conn, "DELETE FROM mahasiswa WHERE NIM = ?");
    mysqli_stmt_bind_param($st_mhs, "s", $nim);
    mysqli_stmt_execute($st_mhs);

    mysqli_commit($conn);
    header("Location: index.php?pesan=sukses_hapus");
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Gagal menghapus data: " . htmlspecialchars($e->getMessage()));
}
