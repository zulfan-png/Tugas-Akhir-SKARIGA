<?php
include "koneksi.php";

$id = $_GET['id'];

$query = "DELETE FROM datauser WHERE id = $id";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENGHAPUS DATA');
    window.location.href = 'admin_user.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENGHAPUS DATA');
    window.location.href = 'admin_user.php';
    </script>";
}
?>