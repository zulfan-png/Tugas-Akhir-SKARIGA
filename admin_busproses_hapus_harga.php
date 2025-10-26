<?php
include "koneksi.php";

$id = $_GET['id'];
$bus_id = $_GET['bus_id'];

$query = "DELETE FROM harga_bus WHERE id = $id";
$result = mysqli_query($connect, $query);

if ($result) {
    header("Location: admin_bus_harga.php?id=$bus_id");
} else {
    echo "<script>
    alert('GAGAL MENGHAPUS HARGA: " . mysqli_error($connect) . "');
    window.location.href = 'admin_bus_harga.php?id=$bus_id';
    </script>";
}
?>