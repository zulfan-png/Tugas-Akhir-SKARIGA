<?php
include "koneksi.php";

$id = $_GET['id'];

// Hapus dari database (akan otomatis hapus di bus_gambar dan harga_bus karena CASCADE)
$query_bus = "DELETE FROM bus WHERE id = $id";
$result = mysqli_query($connect, $query_bus);

if ($result) {
    echo "<script>
    alert('BERHASIL MENGHAPUS DATA');
    window.location.href = 'admin_bus.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENGHAPUS DATA: " . mysqli_error($connect) . "');
    window.location.href = 'admin_bus.php';
    </script>";
}
?>