<?php
include "koneksi.php";

$id = $_GET['id'];

// Ambil semua gambar bus yang akan dihapus
$query_gambar_list = "SELECT * FROM bus_gambar WHERE bus_id = $id";
$result_gambar_list = mysqli_query($connect, $query_gambar_list);

// Hapus file gambar dari server
while ($gambar = mysqli_fetch_array($result_gambar_list)) {
    $file_path = 'uploads/' . $gambar['gambar_url'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Hapus dari database (akan otomatis hapus di bus_gambar karena CASCADE)
$query_bus = "DELETE FROM bus WHERE id = $id";
$result = mysqli_query($connect, $query_bus);

if ($result) {
    echo "<script>
    alert('BERHASIL MENGHAPUS DATA');
    window.location.href = 'admin_bus.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENGHAPUS DATA');
    window.location.href = 'admin_bus.php';
    </script>";
}
?>