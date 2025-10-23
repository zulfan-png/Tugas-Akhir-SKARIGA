<?php
include "koneksi.php";

$bus_id = $_POST['bus_id'];
$gambar_url = $_POST['gambar_url'];

$query = "INSERT INTO bus_gambar (bus_id, gambar_url) VALUES ('$bus_id', '$gambar_url')";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENAMBAH GAMBAR');
    window.location.href = 'admin_bus_gambar.php?id=$bus_id';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENAMBAH GAMBAR');
    window.location.href = 'admin_bus_gambar.php?id=$bus_id';
    </script>";
}
?>