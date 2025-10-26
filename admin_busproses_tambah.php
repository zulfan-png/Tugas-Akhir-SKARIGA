<?php
include "koneksi.php";

$perusahaan_id = $_POST['perusahaan_id'];
$tipe_bus = $_POST['tipe_bus'];
$jenis = $_POST['jenis'];
$kapasitas = $_POST['kapasitas'];
$status = $_POST['status'];
$deskripsi = $_POST['deskripsi'];

// Proses fasilitas dari checkbox
$fasilitas = "";
if(isset($_POST['fasilitas'])) {
    $fasilitas = implode(", ", $_POST['fasilitas']);
}

$query = "INSERT INTO bus (perusahaan_id, `tipe bus`, jenis, kapasitas, status, fasilitas, deskripsi) 
          VALUES ('$perusahaan_id', '$tipe_bus', '$jenis', '$kapasitas', '$status', '$fasilitas', '$deskripsi')";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENAMBAH DATA');
    window.location.href = 'admin_bus.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENAMBAH DATA: " . mysqli_error($connect) . "');
    window.location.href = 'admin_bus.php';
    </script>";
}
?>