<?php
include "koneksi.php";

$bus_id = $_POST['bus_id'];
$jenis_harga = $_POST['jenis_harga'];
$harga = $_POST['harga'];
$satuan = $_POST['satuan'];

$query = "INSERT INTO harga_bus (bus_id, jenis_harga, harga, satuan) 
          VALUES ('$bus_id', '$jenis_harga', '$harga', '$satuan')";
$result = mysqli_query($connect, $query);

if ($result) {
    header("Location: admin_bus_harga.php?id=$bus_id");
} else {
    echo "<script>
    alert('GAGAL MENAMBAH HARGA: " . mysqli_error($connect) . "');
    window.location.href = 'admin_bus_harga.php?id=$bus_id';
    </script>";
}
?>